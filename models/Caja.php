<?php
require_once dirname(__DIR__) . '/config/Database.php';

class Caja {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function listClientsForPayment($concepto = 'mensualidad') {
        // Query to get all clients with their most recent membership status
        // Prioritize Active, then Pending, then Vencida.
        $sql = "
            SELECT c.id_cliente, c.nombre_completo, c.identidad, 
                   CASE WHEN DATALENGTH(c.fotografia) > 0 THEN 1 ELSE 0 END as tiene_foto,
                   m.id_membresia, m.estado_membresia as memb_estado, m.id_plan,
                   m.fecha_fin,
                   DATEDIFF(day, GETDATE(), m.fecha_fin) as dias_restantes
            FROM CLIENTES c
            LEFT JOIN (
                SELECT id_cliente, MAX(id_membresia) as max_id
                FROM MEMBRESIAS
                GROUP BY id_cliente
            ) latest ON c.id_cliente = latest.id_cliente
            LEFT JOIN MEMBRESIAS m ON latest.max_id = m.id_membresia
            WHERE c.estado = 1";
            
        if ($concepto === 'mensualidad') {
            $sql .= " AND c.email != 'consumidor_final@orion.com'";
        }
        
        $sql .= " ORDER BY CASE WHEN c.email = 'consumidor_final@orion.com' THEN 0 ELSE 1 END, c.nombre_completo ASC";

        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in listClientsForPayment: " . $e->getMessage());
            return [];
        }
    }

    public function getConsumidorFinalData() {
        $stmt = $this->db->query("SELECT id_cliente, nombre_completo AS nombre FROM CLIENTES WHERE email = 'consumidor_final@orion.com'");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function processMembershipPayment($idCliente, $idPlan, $cantidad, $idMembresia, $estadoMemb, $idUsuario, $metodoPago = 'Efectivo', $banco = null, $referencia = null) {
        try {
            $this->db->beginTransaction();

            // 1. Get Plan details
            $stmtPlan = $this->db->prepare("SELECT nombre_plan, precio, duracion_dias FROM PLANES WHERE id_plan = ?");
            $stmtPlan->execute([$idPlan]);
            $plan = $stmtPlan->fetch(PDO::FETCH_ASSOC);

            if (!$plan) {
                throw new Exception("El plan seleccionado no existe.");
            }

            $precioUnitario = (float)$plan['precio'];
            $montoTotal = $precioUnitario * $cantidad;
            $duracionTotalDias = (int)$plan['duracion_dias'] * $cantidad;

            $concepto = "Pago de Mensualidad: {$plan['nombre_plan']} (x{$cantidad})";
            $fechaHoy = date('Y-m-d');

            // 2. Logic for membership calculation
            $newMembresiaId = null;

            if ($estadoMemb === 'Pendiente' && $idMembresia) {
                // Activate the pending membership
                $sqlMemb = "UPDATE MEMBRESIAS SET 
                            id_plan = ?,
                            fecha_inicio = ?, 
                            fecha_fin = DATEADD(day, $duracionTotalDias, ?),
                            estado_membresia = 'Activa'
                            WHERE id_membresia = ?";
                $stmtMemb = $this->db->prepare($sqlMemb);
                $stmtMemb->execute([
                    $idPlan,
                    $fechaHoy, 
                    $fechaHoy,
                    $idMembresia
                ]);
                $newMembresiaId = $idMembresia;
            } 
            else if ($estadoMemb === 'Activa' && $idMembresia) {
                // Stack time on top of current active membership
                $sqlGetFin = "SELECT fecha_fin FROM MEMBRESIAS WHERE id_membresia = ?";
                $stmtGetFin = $this->db->prepare($sqlGetFin);
                $stmtGetFin->execute([$idMembresia]);
                $fechaFinAnterior = $stmtGetFin->fetchColumn();

                $sqlMemb = "UPDATE MEMBRESIAS SET 
                            id_plan = ?,
                            fecha_fin = DATEADD(day, $duracionTotalDias, ?)
                            WHERE id_membresia = ?";
                $stmtMemb = $this->db->prepare($sqlMemb);
                $stmtMemb->execute([
                    $idPlan,
                    $fechaFinAnterior,
                    $idMembresia
                ]);
                $newMembresiaId = $idMembresia;
            }
            else {
                // Assign completely new membership (if expired or none exists)
                $sqlMemb = "INSERT INTO MEMBRESIAS (id_cliente, id_plan, fecha_inicio, fecha_fin, estado_membresia)
                            VALUES (?, ?, ?, DATEADD(day, $duracionTotalDias, ?), 'Activa')";
                $stmtMemb = $this->db->prepare($sqlMemb);
                $stmtMemb->execute([
                    $idCliente,
                    $idPlan,
                    $fechaHoy,
                    $fechaHoy
                ]);
                
                // Get the ID of the new membership
                $stmtGetId = $this->db->query("SELECT IDENT_CURRENT('MEMBRESIAS')");
                $newMembresiaId = $stmtGetId->fetchColumn();
            }

            // 3. Insert into VENTAS
            $sqlVenta = "INSERT INTO VENTAS (id_cliente, fecha_venta, total, id_usuario, metodo_pago, banco, referencia) 
                         VALUES (?, GETDATE(), ?, ?, ?, ?, ?)";
            $stmtVenta = $this->db->prepare($sqlVenta);
            $stmtVenta->execute([$idCliente, $montoTotal, $idUsuario, $metodoPago, $banco, $referencia]);

            // Get the ID of the new sale
            $stmtGetIdVenta = $this->db->query("SELECT IDENT_CURRENT('VENTAS')");
            $idVenta = $stmtGetIdVenta->fetchColumn();

            // 4. Insert into CAJA_TRANSACCIONES
            $sqlCaja = "INSERT INTO CAJA_TRANSACCIONES (id_membresia, tipo_transaccion, concepto, monto, fecha_transaccion, id_usuario, metodo_pago, banco, referencia) 
                        VALUES (?, 'Ingreso', ?, ?, GETDATE(), ?, ?, ?, ?)";
            $stmtCaja = $this->db->prepare($sqlCaja);
            $stmtCaja->execute([$newMembresiaId, $concepto, $montoTotal, $idUsuario, $metodoPago, $banco, $referencia]);

            $this->db->commit();
            return ['success' => true];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error processing payment: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error BD: ' . $e->getMessage()];
        }
    }
    public function processEgreso($concepto, $monto, $idUsuario, $metodoPago = 'Efectivo', $banco = null, $referencia = null) {
        try {
            $sql = "INSERT INTO CAJA_TRANSACCIONES (tipo_transaccion, concepto, monto, fecha_transaccion, id_usuario, metodo_pago, banco, referencia) 
                    VALUES ('Egreso', ?, ?, GETDATE(), ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$concepto, $monto, $idUsuario, $metodoPago, $banco, $referencia]);
            return ['success' => true];
        } catch (Exception $e) {
            error_log("Error processing egreso: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error BD: ' . $e->getMessage()];
        }
    }

    public function processProductSale($cart, $idUsuario, $metodoPago, $banco, $referencia) {
        try {
            $this->db->beginTransaction();

            $totalVenta = 0;
            $itemsNombres = [];

            foreach ($cart as $item) {
                $idProducto = $item['id'];
                $qty = (int)$item['qty'];
                
                if ($qty <= 0) continue;

                $stmtP = $this->db->prepare("SELECT nombre_producto, precio_venta, stock FROM PRODUCTOS WHERE id_producto = ?");
                $stmtP->execute([$idProducto]);
                $producto = $stmtP->fetch(PDO::FETCH_ASSOC);

                if (!$producto) throw new Exception("Producto ID $idProducto no existe.");
                if ($producto['stock'] < $qty) throw new Exception("Stock insuficiente para: " . $producto['nombre_producto']);

                $subtotal = $producto['precio_venta'] * $qty;
                $totalVenta += $subtotal;
                $itemsNombres[] = $qty . "x " . $producto['nombre_producto'];

                $stmtUpd = $this->db->prepare("UPDATE PRODUCTOS SET stock = stock - ? WHERE id_producto = ?");
                $stmtUpd->execute([$qty, $idProducto]);

                $sqlK = "INSERT INTO KARDEX_INVENTARIO (id_producto, tipo_movimiento, cantidad, precio, descripcion, id_usuario)
                         VALUES (?, 'VENTA', ?, ?, 'Venta en mostrador', ?)";
                $stmtK = $this->db->prepare($sqlK);
                $stmtK->execute([$idProducto, $qty, $producto['precio_venta'], $idUsuario]);
            }

            if ($totalVenta <= 0) throw new Exception("El monto total es 0.");

            $conceptoStr = "Venta: " . implode(', ', $itemsNombres);
            if (strlen($conceptoStr) > 250) {
                $conceptoStr = substr($conceptoStr, 0, 247) . "...";
            }

            $sqlVenta = "INSERT INTO VENTAS (fecha_venta, total, id_usuario, metodo_pago, banco, referencia) 
                         VALUES (GETDATE(), ?, ?, ?, ?, ?)";
            $stmtVenta = $this->db->prepare($sqlVenta);
            $stmtVenta->execute([$totalVenta, $idUsuario, $metodoPago, $banco, $referencia]);

            $sqlCaja = "INSERT INTO CAJA_TRANSACCIONES (tipo_transaccion, concepto, monto, fecha_transaccion, id_usuario, metodo_pago, banco, referencia) 
                        VALUES ('Ingreso', ?, ?, GETDATE(), ?, ?, ?, ?)";
            $stmtCaja = $this->db->prepare($sqlCaja);
            $stmtCaja->execute([$conceptoStr, $totalVenta, $idUsuario, $metodoPago, $banco, $referencia]);

            $this->db->commit();
            return ['success' => true];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error processing product sale: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function processOtroIngreso($concepto, $monto, $idUsuario, $metodoPago, $banco, $referencia) {
        try {
            $this->db->beginTransaction();

            $conceptoFinal = "Otros Ingresos: " . $concepto;
            if (strlen($conceptoFinal) > 250) {
                $conceptoFinal = substr($conceptoFinal, 0, 247) . "...";
            }

            // Also register in VENTAS to keep income unified
            $sqlVenta = "INSERT INTO VENTAS (fecha_venta, total, id_usuario, metodo_pago, banco, referencia) 
                         VALUES (GETDATE(), ?, ?, ?, ?, ?)";
            $stmtVenta = $this->db->prepare($sqlVenta);
            $stmtVenta->execute([$monto, $idUsuario, $metodoPago, $banco, $referencia]);

            // Register in CAJA_TRANSACCIONES
            $sqlCaja = "INSERT INTO CAJA_TRANSACCIONES (tipo_transaccion, concepto, monto, fecha_transaccion, id_usuario, metodo_pago, banco, referencia) 
                        VALUES ('Ingreso', ?, ?, GETDATE(), ?, ?, ?, ?)";
            $stmtCaja = $this->db->prepare($sqlCaja);
            $stmtCaja->execute([$conceptoFinal, $monto, $idUsuario, $metodoPago, $banco, $referencia]);

            $this->db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error processing otro ingreso: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error BD: ' . $e->getMessage()];
        }
    }
    
    public function getHistorialHoy() {
        $sql = "
            SELECT t.id_transaccion, t.tipo_transaccion, t.concepto, t.monto, t.fecha_transaccion, 
                   t.metodo_pago, t.banco, t.referencia, u.nombre_usuario, c.nombre_completo as nombre_cliente
            FROM CAJA_TRANSACCIONES t
            LEFT JOIN USUARIOS u ON t.id_usuario = u.id_usuario
            LEFT JOIN MEMBRESIAS m ON t.id_membresia = m.id_membresia
            LEFT JOIN CLIENTES c ON m.id_cliente = c.id_cliente
            WHERE CAST(t.fecha_transaccion AS DATE) = CAST(GETDATE() AS DATE)
            ORDER BY t.fecha_transaccion DESC
        ";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getHistorialHoy: " . $e->getMessage());
            return [];
        }
    }
}

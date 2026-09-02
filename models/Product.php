<?php
require_once dirname(__DIR__) . '/config/Database.php';

class Product {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function listAll($activeOnly = false) {
        $sql = "SELECT * FROM PRODUCTOS";
        if ($activeOnly) {
            $sql .= " WHERE estado = 1";
        }
        $sql .= " ORDER BY nombre_producto ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO PRODUCTOS (codigo_barras, nombre_producto, descripcion, precio_compra, precio_venta, stock, estado) 
                VALUES (?, ?, ?, ?, ?, ?, 1)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['codigo_barras'],
            $data['nombre_producto'],
            $data['descripcion'],
            $data['precio_compra'],
            $data['precio_venta'],
            $data['stock']
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE PRODUCTOS SET 
                codigo_barras = ?, 
                nombre_producto = ?, 
                descripcion = ?, 
                precio_compra = ?, 
                precio_venta = ?, 
                stock = ? 
                WHERE id_producto = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['codigo_barras'],
            $data['nombre_producto'],
            $data['descripcion'],
            $data['precio_compra'],
            $data['precio_venta'],
            $data['stock'],
            $id
        ]);
    }

    public function changeState($id, $state) {
        $sql = "UPDATE PRODUCTOS SET estado = ? WHERE id_producto = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$state, $id]);
    }

    public function adjustStock($id, $tipo, $cantidad, $precio, $descripcion, $idUsuario) {
        try {
            $this->db->beginTransaction();

            // Insert into KARDEX_INVENTARIO
            $sqlK = "INSERT INTO KARDEX_INVENTARIO (id_producto, tipo_movimiento, cantidad, precio, descripcion, id_usuario)
                     VALUES (?, ?, ?, ?, ?, ?)";
            $stmtK = $this->db->prepare($sqlK);
            $stmtK->execute([$id, $tipo, $cantidad, $precio, $descripcion, $idUsuario]);

            // Update PRODUCTOS stock
            if ($tipo === 'ENTRADA') {
                $sqlP = "UPDATE PRODUCTOS SET stock = stock + ? WHERE id_producto = ?";
            } else {
                // SALIDA or VENTA
                $sqlP = "UPDATE PRODUCTOS SET stock = stock - ? WHERE id_producto = ?";
            }
            $stmtP = $this->db->prepare($sqlP);
            $stmtP->execute([$cantidad, $id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getKardex($id) {
        $sql = "SELECT k.*, u.nombre_usuario 
                FROM KARDEX_INVENTARIO k
                LEFT JOIN USUARIOS u ON k.id_usuario = u.id_usuario
                WHERE k.id_producto = ?
                ORDER BY k.fecha_movimiento DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

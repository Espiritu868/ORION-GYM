<?php
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/models/Product.php';

class ProductController {
    
    public function list() {
        if (!hasPerm('administracion_view')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $productModel = new Product();
        $activeOnly = isset($_GET['active']) && $_GET['active'] === 'true';
        $products = $productModel->listAll($activeOnly);
        echo json_encode(['success' => true, 'data' => $products]);
    }

    public function save() {
        $id = $_POST['id_producto'] ?? '';
        
        if (empty($id) && !hasPerm('administracion_create')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        if (!empty($id) && !hasPerm('administracion_edit')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $data = [
            'codigo_barras' => trim($_POST['codigo_barras'] ?? ''),
            'nombre_producto' => trim($_POST['nombre_producto'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'precio_compra' => floatval(str_replace(',', '', $_POST['precio_compra'] ?? '0')),
            'precio_venta' => floatval(str_replace(',', '', $_POST['precio_venta'] ?? '0')),
            'stock' => intval($_POST['stock'] ?? 0)
        ];

        if (empty($data['nombre_producto']) || $data['precio_venta'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos. Verifica el nombre y precio de venta.']);
            return;
        }

        $productModel = new Product();

        if (empty($id)) {
            $success = $productModel->create($data);
            $msg = 'Producto creado correctamente.';
        } else {
            $success = $productModel->update($id, $data);
            $msg = 'Producto actualizado correctamente.';
        }

        echo json_encode(['success' => $success, 'message' => $success ? $msg : 'Error al guardar el producto.']);
    }

    public function toggle() {
        if (!hasPerm('administracion_delete')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        $id = $_POST['id_producto'] ?? '';
        $state = intval($_POST['estado'] ?? 0);
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $productModel = new Product();
        $success = $productModel->changeState($id, $state);
        $msg = $state === 1 ? 'Producto activado.' : 'Producto desactivado.';
        
        echo json_encode(['success' => $success, 'message' => $success ? $msg : 'Error al cambiar estado.']);
    }

    public function adjust() {
        if (!hasPerm('administracion_edit')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $id = $_POST['id_producto'] ?? '';
        $tipo = $_POST['tipo_movimiento'] ?? ''; // ENTRADA, SALIDA
        $cantidad = intval($_POST['cantidad'] ?? 0);
        $precio = floatval(str_replace(',', '', $_POST['precio'] ?? '0'));
        $descripcion = trim($_POST['descripcion'] ?? '');
        $idUsuario = $_SESSION['user_id'] ?? null;

        if (empty($id) || empty($tipo) || $cantidad <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos de ajuste inválidos.']);
            return;
        }

        $productModel = new Product();
        $success = $productModel->adjustStock($id, $tipo, $cantidad, $precio, $descripcion, $idUsuario);

        echo json_encode([
            'success' => $success, 
            'message' => $success ? 'Ajuste realizado correctamente.' : 'Error al realizar el ajuste.'
        ]);
    }

    public function kardex() {
        if (!hasPerm('administracion_view')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $id = $_GET['id_producto'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $productModel = new Product();
        $kardex = $productModel->getKardex($id);
        
        echo json_encode(['success' => true, 'data' => $kardex]);
    }
}

// Router
$action = $_GET['action'] ?? '';
$controller = new ProductController();
if (method_exists($controller, $action)) {
    $controller->$action();
}
?>

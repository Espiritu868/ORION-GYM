<?php
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/models/Client.php';

class ClientController {
    
    public function list() {
        if (!hasPerm('clientes_view')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado para ver clientes']);
            return;
        }

        $clientModel = new Client();
        $stateFilter = $_GET['active'] ?? '1';
        if ($stateFilter === 'true') $stateFilter = '1';
        if ($stateFilter === 'false') $stateFilter = 'all';

        $clients = $clientModel->listAll($stateFilter);
        echo json_encode(['success' => true, 'data' => $clients]);
    }

    public function save() {
        $id = $_POST['id_cliente'] ?? '';
        
        if (empty($id) && !hasPerm('clientes_create')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para crear clientes']);
            return;
        }
        if (!empty($id) && !hasPerm('clientes_edit')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar clientes']);
            return;
        }

        $clientModel = new Client();
        
        $data = [
            'identidad' => $_POST['identidad'] ?? '',
            'nombre_completo' => $_POST['nombre_completo'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'email' => $_POST['email'] ?? '',
            'direccion' => $_POST['direccion'] ?? '',
            'fotografia' => $_POST['fotografia'] ?? '' 
        ];

        if (empty($data['nombre_completo'])) {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio.']);
            return;
        }

        if (empty($data['identidad'])) {
            echo json_encode(['success' => false, 'message' => 'La identidad es obligatoria.']);
            return;
        }

        if (empty($data['telefono'])) {
            echo json_encode(['success' => false, 'message' => 'El teléfono es obligatorio.']);
            return;
        }

        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $data['identidad'])) {
            echo json_encode(['success' => false, 'message' => 'La identidad contiene caracteres no válidos.']);
            return;
        }

        if (!preg_match('/^\+?[0-9\-\s]+$/', $data['telefono'])) {
            echo json_encode(['success' => false, 'message' => 'El teléfono contiene caracteres no válidos.']);
            return;
        }

        if (empty($id)) {
            $success = $clientModel->create($data);
            $msg = 'Cliente registrado correctamente.';
        } else {
            $success = $clientModel->update($id, $data);
            $msg = 'Cliente actualizado correctamente.';
        }

        echo json_encode(['success' => $success, 'message' => $success ? $msg : 'Error al guardar el cliente.']);
    }

    public function deactivate() {
        if (!hasPerm('clientes_delete')) return;
        $id = $_POST['id_cliente'] ?? '';
        $clientModel = new Client();

        if ($clientModel->hasActiveMembership($id)) {
            echo json_encode(['success' => false, 'message' => 'No se puede dar de baja al cliente porque tiene una membresía activa o pendiente.']);
            return;
        }

        $success = $clientModel->changeState($id, 0);
        echo json_encode(['success' => $success, 'message' => $success ? 'Cliente desactivado (baja lógica).' : 'Error al desactivar.']);
    }

    public function activate() {
        if (!hasPerm('clientes_delete')) return;
        $id = $_POST['id_cliente'] ?? '';
        $clientModel = new Client();
        $success = $clientModel->changeState($id, 1);
        echo json_encode(['success' => $success, 'message' => $success ? 'Cliente reactivado.' : 'Error al reactivar.']);
    }

    public function cleanup() {
        if (!hasPerm('clientes_delete')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado para limpieza.']);
            return;
        }

        $meses = intval($_POST['meses'] ?? 3);
        if ($meses < 1) {
            echo json_encode(['success' => false, 'message' => 'Cantidad de meses inválida.']);
            return;
        }

        $clientModel = new Client();
        // Here we need to run a query to update state of clients
        // that don't have any membership with fecha_fin > (Today - X months).
        
        $database = new Database();
        $db = $database->getConnection();
        
        $sql = "UPDATE CLIENTES SET estado = 0 
                WHERE estado = 1 
                AND id_cliente NOT IN (
                    SELECT id_cliente FROM MEMBRESIAS 
                    WHERE fecha_fin >= DATEADD(month, -?, GETDATE())
                )";
                
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$meses]);
            $count = $stmt->rowCount();
            echo json_encode(['success' => true, 'message' => "Limpieza completada. $count cliente(s) inactivo(s) desactivado(s)."]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function photo() {
        $id = intval($_GET['id_cliente'] ?? 0);
        $thumb = isset($_GET['thumb']) && $_GET['thumb'] == 'true';
        if (!$id) {
            header('HTTP/1.1 404 Not Found');
            exit;
        }

        $cacheDir = dirname(__DIR__) . '/uploads/thumbs';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }

        $cacheFile = $cacheDir . '/' . $id . ($thumb ? '_thumb' : '_full') . '.jpg';

        if (file_exists($cacheFile)) {
            header('Cache-Control: max-age=604800, public');
            header('Content-Type: image/jpeg');
            readfile($cacheFile);
            exit;
        }

        $clientModel = new Client();
        $photo = $clientModel->getPhoto($id);
        
        if ($photo) {
            if (preg_match('/^data:image\/(\w+);base64,/', $photo, $matches)) {
                $type = $matches[1];
                $data = substr($photo, strpos($photo, ',') + 1);
                $data = str_replace(' ', '+', $data); // Fix spaces to pluses
                $data = base64_decode($data);

                // Increase memory limit for large images
                ini_set('memory_limit', '512M');
                
                $im = false;
                if (extension_loaded('gd')) {
                    $im = @imagecreatefromstring($data);
                }
                if ($im !== false) {
                    if ($thumb) {
                        $width = imagesx($im);
                        $height = imagesy($im);
                        $newWidth = 100;
                        $newHeight = floor($height * ($newWidth / $width));
                        $finalImg = imagecreatetruecolor($newWidth, $newHeight);
                        // white background
                        $bg = imagecolorallocate($finalImg, 255, 255, 255);
                        imagefill($finalImg, 0, 0, $bg);
                        imagecopyresampled($finalImg, $im, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    } else {
                        $finalImg = $im;
                    }

                    header('Cache-Control: max-age=604800, public');
                    header('Content-Type: image/jpeg');
                    // Cache to disk
                    imagejpeg($finalImg, $cacheFile, 80);
                    // Output
                    readfile($cacheFile);

                    if ($thumb) imagedestroy($finalImg);
                    if ($im !== $finalImg) imagedestroy($im);
                    exit;
                } else {
                    // Fallback if GD fails (e.g. extension not loaded or memory limit exceeded)
                    // We can at least save the raw decoded data to the cache file so next time it's fast!
                    file_put_contents($cacheFile, $data);
                    header('Cache-Control: max-age=604800, public');
                    header("Content-Type: image/$type");
                    echo $data;
                    exit;
                }
            }
        }

        // If no photo, output 404
        header('HTTP/1.1 404 Not Found');
        exit;
    }

    public function checkIdentity() {
        $identidad = trim($_POST['identidad'] ?? '');
        $excludeId = $_POST['exclude_id'] ?? null;
        
        if (empty($identidad)) {
            echo json_encode(['exists' => false]);
            return;
        }

        $clientModel = new Client();
        $result = $clientModel->checkIdentity($identidad, $excludeId);

        if ($result) {
            echo json_encode([
                'exists' => true, 
                'nombre' => $result['nombre_completo'], 
                'estado' => $result['estado'] == 1 ? 'Activo' : 'Inactivo'
            ]);
        } else {
            echo json_encode(['exists' => false]);
        }
    }
}

// Router
$action = $_GET['action'] ?? '';
$controller = new ClientController();
if (method_exists($controller, $action)) {
    $controller->$action();
}
?>

<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';

class PhotoController {
    public function upload() {
        $token = $_POST['token'] ?? '';
        $base64Image = $_POST['foto'] ?? '';

        if (empty($token) || empty($base64Image)) {
            echo json_encode(['success' => false, 'message' => 'Token o imagen no proporcionados.']);
            return;
        }

        $db = (new Database())->getConnection();
        
        // Remove old entries if they exist for this token
        $stmtDel = $db->prepare("DELETE FROM TEMP_PHOTOS WHERE token = ?");
        $stmtDel->execute([$token]);

        $stmtIns = $db->prepare("INSERT INTO TEMP_PHOTOS (token, foto) VALUES (?, ?)");
        $success = $stmtIns->execute([$token, $base64Image]);

        echo json_encode(['success' => $success]);
    }

    public function check() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            echo json_encode(['success' => false]);
            return;
        }

        $db = (new Database())->getConnection();
        $stmt = $db->prepare("SELECT foto FROM TEMP_PHOTOS WHERE token = ?");
        $stmt->execute([$token]);
        $res = $stmt->fetch();

        if ($res && !empty($res['foto'])) {
            $stmtDel = $db->prepare("DELETE FROM TEMP_PHOTOS WHERE token = ?");
            $stmtDel->execute([$token]);
            echo json_encode(['success' => true, 'foto' => $res['foto']]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
}

// Router
$action = $_GET['action'] ?? '';
$controller = new PhotoController();
if (method_exists($controller, $action)) {
    $controller->$action();
}
?>

<?php
session_start();
require_once dirname(__DIR__) . '/models/User.php';

class AuthController {
    
    public function index() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'index.php?controller=dashboard&action=index');
            exit;
        }
        require_once dirname(__DIR__) . '/views/login.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            if (empty($username) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Por favor, ingrese usuario y contraseña.']);
                exit;
            }

            $rtn = CLIENT_RTN;

            // Validate license against CAESOLUTION API
            $apiUrl = API_LICENSE_URL;
            
            // Allow self-signed certs for local development if needed
            $context = stream_context_create([
                "ssl" => [
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ],
            ]);
            
            $apiResponse = @file_get_contents($apiUrl, false, $context);
            if ($apiResponse !== false) {
                // Remove UTF-8 BOM or any invisible characters before JSON begins
                $jsonStart = strpos($apiResponse, '{');
                if ($jsonStart !== false) {
                    $apiResponse = substr($apiResponse, $jsonStart);
                }
                
                $licenseData = json_decode($apiResponse, true);
                if (!$licenseData || $licenseData['access'] !== true) {
                    echo json_encode(['success' => false, 'message' => $licenseData['message'] ?? 'Error de validación de licencia.']);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo conectar al servidor de licencias.']);
                exit;
            }

            // Authenticate user
            $userModel = new User();
            $user = $userModel->authenticate($username, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['username'] = $user['nombre_usuario'];
                $_SESSION['permissions'] = $user['permisos'];
                $_SESSION['rtn'] = $rtn;
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Autenticación exitosa.'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos.']);
            }
        }
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . 'index.php?controller=auth&action=index');
        exit;
    }
}
?>

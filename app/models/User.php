<?php
require_once dirname(__DIR__) . '/config/Database.php';

class User {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public static function hashSHA256($password) {
        return hash('sha256', $password);
    }

    public function authenticate($username, $password) {
        $hash = self::hashSHA256($password);
        $sql = "SELECT u.* FROM USUARIOS u WHERE LOWER(u.nombre_usuario) = LOWER(?) AND u.password_hash = ? AND u.estado_usuario = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $hash]);
        $user = $stmt->fetch();

        if ($user) {
            $perms = $this->getPermissions($user['id_usuario']);
            $user['permisos'] = $perms;
        }
        return $user;
    }

    public function getPermissions($userId) {
        $sql = "SELECT codigo_permiso FROM PERMISOS_USUARIO WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>

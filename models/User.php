<?php
require_once dirname(__DIR__) . '/config/Database.php';

class User {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // BCrypt is automatically salted and much more secure
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function authenticate($username, $password) {
        $sql = "SELECT u.* FROM USUARIOS u WHERE LOWER(u.nombre_usuario) = LOWER(?) AND u.estado_usuario = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            $valid = false;
            $needsRehash = false;

            // Seamless upgrade: Check if the password is an old SHA-256 hash (64 chars hex)
            if (strlen($user['password_hash']) === 64 && ctype_xdigit($user['password_hash'])) {
                if (hash('sha256', $password) === $user['password_hash']) {
                    $valid = true;
                    $needsRehash = true;
                }
            } else {
                // Verify with BCrypt
                if (password_verify($password, $user['password_hash'])) {
                    $valid = true;
                    if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT)) {
                        $needsRehash = true;
                    }
                }
            }

            if ($valid) {
                if ($needsRehash) {
                    $newHash = self::hashPassword($password);
                    $stmtUpdate = $this->db->prepare("UPDATE USUARIOS SET password_hash = ? WHERE id_usuario = ?");
                    $stmtUpdate->execute([$newHash, $user['id_usuario']]);
                }

                $user['permisos'] = $this->getPermissions($user['id_usuario']);
                return $user;
            }
        }
        return false;
    }

    public function getPermissions($userId) {
        $sql = "SELECT codigo_permiso FROM PERMISOS_USUARIO WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function listAll($stateFilter = 'all') {
        $sql = "SELECT id_usuario, nombre_usuario, estado_usuario FROM USUARIOS";
        if ($stateFilter === '1' || $stateFilter === '0') {
            $sql .= " WHERE estado_usuario = " . intval($stateFilter);
        }
        $sql .= " ORDER BY id_usuario ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch permissions for each user
        foreach ($users as &$user) {
            $user['permisos'] = $this->getPermissions($user['id_usuario']);
        }

        return $users;
    }

    public function create($username, $password, $permissions = []) {
        try {
            $this->db->beginTransaction();

            $hash = self::hashPassword($password);
            $stmt = $this->db->prepare("INSERT INTO USUARIOS (nombre_usuario, password_hash, estado_usuario) VALUES (?, ?, 1)");
            $stmt->execute([$username, $hash]);
            $userId = $this->db->lastInsertId();

            $this->updatePermissions($userId, $permissions);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return $e->getMessage();
        }
    }

    public function update($userId, $username, $password = null, $permissions = []) {
        try {
            $this->db->beginTransaction();

            if (!empty($password)) {
                $hash = self::hashPassword($password);
                $stmt = $this->db->prepare("UPDATE USUARIOS SET nombre_usuario = ?, password_hash = ? WHERE id_usuario = ?");
                $stmt->execute([$username, $hash, $userId]);
            } else {
                $stmt = $this->db->prepare("UPDATE USUARIOS SET nombre_usuario = ? WHERE id_usuario = ?");
                $stmt->execute([$username, $userId]);
            }

            $this->updatePermissions($userId, $permissions);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return $e->getMessage();
        }
    }

    private function updatePermissions($userId, $permissions) {
        // Delete all old permissions
        $stmt = $this->db->prepare("DELETE FROM PERMISOS_USUARIO WHERE id_usuario = ?");
        $stmt->execute([$userId]);

        // Insert new ones
        if (!empty($permissions)) {
            $stmtInsert = $this->db->prepare("INSERT INTO PERMISOS_USUARIO (id_usuario, codigo_permiso) VALUES (?, ?)");
            foreach ($permissions as $perm) {
                $stmtInsert->execute([$userId, $perm]);
            }
        }
    }

    public function changeState($userId, $state) {
        $stmt = $this->db->prepare("UPDATE USUARIOS SET estado_usuario = ? WHERE id_usuario = ?");
        return $stmt->execute([$state, $userId]);
    }
}
?>

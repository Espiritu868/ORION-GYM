<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'config/config.php';
require_once 'config/Database.php';

$page = $_GET['page'] ?? 'dashboard';
?>
<?php include 'includes/header.php'; ?>
<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="col-md-10 flex-grow-1">
        <div class="topbar">
            <h5 class="mb-0">
                <?php 
                    echo match($page) {
                        'dashboard' => 'Dashboard',
                        'clientes' => 'Gestión de Miembros',
                        'membresias' => 'Membresías',
                        'caja' => 'Control de Caja',
                        'usuarios' => 'Gestión de Usuarios',
                        default => 'Módulo'
                    };
                ?>
            </h5>
        </div>
        
        <div class="content">
            <?php 
            $allowed_pages = ['dashboard', 'clientes', 'membresias', 'caja', 'usuarios', 'administracion'];
            if (in_array($page, $allowed_pages)) {
                $access = true;
                if ($page == 'dashboard' && !hasPerm('dashboard_view')) $access = false;
                if ($page == 'clientes' && !hasPerm('clientes_view')) $access = false;
                if ($page == 'membresias' && !hasPerm('membresias_view')) $access = false;
                if ($page == 'caja' && !hasPerm('caja_view')) $access = false;
                if ($page == 'usuarios' && !hasPerm('usuarios_view')) $access = false;
                if ($page == 'administracion' && !hasPerm('administracion_view')) $access = false;

                if ($access) {
                    if (file_exists("pages/{$page}.php")) {
                        include "pages/{$page}.php";
                    } else {
                        echo "<h3>Módulo en construcción</h3>";
                    }
                } else {
                    echo "<div class='alert alert-danger'><i class='fas fa-lock'></i> No tienes permiso para acceder a este módulo.</div>";
                }
            } else {
                echo "<h3>Página no encontrada</h3>";
            }
            ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>

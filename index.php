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
    <div id="main-content">
        <div class="topbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-light me-3 d-none d-md-block" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <button class="btn btn-light me-3 d-md-none" id="sidebarToggleMobile">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="mb-0 fw-bold">
                    <?php 
                        echo match($page) {
                            'dashboard' => 'Dashboard',
                            'clientes' => 'Gestión de Miembros',
                            'membresias' => 'Membresías',
                            'caja' => 'Control de Caja',
                            'usuarios' => 'Gestión de Usuarios',
                            'administracion' => 'Configuración',
                            'progreso_cliente' => 'Progreso del Cliente',
                            'rutinas_cliente' => 'Rutinas del Cliente',
                            default => 'Módulo'
                        };
                    ?>
                </h5>
            </div>
            <div>
                <!-- Opcional: Info de usuario o notificaciones aquí -->
            </div>
        </div>
        
        <div class="content">
            <?php 
            $allowed_pages = ['dashboard', 'clientes', 'membresias', 'caja', 'usuarios', 'administracion', 'progreso_cliente', 'rutinas_cliente'];
            if (in_array($page, $allowed_pages)) {
                $access = true;
                if ($page == 'dashboard' && !hasPerm('dashboard_view')) $access = false;
                if ($page == 'clientes' && !hasPerm('clientes_view')) $access = false;
                if ($page == 'membresias' && !hasPerm('membresias_view')) $access = false;
                if ($page == 'caja' && !hasPerm('caja_view')) $access = false;
                if ($page == 'usuarios' && !hasPerm('usuarios_view')) $access = false;
                if ($page == 'administracion' && !hasPerm('administracion_view')) $access = false;
                // Allow coaches/admins with 'clientes_view' to see progress and routines
                if (($page == 'progreso_cliente' || $page == 'rutinas_cliente') && !hasPerm('clientes_view')) $access = false;

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

<script>
    function formatCurrencyInput(input) {
        // Remove all non-numeric characters except for a single decimal point
        let val = input.value.replace(/[^0-9.]/g, '');
        
        // Ensure only one decimal point exists
        const parts = val.split('.');
        if (parts.length > 2) {
            val = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Format integer part with commas
        if (parts[0].length > 0) {
            parts[0] = parseInt(parts[0], 10).toLocaleString('en-US');
            val = parts.join('.');
        }
        
        input.value = val;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const toggleBtn = document.getElementById('sidebarToggle');
        const toggleMobileBtn = document.getElementById('sidebarToggleMobile');

        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            document.body.classList.toggle('sidebar-collapsed');
        }

        function toggleMobileSidebar() {
            sidebar.classList.toggle('show');
        }

        if(toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
        if(toggleMobileBtn) toggleMobileBtn.addEventListener('click', toggleMobileSidebar);
    });
</script>

<?php include 'includes/footer.php'; ?>

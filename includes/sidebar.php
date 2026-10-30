<div class="sidebar d-none d-md-flex flex-column" id="sidebar">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 text-white fw-bold" style="font-family: 'Outfit';"><?= SYSTEM_NAME ?></h4>
        <button class="btn btn-link text-white p-0 text-decoration-none" id="sidebarToggle">
            <i class="fas fa-bars fs-5"></i>
        </button>
    </div>
    
    <?php if(hasPerm('dashboard_view')): ?>
        <a href="index.php?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>" title="Dashboard"><i class="fas fa-tachometer-alt me-2"></i> <span class="sidebar-text">Dashboard</span></a>
    <?php endif; ?>
    
    <?php if(hasPerm('clientes_view')): ?>
        <a href="index.php?page=clientes" class="<?= $page == 'clientes' ? 'active' : '' ?>" title="Miembros"><i class="fas fa-users me-2"></i> <span class="sidebar-text">Miembros</span></a>
    <?php endif; ?>
    
    <?php if(hasPerm('asistencia_view')): ?>
        <a href="index.php?page=asistencia" class="<?= $page == 'asistencia' ? 'active' : '' ?>" title="Check-In Acceso"><i class="fas fa-id-badge me-2"></i> <span class="sidebar-text">Check-In Acceso</span></a>
    <?php endif; ?>
    
    <?php if(hasPerm('membresias_view')): ?>
        <a href="index.php?page=membresias" class="<?= $page == 'membresias' ? 'active' : '' ?>" title="Membresías"><i class="fas fa-id-card me-2"></i> <span class="sidebar-text">Membresías</span></a>
    <?php endif; ?>
    
    <?php if(hasPerm('caja_view')): ?>
        <a href="index.php?page=caja" class="<?= $page == 'caja' ? 'active' : '' ?>" title="Caja"><i class="fas fa-cash-register me-2"></i> <span class="sidebar-text">Caja</span></a>
    <?php endif; ?>
    
    <?php if(hasPerm('administracion_view')): ?>
        <a href="index.php?page=administracion" class="<?= $page == 'administracion' ? 'active' : '' ?>" title="Configuración"><i class="fas fa-cogs me-2"></i> <span class="sidebar-text">Configuración</span></a>
    <?php endif; ?>
    
    <?php if(hasPerm('reportes_view')): ?>
        <a href="index.php?page=reportes" class="<?= $page == 'reportes' ? 'active' : '' ?>" title="Reportes"><i class="fas fa-chart-line me-2"></i> <span class="sidebar-text">Reportes</span></a>
    <?php endif; ?>
    
    <?php if(hasPerm('usuarios_view')): ?>
        <a href="index.php?page=usuarios" class="<?= $page == 'usuarios' ? 'active' : '' ?>" title="Usuarios"><i class="fas fa-user-shield me-2"></i> <span class="sidebar-text">Usuarios</span></a>
    <?php endif; ?>
    
    <div class="mt-auto">
        <hr class="border-secondary">
        <a href="controllers/AuthController.php?action=logout" class="text-danger text-decoration-none justify-content-center" style="background: rgba(220, 53, 69, 0.1);" title="Cerrar Sesión">
            <i class="fas fa-sign-out-alt"></i> <span class="sidebar-text ms-2">Cerrar Sesión</span>
        </a>
    </div>
</div>

<div class="sidebar d-flex flex-column" id="sidebar">
    <h4 class="text-center mb-4 text-white font-weight-bold" style="font-family: 'Outfit';"><?= SYSTEM_NAME ?></h4>
    
    <?php if(hasPerm('dashboard_view')): ?>
        <a href="index.php?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
    <?php endif; ?>
    
    <?php if(hasPerm('clientes_view')): ?>
        <a href="index.php?page=clientes" class="<?= $page == 'clientes' ? 'active' : '' ?>"><i class="fas fa-users me-2"></i> Miembros</a>
    <?php endif; ?>
    
    <?php if(hasPerm('membresias_view')): ?>
        <a href="index.php?page=membresias" class="<?= $page == 'membresias' ? 'active' : '' ?>"><i class="fas fa-id-card me-2"></i> Membresías</a>
    <?php endif; ?>
    
    <?php if(hasPerm('caja_view')): ?>
        <a href="index.php?page=caja" class="<?= $page == 'caja' ? 'active' : '' ?>"><i class="fas fa-cash-register me-2"></i> Caja</a>
    <?php endif; ?>
    
    <?php if(hasPerm('administracion_view')): ?>
        <a href="index.php?page=administracion" class="<?= $page == 'administracion' ? 'active' : '' ?>"><i class="fas fa-cogs me-2"></i> Configuración</a>
    <?php endif; ?>
    
    <?php if(hasPerm('usuarios_view')): ?>
        <a href="index.php?page=usuarios" class="<?= $page == 'usuarios' ? 'active' : '' ?>"><i class="fas fa-user-shield me-2"></i> Usuarios</a>
    <?php endif; ?>
    
    <div class="mt-auto">
        <hr class="border-secondary">
        <a href="controllers/AuthController.php?action=logout" class="text-danger text-decoration-none d-block py-2 rounded text-center" style="background: rgba(220, 53, 69, 0.1);">
            <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
        </a>
    </div>
</div>

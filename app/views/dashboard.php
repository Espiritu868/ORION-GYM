<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SYSTEM_NAME ?> - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>public/css/style.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; color: #333; }
        .sidebar { min-height: 100vh; background: var(--bg-color); color: white; padding: 20px;}
        .sidebar a { color: #cbd5e1; text-decoration: none; display: block; padding: 10px; border-radius: 5px; margin-bottom: 5px;}
        .sidebar a:hover { background: rgba(255,255,255,0.1); color: white; }
        .topbar { background: white; padding: 15px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center;}
        .content { padding: 30px; }
        .stat-card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: none;}
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar col-md-2 d-none d-md-block">
            <h4 class="text-center mb-4 text-white font-weight-bold" style="font-family: 'Outfit';"><?= SYSTEM_NAME ?></h4>
            <a href="#" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <?php if(in_array('admin', $_SESSION['permissions'])): ?>
                <a href="#"><i class="bi bi-people me-2"></i> Miembros</a>
                <a href="#"><i class="bi bi-card-checklist me-2"></i> Membresías</a>
                <a href="#"><i class="bi bi-cash-coin me-2"></i> Caja</a>
            <?php endif; ?>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-10 flex-grow-1">
            <div class="topbar">
                <h5 class="mb-0">Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?></h5>
                <a href="<?= BASE_URL ?>index.php?controller=auth&action=logout" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
            </div>
            
            <div class="content">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card stat-card">
                            <h6 class="text-muted">Miembros Activos</h6>
                            <h2 class="mb-0 text-primary">0</h2>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card stat-card">
                            <h6 class="text-muted">Ingresos Hoy</h6>
                            <h2 class="mb-0 text-success">$0.00</h2>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card stat-card">
                            <h6 class="text-muted">Licencia (RTN)</h6>
                            <h4 class="mb-0 text-info"><?= htmlspecialchars($_SESSION['rtn']) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

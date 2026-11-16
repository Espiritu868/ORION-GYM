<?php
// Consultas reales del Dashboard
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/Membership.php';

$db = (new Database())->getConnection();

// Actualizar estados vencidos automáticamente
(new Membership())->autoUpdateExpired();

// 1. Total Miembros Registrados Activos
$stmt = $db->query("SELECT COUNT(*) FROM CLIENTES WHERE estado = 1 AND email != 'consumidor_final@orion.com'");
$total_miembros = (int)$stmt->fetchColumn();

// 2. Membresías Activas
$stmt = $db->query("SELECT COUNT(*) FROM MEMBRESIAS WHERE estado_membresia = 'Activa'");
$membresias_activas = (int)$stmt->fetchColumn();

// 3. Membresías Vencidas
$stmt = $db->query("SELECT COUNT(*) FROM MEMBRESIAS WHERE estado_membresia = 'Vencida'");
$membresias_vencidas = (int)$stmt->fetchColumn();

// 4. Ingresos del Mes Actual (Ventas / Caja)
$stmt = $db->query("
    SELECT ISNULL(SUM(monto), 0) 
    FROM CAJA_TRANSACCIONES 
    WHERE tipo_transaccion = 'Ingreso' 
      AND MONTH(fecha_transaccion) = MONTH(GETDATE()) 
      AND YEAR(fecha_transaccion) = YEAR(GETDATE())
");
$ingresos_mes = (float)$stmt->fetchColumn();

// 5. Productos con Stock Bajo o Agotado (<= 5 unidades)
$stmt = $db->query("
    SELECT id_producto, nombre_producto, stock, precio_venta
    FROM PRODUCTOS
    WHERE estado = 1 AND stock <= 5
    ORDER BY stock ASC, nombre_producto ASC
");
$productos_bajo_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_bajo_stock = count($productos_bajo_stock);

// 6. Próximas a vencer (próximos 7 días o que vencen hoy/mañana)
$stmt = $db->query("
    SELECT TOP 5 
           c.nombre_completo, 
           p.nombre_plan, 
           m.fecha_fin,
           DATEDIFF(day, CAST(GETDATE() AS DATE), m.fecha_fin) as dias_faltan
    FROM MEMBRESIAS m
    JOIN CLIENTES c ON m.id_cliente = c.id_cliente
    JOIN PLANES p ON m.id_plan = p.id_plan
    WHERE m.estado_membresia = 'Activa' 
      AND m.fecha_fin BETWEEN CAST(GETDATE() AS DATE) AND DATEADD(day, 7, CAST(GETDATE() AS DATE))
    ORDER BY m.fecha_fin ASC
");
$proximas_vencer = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 7. Ingresos de los últimos 7 días reales para el gráfico
$dias_grafico = [];
$ingresos_grafico = [];
$nombres_dias_es = [
    'Mon' => 'Lun',
    'Tue' => 'Mar',
    'Wed' => 'Mié',
    'Thu' => 'Jue',
    'Fri' => 'Vie',
    'Sat' => 'Sáb',
    'Sun' => 'Dom'
];

for ($i = 6; $i >= 0; $i--) {
    $fecha_iter = date('Y-m-d', strtotime("-$i days"));
    $dia_ing = date('D', strtotime($fecha_iter));
    $etiqueta = ($nombres_dias_es[$dia_ing] ?? $dia_ing) . ' ' . date('d/m', strtotime($fecha_iter));
    
    $stmt = $db->prepare("
        SELECT ISNULL(SUM(monto), 0) 
        FROM CAJA_TRANSACCIONES 
        WHERE tipo_transaccion = 'Ingreso' 
          AND CAST(fecha_transaccion AS DATE) = ?
    ");
    $stmt->execute([$fecha_iter]);
    $total_dia = (float)$stmt->fetchColumn();
    
    $dias_grafico[] = $etiqueta;
    $ingresos_grafico[] = $total_dia;
}
?>

<!-- Se incluye Chart.js para gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if ($total_bajo_stock > 0): ?>
    <!-- Alerta Global de Stock Bajo -->
    <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center justify-content-between p-3 mb-4 rounded-4" role="alert" style="background: linear-gradient(135deg, rgba(220, 38, 38, 0.12) 0%, rgba(220, 38, 38, 0.05) 100%); border-left: 5px solid #dc2626 !important;">
        <div class="d-flex align-items-center">
            <div class="me-3 fs-3 text-danger">
                <i class="fas fa-boxes-stacked animate-bounce"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-danger">¡Alerta de Inventario!</h6>
                <small class="text-dark">Hay <strong><?= $total_bajo_stock ?></strong> producto(s) con existencias mínimas o agotadas (≤ 5 unidades).</small>
            </div>
        </div>
        <a href="index.php?page=administracion" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm">
            <i class="fas fa-arrow-right me-1"></i> Ir a Inventario
        </a>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 text-center p-3 h-100 bg-white" style="border-radius: 16px;">
            <div class="card-body py-2">
                <div class="display-6 text-primary mb-2"><i class="fas fa-users"></i></div>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_miembros) ?></h3>
                <p class="text-muted small mb-0">Miembros Activos</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 text-center p-3 h-100 bg-white" style="border-radius: 16px;">
            <div class="card-body py-2">
                <div class="display-6 text-success mb-2"><i class="fas fa-check-circle"></i></div>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($membresias_activas) ?></h3>
                <p class="text-muted small mb-0">Membresías Activas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 text-center p-3 h-100 bg-white" style="border-radius: 16px;">
            <div class="card-body py-2">
                <div class="display-6 text-warning mb-2"><i class="fas fa-exclamation-circle"></i></div>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($membresias_vencidas) ?></h3>
                <p class="text-muted small mb-0">Membresías Vencidas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 text-center p-3 h-100 text-white" style="background: linear-gradient(135deg, #111827 0%, #030712 100%); border: 1px solid rgba(212, 175, 55, 0.3) !important; border-radius: 16px;">
            <div class="card-body py-2">
                <div class="display-6 mb-2" style="color: #d4af37;"><i class="fas fa-wallet"></i></div>
                <h3 class="fw-bold mb-0" style="color: #d4af37;">L <?= number_format($ingresos_mes, 2) ?></h3>
                <p class="text-white-50 small mb-0">Ingresos del Mes</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 16px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-chart-area text-primary me-2"></i> Flujo de Ingresos (Últimos 7 Días)</h5>
                    <span class="badge bg-light text-secondary border">En Tiempo Real</span>
                </div>
                <canvas id="ingresosChart" height="110"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 d-flex flex-column gap-4">
        <!-- Tarjeta de Membresías Próximas a Vencer -->
        <div class="card shadow-sm border-0 flex-fill" style="border-radius: 16px;">
            <div class="card-body p-4 d-flex flex-column">
                <h5 class="fw-bold text-danger mb-2"><i class="fas fa-bell me-2"></i> Próximas a Vencer</h5>
                <p class="text-muted small mb-3">Socios por vencer en los próximos 7 días</p>
                
                <?php if (empty($proximas_vencer)): ?>
                    <div class="text-center text-muted my-auto py-3">
                        <i class="fas fa-calendar-check fs-2 text-success mb-2"></i>
                        <p class="mb-0 small">No hay membresías por vencer próximamente.</p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush mb-auto">
                        <?php foreach ($proximas_vencer as $pv): ?>
                            <?php 
                                $dias = (int)$pv['dias_faltan'];
                                if ($dias == 0) {
                                    $badge = '<span class="badge bg-danger rounded-pill">Vence Hoy</span>';
                                } else if ($dias == 1) {
                                    $badge = '<span class="badge bg-warning text-dark rounded-pill">Mañana</span>';
                                } else {
                                    $badge = '<span class="badge bg-secondary rounded-pill">' . $dias . ' días</span>';
                                }
                            ?>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 mb-2">
                                <div class="me-2">
                                    <h6 class="mb-0 fw-bold text-dark text-truncate" style="max-width: 170px;"><?= htmlspecialchars($pv['nombre_completo']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($pv['nombre_plan']) ?></small>
                                </div>
                                <?= $badge ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="text-center mt-3 pt-2 border-top">
                    <a href="index.php?page=membresias" class="btn btn-sm btn-outline-primary w-100 rounded-pill">
                        Ver membresías <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Alerta de Stock de Productos -->
        <div class="card shadow-sm border-0 flex-fill" style="border-radius: 16px;">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-cubes-stacked text-warning me-2"></i> Alerta de Stock
                    </h5>
                    <?php if ($total_bajo_stock > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?= $total_bajo_stock ?> críticos</span>
                    <?php else: ?>
                        <span class="badge bg-success rounded-pill">Óptimo</span>
                    <?php endif; ?>
                </div>
                <p class="text-muted small mb-3">Productos con inventario bajo (≤ 5 unid.)</p>

                <?php if (empty($productos_bajo_stock)): ?>
                    <div class="text-center text-muted my-auto py-3">
                        <i class="fas fa-circle-check fs-2 text-success mb-2"></i>
                        <p class="mb-0 small">Todos los productos cuentan con stock suficiente.</p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush mb-auto">
                        <?php foreach (array_slice($productos_bajo_stock, 0, 4) as $prod): ?>
                            <?php 
                                $stk = (int)$prod['stock'];
                                if ($stk <= 0) {
                                    $stk_badge = '<span class="badge bg-danger rounded-pill">Agotado (0)</span>';
                                } else if ($stk <= 2) {
                                    $stk_badge = '<span class="badge bg-danger rounded-pill">' . $stk . ' unid.</span>';
                                } else {
                                    $stk_badge = '<span class="badge bg-warning text-dark rounded-pill">' . $stk . ' unid.</span>';
                                }
                            ?>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 mb-2">
                                <div class="me-2">
                                    <h6 class="mb-0 fw-bold text-dark text-truncate" style="max-width: 170px;"><?= htmlspecialchars($prod['nombre_producto']) ?></h6>
                                    <small class="text-muted">Precio: L <?= number_format($prod['precio_venta'], 2) ?></small>
                                </div>
                                <?= $stk_badge ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="text-center mt-3 pt-2 border-top">
                    <a href="index.php?page=administracion" class="btn btn-sm btn-outline-warning text-dark w-100 rounded-pill">
                        Ver inventario completo <i class="fas fa-boxes-stacked ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('ingresosChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(13, 110, 253, 0.45)');
        gradient.addColorStop(1, 'rgba(13, 110, 253, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($dias_grafico) ?>,
                datasets: [{
                    label: 'Ingresos Diarios (L)',
                    data: <?= json_encode($ingresos_grafico) ?>,
                    borderColor: '#0d6efd',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#0d6efd',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Ingreso: L ' + context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'L ' + value.toLocaleString();
                            }
                        },
                        grid: { borderDash: [5, 5], color: '#e2e8f0' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>

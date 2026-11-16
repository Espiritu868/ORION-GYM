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

// 5. Próximas a vencer (próximos 7 días o que vencen hoy/mañana)
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

// 6. Ingresos de los últimos 7 días reales para el gráfico
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
    
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 16px;">
            <div class="card-body p-4 d-flex flex-column">
                <h5 class="fw-bold text-danger mb-3"><i class="fas fa-bell me-2"></i> Próximas a Vencer</h5>
                <p class="text-muted small mb-3">Socios con vencimiento en los próximos 7 días</p>
                
                <?php if (empty($proximas_vencer)): ?>
                    <div class="text-center text-muted my-auto py-4">
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

                <div class="text-center mt-3 pt-3 border-top">
                    <a href="index.php?page=membresias" class="btn btn-sm btn-outline-primary w-100 rounded-pill">
                        Ver todas las membresías <i class="fas fa-arrow-right ms-1"></i>
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

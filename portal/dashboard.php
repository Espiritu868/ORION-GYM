<?php
session_start();
if (!isset($_SESSION['socio_logged']) || !$_SESSION['socio_logged']) {
    header("Location: index.php");
    exit();
}

require_once '../config/Database.php';

$id_cliente = $_SESSION['socio_id'];
$db = new Database();
$conn = $db->getConnection();

// 1. Obtener datos actualizados del socio y su membresía
$stmt = $conn->prepare("
    SELECT TOP 1 c.id_cliente, c.nombre_completo, c.identidad, c.telefono, c.fecha_ingreso,
           CASE WHEN DATALENGTH(c.fotografia) > 0 THEN 1 ELSE 0 END AS tiene_foto,
           m.id_membresia, m.fecha_inicio, m.fecha_fin, m.estado_membresia,
           p.nombre_plan, p.duracion_dias
    FROM CLIENTES c
    LEFT JOIN MEMBRESIAS m ON c.id_cliente = m.id_cliente AND m.estado_membresia = 'Activa'
    LEFT JOIN PLANES p ON m.id_plan = p.id_plan
    WHERE c.id_cliente = :id
    ORDER BY m.fecha_fin DESC
");
$stmt->execute([':id' => $id_cliente]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

// Días restantes de membresía
$dias_restantes = 0;
$estado_texto = 'Sin Membresía';
$badge_color = 'bg-neutral-800 text-neutral-400 border-neutral-700';

if (!empty($cliente['fecha_fin'])) {
    $hoy = new DateTime();
    $fin = new DateTime($cliente['fecha_fin']);
    $diff = $hoy->diff($fin);
    if ($fin >= $hoy) {
        $dias_restantes = (int)$diff->format("%r%a");
        $estado_texto = "Activa ({$dias_restantes} días rest.)";
        $badge_color = "bg-emerald-500/10 text-emerald-400 border-emerald-500/30";
    } else {
        $estado_texto = "Vencida";
        $badge_color = "bg-rose-500/10 text-rose-400 border-rose-500/30";
    }
}

// 2. Última medida registrada
$stmt_medidas = $conn->prepare("SELECT TOP 1 * FROM MEDIDAS_CLIENTE WHERE id_cliente = :id ORDER BY fecha_evaluacion DESC");
$stmt_medidas->execute([':id' => $id_cliente]);
$ultima_medida = $stmt_medidas->fetch(PDO::FETCH_ASSOC);

// 3. Contar rutinas activas
$stmt_rut = $conn->prepare("SELECT COUNT(*) FROM RUTINA_DIAS WHERE id_cliente = :id");
$stmt_rut->execute([':id' => $id_cliente]);
$total_dias_rutina = $stmt_rut->fetchColumn();

// Asistencias este mes
$stmt_asist = $conn->prepare("SELECT COUNT(*) FROM ASISTENCIA WHERE id_cliente = :id AND MONTH(fecha_hora) = MONTH(GETDATE()) AND YEAR(fecha_hora) = YEAR(GETDATE())");
$stmt_asist->execute([':id' => $id_cliente]);
$asistencias_mes = $stmt_asist->fetchColumn();

// 4. Último Pago Realizado (Ventas / Mensualidad)
$stmt_pago = $conn->prepare("
    SELECT TOP 1 total, fecha_venta, metodo_pago 
    FROM VENTAS 
    WHERE id_cliente = :id 
    ORDER BY fecha_venta DESC
");
$stmt_pago->execute([':id' => $id_cliente]);
$ultimo_pago = $stmt_pago->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard - <?= htmlspecialchars($cliente['nombre_completo']) ?></title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#f59e0b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="https://cdn-icons-png.flaticon.com/512/2964/2964514.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-neutral-950 text-white min-h-screen pb-24 selection:bg-amber-500 selection:text-black">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-30 bg-neutral-900/80 backdrop-blur-md border-b border-neutral-800 px-5 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-600 to-amber-400 flex items-center justify-center text-neutral-950 shadow-md shadow-amber-500/20 font-bold">
                <i class="fa-solid fa-dumbbell"></i>
            </div>
            <div>
                <h1 class="text-sm font-bold tracking-tight text-white leading-none">ORION GYM</h1>
                <span class="text-[11px] text-neutral-400">Portal de Socio</span>
            </div>
        </div>

        <a href="logout.php" title="Cerrar sesión" class="w-9 h-9 rounded-xl bg-neutral-800 hover:bg-neutral-700 border border-neutral-700 flex items-center justify-center text-neutral-300 transition-colors">
            <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
        </a>
    </header>

    <main class="max-w-md mx-auto px-4 pt-5 space-y-5">
        
        <!-- Tarjeta de Bienvenida y Membresía -->
        <div class="relative overflow-hidden bg-gradient-to-br from-neutral-900 via-neutral-900 to-neutral-800/80 border border-neutral-800 rounded-3xl p-5 shadow-xl">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <?php if (!empty($cliente['tiene_foto'])): ?>
                        <img src="../controllers/ClientController.php?action=photo&id_cliente=<?= $cliente['id_cliente'] ?>&thumb=true" alt="Foto" class="w-16 h-16 rounded-2xl object-cover border-2 border-amber-500 shadow-md">
                    <?php else: ?>
                        <div class="w-16 h-16 rounded-2xl bg-neutral-800 border-2 border-amber-500/60 flex items-center justify-center text-amber-400 text-2xl font-bold">
                            <?= strtoupper(substr($cliente['nombre_completo'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex-1 min-w-0">
                    <span class="text-xs font-semibold text-amber-500 tracking-wide uppercase">¡Bienvenido de vuelta!</span>
                    <h2 class="text-lg font-bold text-white truncate"><?= htmlspecialchars($cliente['nombre_completo']) ?></h2>
                    <p class="text-xs text-neutral-400 font-mono">DNI: <?= htmlspecialchars($cliente['identidad']) ?></p>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-neutral-800/80 grid grid-cols-2 gap-3 text-center">
                <div class="bg-neutral-950/50 rounded-2xl p-2.5 border border-neutral-800/60">
                    <span class="text-[10px] uppercase font-bold text-neutral-400">Plan Actual</span>
                    <p class="text-xs font-semibold text-white truncate mt-0.5"><?= htmlspecialchars($cliente['nombre_plan'] ?? 'Sin Plan') ?></p>
                </div>
                <div class="bg-neutral-950/50 rounded-2xl p-2.5 border border-neutral-800/60">
                    <span class="text-[10px] uppercase font-bold text-neutral-400">Estado</span>
                    <div class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[11px] font-bold border <?= $badge_color ?>">
                        <?= $estado_texto ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Métricas Rápidas -->
        <div class="grid grid-cols-3 gap-2.5">
            <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-3 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] text-neutral-400 font-medium">Asistencias</span>
                    <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>
                <div>
                    <span class="text-lg font-bold text-white"><?= $asistencias_mes ?></span>
                    <span class="text-[9px] text-neutral-500 block">Este mes</span>
                </div>
            </div>

            <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-3 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] text-neutral-400 font-medium">Últ. Peso</span>
                    <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-weight-scale"></i>
                    </div>
                </div>
                <div>
                    <span class="text-base font-bold text-white">
                        <?= !empty($ultima_medida['peso']) ? number_format($ultima_medida['peso'], 1) . ' <span class="text-[10px] font-normal text-neutral-400">lbs</span>' : '--' ?>
                    </span>
                    <span class="text-[9px] text-neutral-500 block">Báscula</span>
                </div>
            </div>

            <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-3 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] text-neutral-400 font-medium">Últ. Pago</span>
                    <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                </div>
                <div>
                    <span class="text-base font-bold text-emerald-400">
                        <?= !empty($ultimo_pago['total']) ? 'L. ' . number_format($ultimo_pago['total'], 0) : '--' ?>
                    </span>
                    <span class="text-[9px] text-neutral-500 block truncate">
                        <?= !empty($ultimo_pago['fecha_venta']) ? date('d/m/y', strtotime($ultimo_pago['fecha_venta'])) : 'Sin pagos' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Accesos Directos Principales -->
        <h3 class="text-sm font-bold uppercase tracking-wider text-neutral-400 pt-2">Tu Entrenamiento</h3>

        <div class="grid grid-cols-1 gap-3">
            
            <!-- Botón Mis Rutinas -->
            <a href="rutinas.php" class="group bg-gradient-to-r from-neutral-900 to-neutral-800/80 hover:border-amber-500/50 border border-neutral-800 rounded-3xl p-5 flex items-center justify-between transition-all active:scale-[0.98] shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-dumbbell"></i>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-white">Mis Rutinas</h4>
                        <p class="text-xs text-neutral-400">Ver ejercicios por día, series y descanso</p>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-full bg-neutral-800 flex items-center justify-center text-neutral-400 group-hover:text-amber-400 group-hover:translate-x-1 transition-all">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </div>
            </a>

            <!-- Botón Mi Progreso y Medidas -->
            <a href="progreso.php" class="group bg-gradient-to-r from-neutral-900 to-neutral-800/80 hover:border-amber-500/50 border border-neutral-800 rounded-3xl p-5 flex items-center justify-between transition-all active:scale-[0.98] shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-white">Mi Progreso Físico</h4>
                        <p class="text-xs text-neutral-400">Historial de medidas, peso y evolución</p>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-full bg-neutral-800 flex items-center justify-center text-neutral-400 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </div>
            </a>

        </div>

    </main>

    <!-- Barra de Navegación Inferior (Estilo App Nativa) -->
    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-neutral-900/95 backdrop-blur-xl border-t border-neutral-800 py-2">
        <div class="max-w-md mx-auto grid grid-cols-3 text-center">
            <a href="dashboard.php" class="flex flex-col items-center gap-1 text-amber-500 font-bold py-1">
                <i class="fa-solid fa-house text-lg"></i>
                <span class="text-[10px]">Inicio</span>
            </a>
            <a href="rutinas.php" class="flex flex-col items-center gap-1 text-neutral-400 hover:text-white py-1">
                <i class="fa-solid fa-dumbbell text-lg"></i>
                <span class="text-[10px]">Rutinas</span>
            </a>
            <a href="progreso.php" class="flex flex-col items-center gap-1 text-neutral-400 hover:text-white py-1">
                <i class="fa-solid fa-chart-pie text-lg"></i>
                <span class="text-[10px]">Progreso</span>
            </a>
        </div>
    </nav>

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js');
        }
    </script>
</body>
</html>


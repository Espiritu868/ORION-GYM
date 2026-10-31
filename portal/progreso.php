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

// Obtener todo el historial de medidas
$stmt = $conn->prepare("SELECT * FROM MEDIDAS_CLIENTE WHERE id_cliente = :id ORDER BY fecha_evaluacion DESC");
$stmt->execute([':id' => $id_cliente]);
$medidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Última y penúltima medida para calcular diferencias
$ultima = $medidas[0] ?? null;
$penultima = $medidas[1] ?? null;

function diff_badge($val1, $val2, $unidad = 'lbs', $invert = false) {
    if ($val1 === null || $val2 === null) return '';
    $diff = round($val1 - $val2, 1);
    if ($diff == 0) return '<span class="text-neutral-500 text-[10px]">Sin cambio</span>';
    
    // Si $invert es true (por ejemplo para cintura/grasa donde bajar es bueno)
    $is_good = $invert ? ($diff < 0) : ($diff > 0);
    $color = $is_good ? 'text-emerald-400' : 'text-rose-400';
    $sign = $diff > 0 ? '+' : '';
    
    return "<span class='{$color} text-[10px] font-bold'>{$sign}{$diff} {$unidad}</span>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mi Progreso - Orion Gym</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#f59e0b">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-neutral-950 text-white min-h-screen pb-24 selection:bg-amber-500 selection:text-black">

    <!-- Header -->
    <header class="sticky top-0 z-30 bg-neutral-900/80 backdrop-blur-md border-b border-neutral-800 px-5 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="dashboard.php" class="w-9 h-9 rounded-xl bg-neutral-800 hover:bg-neutral-700 flex items-center justify-center text-neutral-300">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div>
                <h1 class="text-sm font-bold tracking-tight text-white leading-none">Mi Progreso</h1>
                <span class="text-[11px] text-neutral-400">Historial y Antropometría</span>
            </div>
        </div>
    </header>

    <main class="max-w-md mx-auto px-4 pt-5 space-y-5">
        
        <?php if (empty($medidas)): ?>
            <div class="bg-neutral-900 border border-neutral-800 rounded-3xl p-8 text-center text-neutral-400">
                <i class="fa-solid fa-chart-line text-4xl mb-3 text-neutral-600"></i>
                <h3 class="text-base font-bold text-white mb-1">Aún no tienes medidas registradas</h3>
                <p class="text-xs">Consulta con el personal o tu entrenador para realizarte una evaluación corporal.</p>
            </div>
        <?php else: ?>

            <!-- Tarjeta de Última Evaluación -->
            <div class="bg-gradient-to-br from-neutral-900 via-neutral-900 to-neutral-800 border border-neutral-800 rounded-3xl p-5 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold text-amber-500 uppercase tracking-wider">Última Evaluación</span>
                    <span class="text-xs text-neutral-400 font-mono">
                        <i class="fa-regular fa-calendar mr-1"></i> <?= date('d/m/Y', strtotime($ultima['fecha_evaluacion'])) ?>
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="bg-neutral-950/60 rounded-2xl p-3 border border-neutral-800/80">
                        <span class="text-[10px] text-neutral-400 font-semibold uppercase block">Peso Actual</span>
                        <span class="text-2xl font-black text-white"><?= number_format($ultima['peso'], 1) ?> <span class="text-xs text-neutral-400 font-normal">lbs</span></span>
                        <div class="mt-1">
                            <?= diff_badge($ultima['peso'], $penultima['peso'] ?? null, 'lbs') ?>
                        </div>
                    </div>

                    <div class="bg-neutral-950/60 rounded-2xl p-3 border border-neutral-800/80">
                        <span class="text-[10px] text-neutral-400 font-semibold uppercase block">Grasa Corporal</span>
                        <span class="text-2xl font-black text-amber-400">
                            <?= !empty($ultima['porcentaje_grasa']) ? number_format($ultima['porcentaje_grasa'], 1) . '%' : '--' ?>
                        </span>
                        <div class="mt-1">
                            <?= diff_badge($ultima['porcentaje_grasa'] ?? null, $penultima['porcentaje_grasa'] ?? null, '%', true) ?>
                        </div>
                    </div>
                </div>

                <!-- Detalle de Medidas Corporales en cm -->
                <div class="mt-4 pt-4 border-t border-neutral-800/80 grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="bg-neutral-950/40 p-2 rounded-xl border border-neutral-800/50">
                        <span class="text-[10px] text-neutral-400 block font-medium">Pecho</span>
                        <span class="font-bold text-white"><?= $ultima['pecho'] ?? '--' ?> cm</span>
                    </div>
                    <div class="bg-neutral-950/40 p-2 rounded-xl border border-neutral-800/50">
                        <span class="text-[10px] text-neutral-400 block font-medium">Brazo</span>
                        <span class="font-bold text-white"><?= $ultima['brazo'] ?? '--' ?> cm</span>
                    </div>
                    <div class="bg-neutral-950/40 p-2 rounded-xl border border-neutral-800/50">
                        <span class="text-[10px] text-neutral-400 block font-medium">Cintura</span>
                        <span class="font-bold text-white"><?= $ultima['cintura'] ?? '--' ?> cm</span>
                    </div>
                    <div class="bg-neutral-950/40 p-2 rounded-xl border border-neutral-800/50">
                        <span class="text-[10px] text-neutral-400 block font-medium">Cadera</span>
                        <span class="font-bold text-white"><?= $ultima['cadera'] ?? '--' ?> cm</span>
                    </div>
                    <div class="bg-neutral-950/40 p-2 rounded-xl border border-neutral-800/50">
                        <span class="text-[10px] text-neutral-400 block font-medium">Pierna</span>
                        <span class="font-bold text-white"><?= $ultima['pierna'] ?? '--' ?> cm</span>
                    </div>
                    <div class="bg-neutral-950/40 p-2 rounded-xl border border-neutral-800/50">
                        <span class="text-[10px] text-neutral-400 block font-medium">Pantorrilla</span>
                        <span class="font-bold text-white"><?= $ultima['pantorrilla'] ?? '--' ?> cm</span>
                    </div>
                </div>
            </div>

            <!-- Historial Completo -->
            <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-400 pt-2">Historial de Evaluaciones</h3>

            <div class="space-y-3">
                <?php foreach ($medidas as $idx => $m): ?>
                    <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-neutral-800 flex items-center justify-center text-amber-500 font-bold text-xs">
                                #<?= count($medidas) - $idx ?>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-white block">
                                    <?= date('d M, Y', strtotime($m['fecha_evaluacion'])) ?>
                                </span>
                                <span class="text-[11px] text-neutral-400">
                                    <?= !empty($m['notas']) ? htmlspecialchars($m['notas']) : 'Evaluación rutinaria' ?>
                                </span>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-sm font-black text-amber-400 block"><?= number_format($m['peso'], 1) ?> lbs</span>
                            <?php if (!empty($m['porcentaje_grasa'])): ?>
                                <span class="text-[10px] text-neutral-400"><?= $m['porcentaje_grasa'] ?>% Grasa</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </main>

    <!-- Barra de Navegación Inferior -->
    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-neutral-900/95 backdrop-blur-xl border-t border-neutral-800 py-2">
        <div class="max-w-md mx-auto grid grid-cols-3 text-center">
            <a href="dashboard.php" class="flex flex-col items-center gap-1 text-neutral-400 hover:text-white py-1">
                <i class="fa-solid fa-house text-lg"></i>
                <span class="text-[10px]">Inicio</span>
            </a>
            <a href="rutinas.php" class="flex flex-col items-center gap-1 text-neutral-400 hover:text-white py-1">
                <i class="fa-solid fa-dumbbell text-lg"></i>
                <span class="text-[10px]">Rutinas</span>
            </a>
            <a href="progreso.php" class="flex flex-col items-center gap-1 text-amber-500 font-bold py-1">
                <i class="fa-solid fa-chart-pie text-lg"></i>
                <span class="text-[10px]">Progreso</span>
            </a>
        </div>
    </nav>
</body>
</html>


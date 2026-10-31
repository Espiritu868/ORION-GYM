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

$dias_semana_nombres = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    7 => 'Domingo'
];

// Obtener los días de rutina configurados
$stmt = $conn->prepare("SELECT * FROM RUTINA_DIAS WHERE id_cliente = :idc ORDER BY dia_semana ASC");
$stmt->execute([':idc' => $id_cliente]);
$rutinas_dias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cargar ejercicios por cada día
$rutinas_por_dia = [];
foreach ($rutinas_dias as $rd) {
    $dia_num = $rd['dia_semana'];
    
    $stmt_ej = $conn->prepare("
        SELECT re.id_rutina_ej, re.orden, re.descanso_segundos, c.nombre as ejercicio, c.imagen_url, c.grupo_muscular 
        FROM RUTINA_EJERCICIOS re 
        JOIN CATALOGO_EJERCICIOS c ON re.id_ejercicio = c.id_ejercicio 
        WHERE re.id_rutina_dia = :id 
        ORDER BY re.orden ASC
    ");
    $stmt_ej->execute([':id' => $rd['id_rutina_dia']]);
    $ejercicios = $stmt_ej->fetchAll(PDO::FETCH_ASSOC);

    foreach ($ejercicios as &$ej) {
        $stmt_ser = $conn->prepare("SELECT numero_serie, lbs_obj, reps_obj, rpe_obj, tipo_serie FROM RUTINA_SERIES WHERE id_rutina_ej = :id ORDER BY numero_serie ASC");
        $stmt_ser->execute([':id' => $ej['id_rutina_ej']]);
        $ej['series'] = $stmt_ser->fetchAll(PDO::FETCH_ASSOC);
    }

    $rutinas_por_dia[$dia_num] = [
        'info' => $rd,
        'ejercicios' => $ejercicios
    ];
}

// Chequear si existe la tabla opcional de notas de rutinas simples
$rutinas_simples = [];
try {
    $stmt_simple = $conn->prepare("SELECT dia_semana, descripcion_rutina FROM RUTINAS_CLIENTE WHERE id_cliente = :id ORDER BY dia_semana ASC");
    $stmt_simple->execute([':id' => $id_cliente]);
    $rutinas_simples = $stmt_simple->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
    // Si no existe la tabla RUTINAS_CLIENTE, simplemente se continúa con el esquema avanzado
    $rutinas_simples = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mis Rutinas - Orion Gym</title>
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
                <h1 class="text-sm font-bold tracking-tight text-white leading-none">Mis Rutinas</h1>
                <span class="text-[11px] text-neutral-400">Plan de Entrenamiento Semanal</span>
            </div>
        </div>
    </header>

    <main class="max-w-md mx-auto px-4 pt-5 space-y-4">
        
        <?php if (empty($rutinas_por_dia) && empty($rutinas_simples)): ?>
            <div class="bg-neutral-900 border border-neutral-800 rounded-3xl p-8 text-center text-neutral-400">
                <i class="fa-solid fa-dumbbell text-4xl mb-3 text-neutral-600"></i>
                <h3 class="text-base font-bold text-white mb-1">Aún no tienes rutinas asignadas</h3>
                <p class="text-xs">Pídele a tu entrenador o al personal del gimnasio que te configure tu rutina.</p>
            </div>
        <?php else: ?>

            <!-- Días de la semana -->
            <?php foreach ($dias_semana_nombres as $num_dia => $nombre_dia): ?>
                <?php 
                    $tiene_avanzada = isset($rutinas_por_dia[$num_dia]);
                    $tiene_simple = !empty($rutinas_simples[$num_dia]);
                    if (!$tiene_avanzada && !$tiene_simple) continue;
                ?>

                <div class="bg-neutral-900 border border-neutral-800 rounded-3xl overflow-hidden shadow-lg">
                    <!-- Cabecera del día -->
                    <div class="bg-neutral-850 px-5 py-3.5 border-b border-neutral-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-xs">
                                <?= $num_dia ?>
                            </span>
                            <span class="font-bold text-sm text-white"><?= $nombre_dia ?></span>
                        </div>
                        <?php if ($tiene_avanzada): ?>
                            <span class="text-[11px] bg-neutral-800 px-2.5 py-1 rounded-full text-neutral-400 font-medium">
                                <?= count($rutinas_por_dia[$num_dia]['ejercicios']) ?> Ejercicios
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Contenido de ejercicios -->
                    <div class="p-4 space-y-3">
                        <?php if ($tiene_avanzada): ?>
                            <?php foreach ($rutinas_por_dia[$num_dia]['ejercicios'] as $ej): ?>
                                <div class="bg-neutral-950/70 border border-neutral-800/80 rounded-2xl p-3.5">
                                    <div class="flex items-center justify-between gap-3 mb-2">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-neutral-800 flex items-center justify-center text-amber-400">
                                                <i class="fa-solid fa-person-walking-arrow-right"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-bold text-white"><?= htmlspecialchars($ej['ejercicio']) ?></h4>
                                                <span class="text-[10px] text-amber-500 uppercase font-semibold"><?= htmlspecialchars($ej['grupo_muscular'] ?? 'General') ?></span>
                                            </div>
                                        </div>

                                        <?php if (!empty($ej['descanso_segundos'])): ?>
                                            <span class="text-[10px] bg-neutral-800 text-neutral-300 px-2 py-0.5 rounded-lg flex items-center gap-1">
                                                <i class="fa-regular fa-clock text-[9px] text-amber-400"></i> <?= $ej['descanso_segundos'] ?>s desc.
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Series -->
                                    <?php if (!empty($ej['series'])): ?>
                                        <div class="grid grid-cols-3 gap-1.5 pt-2 border-t border-neutral-800/60 text-center">
                                            <?php foreach ($ej['series'] as $ser): ?>
                                                <div class="bg-neutral-900 rounded-xl p-1.5 border border-neutral-800/40">
                                                    <span class="text-[9px] text-neutral-400 font-bold block">Serie <?= $ser['numero_serie'] ?></span>
                                                    <span class="text-xs font-bold text-white">
                                                        <?= $ser['reps_obj'] ?> reps
                                                        <?php if (!empty($ser['lbs_obj'])): ?>
                                                            <span class="text-[10px] text-amber-400 block font-normal"><?= $ser['lbs_obj'] ?> lbs</span>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Nota o rutina simple si existe -->
                        <?php if ($tiene_simple): ?>
                            <div class="bg-neutral-950/60 border border-neutral-800 rounded-2xl p-3 text-xs text-neutral-300 whitespace-pre-line">
                                <?= htmlspecialchars($rutinas_simples[$num_dia]) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

    </main>

    <!-- Barra de Navegación Inferior -->
    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-neutral-900/95 backdrop-blur-xl border-t border-neutral-800 py-2">
        <div class="max-w-md mx-auto grid grid-cols-3 text-center">
            <a href="dashboard.php" class="flex flex-col items-center gap-1 text-neutral-400 hover:text-white py-1">
                <i class="fa-solid fa-house text-lg"></i>
                <span class="text-[10px]">Inicio</span>
            </a>
            <a href="rutinas.php" class="flex flex-col items-center gap-1 text-amber-500 font-bold py-1">
                <i class="fa-solid fa-dumbbell text-lg"></i>
                <span class="text-[10px]">Rutinas</span>
            </a>
            <a href="progreso.php" class="flex flex-col items-center gap-1 text-neutral-400 hover:text-white py-1">
                <i class="fa-solid fa-chart-pie text-lg"></i>
                <span class="text-[10px]">Progreso</span>
            </a>
        </div>
    </nav>
</body>
</html>


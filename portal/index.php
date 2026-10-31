<?php
session_start();
require_once '../config/Database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identidad = trim($_POST['identidad'] ?? '');
    // Limpiar caracteres no numéricos o espacios para permitir búsqueda flexible
    $identidad_limpia = preg_replace('/[^0-9kK]/', '', $identidad);

    if (empty($identidad_limpia)) {
        $error = 'Por favor ingresa tu número de identidad.';
    } else {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            // Buscar socio activo comparando con y sin guiones (usando REPLACE en SQL)
            $stmt = $conn->prepare("
                SELECT TOP 1 c.id_cliente, c.nombre_completo, c.identidad, c.telefono, c.fecha_ingreso,
                       CASE WHEN DATALENGTH(c.fotografia) > 0 THEN 1 ELSE 0 END AS tiene_foto,
                       m.id_membresia, m.fecha_inicio, m.fecha_fin, m.estado_membresia,
                       p.nombre_plan, p.duracion_dias
                FROM CLIENTES c
                LEFT JOIN MEMBRESIAS m ON c.id_cliente = m.id_cliente AND m.estado_membresia = 'Activa'
                LEFT JOIN PLANES p ON m.id_plan = p.id_plan
                WHERE (c.identidad = :identidad_raw OR REPLACE(REPLACE(c.identidad, '-', ''), ' ', '') = :identidad_clean)
                  AND c.estado = 1
                ORDER BY m.fecha_fin DESC
            ");
            $stmt->execute([
                ':identidad_raw' => $identidad,
                ':identidad_clean' => $identidad_limpia
            ]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cliente) {
                $_SESSION['socio_logged'] = true;
                $_SESSION['socio_id'] = $cliente['id_cliente'];
                $_SESSION['socio_nombre'] = $cliente['nombre_completo'];
                $_SESSION['socio_identidad'] = $cliente['identidad'];
                $_SESSION['socio_plan'] = $cliente['nombre_plan'] ?? 'Sin membresía activa';
                $_SESSION['socio_vence'] = $cliente['fecha_fin'] ?? null;
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'No encontramos una cuenta activa con esta identidad.';
            }
        } catch (Exception $e) {
            $error = 'Error de conexión: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Orion Gym - Portal Socio</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#f59e0b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="https://cdn-icons-png.flaticon.com/512/2964/2964514.png">
    
    <!-- Tailwind CSS (CDN para carga ultra rápida sin build steps) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-neutral-950 text-white min-h-screen flex flex-col justify-between p-4 selection:bg-amber-500 selection:text-black">

    <!-- Header / Branding -->
    <div class="w-full max-w-md mx-auto pt-8 pb-4 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-tr from-amber-600 to-amber-400 text-neutral-950 shadow-xl shadow-amber-500/20 mb-4 animate-pulse">
            <i class="fa-solid fa-dumbbell text-3xl"></i>
        </div>
        <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-white via-neutral-200 to-neutral-400 bg-clip-text text-transparent">
            ORION GYM
        </h1>
        <p class="text-neutral-400 text-sm mt-1">Portal Oficial de Socios</p>
    </div>

    <!-- Login Card -->
    <div class="w-full max-w-md mx-auto bg-neutral-900/90 border border-neutral-800 rounded-3xl p-6 shadow-2xl backdrop-blur-xl">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-right-to-bracket text-amber-500 text-lg"></i> Iniciar Sesión
            </h2>
            <p class="text-xs text-neutral-400 mt-1">Ingresa tu número de identidad para ver tus rutinas y avance.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-5 p-3 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-400 text-lg"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2">
                    Número de Identidad / DNI
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-neutral-500">
                        <i class="fa-solid fa-id-card"></i>
                    </span>
                    <input type="text" name="identidad" required autofocus
                        placeholder="Ej: 0801199912345"
                        class="w-full bg-neutral-950 border border-neutral-800 rounded-2xl pl-11 pr-4 py-3.5 text-white placeholder-neutral-600 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all font-mono text-base">
                </div>
            </div>

            <button type="submit" 
                class="w-full mt-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-neutral-950 font-bold py-3.5 px-6 rounded-2xl shadow-lg shadow-amber-500/25 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                <span>Acceder a mi Cuenta</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <!-- Botón para instalar PWA si el navegador lo permite -->
        <div id="pwa-install-banner" class="hidden mt-6 pt-5 border-t border-neutral-800 text-center">
            <button id="pwa-install-btn" class="w-full bg-neutral-800 hover:bg-neutral-700 text-amber-400 font-medium py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 border border-neutral-700">
                <i class="fa-solid fa-download"></i>
                <span>Instalar como App en mi celular</span>
            </button>
        </div>
    </div>

    <!-- Footer -->
    <div class="w-full max-w-md mx-auto py-6 text-center text-xs text-neutral-600">
        <p>&copy; <?= date('Y') ?> Orion Gym System. Todos los derechos reservados.</p>
    </div>

    <script>
        // Registrar Service Worker para soporte PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                    .then(reg => console.log('SW registrado con éxito:', reg.scope))
                    .catch(err => console.log('SW fallo al registrar:', err));
            });
        }

        // Detección de instalación PWA
        let deferredPrompt;
        const installBanner = document.getElementById('pwa-install-banner');
        const installBtn = document.getElementById('pwa-install-btn');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (installBanner) installBanner.classList.remove('hidden');
        });

        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    if (outcome === 'accepted') {
                        installBanner.classList.add('hidden');
                    }
                    deferredPrompt = null;
                }
            });
        }
    </script>
</body>
</html>


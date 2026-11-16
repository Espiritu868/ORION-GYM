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
    <title>Fortia Gym - Portal Socio</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#d4af37">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="logo.jpeg">
    
    <!-- Tailwind CSS (CDN para carga ultra rápida sin build steps) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        fortia: {
                            gold: '#d4af37',
                            'gold-hover': '#b8972e',
                            red: '#dc2626',
                            dark: '#0a0d14'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#0b0f19] text-white min-h-screen flex flex-col justify-between p-4 selection:bg-[#d4af37] selection:text-black">

    <!-- Header / Branding -->
    <div class="w-full max-w-md mx-auto pt-6 pb-4 text-center">
        <div class="inline-block relative mb-3">
            <img src="logo.jpeg" alt="Logo Fortia Gym" class="w-24 h-24 rounded-full mx-auto object-cover border-4 border-[#d4af37] shadow-2xl shadow-[#d4af37]/30">
        </div>
        <h1 class="text-3xl font-extrabold tracking-tight text-white">
            FORTIA <span class="text-[#dc2626]">GYM</span>
        </h1>
        <p class="text-[#d4af37] text-xs font-semibold tracking-wider uppercase mt-1">Portal Oficial de Socios</p>
        <p class="text-neutral-500 text-[10px] mt-0.5">SATEC (Sistemas y Automatizacion Tecnologica S de R.L)</p>
    </div>

    <!-- Login Card -->
    <div class="w-full max-w-md mx-auto bg-neutral-900/90 border border-[#d4af37]/30 rounded-3xl p-6 shadow-2xl backdrop-blur-xl">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-right-to-bracket text-[#d4af37] text-lg"></i> Iniciar Sesión
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
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-300 mb-2">
                    Número de Identidad / DNI
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-neutral-500">
                        <i class="fa-solid fa-id-card"></i>
                    </span>
                    <input type="text" name="identidad" required autofocus
                        placeholder="Ej: 1625-1998-00074 o sin guiones"
                        class="w-full bg-neutral-950 border border-neutral-800 rounded-2xl pl-11 pr-4 py-3.5 text-white placeholder-neutral-600 focus:outline-none focus:border-[#d4af37] focus:ring-1 focus:ring-[#d4af37] transition-all font-mono text-base">
                </div>
            </div>

            <button type="submit" 
                class="w-full mt-2 bg-gradient-to-r from-[#d4af37] to-[#b8972e] hover:from-[#dfbc45] hover:to-[#c4a132] text-neutral-950 font-bold py-3.5 px-6 rounded-2xl shadow-lg shadow-[#d4af37]/25 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                <span>Acceder a mi Cuenta</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <!-- Botón para instalar PWA si el navegador lo permite -->
        <div id="pwa-install-banner" class="hidden mt-6 pt-5 border-t border-neutral-800 text-center">
            <button id="pwa-install-btn" class="w-full bg-neutral-800 hover:bg-neutral-700 text-[#d4af37] font-medium py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 border border-neutral-700">
                <i class="fa-solid fa-download"></i>
                <span>Instalar como App en mi celular</span>
            </button>
        </div>

        <!-- Banner especial para usuarios de iPhone (iOS) -->
        <div id="ios-install-banner" class="hidden mt-6 pt-4 border-t border-neutral-800 text-center">
            <button onclick="document.getElementById('ios-modal').classList.remove('hidden')" class="w-full bg-neutral-800/80 hover:bg-neutral-800 text-[#d4af37] font-medium py-2 px-3 rounded-xl text-xs flex items-center justify-center gap-2 border border-[#d4af37]/30">
                <i class="fa-brands fa-apple text-base"></i>
                <span>¿Cómo instalar en mi iPhone?</span>
            </button>
        </div>
    </div>

    <!-- Modal instructivo para iPhone -->
    <div id="ios-modal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-end sm:items-center justify-center p-4">
        <div class="bg-neutral-900 border border-neutral-800 rounded-3xl max-w-sm w-full p-6 text-center space-y-4 shadow-2xl animate-fade-in">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-[#d4af37]/10 text-[#d4af37] flex items-center justify-center text-2xl">
                <i class="fa-brands fa-apple"></i>
            </div>
            <h3 class="text-base font-bold text-white">Instalar en iPhone / iPad</h3>
            <p class="text-xs text-neutral-400">
                Para tener la app en tu pantalla de inicio en iOS:
            </p>
            <div class="space-y-3 text-left text-xs bg-neutral-950/80 p-4 rounded-2xl border border-neutral-800">
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#d4af37] text-neutral-950 font-bold flex items-center justify-center text-xs shrink-0">1</span>
                    <span>Toca el botón <strong class="text-white">Compartir</strong> de Safari abajo (<i class="fa-solid fa-arrow-up-from-bracket text-[#d4af37]"></i>).</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#d4af37] text-neutral-950 font-bold flex items-center justify-center text-xs shrink-0">2</span>
                    <span>Desliza hacia abajo y elige <strong class="text-white">"Agregar a inicio"</strong> (<i class="fa-regular fa-square-plus text-[#d4af37]"></i>).</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#d4af37] text-neutral-950 font-bold flex items-center justify-center text-xs shrink-0">3</span>
                    <span>Pulsa <strong class="text-white">"Agregar"</strong> arriba a la derecha.</span>
                </div>
            </div>
            <button onclick="document.getElementById('ios-modal').classList.add('hidden')" class="w-full bg-neutral-800 hover:bg-neutral-700 text-white font-semibold py-2.5 rounded-xl text-xs">
                Entendido
            </button>
        </div>
    </div>

    <!-- Footer -->
    <div class="w-full max-w-md mx-auto py-5 text-center text-xs text-neutral-500">
        <p>&copy; <?= date('Y') ?> <strong>FORTIA GYM</strong></p>
        <p class="text-[11px] text-neutral-600 mt-1">Desarrollado por <strong>SATEC</strong> (Sistemas y Automatizacion Tecnologica S de R.L)</p>
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

        // Detectar si es iOS (iPhone / iPad) y no está ya instalado en standalone
        const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        const isStandalone = window.navigator.standalone === true || window.matchMedia('(display-mode: standalone)').matches;

        if (isIos && !isStandalone) {
            const iosBanner = document.getElementById('ios-install-banner');
            if (iosBanner) iosBanner.classList.remove('hidden');
        }

        // Detección de instalación PWA en Android/Chrome
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


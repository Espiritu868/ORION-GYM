<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SYSTEM_NAME ?> - Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- QRCode JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- IMask for input formatting -->
    <script src="https://unpkg.com/imask"></script>
    <style>
        :root {
            --fortia-gold: #d4af37;
            --fortia-gold-hover: #b8972e;
            --fortia-red: #dc2626;
            --fortia-dark: #0f172a;
        }
        html, body { 
            height: 100%; 
            margin: 0; 
            padding: 0; 
            background: #f8fafc; 
            color: #333; 
            font-family: 'Outfit'; 
            overflow: hidden; 
        }
        
        .sidebar { 
            height: 100vh; 
            background: linear-gradient(180deg, #111827 0%, #030712 100%); 
            border-right: 1px solid rgba(212, 175, 55, 0.2);
            color: white; 
            padding: 20px 15px;
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1040;
            transition: all 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .sidebar.collapsed {
            width: 80px;
        }
        
        #main-content {
            transition: all 0.3s ease;
            margin-left: 250px;
            width: calc(100% - 250px);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        #main-content.expanded {
            margin-left: 80px;
            width: calc(100% - 80px);
        }

        @media (max-width: 768px) {
            .sidebar { 
                left: -260px; 
                width: 260px !important; 
                box-shadow: 10px 0 25px rgba(0,0,0,0.5);
                z-index: 1060;
            }
            .sidebar.show { 
                left: 0 !important; 
            }
            #main-content { margin-left: 0; width: 100%; }
            #main-content.expanded { margin-left: 0; width: 100%; }
            .sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0,0,0,0.55);
                backdrop-filter: blur(2px);
                z-index: 1050;
            }
            .sidebar-backdrop.show {
                display: block;
            }
        }

        /* Modal centering relative to the white panel */
        body:not(.sidebar-collapsed) .modal,
        body:not(.sidebar-collapsed) .modal-backdrop {
            left: 250px !important;
            width: calc(100% - 250px) !important;
        }
        body.sidebar-collapsed .modal,
        body.sidebar-collapsed .modal-backdrop {
            left: 80px !important;
            width: calc(100% - 80px) !important;
        }
        @media (max-width: 768px) {
            body:not(.sidebar-collapsed) .modal,
            body:not(.sidebar-collapsed) .modal-backdrop,
            body.sidebar-collapsed .modal,
            body.sidebar-collapsed .modal-backdrop { 
                left: 0 !important; 
                width: 100% !important; 
            }
        }

        .sidebar a { color: #cbd5e1; text-decoration: none; display: flex; align-items: center; padding: 12px 10px; border-radius: 8px; margin-bottom: 5px; white-space: nowrap; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(212, 175, 55, 0.15); color: #d4af37; border-left: 3px solid #d4af37; font-weight: 600; }
        .sidebar i { width: 25px; text-align: center; }
        .sidebar.collapsed .sidebar-text, .sidebar.collapsed h4 { display: none; }
        .sidebar.collapsed a { justify-content: center; }
        .sidebar.collapsed i { margin-right: 0 !important; font-size: 1.2rem; }
        .topbar { background: white; padding: 15px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid rgba(212, 175, 55, 0.25); flex-shrink: 0; }
        .content { padding: 30px; flex: 1 1 auto; overflow-y: auto; }
        .fixed-footer { flex-shrink: 0; z-index: 1020; }
        .table-card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .client-photo-sm { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .qr-container { text-align: center; margin: 20px 0; display: none; }
        #qrcode { display: inline-block; padding: 10px; background: white; border-radius: 10px; }
        .preview-img { max-width: 150px; border-radius: 10px; display: none; margin-top: 10px; }
    </style>
</head>
<body>

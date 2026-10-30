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
        body { background: #f8fafc; color: #333; font-family: 'Outfit'; overflow-x: hidden; }
        
        .sidebar { 
            min-height: 100vh; 
            background: #0f172a; 
            color: white; 
            padding: 20px 15px;
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1040;
            transition: all 0.3s ease;
            overflow-x: hidden;
        }
        .sidebar.collapsed {
            width: 80px;
        }
        
        #main-content {
            transition: all 0.3s ease;
            margin-left: 250px;
            width: calc(100% - 250px);
            min-height: 100vh;
        }
        #main-content.expanded {
            margin-left: 80px;
            width: calc(100% - 80px);
        }

        @media (max-width: 768px) {
            .sidebar { left: -250px; width: 250px !important; }
            .sidebar.show { left: 0; }
            #main-content { margin-left: 0; width: 100%; }
            #main-content.expanded { margin-left: 0; width: 100%; }
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

        .sidebar a { color: #cbd5e1; text-decoration: none; display: flex; align-items: center; padding: 12px 10px; border-radius: 5px; margin-bottom: 5px; white-space: nowrap; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: white; }
        .sidebar i { width: 25px; text-align: center; }
        .sidebar.collapsed .sidebar-text, .sidebar.collapsed h4 { display: none; }
        .sidebar.collapsed a { justify-content: center; }
        .sidebar.collapsed i { margin-right: 0 !important; font-size: 1.2rem; }
        .topbar { background: white; padding: 15px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between;}
        .content { padding: 30px; }
        .table-card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .client-photo-sm { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .qr-container { text-align: center; margin: 20px 0; display: none; }
        #qrcode { display: inline-block; padding: 10px; background: white; border-radius: 10px; }
        .preview-img { max-width: 150px; border-radius: 10px; display: none; margin-top: 10px; }
    </style>
</head>
<body>

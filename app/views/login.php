<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SYSTEM_NAME ?> - Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>public/css/style.css" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2><?= SYSTEM_NAME ?></h2>
                <p>Ingreso al Sistema</p>
            </div>
            
            <div id="login-alert" class="alert alert-danger d-none" role="alert"></div>

            <form id="login-form">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="username" name="username" required placeholder="Ingrese su usuario">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="Ingrese su contraseña">
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-login">Ingresar</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const alertBox = document.getElementById('login-alert');
            const submitBtn = document.querySelector('.btn-login');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Validando...';
            
            fetch('<?= BASE_URL ?>index.php?controller=auth&action=login', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '<?= BASE_URL ?>index.php?controller=dashboard&action=index';
                } else {
                    alertBox.textContent = data.message;
                    alertBox.classList.remove('d-none');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Ingresar';
                }
            })
            .catch(error => {
                alertBox.textContent = 'Error de conexión con el servidor.';
                alertBox.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Ingresar';
            });
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Captura de Foto - ORION GYM</title>
    <style>
        body { font-family: sans-serif; background: #1e293b; color: white; text-align: center; padding: 20px; }
        .btn { background: #27ae60; color: white; padding: 15px 30px; border: none; border-radius: 10px; font-size: 1.2rem; cursor: pointer; width: 100%; margin-top: 20px;}
        .preview { max-width: 100%; margin-top: 20px; border-radius: 10px; display: none; }
        #loader { display: none; margin-top: 20px; }
        .success-box { display: none; background: #27ae60; padding: 20px; border-radius: 10px; margin-top: 20px; }
    </style>
</head>
<body>
    <h2>Tomar Fotografía</h2>
    <p>Usa la cámara de tu teléfono para capturar la foto del cliente.</p>

    <!-- HTML5 Native Camera Capture -->
    <input type="file" id="cameraInput" accept="image/*" capture="environment" style="display: none;">
    <button class="btn" onclick="document.getElementById('cameraInput').click()">Abrir Cámara 📷</button>

    <img id="preview" class="preview">
    
    <div id="loader">Subiendo imagen, por favor espera... ⏳</div>
    <div id="success" class="success-box">¡Foto capturada y subida correctamente! Ya puedes cerrar esta pantalla. ✅</div>

    <script>
        const token = "<?= htmlspecialchars($_GET['token'] ?? '') ?>";

        document.getElementById('cameraInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Mostrar preview
            const img = document.getElementById('preview');
            img.src = URL.createObjectURL(file);
            img.style.display = 'block';

            // Convertir a Base64 y subir
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function () {
                const base64String = reader.result;
                uploadPhoto(base64String);
            };
        });

        function uploadPhoto(base64Data) {
            document.getElementById('loader').style.display = 'block';
            const formData = new FormData();
            formData.append('token', token);
            formData.append('foto', base64Data);

            fetch('controllers/PhotoController.php?action=upload', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('loader').style.display = 'none';
                if(data.success) {
                    document.getElementById('success').style.display = 'block';
                } else {
                    alert("Error al subir la foto.");
                }
            })
            .catch(err => {
                document.getElementById('loader').style.display = 'none';
                alert("Error de red.");
            });
        }
    </script>
</body>
</html>

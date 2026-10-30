<div class="container-fluid py-3">
    <div class="row">
        <!-- Columna Izquierda: Entrada Manual / Scanner DNI -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 pb-0 text-center">
                    <h4 class="fw-bold text-primary mb-1"><i class="fas fa-fingerprint me-2"></i>Control de Acceso</h4>
                    <p class="text-muted small">Lector biométrico activo y check-in manual</p>
                </div>
                <div class="card-body p-4 text-center">
                    <form id="form-checkin" class="mb-4">
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold">DNI del Cliente</label>
                            <input type="text" id="identidad_input" name="identidad" class="form-control form-control-lg text-center fw-bold fs-3" placeholder="0000-0000-00000" autocomplete="off" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm" id="btn-checkin">
                            <i class="fas fa-check-circle me-2"></i>Verificar Acceso
                        </button>
                    </form>

                    <!-- Alerta de Resultado de Acceso -->
                    <div id="resultado-card" class="card border-0 shadow-sm d-none" style="border-radius: 15px; overflow: hidden;">
                        <div id="resultado-header" class="card-header text-white p-3">
                            <h4 id="resultado-mensaje" class="fw-bold mb-0">ACCESO PERMITIDO</h4>
                        </div>
                        <div class="card-body p-4 text-center">
                            <img id="resultado-foto" src="" class="rounded-circle shadow-sm mb-3" style="width: 130px; height: 130px; object-fit: cover; display: none;">
                            <div id="resultado-icono" class="display-3 text-secondary mb-3"><i class="fas fa-user-circle"></i></div>
                            
                            <h4 id="resultado-nombre" class="fw-bold mb-2">Nombre</h4>
                            
                            <div id="resultado-detalles">
                                <span class="badge bg-primary fs-6 mb-1" id="resultado-plan">Plan</span><br>
                                <small class="text-muted">Vence: <strong id="resultado-vence" class="text-dark"></strong></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Panel de Ingresos en Tiempo Real -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 pb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-0"><i class="fas fa-history text-success me-2"></i>Últimos Ingresos</h4>
                        <small class="text-muted">Actualización automática en tiempo real</small>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill">
                        <i class="fas fa-circle text-success me-1 fa-beat-fade"></i> Enlace Biométrico En Vivo
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 50px;">Foto</th>
                                    <th>Miembro</th>
                                    <th>DNI</th>
                                    <th>Plan</th>
                                    <th class="text-end">Hora Ingreso</th>
                                </tr>
                            </thead>
                            <tbody id="live-table-body">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Cargando ingresos...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let checkinMask;
    let lastTopId = 0;

    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('identidad_input');
        if (typeof IMask !== 'undefined') {
            checkinMask = IMask(input, { mask: '0000-0000-00000' });
        }
        input.focus();

        // Cargar ingresos en vivo y mantener sondeo cada 2.5 segundos
        loadRecentAttendance();
        setInterval(loadRecentAttendance, 2500);
    });

    function loadRecentAttendance() {
        fetch('controllers/AsistenciaController.php?action=list_recent')
            .then(r => r.json())
            .then(res => {
                if (!res.success || !res.data) return;
                
                const tbody = document.getElementById('live-table-body');
                if (res.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No hay ingresos registrados hoy.</td></tr>';
                    return;
                }

                // Detectar si llego un nuevo registro para mostrar alerta visual
                const latest = res.data[0];
                if (lastTopId !== 0 && latest.id_asistencia !== lastTopId) {
                    showAccessAlert(latest);
                }
                lastTopId = latest.id_asistencia;

                let html = '';
                res.data.forEach(item => {
                    const photoHtml = item.tiene_foto == 1
                        ? `<img src="controllers/ClientController.php?action=photo&id_cliente=${item.id_cliente}&thumb=true" class="rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;">`
                        : `<div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-user"></i></div>`;
                    
                    const horaServidor = item.fecha_servidor || '-';
                    const horaBiometrico = item.fecha_reloj ? `<small class="text-muted d-block" style="font-size: 0.75rem;"><i class="fas fa-fingerprint me-1"></i>Bio: ${item.fecha_reloj.substring(11)}</small>` : '';

                    html += `
                        <tr class="align-middle">
                            <td>${photoHtml}</td>
                            <td>
                                <strong class="d-block">${item.nombre_completo}</strong>
                                <small class="text-muted">ID Bio: #${item.id_cliente}</small>
                            </td>
                            <td><span class="badge bg-light text-dark border">${item.identidad || '-'}</span></td>
                            <td><span class="badge bg-info text-dark">${item.plan_nombre || 'Membresía'}</span></td>
                            <td class="text-end">
                                <span class="fw-bold text-success"><i class="far fa-clock me-1"></i>${horaServidor}</span>
                                ${horaBiometrico}
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            })
            .catch(err => console.error(err));
    }

    function showAccessAlert(data) {
        const resCard = document.getElementById('resultado-card');
        const resHeader = document.getElementById('resultado-header');
        const resMsg = document.getElementById('resultado-mensaje');
        const resNombre = document.getElementById('resultado-nombre');
        const resFoto = document.getElementById('resultado-foto');
        const resIcono = document.getElementById('resultado-icono');
        const resPlan = document.getElementById('resultado-plan');
        const resVence = document.getElementById('resultado-vence');

        resHeader.className = 'card-header text-white p-3 bg-success';
        resMsg.textContent = '¡ACCESO PERMITIDO!';
        resNombre.textContent = data.nombre_completo;
        resPlan.textContent = data.plan_nombre || 'Membresía Activa';
        resVence.textContent = data.fecha_hora;

        if (data.tiene_foto == 1) {
            resFoto.src = `controllers/ClientController.php?action=photo&id_cliente=${data.id_cliente}&t=${new Date().getTime()}`;
            resFoto.style.display = 'inline-block';
            resIcono.style.display = 'none';
        } else {
            resFoto.style.display = 'none';
            resIcono.style.display = 'block';
        }

        resCard.classList.remove('d-none');
        playBeep(800, 200, 'sine');

        setTimeout(() => {
            resCard.classList.add('d-none');
        }, 5000);
    }

    // Formulario Manual
    document.getElementById('form-checkin').addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('identidad_input');
        const btn = document.getElementById('btn-checkin');
        const fd = new FormData(this);
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';

        fetch('controllers/AsistenciaController.php?action=check_in', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Verificar Acceso';
            input.value = '';
            input.focus();

            const resCard = document.getElementById('resultado-card');
            const resHeader = document.getElementById('resultado-header');
            const resMsg = document.getElementById('resultado-mensaje');
            const resNombre = document.getElementById('resultado-nombre');
            const resFoto = document.getElementById('resultado-foto');
            const resIcono = document.getElementById('resultado-icono');
            const resDetalles = document.getElementById('resultado-detalles');

            resCard.classList.remove('d-none');
            resHeader.className = `card-header text-white p-3 bg-${res.status_color || 'danger'}`;
            resMsg.textContent = res.message;
            resNombre.textContent = res.cliente || 'Desconocido';

            if (res.tiene_foto == 1) {
                resFoto.src = `controllers/ClientController.php?action=photo&id_cliente=${res.id_cliente}&t=${new Date().getTime()}`;
                resFoto.style.display = 'inline-block';
                resIcono.style.display = 'none';
            } else {
                resFoto.style.display = 'none';
                resIcono.style.display = 'block';
            }

            if (res.success) {
                resDetalles.style.display = 'block';
                document.getElementById('resultado-plan').textContent = res.plan;
                document.getElementById('resultado-vence').textContent = res.vence;
                playBeep(800, 200, 'sine');
                loadRecentAttendance();
            } else {
                resDetalles.style.display = 'none';
                playBeep(300, 500, 'sawtooth');
            }

            setTimeout(() => {
                resCard.classList.add('d-none');
            }, 5000);
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Verificar Acceso';
            console.error(err);
        });
    });

    function playBeep(frequency, duration, type) {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            oscillator.type = type;
            oscillator.frequency.value = frequency;
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            oscillator.start();
            setTimeout(() => oscillator.stop(), duration);
        } catch(e) {}
    }
</script>

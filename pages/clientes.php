<div class="table-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <input type="text" id="search" class="form-control" placeholder="Buscar miembro..." style="width: 250px; display: inline-block;">
            <select id="filter-status" class="form-select ms-2" style="width: auto; display: inline-block;">
                <option value="1">Activos</option>
                <option value="0">Inactivos (Bajas)</option>
            </select>
        </div>
        <?php if(hasPerm('clientes_create')): ?>
        <button class="btn btn-primary" onclick="openModal()">+ Nuevo Miembro</button>
        <?php endif; ?>
    </div>

    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th style="width: 80px;">ID Bio</th>
                <th>Foto</th>
                <th>Identidad</th>
                <th>Nombre Completo</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="clients-tbody">
            <!-- JS Injection -->
        </tbody>
    </table>
</div>

<!-- Modal Formulario -->
<div class="modal fade" id="clientModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="client-form">
          <div class="modal-header">
            <h5 class="modal-title" id="modal-title">Registrar Miembro</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" id="clie-id" name="id_cliente">
              <input type="hidden" id="clie-fotografia" name="fotografia">
              
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label>DNI / Identidad <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="clie-identidad" name="identidad" required pattern="[a-zA-Z0-9\-]+" maxlength="20" title="Debe contener solo letras, números o guiones">
                      <div id="identidad-feedback" class="small mt-1"></div>
                  </div>
                  <div class="col-md-6 mb-3">
                      <label>Nombre Completo <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="clie-nombre" name="nombre_completo" required>
                  </div>
                  <div class="col-md-6 mb-3">
                      <label>Teléfono <span class="text-danger">*</span></label>
                      <input type="tel" class="form-control" id="clie-telefono" name="telefono" required pattern="\+?[0-9\-\s]+" maxlength="15" title="Número telefónico válido, puede incluir '+' al inicio">
                  </div>
                  <div class="col-md-6 mb-3">
                      <label>Correo Electrónico</label>
                      <input type="email" class="form-control" id="clie-email" name="email">
                  </div>
                  <div class="col-12 mb-3">
                      <label>Dirección</label>
                      <textarea class="form-control" id="clie-direccion" name="direccion"></textarea>
                  </div>
              </div>

              <hr>
              <h6>Fotografía del Cliente</h6>
              <div class="d-flex gap-2 mb-2">
                  <button type="button" class="btn btn-secondary btn-sm" onclick="startQrCapture()">
                      <i class="fas fa-qrcode"></i> Tomar Foto con el Teléfono (QR)
                  </button>
                  <label class="btn btn-outline-secondary btn-sm mb-0" style="cursor: pointer;">
                      <i class="fas fa-upload"></i> Subir desde la PC
                      <input type="file" accept="image/*" class="d-none" id="file-upload-photo" onchange="handleFileUpload(event)">
                  </label>
              </div>
              
              <div id="qr-container" class="qr-container">
                  <p class="text-muted small">Escanea este código con tu celular para abrir la cámara.</p>
                  <div id="qrcode"></div>
                  <p id="qr-status" class="mt-2 text-primary"><i class="fas fa-spinner fa-spin"></i> Esperando foto...</p>
              </div>
              
              <div>
                  <img id="photo-preview" class="preview-img">
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btn-save">Guardar Miembro</button>
          </div>
      </form>
    </div>
  </div>
</div>


<!-- Modal Perfil -->
<div class="modal fade" id="profileModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="fas fa-folder-open"></i> Expediente del Cliente: <span id="profile-name"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
          <ul class="nav nav-tabs px-3 pt-3 bg-light" id="profileTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#prof-membresias">Historial Membresías</button></li>
            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#prof-pagos">Pagos Registrados</button></li>
            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#prof-asistencia">Registro Asistencia</button></li>
          </ul>
          
          <div class="tab-content p-4" style="min-height: 400px; max-height: 60vh; overflow-y: auto;">
              <!-- Membresías -->
              <div class="tab-pane fade show active" id="prof-membresias">
                  <div id="prof-membresias-content">Cargando...</div>
              </div>
              
              <!-- Pagos -->
              <div class="tab-pane fade" id="prof-pagos">
                  <div id="prof-pagos-content">Cargando...</div>
              </div>
              
              <!-- Asistencia -->
              <div class="tab-pane fade" id="prof-asistencia">
                  <div id="prof-asistencia-content">Cargando...</div>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>

<script>
    let profileModalInstance;
    document.addEventListener('DOMContentLoaded', () => {
        profileModalInstance = new bootstrap.Modal(document.getElementById('profileModal'));
    });

    function viewProfile(id_cliente, nombre) {
        document.getElementById('profile-name').textContent = nombre;
        document.getElementById('prof-membresias-content').innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
        document.getElementById('prof-pagos-content').innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
        document.getElementById('prof-asistencia-content').innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
        
        profileModalInstance.show();

        fetch(`controllers/ClientController.php?action=profile&id_cliente=${id_cliente}`)
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    renderMembresias(res.membresias);
                    renderPagos(res.pagos);
                    renderAsistencia(res.asistencias);
                } else {
                    document.getElementById('prof-membresias-content').innerHTML = `<div class="alert alert-danger">${res.message}</div>`;
                }
            });
    }

    function renderMembresias(arr) {
        if(!arr.length) return document.getElementById('prof-membresias-content').innerHTML = '<p class="text-muted">No hay historial de membresías.</p>';
        let html = '<table class="table table-bordered"><thead><tr><th>Plan</th><th>Fechas</th><th>Estado</th></tr></thead><tbody>';
        arr.forEach(m => {
            html += `<tr>
                <td>${m.nombre_plan} <br><small class="text-muted">Precio Base: L ${m.precio}</small></td>
                <td>Inicio: ${m.fecha_inicio} <br> Fin: ${m.fecha_fin}</td>
                <td><span class="badge bg-${m.estado_membresia === 'Activa' ? 'success' : (m.estado_membresia === 'Vencida' ? 'danger' : 'secondary')}">${m.estado_membresia}</span></td>
            </tr>`;
        });
        html += '</tbody></table>';
        document.getElementById('prof-membresias-content').innerHTML = html;
    }

    function renderPagos(arr) {
        if(!arr.length) return document.getElementById('prof-pagos-content').innerHTML = '<p class="text-muted">No hay pagos registrados.</p>';
        let html = '<table class="table table-bordered"><thead><tr><th>Fecha</th><th>Concepto</th><th>Monto</th></tr></thead><tbody>';
        arr.forEach(p => {
            html += `<tr>
                <td>${p.fecha}</td>
                <td>${p.concepto}</td>
                <td class="text-success fw-bold">+ L ${p.monto}</td>
            </tr>`;
        });
        html += '</tbody></table>';
        document.getElementById('prof-pagos-content').innerHTML = html;
    }

    function renderAsistencia(arr) {
        if(!arr.length) return document.getElementById('prof-asistencia-content').innerHTML = '<p class="text-muted">No hay registros de asistencia.</p>';
        let html = '<div class="row">';
        arr.forEach(a => {
            html += `<div class="col-md-3 col-6 mb-3">
                <div class="card bg-light border-0 shadow-sm text-center p-3">
                    <i class="fas fa-check-circle text-success fs-3 mb-2"></i>
                    <small class="fw-bold">${a.fecha_hora}</small>
                </div>
            </div>`;
        });
        html += '</div>';
        document.getElementById('prof-asistencia-content').innerHTML = html;
    }
    let clients = [];
    let pollingInterval = null;
    let modal;
    let maskDni, maskTelefono;

    document.addEventListener('DOMContentLoaded', () => {
        modal = new bootstrap.Modal(document.getElementById('clientModal'));
        
        // Initialize masks
        maskDni = IMask(document.getElementById('clie-identidad'), { mask: '0000-0000-00000' });
        maskTelefono = IMask(document.getElementById('clie-telefono'), { mask: '0000-0000' });

        loadClients();
        document.getElementById('filter-status').addEventListener('change', loadClients);
        document.getElementById('search').addEventListener('input', renderTable);
    });

    function loadClients() {
        const activeVal = document.getElementById('filter-status').value;
        fetch(`controllers/ClientController.php?action=list&active=${activeVal}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    clients = data.data;
                    renderTable();
                }
            });
    }

    function renderTable() {
        const filter = document.getElementById('search').value.toLowerCase();
        const tbody = document.getElementById('clients-tbody');
        tbody.innerHTML = '';

        const filtered = clients.filter(c => 
            c.nombre_completo.toLowerCase().includes(filter) || 
            (c.identidad && c.identidad.toLowerCase().includes(filter))
        );

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">No se encontraron miembros.</td></tr>`;
            return;
        }

        filtered.forEach(c => {
            const photoUrl = `controllers/ClientController.php?action=photo&id_cliente=${c.id_cliente}&thumb=true`;
            const fullPhotoUrl = `controllers/ClientController.php?action=photo&id_cliente=${c.id_cliente}`;
            const imgTag = c.tiene_foto == 1 
                ? `<div class="position-relative d-inline-block" style="width:40px; height:40px; cursor: pointer;" onclick="previewPhoto('${fullPhotoUrl}', '${c.nombre_completo.replace(/'/g, "\\'")}', '${c.identidad || '-'}', '${c.telefono || '-'}')">
                       <div class="placeholder-glow w-100 h-100 position-absolute top-0 start-0 rounded-circle" id="pl-${c.id_cliente}">
                           <span class="placeholder col-12 h-100 rounded-circle"></span>
                       </div>
                       <img src="${photoUrl}" class="client-photo-sm position-absolute top-0 start-0 border shadow-sm" style="opacity:0; transition: opacity 0.3s;" onload="this.style.opacity=1; document.getElementById('pl-${c.id_cliente}')?.remove();">
                   </div>`
                : `<div class="client-photo-sm bg-secondary text-white d-flex align-items-center justify-content-center border shadow-sm"><i class="fas fa-user"></i></div>`;
            const isActive = c.estado == 1;
            
            let btns = '';
            <?php if(hasPerm('clientes_edit')): ?>
            btns += `<button class="btn btn-sm btn-info text-white me-1" onclick="editClient(${c.id_cliente})"><i class="fas fa-edit"></i></button>`;
            <?php endif; ?>
            
            <?php if(hasPerm('clientes_delete')): ?>
            if (isActive) {
                btns += `<button class="btn btn-sm btn-danger me-1" title="Dar de baja" onclick="changeState(${c.id_cliente}, 'deactivate')"><i class="fas fa-user-slash"></i></button>`;
            } else {
                btns += `<button class="btn btn-sm btn-success me-1" title="Reactivar" onclick="changeState(${c.id_cliente}, 'activate')"><i class="fas fa-check"></i></button>`;
            }
            <?php endif; ?>
            
            // Add Profile button
            btns += `<button class="btn btn-sm btn-secondary" title="Ver Expediente" onclick="viewProfile(${c.id_cliente}, '${c.nombre_completo.replace(/'/g, "\\'")}')"><i class="fas fa-id-badge"></i></button>`;

            let displayDni = (c.identidad || '-').replace(/[^0-9]/g, '');
            if (displayDni.length === 13) {
                displayDni = displayDni.replace(/(\d{4})(\d{4})(\d{5})/, '$1-$2-$3');
            } else if (!displayDni) { displayDni = '-'; } else { displayDni = c.identidad; }

            let displayTel = (c.telefono || '-').replace(/[^0-9]/g, '');
            if (displayTel.length === 8) {
                displayTel = displayTel.replace(/(\d{4})(\d{4})/, '$1-$2');
            } else if (!displayTel) { displayTel = '-'; } else { displayTel = c.telefono; }

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td><span class="badge bg-dark fs-6">#${c.id_cliente}</span></td>
                <td>${imgTag}</td>
                    <td>${displayDni}</td>
                    <td class="fw-bold">${c.nombre_completo}</td>
                    <td>${displayTel}</td>
                    <td><span class="badge bg-${isActive ? 'success' : 'secondary'}">${isActive ? 'Activo' : 'Inactivo'}</span></td>
                    <td>${btns}</td>
                </tr>
            `);
        });
    }

    function openModal() {
        document.getElementById('client-form').reset();
        document.getElementById('clie-id').value = '';
        document.getElementById('clie-fotografia').value = '';
        document.getElementById('photo-preview').style.display = 'none';
        document.getElementById('qr-container').style.display = 'none';
        document.getElementById('modal-title').textContent = 'Registrar Nuevo Miembro';
        document.getElementById('identidad-feedback').innerHTML = '';
        document.getElementById('btn-save').disabled = false;
        stopPolling();
        modal.show();
    }

    function editClient(id) {
        const c = clients.find(x => x.id_cliente == id);
        if (!c) return;
        openModal();
        document.getElementById('modal-title').textContent = 'Editar Miembro';
        document.getElementById('clie-id').value = c.id_cliente;
        
        maskDni.value = c.identidad || '';
        document.getElementById('clie-nombre').value = c.nombre_completo || '';
        maskTelefono.value = c.telefono || '';
        
        document.getElementById('clie-email').value = c.email || '';
        document.getElementById('clie-direccion').value = c.direccion || '';
        document.getElementById('identidad-feedback').innerHTML = '';
        document.getElementById('btn-save').disabled = false;
        
        if (c.tiene_foto == 1) {
            // Do NOT populate clie-fotografia input so we don't resend the base64 string unless changed
            const timestamp = new Date().getTime();
            document.getElementById('photo-preview').src = `controllers/ClientController.php?action=photo&id_cliente=${c.id_cliente}&t=${timestamp}`;
            document.getElementById('photo-preview').style.display = 'block';
        }
    }

    document.getElementById('client-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        fetch('controllers/ClientController.php?action=save', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            btn.textContent = 'Guardar Miembro';
            if(res.success) {
                modal.hide();
                Swal.fire('¡Éxito!', res.message, 'success');
                loadClients();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
    });

    function changeState(id, actionStr) {
        Swal.fire({
            title: actionStr === 'deactivate' ? '¿Dar de baja a este cliente?' : '¿Reactivar cliente?',
            text: "Podrás cambiar su estado en el futuro.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('id_cliente', id);
                fetch(`controllers/ClientController.php?action=${actionStr}`, { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(res => {
                        if(res.success) {
                            Swal.fire('¡Hecho!', res.message, 'success');
                            loadClients();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    });
            }
        });
    }

    let currentToken = '';
    function startQrCapture() {
        currentToken = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
        const qrBox = document.getElementById('qrcode');
        qrBox.innerHTML = '';
        
        let currentUrl = window.location.href.split('?')[0];
        currentUrl = currentUrl.replace(/index\.php$/, '').replace(/login\.php$/, '');
        const captureUrl = currentUrl + 'capture.php?token=' + currentToken;
        
        new QRCode(qrBox, {
            text: captureUrl,
            width: 150,
            height: 150
        });

        document.getElementById('qr-container').style.display = 'block';
        document.getElementById('qr-status').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Esperando foto desde el celular...';
        
        stopPolling();
        pollingInterval = setInterval(checkPhoto, 2000);
    }

    function checkPhoto() {
        fetch(`controllers/PhotoController.php?action=check&token=${currentToken}`)
            .then(r => r.json())
            .then(res => {
                if (res.success && res.foto) {
                    stopPolling();
                    document.getElementById('clie-fotografia').value = res.foto;
                    document.getElementById('photo-preview').src = res.foto;
                    document.getElementById('photo-preview').style.display = 'block';
                    document.getElementById('qr-status').innerHTML = '<i class="fas fa-check text-success"></i> Foto recibida correctamente.';
                    setTimeout(() => { document.getElementById('qr-container').style.display = 'none'; }, 2000);
                }
            })
            .catch(e => console.error(e));
    }

    function handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                const maxSize = 800;

                if (width > height && width > maxSize) {
                    height = Math.round(height * (maxSize / width));
                    width = maxSize;
                } else if (height > maxSize) {
                    width = Math.round(width * (maxSize / height));
                    height = maxSize;
                } else {
                    // Si es más pequeña que maxSize, no redimensionar
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                document.getElementById('clie-fotografia').value = dataUrl;
                document.getElementById('photo-preview').src = dataUrl;
                document.getElementById('photo-preview').style.display = 'block';
                
                stopPolling();
                document.getElementById('qr-container').style.display = 'none';
            }
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    function stopPolling() {
        if (pollingInterval) clearInterval(pollingInterval);
    }

    document.getElementById('clientModal').addEventListener('hidden.bs.modal', stopPolling);

    // Identity validation debounce
    let typingTimer;
    const doneTypingInterval = 500;
    const idInput = document.getElementById('clie-identidad');

    idInput.addEventListener('keyup', () => {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(checkIdentity, doneTypingInterval);
    });

    idInput.addEventListener('keydown', () => {
        clearTimeout(typingTimer);
    });

    function checkIdentity() {
        const idVal = idInput.value.trim();
        const feedback = document.getElementById('identidad-feedback');
        const btnSave = document.getElementById('btn-save');
        const currentId = document.getElementById('clie-id').value;

        if (idVal.length < 5) {
            feedback.innerHTML = '';
            btnSave.disabled = false;
            return;
        }

        const fd = new FormData();
        fd.append('identidad', idVal);
        if (currentId) fd.append('exclude_id', currentId);

        fetch('controllers/ClientController.php?action=checkIdentity', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.exists) {
                    feedback.innerHTML = `<span class="text-danger fw-bold"><i class="fas fa-exclamation-triangle"></i> Este DNI ya está registrado a nombre de: ${res.nombre} (Estado: ${res.estado})</span>`;
                    btnSave.disabled = true;
                } else {
                    feedback.innerHTML = `<span class="text-success"><i class="fas fa-check-circle"></i> Identidad disponible.</span>`;
                    btnSave.disabled = false;
                }
            });
    }
</script>

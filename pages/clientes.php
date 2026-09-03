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
                      <label>DNI / Identidad</label>
                      <input type="text" class="form-control" id="clie-identidad" name="identidad">
                  </div>
                  <div class="col-md-6 mb-3">
                      <label>Nombre Completo *</label>
                      <input type="text" class="form-control" id="clie-nombre" name="nombre_completo" required>
                  </div>
                  <div class="col-md-6 mb-3">
                      <label>Teléfono</label>
                      <input type="text" class="form-control" id="clie-telefono" name="telefono">
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
              <button type="button" class="btn btn-secondary btn-sm" onclick="startQrCapture()">
                  <i class="fas fa-qrcode"></i> Tomar Foto con el Teléfono (QR)
              </button>
              
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


<script>
    let clients = [];
    let pollingInterval = null;
    let modal;

    document.addEventListener('DOMContentLoaded', () => {
        modal = new bootstrap.Modal(document.getElementById('clientModal'));
        loadClients();
        document.getElementById('filter-status').addEventListener('change', loadClients);
        document.getElementById('search').addEventListener('input', renderTable);
    });

    function loadClients() {
        const active = document.getElementById('filter-status').value === '1';
        fetch(`controllers/ClientController.php?action=list&active=${active}`)
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
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No se encontraron miembros.</td></tr>`;
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
                btns += `<button class="btn btn-sm btn-danger" onclick="changeState(${c.id_cliente}, 'deactivate')"><i class="fas fa-user-slash"></i></button>`;
            } else {
                btns += `<button class="btn btn-sm btn-success" onclick="changeState(${c.id_cliente}, 'activate')"><i class="fas fa-check"></i></button>`;
            }
            <?php endif; ?>

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${imgTag}</td>
                    <td>${c.identidad || '-'}</td>
                    <td class="fw-bold">${c.nombre_completo}</td>
                    <td>${c.telefono || '-'}</td>
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
        stopPolling();
        modal.show();
    }

    function editClient(id) {
        const c = clients.find(x => x.id_cliente == id);
        if (!c) return;
        openModal();
        document.getElementById('modal-title').textContent = 'Editar Miembro';
        document.getElementById('clie-id').value = c.id_cliente;
        document.getElementById('clie-identidad').value = c.identidad || '';
        document.getElementById('clie-nombre').value = c.nombre_completo || '';
        document.getElementById('clie-telefono').value = c.telefono || '';
        document.getElementById('clie-email').value = c.email || '';
        document.getElementById('clie-direccion').value = c.direccion || '';
        
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

    function stopPolling() {
        if (pollingInterval) clearInterval(pollingInterval);
    }

    document.getElementById('clientModal').addEventListener('hidden.bs.modal', stopPolling);
</script>

<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="text-primary fw-bold"><i class="fas fa-id-card"></i> Asignación de Membresías</h4>
        <p class="text-muted">Inscriba clientes en planes de gimnasio o renueve suscripciones vencidas.</p>
    </div>
    <div class="col-md-4 text-end">
        <?php if(hasPerm('membresias_create')): ?>
        <button class="btn btn-lg btn-success shadow-sm" onclick="openAssignModal()"><i class="fas fa-plus"></i> Asignar Plan a Cliente</button>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-4">
                <input type="text" id="search-memb" class="form-control form-control-lg" placeholder="Buscar por DNI o Nombre de Miembro...">
            </div>
            <div class="col-md-3">
                <select id="filter-status" class="form-select form-select-lg">
                    <option value="todas">Todas las Membresías</option>
                    <option value="activas" selected>Solo Activas</option>
                    <option value="vencidas">Vencidas / Por vencer</option>
                    <option value="canceladas">Canceladas</option>
                </select>
            </div>
        </div>

        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Cliente</th>
                    <th>Plan Asignado</th>
                    <th>Fechas (Inicio - Fin)</th>
                    <th>Estado de Días</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="membresias-tbody">
                <!-- JS Injection -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Asignar Plan -->
<div class="modal fade" id="assignModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content shadow-lg border-0">
      <form id="assign-form">
          <div class="modal-header">
            <h5 class="modal-title text-primary"><i class="fas fa-id-card"></i> Asignar Nuevo Plan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <div class="mb-4">
                  <label class="form-label fw-bold">Seleccionar Cliente <span class="text-danger">*</span></label>
                  <div class="input-group">
                      <input type="hidden" id="assign-cliente-id" name="id_cliente">
                      <input type="text" class="form-control" id="assign-cliente-name" placeholder="Ningún cliente seleccionado" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                      <button class="btn btn-primary" type="button" onclick="openSearchClientModal()">
                          <i class="fas fa-search"></i> Buscar Cliente
                      </button>
                  </div>
              </div>
              <div class="mb-3">
                  <label>Seleccionar Plan <span class="text-danger">*</span></label>
                  <select class="form-select" id="assign-plan" name="id_plan" required>
                      <!-- Populated via JS -->
                  </select>
              </div>
              <div class="mb-3">
                  <label>Fecha de Inicio <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="assign-fecha" name="fecha_inicio" required value="<?php echo date('Y-m-d'); ?>">
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Asignar Membresía</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar Membresía (Solo Pendientes) -->
<div class="modal fade" id="editMembershipModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content shadow-lg border-0">
      <form id="edit-memb-form">
          <input type="hidden" id="edit-memb-id" name="id_membresia">
          <div class="modal-header">
            <h5 class="modal-title text-primary"><i class="fas fa-edit"></i> Editar Membresía Pendiente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <div class="mb-4">
                  <label class="form-label fw-bold">Cliente</label>
                  <input type="text" class="form-control" id="edit-memb-cliente-name" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
              </div>
              <div class="mb-3">
                  <label>Seleccionar Plan <span class="text-danger">*</span></label>
                  <select class="form-select" id="edit-memb-plan" name="id_plan" required>
                      <!-- Populated via JS -->
                  </select>
              </div>
              <div class="mb-3">
                  <label>Fecha de Inicio <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="edit-memb-fecha" name="fecha_inicio" required>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
          </div>
      </form>
    </div>
  </div>
</div>
<!-- Modal Buscar Cliente -->
<div class="modal fade" id="searchClientModal" tabindex="-1" style="z-index: 1060;">
  <div class="modal-dialog modal-xl">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header">
        <h5 class="modal-title text-primary"><i class="fas fa-users"></i> Buscar Cliente</h5>
        <button type="button" class="btn-close" onclick="closeSearchClientModal()"></button>
      </div>
      <div class="modal-body p-4">
        <input type="text" id="search-client-input" class="form-control form-control-lg mb-3" placeholder="Buscar por Nombre o Identidad...">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-hover align-middle">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th>Identidad</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="search-clients-tbody">
                    <!-- Populated via JS -->
                </tbody>
            </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
    let membresias = [];
    let allClientsList = [];
    let assignModal;
    let searchClientModal;
    let editMembershipModal;

    document.addEventListener('DOMContentLoaded', () => {
        assignModal = new bootstrap.Modal(document.getElementById('assignModal'));
        searchClientModal = new bootstrap.Modal(document.getElementById('searchClientModal'));
        editMembershipModal = new bootstrap.Modal(document.getElementById('editMembershipModal'));
        
        loadMembresias();

        document.getElementById('search-memb').addEventListener('input', renderMembresias);
        document.getElementById('filter-status').addEventListener('change', loadMembresias);
    });

    function loadMembresias() {
        const status = document.getElementById('filter-status').value;
        fetch(`controllers/MembershipController.php?action=list&status=${status}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    membresias = data.data;
                    renderMembresias();
                } else {
                    console.error("Error API:", data);
                    const tbody = document.getElementById('membresias-tbody');
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Error: ${data.message}</td></tr>`;
                }
            })
            .catch(err => {
                console.error("Error Fetch:", err);
                const tbody = document.getElementById('membresias-tbody');
                tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Error de conexión o parseo. Revisa la consola.</td></tr>`;
            });
    }

    function renderMembresias() {
        const tbody = document.getElementById('membresias-tbody');
        tbody.innerHTML = '';
        const filter = document.getElementById('search-memb').value.toLowerCase();

        const filtered = membresias.filter(m => 
            m.nombre_completo.toLowerCase().includes(filter) || 
            (m.identidad && String(m.identidad).toLowerCase().includes(filter))
        );

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No se encontraron membresías.</td></tr>`;
            return;
        }

        filtered.forEach(m => {
            let badgeColor = 'success';
            let statusText = m.estado_ui;
            let subText = `Faltan ${m.dias_restantes} días`;
            let progress = (m.dias_restantes / m.duracion_dias) * 100;
            if (progress > 100) progress = 100;
            if (progress < 0) progress = 0;

            if (m.estado_ui === 'Pendiente') {
                badgeColor = 'warning';
                subText = 'Pendiente de cobro en Caja';
                progress = 0;
            } else if (m.estado_ui === 'Por Vencer') {
                badgeColor = 'warning';
            } else if (m.estado_ui === 'Vencida') {
                badgeColor = 'danger';
                progress = 100;
                subText = `Hace ${Math.abs(m.dias_restantes)} días`;
            } else if (m.estado_ui === 'Cancelada') {
                badgeColor = 'dark';
                progress = 100;
                const fCanc = new Date(m.fecha_cancelacion).toLocaleDateString('es-HN');
                subText = `Cancelada el ${fCanc}`;
            }

            const photoUrl = `controllers/ClientController.php?action=photo&id_cliente=${m.id_cliente}&thumb=true`;
            const fullPhotoUrl = `controllers/ClientController.php?action=photo&id_cliente=${m.id_cliente}`;
            
            const imgTag = m.tiene_foto == 1 
                ? `<div class="position-relative d-inline-block" style="width:40px; height:40px; cursor: pointer;" onclick="previewPhoto('${fullPhotoUrl}', '${m.nombre_completo.replace(/'/g, "\\'")}', '${m.identidad || '-'}', '${m.telefono || '-'}', '${statusText}', '${badgeColor}', '${subText}')">
                       <img src="${photoUrl}" class="rounded-circle object-fit-cover w-100 h-100 border shadow-sm">
                   </div>`
                : `<div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded-circle border shadow-sm" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-user"></i></div>`;
            
            // Format dates
            const fInicio = new Date(m.fecha_inicio).toLocaleDateString('es-HN', { day: '2-digit', month: 'short', year: 'numeric' });
            const fFin = new Date(m.fecha_fin).toLocaleDateString('es-HN', { day: '2-digit', month: 'short', year: 'numeric' });

            let btns = '';
            
            <?php if(hasPerm('membresias_edit')): ?>
            if (m.estado_ui === 'Pendiente') {
                btns += `<button class="btn btn-sm btn-primary me-1" onclick="editMembership(${m.id_membresia})" title="Editar Plan"><i class="fas fa-edit"></i></button>`;
            }
            if (m.estado_ui !== 'Cancelada') {
                btns += `<a href="index.php?page=caja&id_cliente=${m.id_cliente}" class="btn btn-sm btn-success me-1" title="Cobrar / Renovar en Caja"><i class="fas fa-cash-register"></i></a>`;
            }
            <?php endif; ?>
            
            <?php if(hasPerm('membresias_delete')): ?>
            if (m.estado_ui !== 'Cancelada') {
                btns += `<button class="btn btn-sm btn-danger" onclick="cancelMembership(${m.id_membresia})" title="Eliminar/Cancelar"><i class="fas fa-trash"></i></button>`;
            }
            <?php endif; ?>

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="me-3">${imgTag}</div>
                            <div>
                                <h6 class="mb-0 fw-bold">${m.nombre_completo}</h6>
                                <small class="text-muted">ID: ${m.identidad || 'N/A'}</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-dark">${m.nombre_plan}</span></td>
                    <td>
                        <small><i class="fas fa-calendar-alt"></i> ${fInicio}<br><i class="fas fa-calendar-check"></i> ${fFin}</small>
                    </td>
                    <td style="width: 25%;">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-bold text-${badgeColor}">${statusText}</small>
                            <small class="text-muted">${subText}</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-${badgeColor}" role="progressbar" style="width: ${progress}%;"></div>
                        </div>
                    </td>
                    <td>${btns}</td>
                </tr>
            `);
        });
    }

    function openAssignModal() {
        // Load clients and plans
        fetch('controllers/MembershipController.php?action=getFormData')
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    const selectP = document.getElementById('assign-plan');
                    selectP.innerHTML = '<option value="">Seleccione un plan...</option>';
                    res.plans.forEach(p => {
                        selectP.innerHTML += `<option value="${p.id_plan}">${p.nombre_plan} - L ${parseFloat(p.precio).toFixed(2)}</option>`;
                    });

                    document.getElementById('assign-form').reset();
                    document.getElementById('assign-cliente-id').value = '';
                    document.getElementById('assign-cliente-name').value = '';
                    document.getElementById('assign-fecha').value = new Date().toISOString().split('T')[0];
                    assignModal.show();
                }
            });
    }

    document.getElementById('assign-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const idCliente = document.getElementById('assign-cliente-id').value;
        if (!idCliente) {
            Swal.fire('Atención', 'Debe seleccionar un cliente primero utilizando el botón de Buscar Cliente.', 'warning');
            return;
        }

        fetch('controllers/MembershipController.php?action=assign', {
            method: 'POST',
            body: new FormData(this)
        }).then(r => r.json()).then(res => {
            if(res.success) {
                assignModal.hide();
                Swal.fire('¡Éxito!', res.message, 'success');
                loadMembresias();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
    });

    function renewMembership(id) {
        Swal.fire({
            title: '¿Renovar membresía?',
            text: "Se añadirá el tiempo del plan a la fecha actual o fecha de vencimiento.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Sí, renovar'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('id_membresia', id);
                fetch('controllers/MembershipController.php?action=renew', { method: 'POST', body: fd })
                    .then(r => r.json()).then(res => {
                        if(res.success) {
                            Swal.fire('¡Renovado!', res.message, 'success');
                            loadMembresias();
                        } else Swal.fire('Error', res.message, 'error');
                    });
            }
        });
    }

    function cancelMembership(id) {
        Swal.fire({
            title: '¿Cancelar membresía?',
            text: "Esta acción marcará la membresía como Cancelada y registrará la fecha de hoy.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('id_membresia', id);
                fetch('controllers/MembershipController.php?action=cancel', { method: 'POST', body: fd })
                    .then(r => r.json()).then(res => {
                        if(res.success) {
                            Swal.fire('¡Cancelada!', res.message, 'success');
                            loadMembresias();
                        } else Swal.fire('Error', res.message, 'error');
                    });
            }
        });
    }

    function openSearchClientModal() {
        assignModal.hide();
        searchClientModal.show();
        document.getElementById('search-clients-tbody').innerHTML = '<tr><td colspan="5" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i><br>Cargando clientes...</td></tr>';
        
        fetch('controllers/ClientController.php?action=list&active=all')
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    allClientsList = data.data;
                    renderSearchClients();
                }
            });
    }

    function closeSearchClientModal() {
        searchClientModal.hide();
    }

    document.getElementById('searchClientModal').addEventListener('hidden.bs.modal', function () {
        assignModal.show();
    });

    document.getElementById('search-client-input').addEventListener('input', renderSearchClients);

    function renderSearchClients() {
        const q = document.getElementById('search-client-input').value.toLowerCase();
        const tbody = document.getElementById('search-clients-tbody');
        tbody.innerHTML = '';

        const filtered = allClientsList.filter(c => 
            c.nombre_completo.toLowerCase().includes(q) || 
            (c.identidad && c.identidad.toLowerCase().includes(q))
        );

        if(filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No se encontraron clientes.</td></tr>';
            return;
        }

        filtered.forEach(c => {
            const photoUrl = `controllers/ClientController.php?action=photo&id_cliente=${c.id_cliente}&thumb=true`;
            const fullPhotoUrl = `controllers/ClientController.php?action=photo&id_cliente=${c.id_cliente}`;
            
            const imgTag = c.tiene_foto == 1 
                ? `<div class="position-relative d-inline-block" style="width:30px; height:30px; cursor: pointer;" onclick="previewPhoto('${fullPhotoUrl}', '${c.nombre_completo.replace(/'/g, "\\'")}', '${c.identidad || '-'}', '${c.telefono || '-'}')">
                       <img src="${photoUrl}" class="rounded-circle object-fit-cover w-100 h-100 border shadow-sm">
                   </div>`
                : `<div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded-circle border shadow-sm" style="width:30px;height:30px;font-size:12px;"><i class="fas fa-user"></i></div>`;
            
            const isActive = c.estado == 1;
            let actionBtn = isActive 
                ? `<button class="btn btn-sm btn-primary" onclick="selectClientForPlan(${c.id_cliente}, '${c.nombre_completo.replace(/'/g, "\\'")}')">Seleccionar</button>`
                : `<button class="btn btn-sm btn-warning" onclick="reactivateClientFromSearch(${c.id_cliente})"><i class="fas fa-sync"></i> Reactivar</button>`;

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${imgTag}</td>
                    <td class="fw-bold">${c.nombre_completo}</td>
                    <td>${c.identidad || '-'}</td>
                    <td><span class="badge bg-${isActive ? 'success' : 'secondary'}">${isActive ? 'Activo' : 'Inactivo'}</span></td>
                    <td>${actionBtn}</td>
                </tr>
            `);
        });
    }

    function selectClientForPlan(id, name) {
        document.getElementById('assign-cliente-id').value = id;
        document.getElementById('assign-cliente-name').value = name;
        closeSearchClientModal();
    }

    function reactivateClientFromSearch(id) {
        const fd = new FormData();
        fd.append('id_cliente', id);
        fetch('controllers/ClientController.php?action=activate', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    Swal.fire({icon: 'success', title: 'Reactivado', text: 'El cliente ha sido reactivado correctamente.', timer: 1500, showConfirmButton: false});
                    // Refresh list
                    fetch('controllers/ClientController.php?action=list&active=all')
                        .then(r => r.json())
                        .then(data => {
                            if(data.success) {
                                allClientsList = data.data;
                                renderSearchClients();
                            }
                        });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            });
    }

    function editMembership(idMembresia) {
        const memb = membresias.find(m => m.id_membresia == idMembresia);
        if(!memb) return;
        
        document.getElementById('edit-memb-id').value = memb.id_membresia;
        document.getElementById('edit-memb-cliente-name').value = memb.nombre_completo;
        document.getElementById('edit-memb-fecha').value = memb.fecha_inicio.split(' ')[0]; // ensure format YYYY-MM-DD
        
        // Fetch plans
        fetch('controllers/MembershipController.php?action=getFormData')
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    const selectP = document.getElementById('edit-memb-plan');
                    selectP.innerHTML = '';
                    res.plans.forEach(p => {
                        const selected = (p.id_plan == memb.id_plan) ? 'selected' : '';
                        selectP.innerHTML += `<option value="${p.id_plan}" ${selected}>${p.nombre_plan} - L ${parseFloat(p.precio).toFixed(2)}</option>`;
                    });
                    editMembershipModal.show();
                }
            });
    }

    document.getElementById('edit-memb-form').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('controllers/MembershipController.php?action=update', {
            method: 'POST',
            body: new FormData(this)
        }).then(r => r.json()).then(res => {
            if(res.success) {
                editMembershipModal.hide();
                Swal.fire('¡Actualizado!', res.message, 'success');
                loadMembresias();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
    });
</script>

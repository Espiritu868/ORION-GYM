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
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="assign-form">
          <div class="modal-header">
            <h5 class="modal-title">Asignar Nuevo Plan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <div class="mb-3">
                  <label>Seleccionar Cliente *</label>
                  <select class="form-select" id="assign-cliente" name="id_cliente" required>
                      <!-- Populated via JS -->
                  </select>
              </div>
              <div class="mb-3">
                  <label>Seleccionar Plan *</label>
                  <select class="form-select" id="assign-plan" name="id_plan" required>
                      <!-- Populated via JS -->
                  </select>
              </div>
              <div class="mb-3">
                  <label>Fecha de Inicio *</label>
                  <input type="date" class="form-control" id="assign-fecha" name="fecha_inicio" required value="<?php echo date('Y-m-d'); ?>">
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Asignar Membresía</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
    let membresias = [];
    let assignModal;

    document.addEventListener('DOMContentLoaded', () => {
        assignModal = new bootstrap.Modal(document.getElementById('assignModal'));
        
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
                }
            });
    }

    function renderMembresias() {
        const tbody = document.getElementById('membresias-tbody');
        tbody.innerHTML = '';
        const filter = document.getElementById('search-memb').value.toLowerCase();

        const filtered = membresias.filter(m => 
            m.nombre_completo.toLowerCase().includes(filter) || 
            (m.identidad && m.identidad.toLowerCase().includes(filter))
        );

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No se encontraron membresías.</td></tr>`;
            return;
        }

        filtered.forEach(m => {
            const imgTag = m.fotografia ? `<img src="${m.fotografia}" class="client-photo-sm">` : '<div class="client-photo-sm bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 50%;"><i class="fas fa-user"></i></div>';
            
            // Format dates
            const fInicio = new Date(m.fecha_inicio).toLocaleDateString('es-HN', { day: '2-digit', month: 'short', year: 'numeric' });
            const fFin = new Date(m.fecha_fin).toLocaleDateString('es-HN', { day: '2-digit', month: 'short', year: 'numeric' });

            let badgeColor = 'success';
            let statusText = m.estado_ui;
            let subText = `Faltan ${m.dias_restantes} días`;
            let progress = (m.dias_restantes / m.duracion_dias) * 100;
            if (progress > 100) progress = 100;
            if (progress < 0) progress = 0;

            if (m.estado_ui === 'Por Vencer') {
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

            let btns = '';
            <?php if(hasPerm('membresias_edit')): ?>
            if (m.estado_ui !== 'Cancelada') {
                btns += `<button class="btn btn-sm btn-primary me-1" onclick="renewMembership(${m.id_membresia})" title="Renovar"><i class="fas fa-sync"></i></button>`;
            }
            <?php endif; ?>
            
            <?php if(hasPerm('membresias_delete')): ?>
            if (m.estado_ui !== 'Cancelada') {
                btns += `<button class="btn btn-sm btn-danger" onclick="cancelMembership(${m.id_membresia})" title="Cancelar"><i class="fas fa-times"></i></button>`;
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
                    const selectC = document.getElementById('assign-cliente');
                    selectC.innerHTML = '<option value="">Seleccione un cliente...</option>';
                    res.clients.forEach(c => {
                        selectC.innerHTML += `<option value="${c.id_cliente}">${c.nombre_completo} (${c.identidad || 'Sin ID'})</option>`;
                    });

                    const selectP = document.getElementById('assign-plan');
                    selectP.innerHTML = '<option value="">Seleccione un plan...</option>';
                    res.plans.forEach(p => {
                        selectP.innerHTML += `<option value="${p.id_plan}">${p.nombre_plan} - L ${parseFloat(p.precio).toFixed(2)}</option>`;
                    });

                    document.getElementById('assign-form').reset();
                    document.getElementById('assign-fecha').value = new Date().toISOString().split('T')[0];
                    assignModal.show();
                }
            });
    }

    document.getElementById('assign-form').addEventListener('submit', function(e) {
        e.preventDefault();
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
</script>

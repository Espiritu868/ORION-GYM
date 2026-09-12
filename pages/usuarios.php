<div class="table-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <input type="text" id="search-user" class="form-control" placeholder="Buscar usuario..." style="width: 250px; display: inline-block;">
            <select id="filter-user-status" class="form-select ms-2" style="width: auto; display: inline-block;">
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>
        <button class="btn btn-primary" onclick="openUserModal()">+ Nuevo Usuario</button>
    </div>

    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Nombre de Usuario</th>
                <th>Permisos</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="users-tbody">
            <!-- JS Injection -->
        </tbody>
    </table>
</div>

<!-- Modal Formulario Usuario -->
<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="user-form">
          <div class="modal-header">
            <h5 class="modal-title" id="user-modal-title">Registrar Usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" id="user-id" name="id_usuario">
              
              <div class="mb-3">
                  <label>Nombre de Usuario <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="user-username" name="nombre_usuario" required autocomplete="off" pattern="[a-zA-Z0-9_]+" maxlength="30" title="Solo letras, números y guiones bajos, sin espacios">
              </div>

              <div class="mb-3">
                  <label>Contraseña <span class="text-danger" id="user-password-asterisk">*</span> <small class="text-muted" id="user-password-hint">(Obligatoria para nuevos)</small></label>
                  <input type="password" class="form-control" id="user-password" name="password" autocomplete="new-password" minlength="6" title="La contraseña debe tener al menos 6 caracteres">
              </div>

              <hr>
              <h6>Permisos de Acceso</h6>
              <div class="table-responsive">
                  <table class="table table-sm table-bordered text-center align-middle">
                      <thead class="table-light">
                          <tr>
                              <th class="text-start">Módulo</th>
                              <th>Ver</th>
                              <th>Crear</th>
                              <th>Editar</th>
                              <th>Eliminar</th>
                          </tr>
                      </thead>
                      <tbody>
                          <tr>
                              <td class="text-start">Dashboard</td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="dashboard_view"></td>
                              <td colspan="3" class="text-muted"><small>N/A</small></td>
                          </tr>
                          <tr>
                              <td class="text-start">Miembros</td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="clientes_view"></td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="clientes_create"></td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="clientes_edit"></td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="clientes_delete"></td>
                          </tr>
                          <tr>
                              <td class="text-start">Membresías</td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="membresias_view"></td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="membresias_create"></td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="membresias_edit"></td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="membresias_delete"></td>
                          </tr>
                          <tr>
                              <td class="text-start">Caja</td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="caja_view"></td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="caja_create"></td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="caja_edit"></td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="caja_delete"></td>
                          </tr>
                          <tr>
                              <td class="text-start">Configuración</td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="administracion_view"></td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="administracion_create"></td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="administracion_edit"></td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="administracion_delete"></td>
                          </tr>
                          <tr>
                              <td class="text-start">Usuarios</td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="usuarios_view"></td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="usuarios_create"></td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="usuarios_edit"></td>
                              <td><input type="checkbox" class="form-check-input perm-checkbox" value="usuarios_delete"></td>
                          </tr>
                      </tbody>
                  </table>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btn-save-user">Guardar Usuario</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
    let usersList = [];
    let userModal;

    document.addEventListener('DOMContentLoaded', () => {
        userModal = new bootstrap.Modal(document.getElementById('userModal'));
        loadUsers();
        document.getElementById('filter-user-status').addEventListener('change', loadUsers);
        document.getElementById('search-user').addEventListener('input', renderUsersTable);
    });

    function loadUsers() {
        const activeVal = document.getElementById('filter-user-status').value;
        fetch(`controllers/UserController.php?action=list&active=${activeVal}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    usersList = data.data;
                    renderUsersTable();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
    }

    function renderUsersTable() {
        const filter = document.getElementById('search-user').value.toLowerCase();
        const tbody = document.getElementById('users-tbody');
        tbody.innerHTML = '';

        const filtered = usersList.filter(u => u.nombre_usuario.toLowerCase().includes(filter));

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No se encontraron usuarios.</td></tr>`;
            return;
        }

        filtered.forEach(u => {
            const isActive = u.estado_usuario == 1;
            
            let permsHtml = '';
            if ((u.permisos || []).includes('admin')) {
                permsHtml = '<span class="badge bg-danger"><i class="fas fa-crown"></i> Admin Global</span>';
            } else {
                permsHtml = `<span class="badge bg-secondary">${(u.permisos || []).length} permisos específicos</span>`;
            }
            
            let btns = `<?php if(hasPerm('usuarios_edit')): ?><button class="btn btn-sm btn-info text-white me-1" onclick="editUser(${u.id_usuario})"><i class="fas fa-edit"></i></button><?php endif; ?>`;
            
            <?php if(hasPerm('usuarios_delete')): ?>
            if (u.id_usuario != <?= $_SESSION['user_id'] ?>) {
                if (isActive) {
                    btns += `<button class="btn btn-sm btn-danger" onclick="changeUserState(${u.id_usuario}, 'deactivate')"><i class="fas fa-ban"></i></button>`;
                } else {
                    btns += `<button class="btn btn-sm btn-success" onclick="changeUserState(${u.id_usuario}, 'activate')"><i class="fas fa-check"></i></button>`;
                }
            }
            <?php endif; ?>

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${u.id_usuario}</td>
                    <td class="fw-bold">${u.nombre_usuario}</td>
                    <td>${permsHtml}</td>
                    <td><span class="badge bg-${isActive ? 'success' : 'secondary'}">${isActive ? 'Activo' : 'Inactivo'}</span></td>
                    <td>${btns}</td>
                </tr>
            `);
        });
    }

    function openUserModal() {
        document.getElementById('user-form').reset();
        document.getElementById('user-id').value = '';
        document.getElementById('user-modal-title').textContent = 'Registrar Nuevo Usuario';
        document.getElementById('user-password').required = true;
        document.getElementById('user-password-asterisk').style.display = 'inline';
        document.getElementById('user-password-hint').textContent = '(Obligatoria)';
        
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
        userModal.show();
    }

    function editUser(id) {
        const u = usersList.find(x => x.id_usuario == id);
        if (!u) return;
        
        document.getElementById('user-form').reset();
        document.getElementById('user-id').value = u.id_usuario;
        document.getElementById('user-username').value = u.nombre_usuario;
        document.getElementById('user-modal-title').textContent = 'Editar Usuario';
        
        document.getElementById('user-password').required = false;
        document.getElementById('user-password-asterisk').style.display = 'none';
        document.getElementById('user-password-hint').textContent = '(Dejar en blanco para no cambiar)';
        
        document.querySelectorAll('.perm-checkbox').forEach(cb => {
            cb.checked = (u.permisos || []).includes(cb.value);
        });
        
        userModal.show();
    }

    document.getElementById('user-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btn-save-user');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        const fd = new FormData(this);
        const perms = [];
        document.querySelectorAll('.perm-checkbox:checked').forEach(cb => {
            perms.push(cb.value);
        });
        fd.append('permisos', JSON.stringify(perms));

        fetch('controllers/UserController.php?action=save', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            btn.textContent = 'Guardar Usuario';
            if(res.success) {
                userModal.hide();
                Swal.fire('¡Éxito!', res.message, 'success');
                loadUsers();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
    });

    function changeUserState(id, actionStr) {
        Swal.fire({
            title: actionStr === 'deactivate' ? '¿Deshabilitar usuario?' : '¿Reactivar usuario?',
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
                fd.append('id_usuario', id);
                fetch(`controllers/UserController.php?action=${actionStr}`, { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(res => {
                        if(res.success) {
                            Swal.fire('¡Hecho!', res.message, 'success');
                            loadUsers();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    });
            }
        });
    }
</script>

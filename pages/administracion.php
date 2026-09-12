<ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active fw-bold" id="planes-tab" data-bs-toggle="tab" data-bs-target="#planes" type="button" role="tab"><i class="fas fa-tags me-2"></i> Planes de Mensualidad</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link fw-bold" id="inventario-tab" data-bs-toggle="tab" data-bs-target="#inventario" type="button" role="tab"><i class="fas fa-box-open me-2"></i> Inventario de Productos</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link fw-bold text-danger" id="inactividad-tab" data-bs-toggle="tab" data-bs-target="#inactividad" type="button" role="tab"><i class="fas fa-user-clock me-2"></i> Control de Inactividad</button>
  </li>
</ul>

<div class="tab-content" id="adminTabsContent">
  <!-- Pestaña Planes -->
  <div class="tab-pane fade show active" id="planes" role="tabpanel">
      <div class="card shadow-sm border-0">
          <div class="card-body">
              <div class="d-flex justify-content-between mb-3">
                  <h5 class="card-title text-primary"><i class="fas fa-list"></i> Lista de Planes</h5>
                  <?php if(hasPerm('administracion_create')): ?>
                  <button class="btn btn-primary btn-sm" onclick="openPlanModal()"><i class="fas fa-plus"></i> Nuevo Plan</button>
                  <?php endif; ?>
              </div>
              <table class="table table-hover align-middle">
                  <thead class="table-light">
                      <tr>
                          <th>Nombre del Plan</th>
                          <th>Descripción</th>
                          <th>Duración (Días)</th>
                          <th>Precio</th>
                          <th>Estado</th>
                          <th>Acciones</th>
                      </tr>
                  </thead>
                  <tbody id="planes-tbody">
                      <!-- JS Injection -->
                  </tbody>
              </table>
          </div>
      </div>
  </div>

  <!-- Pestaña Inventario -->
  <div class="tab-pane fade" id="inventario" role="tabpanel">
      <div class="card shadow-sm border-0">
          <div class="card-body">
              <div class="d-flex justify-content-between mb-3">
                  <h5 class="card-title text-primary"><i class="fas fa-boxes"></i> Productos Disponibles</h5>
                  <?php if(hasPerm('administracion_create')): ?>
                  <button class="btn btn-primary btn-sm" onclick="openProductModal()"><i class="fas fa-plus"></i> Registrar Producto</button>
                  <?php endif; ?>
              </div>
              <div class="row mb-3">
                  <div class="col-md-4">
                      <input type="text" id="search-product" class="form-control" placeholder="Buscar por código o nombre...">
                  </div>
              </div>
              <table class="table table-hover align-middle">
                  <thead class="table-light">
                      <tr>
                          <th>Código</th>
                          <th>Producto</th>
                          <th>Precio Costo</th>
                          <th>Precio Venta</th>
                          <th>Stock</th>
                          <th>Estado</th>
                          <th>Acciones</th>
                      </tr>
                  </thead>
                  <tbody id="productos-tbody">
                      <!-- JS Injection -->
                  </tbody>
              </table>
          </div>
      </div>
  </div>

  <!-- Pestaña Control de Inactividad -->
  <div class="tab-pane fade" id="inactividad" role="tabpanel">
      <div class="card shadow-sm border-0 border-danger">
          <div class="card-body">
              <h5 class="card-title text-danger"><i class="fas fa-broom"></i> Limpieza de Clientes Inactivos</h5>
              <p class="text-muted">
                  Esta herramienta cambiará el estado a <strong>Inactivo</strong> a todos los clientes que no hayan tenido ninguna membresía activa en la cantidad de meses especificada.
              </p>
              
              <div class="row align-items-end mt-4">
                  <div class="col-md-3">
                      <label class="form-label">Meses de inactividad límite:</label>
                      <input type="number" id="meses-inactividad" class="form-control form-control-lg" value="3" min="1" max="24">
                  </div>
                  <div class="col-md-4">
                      <?php if(hasPerm('clientes_delete')): ?>
                      <button class="btn btn-danger btn-lg w-100" onclick="ejecutarLimpieza()"><i class="fas fa-trash-alt"></i> Ejecutar Limpieza</button>
                      <?php else: ?>
                      <button class="btn btn-secondary btn-lg w-100" disabled>No autorizado</button>
                      <?php endif; ?>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>

<!-- Modal Planes -->
<div class="modal fade" id="planModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="plan-form">
          <div class="modal-header">
            <h5 class="modal-title" id="plan-modal-title">Plan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" id="plan-id" name="id_plan">
              <div class="mb-3">
                  <label>Nombre del Plan <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="plan-nombre" name="nombre_plan" required>
              </div>
              <div class="mb-3">
                  <label>Descripción</label>
                  <textarea class="form-control" id="plan-desc" name="descripcion"></textarea>
              </div>
              <div class="row">
                  <div class="col-6 mb-3">
                      <label>Duración (Días) <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" id="plan-dias" name="duracion_dias" required min="1" step="1" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                  </div>
                  <div class="col-6 mb-3">
                      <label>Precio <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="plan-precio" name="precio" required oninput="formatCurrencyInput(this)">
                  </div>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btn-save-plan">Guardar</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Productos -->
<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="product-form">
          <div class="modal-header">
            <h5 class="modal-title" id="product-modal-title">Producto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" id="prod-id" name="id_producto">
              
              <div class="row">
                  <div class="col-md-4 mb-3">
                      <label>Código de Barras</label>
                      <input type="text" class="form-control" id="prod-codigo" name="codigo_barras">
                  </div>
                  <div class="col-md-8 mb-3">
                      <label>Nombre del Producto <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="prod-nombre" name="nombre_producto" required>
                  </div>
                  <div class="col-12 mb-3">
                      <label>Descripción</label>
                      <textarea class="form-control" id="prod-desc" name="descripcion"></textarea>
                  </div>
                  <div class="col-md-4 mb-3">
                      <label>Precio Costo (Opcional)</label>
                      <input type="text" class="form-control" id="prod-costo" name="precio_compra" oninput="formatCurrencyInput(this)">
                  </div>
                  <div class="col-md-4 mb-3">
                      <label>Precio Venta <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="prod-venta" name="precio_venta" required oninput="formatCurrencyInput(this)">
                  </div>
                  <div class="col-md-4 mb-3">
                      <label>Stock Actual</label>
                      <input type="number" class="form-control" id="prod-stock" name="stock" value="0" min="0" step="1" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                  </div>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btn-save-product">Guardar</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Kardex -->
<div class="modal fade" id="kardexModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Kardex de Inventario - <span id="kardex-product-name" class="text-primary"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
          <div class="row">
              <!-- Formulario de Ajuste -->
              <div class="col-md-4 border-end pe-4">
                  <h6 class="fw-bold mb-3"><i class="fas fa-plus-minus text-secondary"></i> Nuevo Movimiento</h6>
                  <form id="kardex-form">
                      <input type="hidden" id="kardex-prod-id" name="id_producto">
                      <div class="mb-3">
                          <label>Tipo de Movimiento <span class="text-danger">*</span></label>
                          <select class="form-select" name="tipo_movimiento" required>
                              <option value="ENTRADA">Entrada (Sumar stock)</option>
                              <option value="SALIDA">Salida (Restar stock)</option>
                          </select>
                      </div>
                      <div class="mb-3">
                          <label>Cantidad <span class="text-danger">*</span></label>
                          <input type="number" class="form-control" name="cantidad" required min="1" step="1" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                      </div>
                      <div class="mb-3">
                          <label>Precio Unitario / Costo</label>
                          <input type="text" class="form-control" name="precio" value="0" oninput="formatCurrencyInput(this)">
                      </div>
                      <div class="mb-3">
                          <label>Descripción / Motivo</label>
                          <textarea class="form-control" name="descripcion" rows="4" placeholder="Ej. Ajuste inicial, mercadería dañada..."></textarea>
                      </div>
                      <button type="submit" class="btn btn-primary w-100 py-2"><i class="fas fa-save"></i> Registrar Movimiento</button>
                  </form>
              </div>
              
              <!-- Historial -->
              <div class="col-md-8 ps-4">
                  <h6 class="fw-bold mb-3"><i class="fas fa-history text-secondary"></i> Historial de Movimientos</h6>
                  <div class="table-responsive" style="min-height: 450px; max-height: 500px;">
                      <table class="table table-hover table-sm align-middle">
                          <thead class="table-light position-sticky top-0">
                              <tr>
                                  <th>Fecha</th>
                                  <th>Tipo</th>
                                  <th>Cant.</th>
                                  <th>Precio</th>
                                  <th>Descripción</th>
                                  <th>Usuario</th>
                              </tr>
                          </thead>
                          <tbody id="kardex-tbody">
                              <!-- JS Injection -->
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>

<script>
    let planes = [];
    let productos = [];
    let planModal, productModal, kardexModal;

    document.addEventListener('DOMContentLoaded', () => {
        planModal = new bootstrap.Modal(document.getElementById('planModal'));
        productModal = new bootstrap.Modal(document.getElementById('productModal'));
        kardexModal = new bootstrap.Modal(document.getElementById('kardexModal'));
        
        loadPlanes();
        loadProductos();

        document.getElementById('search-product').addEventListener('input', renderProductos);
    });

    // --- PLANES ---
    function loadPlanes() {
        fetch('controllers/PlanController.php?action=list')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    planes = data.data;
                    renderPlanes();
                }
            });
    }

    function renderPlanes() {
        const tbody = document.getElementById('planes-tbody');
        tbody.innerHTML = '';
        if (planes.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">No hay planes registrados.</td></tr>`;
            return;
        }

        planes.forEach(p => {
            const isActive = p.estado == 1;
            let btns = '';
            <?php if(hasPerm('administracion_edit')): ?>
            btns += `<button class="btn btn-sm btn-info text-white me-1" onclick="editPlan(${p.id_plan})"><i class="fas fa-edit"></i></button>`;
            <?php endif; ?>
            <?php if(hasPerm('administracion_delete')): ?>
            if(isActive){
                btns += `<button class="btn btn-sm btn-danger" onclick="togglePlan(${p.id_plan}, 0)"><i class="fas fa-ban"></i></button>`;
            } else {
                btns += `<button class="btn btn-sm btn-success" onclick="togglePlan(${p.id_plan}, 1)"><i class="fas fa-check"></i></button>`;
            }
            <?php endif; ?>

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="fw-bold">${p.nombre_plan}</td>
                    <td>${p.descripcion || '-'}</td>
                    <td>${p.duracion_dias} días</td>
                    <td class="text-success fw-bold">L ${parseFloat(p.precio).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                    <td><span class="badge bg-${isActive ? 'success' : 'secondary'}">${isActive ? 'Activo' : 'Inactivo'}</span></td>
                    <td>${btns}</td>
                </tr>
            `);
        });
    }

    function openPlanModal() {
        document.getElementById('plan-form').reset();
        document.getElementById('plan-id').value = '';
        document.getElementById('plan-modal-title').textContent = 'Registrar Nuevo Plan';
        planModal.show();
    }

    function editPlan(id) {
        const p = planes.find(x => x.id_plan == id);
        if(!p) return;
        openPlanModal();
        document.getElementById('plan-modal-title').textContent = 'Editar Plan';
        document.getElementById('plan-id').value = p.id_plan;
        document.getElementById('plan-nombre').value = p.nombre_plan;
        document.getElementById('plan-desc').value = p.descripcion;
        document.getElementById('plan-dias').value = p.duracion_dias;
        document.getElementById('plan-precio').value = p.precio;
        formatCurrencyInput(document.getElementById('plan-precio'));
    }

    document.getElementById('plan-form').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('controllers/PlanController.php?action=save', {
            method: 'POST',
            body: new FormData(this)
        }).then(r => r.json()).then(res => {
            if(res.success) {
                planModal.hide();
                Swal.fire('¡Éxito!', res.message, 'success');
                loadPlanes();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
    });

    function togglePlan(id, state) {
        const fd = new FormData();
        fd.append('id_plan', id);
        fd.append('estado', state);
        fetch('controllers/PlanController.php?action=toggle', { method: 'POST', body: fd })
            .then(r => r.json()).then(res => {
                if(res.success) loadPlanes();
                else Swal.fire('Error', res.message, 'error');
            });
    }

    // --- PRODUCTOS ---
    function loadProductos() {
        fetch('controllers/ProductController.php?action=list')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    productos = data.data;
                    renderProductos();
                }
            });
    }

    function renderProductos() {
        const tbody = document.getElementById('productos-tbody');
        tbody.innerHTML = '';
        const filter = document.getElementById('search-product').value.toLowerCase();

        const filtered = productos.filter(p => 
            p.nombre_producto.toLowerCase().includes(filter) || 
            (p.codigo_barras && p.codigo_barras.toLowerCase().includes(filter))
        );

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">No hay productos registrados.</td></tr>`;
            return;
        }

        filtered.forEach(p => {
            const isActive = p.estado == 1;
            let stockBadge = 'success';
            if(p.stock <= 5 && p.stock > 0) stockBadge = 'warning text-dark';
            if(p.stock <= 0) stockBadge = 'danger';

            let btns = '';
            <?php if(hasPerm('administracion_edit')): ?>
            btns += `<button class="btn btn-sm btn-info text-white me-1" onclick="editProduct(${p.id_producto})"><i class="fas fa-edit"></i></button>`;
            btns += `<button class="btn btn-sm btn-secondary me-1" title="Ajuste de Stock / Kardex" onclick="openKardexModal(${p.id_producto}, '${p.nombre_producto}')"><i class="fas fa-boxes"></i></button>`;
            <?php endif; ?>
            <?php if(hasPerm('administracion_delete')): ?>
            if(isActive){
                btns += `<button class="btn btn-sm btn-danger" onclick="toggleProduct(${p.id_producto}, 0)"><i class="fas fa-ban"></i></button>`;
            } else {
                btns += `<button class="btn btn-sm btn-success" onclick="toggleProduct(${p.id_producto}, 1)"><i class="fas fa-check"></i></button>`;
            }
            <?php endif; ?>

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td><small class="text-muted">${p.codigo_barras || 'N/A'}</small></td>
                    <td class="fw-bold">${p.nombre_producto}</td>
                    <td>L ${parseFloat(p.precio_compra).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                    <td class="text-success fw-bold">L ${parseFloat(p.precio_venta).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                    <td><span class="badge bg-${stockBadge}" style="font-size: 0.9em;">${p.stock} unidades</span></td>
                    <td><span class="badge bg-${isActive ? 'success' : 'secondary'}">${isActive ? 'Activo' : 'Inactivo'}</span></td>
                    <td>${btns}</td>
                </tr>
            `);
        });
    }

    function openProductModal() {
        document.getElementById('product-form').reset();
        document.getElementById('prod-id').value = '';
        document.getElementById('product-modal-title').textContent = 'Registrar Nuevo Producto';
        productModal.show();
    }

    function editProduct(id) {
        const p = productos.find(x => x.id_producto == id);
        if(!p) return;
        openProductModal();
        document.getElementById('product-modal-title').textContent = 'Editar Producto';
        document.getElementById('prod-id').value = p.id_producto;
        document.getElementById('prod-codigo').value = p.codigo_barras || '';
        document.getElementById('prod-nombre').value = p.nombre_producto;
        document.getElementById('prod-desc').value = p.descripcion || '';
        document.getElementById('prod-costo').value = p.precio_compra;
        if(p.precio_compra) formatCurrencyInput(document.getElementById('prod-costo'));
        document.getElementById('prod-venta').value = p.precio_venta;
        if(p.precio_venta) formatCurrencyInput(document.getElementById('prod-venta'));
        document.getElementById('prod-stock').value = p.stock;
    }

    document.getElementById('product-form').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('controllers/ProductController.php?action=save', {
            method: 'POST',
            body: new FormData(this)
        }).then(r => r.json()).then(res => {
            if(res.success) {
                productModal.hide();
                Swal.fire('¡Éxito!', res.message, 'success');
                loadProductos();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
    });

    function toggleProduct(id, state) {
        const fd = new FormData();
        fd.append('id_producto', id);
        fd.append('estado', state);
        fetch('controllers/ProductController.php?action=toggle', { method: 'POST', body: fd })
            .then(r => r.json()).then(res => {
                if(res.success) loadProductos();
                else Swal.fire('Error', res.message, 'error');
            });
    }

    // --- INACTIVIDAD ---
    function ejecutarLimpieza() {
        const meses = document.getElementById('meses-inactividad').value;
        if (meses < 1) return;

        Swal.fire({
            title: '¿Ejecutar limpieza de inactivos?',
            text: `Se desactivarán todos los clientes que lleven ${meses} meses o más sin una membresía activa. Esta acción no se puede deshacer fácilmente en bloque.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, ejecutar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('meses', meses);
                fetch('controllers/ClientController.php?action=cleanup', {
                    method: 'POST',
                    body: fd
                }).then(r => r.json()).then(res => {
                    if (res.success) {
                        Swal.fire('¡Proceso Finalizado!', res.message, 'success');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                });
            }
        });
    }

    // --- KARDEX ---
    function openKardexModal(id, nombre) {
        document.getElementById('kardex-product-name').textContent = nombre;
        document.getElementById('kardex-prod-id').value = id;
        document.getElementById('kardex-form').reset();
        
        loadKardexHistory(id);
        kardexModal.show();
    }

    function loadKardexHistory(id) {
        fetch(`controllers/ProductController.php?action=kardex&id_producto=${id}`)
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('kardex-tbody');
                tbody.innerHTML = '';
                
                if (data.success) {
                    if (data.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted" style="height: 250px; vertical-align: middle;">No hay movimientos registrados.</td></tr>';
                        return;
                    }
                    
                    data.data.forEach(k => {
                        const dateObj = new Date(k.fecha_movimiento);
                        const dateStr = dateObj.toLocaleDateString('es-HN') + ' ' + dateObj.toLocaleTimeString('es-HN', {hour: '2-digit', minute:'2-digit'});
                        
                        let badgeClass = 'secondary';
                        if(k.tipo_movimiento === 'ENTRADA') badgeClass = 'success';
                        else if(k.tipo_movimiento === 'SALIDA') badgeClass = 'danger';
                        else if(k.tipo_movimiento === 'VENTA') badgeClass = 'primary';
                        
                        tbody.insertAdjacentHTML('beforeend', `
                            <tr>
                                <td><small>${dateStr}</small></td>
                                <td><span class="badge bg-${badgeClass}">${k.tipo_movimiento}</span></td>
                                <td class="fw-bold">${k.cantidad}</td>
                                <td>L ${parseFloat(k.precio).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                                <td><small>${k.descripcion || '-'}</small></td>
                                <td><small class="text-muted">${k.nombre_usuario || '-'}</small></td>
                            </tr>
                        `);
                    });
                }
            });
    }

    document.getElementById('kardex-form').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('controllers/ProductController.php?action=adjust', {
            method: 'POST',
            body: new FormData(this)
        }).then(r => r.json()).then(res => {
            if(res.success) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: res.message,
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                // Recargar historial y productos subyacentes
                loadKardexHistory(document.getElementById('kardex-prod-id').value);
                document.getElementById('kardex-form').reset();
                loadProductos();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
    });
</script>

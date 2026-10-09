<ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active fw-bold" id="planes-tab" data-bs-toggle="tab" data-bs-target="#planes" type="button" role="tab"><i class="fas fa-tags me-2"></i> Planes de Mensualidad</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link fw-bold" id="inventario-tab" data-bs-toggle="tab" data-bs-target="#inventario" type="button" role="tab"><i class="fas fa-box-open me-2"></i> Inventario de Productos</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link fw-bold" id="recibos-tab" data-bs-toggle="tab" data-bs-target="#recibos" type="button" role="tab"><i class="fas fa-receipt me-2"></i> Configuración de Recibos</button>
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

  <!-- Pestaña Configuración de Recibos -->
  <div class="tab-pane fade" id="recibos" role="tabpanel">
      <div class="row">
          <div class="col-md-6">
              <div class="card shadow-sm border-0 h-100">
                  <div class="card-body">
                      <h5 class="card-title text-primary mb-4"><i class="fas fa-cogs"></i> Datos del Recibo</h5>
                      <form id="receipt-config-form">
                          <div class="mb-3">
                              <label class="form-label text-muted small fw-bold">Nombre del Establecimiento <span class="text-danger">*</span></label>
                              <input type="text" class="form-control" id="receipt-name" name="gym_name" required oninput="updateReceiptPreview()">
                          </div>
                          
                          <div class="row">
                              <div class="col-md-8 mb-3">
                                  <label class="form-label text-muted small fw-bold">RTN</label>
                                  <input type="text" class="form-control" id="receipt-rtn" name="rtn" oninput="updateReceiptPreview()">
                              </div>
                              <div class="col-md-4 mb-3 d-flex align-items-end">
                                  <div class="form-check form-switch mb-2">
                                      <input class="form-check-input" type="checkbox" id="receipt-show-rtn" name="show_rtn" value="1" onchange="updateReceiptPreview()">
                                      <label class="form-check-label small" for="receipt-show-rtn">Mostrar</label>
                                  </div>
                              </div>
                          </div>
                          
                          <div class="row">
                              <div class="col-md-8 mb-3">
                                  <label class="form-label text-muted small fw-bold">Número de Teléfono</label>
                                  <input type="text" class="form-control" id="receipt-phone" name="phone" oninput="updateReceiptPreview()">
                              </div>
                              <div class="col-md-4 mb-3 d-flex align-items-end">
                                  <div class="form-check form-switch mb-2">
                                      <input class="form-check-input" type="checkbox" id="receipt-show-phone" name="show_phone" value="1" onchange="updateReceiptPreview()">
                                      <label class="form-check-label small" for="receipt-show-phone">Mostrar</label>
                                  </div>
                              </div>
                          </div>
                          
                          <div class="row">
                              <div class="col-md-8 mb-3">
                                  <label class="form-label text-muted small fw-bold">Dirección</label>
                                  <textarea class="form-control" id="receipt-address" name="address" rows="2" oninput="updateReceiptPreview()"></textarea>
                              </div>
                              <div class="col-md-4 mb-3 d-flex align-items-center">
                                  <div class="form-check form-switch mt-3">
                                      <input class="form-check-input" type="checkbox" id="receipt-show-address" name="show_address" value="1" onchange="updateReceiptPreview()">
                                      <label class="form-check-label small" for="receipt-show-address">Mostrar</label>
                                  </div>
                              </div>
                          </div>
                          
                          <div class="row">
                              <div class="col-md-8 mb-3">
                                  <label class="form-label text-muted small fw-bold">Redes Sociales</label>
                                  <input type="text" class="form-control" id="receipt-social" name="social" placeholder="Ej: FB: GymOrion | IG: @gymorion" oninput="updateReceiptPreview()">
                              </div>
                              <div class="col-md-4 mb-3 d-flex align-items-end">
                                  <div class="form-check form-switch mb-2">
                                      <input class="form-check-input" type="checkbox" id="receipt-show-social" name="show_social" value="1" onchange="updateReceiptPreview()">
                                      <label class="form-check-label small" for="receipt-show-social">Mostrar</label>
                                  </div>
                              </div>
                          </div>
                          
                          <div class="mb-4">
                              <label class="form-label text-muted small fw-bold">Texto Pie de Recibo</label>
                              <textarea class="form-control" id="receipt-footer" name="footer" rows="2" placeholder="Ej: ¡Gracias por tu preferencia!" oninput="updateReceiptPreview()"></textarea>
                          </div>
                          <?php if(hasPerm('administracion_edit')): ?>
                          <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Guardar Configuración</button>
                          <?php else: ?>
                          <button type="button" class="btn btn-secondary w-100" disabled>No autorizado</button>
                          <?php endif; ?>
                      </form>
                  </div>
              </div>
          </div>
          <div class="col-md-6">
              <div class="card shadow-sm border-0 h-100 bg-light">
                  <div class="card-body d-flex flex-column align-items-center">
                      <div class="d-flex justify-content-between w-100 mb-4 align-items-center">
                          <h5 class="text-muted mb-0">Vista Previa</h5>
                          <select class="form-select form-select-sm w-auto" id="preview-type" onchange="updateReceiptPreview()">
                              <option value="membresia">Pago Mensualidad</option>
                              <option value="producto">Venta Producto</option>
                              <option value="ingreso">Otro Ingreso</option>
                          </select>
                      </div>
                      
                      <!-- Ticket Preview -->
                      <div class="bg-white p-4 shadow-sm my-3" style="width: 340px; min-height: 400px; font-family: monospace; color: #333; border: 1px dashed #ccc; box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important;">
                          <div class="text-center mb-4">
                              <h4 class="fw-bold mb-1" id="preview-name" style="text-transform: uppercase;">ORION GYM</h4>
                              <div style="font-size: 0.9em;" id="preview-rtn-container">RTN: <span id="preview-rtn">0000-0000-000000</span></div>
                              <div style="font-size: 0.9em;" id="preview-address">Dirección del Gym</div>
                              <div style="font-size: 0.9em;" id="preview-phone-container">Tel: <span id="preview-phone">0000-0000</span></div>
                              <div style="font-size: 0.9em;" id="preview-social">@redsocial</div>
                          </div>
                          
                          <div class="py-3 mb-4 text-center" style="border-top: 1px dashed #ccc; border-bottom: 1px dashed #ccc;">
                              <div class="fw-bold" id="preview-title">RECIBO DE PAGO #0001</div>
                              <div id="preview-date">Fecha: <?php echo date('d/m/Y H:i'); ?></div>
                              <div id="preview-cashier" style="font-size: 0.9em;">Le atendió: Admin</div>
                              <div id="preview-client-line" class="mt-2 fw-bold" style="font-size: 0.9em;">Cliente: Consumidor Final</div>
                          </div>
                          
                          <div class="mb-4" style="font-size: 0.9em;" id="preview-body">
                              <!-- Dynamic content depending on receipt type -->
                          </div>
                          
                          <div class="pt-3 pb-1 mb-5" style="border-top: 1px dashed #ccc; font-size: 1em;">
                              <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.9em;" id="preview-subtotal-line">
                                  <span>SUBTOTAL:</span>
                                  <span id="preview-subtotal">L 434.78</span>
                              </div>
                              <div class="d-flex justify-content-between text-muted mb-2" style="font-size: 0.9em;" id="preview-tax-line">
                                  <span>I.S.V. (15%):</span>
                                  <span id="preview-tax">L 65.22</span>
                              </div>
                              <div class="d-flex justify-content-between fw-bold mb-2" style="font-size: 1.2em;">
                                  <span>TOTAL:</span>
                                  <span id="preview-total">L 500.00</span>
                              </div>
                              <div class="d-flex justify-content-between text-muted" style="font-size: 0.85em;">
                                  <span>MÉTODO:</span>
                                  <span id="preview-method">Efectivo</span>
                              </div>
                              <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 0.85em; display: none !important;" id="preview-ref-line">
                                  <span>REFERENCIA:</span>
                                  <span id="preview-ref">123456789</span>
                              </div>
                          </div>
                          
                          <div class="text-center mt-auto" style="font-size: 0.85em;">
                              <div id="preview-footer">¡Gracias por su preferencia!</div>
                          </div>
                      </div>
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
        loadReceiptConfig();
        
        updateReceiptPreview();

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

    // --- CONFIGURACIÓN DE RECIBOS ---
    function loadReceiptConfig() {
        fetch('controllers/ConfigController.php?action=getReceiptConfig')
            .then(r => r.json())
            .then(res => {
                if(res.success && res.data) {
                    document.getElementById('receipt-name').value = res.data.gym_name || '';
                    document.getElementById('receipt-rtn').value = res.data.rtn || '';
                    document.getElementById('receipt-show-rtn').checked = res.data.show_rtn;
                    
                    document.getElementById('receipt-phone').value = res.data.phone || '';
                    document.getElementById('receipt-show-phone').checked = res.data.show_phone;
                    
                    document.getElementById('receipt-address').value = res.data.address || '';
                    document.getElementById('receipt-show-address').checked = res.data.show_address;
                    
                    document.getElementById('receipt-social').value = res.data.social || '';
                    document.getElementById('receipt-show-social').checked = res.data.show_social;
                    
                    document.getElementById('receipt-footer').value = res.data.footer || '';
                }
                updateReceiptPreview();
            });
    }

    function updateReceiptPreview() {
        const name = document.getElementById('receipt-name').value;
        const rtn = document.getElementById('receipt-rtn').value;
        const showRtn = document.getElementById('receipt-show-rtn').checked;
        
        const phone = document.getElementById('receipt-phone').value;
        const showPhone = document.getElementById('receipt-show-phone').checked;
        
        const address = document.getElementById('receipt-address').value;
        const showAddress = document.getElementById('receipt-show-address').checked;
        
        const social = document.getElementById('receipt-social').value;
        const showSocial = document.getElementById('receipt-show-social').checked;
        
        const footer = document.getElementById('receipt-footer').value;

        // Header
        document.getElementById('preview-name').textContent = name || 'NOMBRE GYM';
        
        document.getElementById('preview-rtn').textContent = rtn;
        document.getElementById('preview-rtn-container').style.display = (showRtn && rtn) ? 'block' : 'none';
        
        document.getElementById('preview-address').textContent = address;
        document.getElementById('preview-address').style.display = (showAddress && address) ? 'block' : 'none';
        
        document.getElementById('preview-phone').textContent = phone;
        document.getElementById('preview-phone-container').style.display = (showPhone && phone) ? 'block' : 'none';
        
        document.getElementById('preview-social').textContent = social;
        document.getElementById('preview-social').style.display = (showSocial && social) ? 'block' : 'none';
        
        document.getElementById('preview-footer').textContent = footer;
        document.getElementById('preview-footer').style.display = footer ? 'block' : 'none';

        // Body Content based on selector
        const type = document.getElementById('preview-type').value;
        const bodyEl = document.getElementById('preview-body');
        
        if (type === 'membresia') {
            document.getElementById('preview-title').textContent = 'RECIBO DE PAGO #0001';
            document.getElementById('preview-client-line').textContent = 'Cliente: Juan Pérez';
            document.getElementById('preview-client-line').style.display = 'block';
            
            document.getElementById('preview-subtotal').textContent = 'L 521.74';
            document.getElementById('preview-tax').textContent = 'L 78.26';
            document.getElementById('preview-total').textContent = 'L 600.00';
            document.getElementById('preview-method').textContent = 'Efectivo';
            document.getElementById('preview-ref-line').style.setProperty('display', 'none', 'important');
            
            bodyEl.innerHTML = `
                <div class="d-flex justify-content-between mb-2">
                    <span>1x Plan Anual (1 Meses)</span>
                    <span>L 600.00</span>
                </div>
            `;
        } else if (type === 'producto') {
            document.getElementById('preview-title').textContent = 'VENTA MOSTRADOR #0002';
            document.getElementById('preview-client-line').textContent = 'Cliente: Consumidor Final';
            document.getElementById('preview-client-line').style.display = 'block';
            
            document.getElementById('preview-subtotal').textContent = 'L 730.43';
            document.getElementById('preview-tax').textContent = 'L 109.57';
            document.getElementById('preview-total').textContent = 'L 840.00';
            document.getElementById('preview-method').textContent = 'Tarjeta';
            document.getElementById('preview-ref-line').style.setProperty('display', 'flex', 'important');
            
            bodyEl.innerHTML = `
                <div class="d-flex justify-content-between mb-2">
                    <span>2x Bote con Agua</span>
                    <span>L 40.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>1x Proteína Whey 1lb</span>
                    <span>L 800.00</span>
                </div>
            `;
        } else if (type === 'ingreso') {
            document.getElementById('preview-title').textContent = 'RECIBO DE INGRESO #0003';
            document.getElementById('preview-client-line').style.display = 'none';
            
            document.getElementById('preview-subtotal').textContent = 'L 130.43';
            document.getElementById('preview-tax').textContent = 'L 19.57';
            document.getElementById('preview-total').textContent = 'L 150.00';
            document.getElementById('preview-method').textContent = 'Transferencia';
            document.getElementById('preview-ref-line').style.setProperty('display', 'flex', 'important');
            
            bodyEl.innerHTML = `
                <div class="d-flex justify-content-between mb-2">
                    <span>Multa reposición llave</span>
                    <span>L 150.00</span>
                </div>
            `;
        }
    }

    const receiptForm = document.getElementById('receipt-config-form');
    if (receiptForm) {
        receiptForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetch('controllers/ConfigController.php?action=saveReceiptConfig', {
                method: 'POST',
                body: new FormData(this)
            }).then(r => r.json()).then(res => {
                if(res.success) {
                    Swal.fire({
                        title: '¡Guardado!',
                        text: 'La configuración de recibos se ha actualizado.',
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            });
        });
    }
</script>

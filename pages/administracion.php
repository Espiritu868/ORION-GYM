<ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active fw-bold" id="planes-tab" data-bs-toggle="tab" data-bs-target="#planes" type="button" role="tab"><i class="fas fa-tags me-2"></i> Planes de Mensualidad</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link fw-bold" id="inventario-tab" data-bs-toggle="tab" data-bs-target="#inventario" type="button" role="tab"><i class="fas fa-box-open me-2"></i> Inventario de Productos</button>
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
                  <button class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Plan</button>
                  <?php endif; ?>
              </div>
              <table class="table table-hover align-middle">
                  <thead class="table-light">
                      <tr>
                          <th>Nombre del Plan</th>
                          <th>Duración (Días)</th>
                          <th>Precio</th>
                          <th>Estado</th>
                          <th>Acciones</th>
                      </tr>
                  </thead>
                  <tbody>
                      <!-- DUMMY DATA -->
                      <tr>
                          <td class="fw-bold">Plan Mensual Básico</td>
                          <td>30 días</td>
                          <td class="text-success fw-bold">L 500.00</td>
                          <td><span class="badge bg-success">Activo</span></td>
                          <td>
                              <button class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></button>
                              <button class="btn btn-sm btn-danger"><i class="fas fa-ban"></i></button>
                          </td>
                      </tr>
                      <tr>
                          <td class="fw-bold">Plan Trimestral</td>
                          <td>90 días</td>
                          <td class="text-success fw-bold">L 1,300.00</td>
                          <td><span class="badge bg-success">Activo</span></td>
                          <td>
                              <button class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></button>
                              <button class="btn btn-sm btn-danger"><i class="fas fa-ban"></i></button>
                          </td>
                      </tr>
                      <tr>
                          <td class="fw-bold">Visita Diaria</td>
                          <td>1 día</td>
                          <td class="text-success fw-bold">L 50.00</td>
                          <td><span class="badge bg-success">Activo</span></td>
                          <td>
                              <button class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></button>
                              <button class="btn btn-sm btn-danger"><i class="fas fa-ban"></i></button>
                          </td>
                      </tr>
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
                  <button class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Registrar Producto</button>
                  <?php endif; ?>
              </div>
              <div class="row mb-3">
                  <div class="col-md-4">
                      <input type="text" class="form-control" placeholder="Buscar por código de barras o nombre...">
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
                  <tbody>
                      <!-- DUMMY DATA -->
                      <tr>
                          <td><small class="text-muted">745123456789</small></td>
                          <td class="fw-bold">Agua Embotellada 500ml</td>
                          <td>L 10.00</td>
                          <td class="text-success fw-bold">L 20.00</td>
                          <td>
                              <span class="badge bg-success" style="font-size: 0.9em;">45 unidades</span>
                          </td>
                          <td><span class="badge bg-success">Activo</span></td>
                          <td>
                              <button class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></button>
                              <button class="btn btn-sm btn-secondary" title="Ajuste de Stock"><i class="fas fa-boxes"></i></button>
                          </td>
                      </tr>
                      <tr>
                          <td><small class="text-muted">745987654321</small></td>
                          <td class="fw-bold">Pre-Entreno C4</td>
                          <td>L 500.00</td>
                          <td class="text-success fw-bold">L 850.00</td>
                          <td>
                              <span class="badge bg-warning text-dark" style="font-size: 0.9em;">5 unidades</span>
                          </td>
                          <td><span class="badge bg-success">Activo</span></td>
                          <td>
                              <button class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></button>
                              <button class="btn btn-sm btn-secondary" title="Ajuste de Stock"><i class="fas fa-boxes"></i></button>
                          </td>
                      </tr>
                      <tr>
                          <td><small class="text-muted">N/A</small></td>
                          <td class="fw-bold">Toalla de Sudor (Logo Gym)</td>
                          <td>L 80.00</td>
                          <td class="text-success fw-bold">L 150.00</td>
                          <td>
                              <span class="badge bg-danger" style="font-size: 0.9em;">0 unidades</span>
                          </td>
                          <td><span class="badge bg-danger">Agotado</span></td>
                          <td>
                              <button class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></button>
                              <button class="btn btn-sm btn-secondary" title="Ajuste de Stock"><i class="fas fa-boxes"></i></button>
                          </td>
                      </tr>
                  </tbody>
              </table>
          </div>
      </div>
  </div>
</div>

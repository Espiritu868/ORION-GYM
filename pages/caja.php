<div class="row">
    <!-- Panel Izquierdo: Punto de Venta -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4" style="background-color: #f8f9fa;">
            <div class="card-body">
                <h5 class="fw-bold text-primary mb-3"><i class="fas fa-calculator"></i> Nueva Transacción</h5>
                
                <ul class="nav nav-pills nav-fill mb-3" id="posTabs" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="ingreso-tab" data-bs-toggle="pill" data-bs-target="#ingreso" type="button" role="tab">Ingreso / Venta</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link bg-light text-danger" id="egreso-tab" data-bs-toggle="pill" data-bs-target="#egreso" type="button" role="tab">Egreso (Gasto)</button>
                  </li>
                </ul>

                <div class="tab-content" id="posTabsContent">
                  <!-- Pestaña Ingreso -->
                  <div class="tab-pane fade show active" id="ingreso" role="tabpanel">
                      <div class="mb-3">
                          <label class="form-label text-muted small fw-bold">Concepto / Producto</label>
                          <select class="form-select">
                              <option>Venta de Producto (Inventario)</option>
                              <option>Pago de Mensualidad</option>
                              <option>Pago de Visita Diaria</option>
                              <option>Otro Ingreso</option>
                          </select>
                      </div>
                      <div class="mb-3">
                          <label class="form-label text-muted small fw-bold">Buscar (Cliente o Producto)</label>
                          <input type="text" class="form-control" placeholder="Escanear código o buscar nombre...">
                      </div>
                      <hr>
                      <div class="d-flex justify-content-between align-items-center mb-3">
                          <span class="fs-5">Total a Cobrar:</span>
                          <span class="fs-3 fw-bold text-success">L 0.00</span>
                      </div>
                      <button class="btn btn-success w-100 btn-lg shadow-sm"><i class="fas fa-check-circle"></i> Procesar Cobro</button>
                  </div>

                  <!-- Pestaña Egreso -->
                  <div class="tab-pane fade" id="egreso" role="tabpanel">
                      <div class="mb-3">
                          <label class="form-label text-muted small fw-bold">Motivo del Gasto</label>
                          <input type="text" class="form-control" placeholder="Ej: Pago de Energía Eléctrica, Agua...">
                      </div>
                      <div class="mb-3">
                          <label class="form-label text-muted small fw-bold">Monto (Lempiras)</label>
                          <input type="number" class="form-control" placeholder="0.00">
                      </div>
                      <hr>
                      <button class="btn btn-danger w-100 btn-lg shadow-sm"><i class="fas fa-minus-circle"></i> Registrar Gasto</button>
                  </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Caja del Día -->
        <div class="card shadow-sm border-0 bg-dark text-white">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="fas fa-chart-line"></i> Resumen de Hoy</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span>Ingresos:</span>
                    <span class="text-success fw-bold">+ L 2,350.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Egresos:</span>
                    <span class="text-danger fw-bold">- L 500.00</span>
                </div>
                <hr style="border-color: #555;">
                <div class="d-flex justify-content-between">
                    <span class="fs-5">Balance Caja:</span>
                    <span class="fs-5 fw-bold text-info">L 1,850.00</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Derecho: Historial de Transacciones -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-4">
                    <h5 class="card-title text-primary"><i class="fas fa-history"></i> Historial de Transacciones</h5>
                    <button class="btn btn-outline-secondary btn-sm"><i class="fas fa-print"></i> Cierre de Caja (Z)</button>
                </div>

                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Hora</th>
                            <th>Tipo</th>
                            <th>Concepto</th>
                            <th>Usuario</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DUMMY DATA -->
                        <tr>
                            <td class="text-muted"><small>10:15 AM</small></td>
                            <td><span class="badge bg-success">Ingreso</span></td>
                            <td>Pago de Mensualidad (Juan Carlos Reyes)</td>
                            <td><small>admin</small></td>
                            <td class="text-end fw-bold text-success">+ L 500.00</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><small>11:30 AM</small></td>
                            <td><span class="badge bg-success">Ingreso</span></td>
                            <td>Venta Producto: Agua Embotellada (x2)</td>
                            <td><small>recepcion1</small></td>
                            <td class="text-end fw-bold text-success">+ L 40.00</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><small>01:45 PM</small></td>
                            <td><span class="badge bg-danger">Egreso</span></td>
                            <td>Pago Botellón de Agua Oasis</td>
                            <td><small>admin</small></td>
                            <td class="text-end fw-bold text-danger">- L 90.00</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><small>03:10 PM</small></td>
                            <td><span class="badge bg-success">Ingreso</span></td>
                            <td>Renovación Plan Trimestral (Carlos Videz)</td>
                            <td><small>admin</small></td>
                            <td class="text-end fw-bold text-success">+ L 1,300.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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
                          <select class="form-select" id="caja-concepto" onchange="toggleCajaConcepto()">
                              <option value="mensualidad">Pago de Mensualidad / Visita</option>
                              <option value="producto">Venta de Producto (Inventario)</option>
                              <option value="otro">Otro Ingreso</option>
                          </select>
                      </div>
                      
                      <!-- Contenedor Mensualidad -->
                      <div id="caja-mensualidad-container">
                          <div class="mb-3">
                              <label class="form-label text-muted small fw-bold">Buscar Cliente</label>
                              <div class="input-group">
                                  <input type="hidden" id="caja-cliente-id">
                                  <input type="hidden" id="caja-membresia-id">
                                  <input type="hidden" id="caja-membresia-estado">
                                  <input type="text" class="form-control" id="caja-cliente-name" placeholder="Seleccione un cliente..." readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                                  <button class="btn btn-primary" type="button" onclick="openCajaSearchClient()">
                                      <i class="fas fa-search"></i> Buscar
                                  </button>
                              </div>
                              <div id="caja-client-status" class="mt-2 small d-none"></div>
                          </div>
                          
                          <div class="row d-none" id="caja-plan-details">
                              <div class="col-md-8 mb-3">
                                  <label class="form-label text-muted small fw-bold">Plan a Cobrar</label>
                                  <select class="form-select" id="caja-plan-select" onchange="calculateCajaTotal()">
                                      <!-- Planes populated via JS -->
                                  </select>
                              </div>
                              <div class="col-md-4 mb-3">
                                  <label class="form-label text-muted small fw-bold">Cantidad (Meses)</label>
                                  <input type="number" class="form-control" id="caja-plan-qty" value="1" min="1" step="1" oninput="this.value = this.value.replace(/[^0-9]/g, ''); calculateCajaTotal()">
                              </div>
                          </div>
                      </div>

                      <div class="mb-3">
                          <label class="form-label text-muted small fw-bold">Método de Pago</label>
                          <select class="form-select" id="caja-metodo-pago" onchange="toggleMetodoPago()">
                              <option value="Efectivo">Efectivo</option>
                              <option value="Transferencia">Transferencia</option>
                              <option value="Tarjeta">Tarjeta</option>
                          </select>
                      </div>

                      <div class="row d-none" id="caja-banco-container">
                          <div class="col-md-6 mb-3">
                              <label class="form-label text-muted small fw-bold">Banco</label>
                              <select class="form-select" id="caja-banco">
                                  <option value="">Seleccione...</option>
                                  <option value="BAC">BAC Credomatic</option>
                                  <option value="Ficohsa">Ficohsa</option>
                                  <option value="Atlantida">Atlántida</option>
                                  <option value="Occidente">Occidente</option>
                                  <option value="Banpais">Banpaís</option>
                                  <option value="Otro">Otro</option>
                              </select>
                          </div>
                          <div class="col-md-6 mb-3">
                              <label class="form-label text-muted small fw-bold">No. Voucher/Ref</label>
                              <input type="text" class="form-control" id="caja-referencia" placeholder="Referencia">
                          </div>
                      </div>

                      <hr>
                      <div class="d-flex justify-content-between align-items-center mb-3">
                          <span class="fs-5">Total a Cobrar:</span>
                          <span class="fs-3 fw-bold text-success" id="caja-total-display">L 0.00</span>
                      </div>
                      <button class="btn btn-success w-100 btn-lg shadow-sm" id="btn-process-payment" onclick="processPayment()" disabled><i class="fas fa-check-circle"></i> Procesar Cobro</button>
                  </div>

                  <!-- Pestaña Egreso -->
                  <div class="tab-pane fade" id="egreso" role="tabpanel">
                      <div class="mb-3">
                          <label class="form-label text-muted small fw-bold">Motivo del Gasto</label>
                          <input type="text" class="form-control" placeholder="Ej: Pago de Energía Eléctrica, Agua...">
                      </div>
                      <div class="mb-3">
                          <label class="form-label text-muted small fw-bold">Monto (Lempiras)</label>
                          <input type="text" class="form-control" id="caja-egreso-monto" placeholder="0.00" oninput="formatCurrencyInput(this)">
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
                    <span class="text-success fw-bold" id="caja-resumen-ingresos">L 0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Egresos:</span>
                    <span class="text-danger fw-bold" id="caja-resumen-egresos">L 0.00</span>
                </div>
                <hr style="border-color: #555;">
                <div class="d-flex justify-content-between">
                    <span class="fs-5">Balance Caja:</span>
                    <span class="fs-5 fw-bold text-info" id="caja-resumen-balance">L 0.00</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Derecho: Historial de Transacciones -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-4">
                    <h5 class="card-title text-primary"><i class="fas fa-history"></i> Historial de Transacciones (Hoy)</h5>
                    <button class="btn btn-outline-secondary btn-sm" onclick="loadHistorialCaja()"><i class="fas fa-sync"></i> Actualizar</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Hora</th>
                                <th>Cliente</th>
                                <th>Concepto</th>
                                <th>Pago</th>
                                <th>Usuario</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody id="caja-historial-tbody">
                            <tr>
                                <td colspan="6" class="text-center text-muted">Cargando transacciones...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buscar Cliente Caja -->
<div class="modal fade" id="cajaSearchClientModal" tabindex="-1" style="z-index: 1060;">
  <div class="modal-dialog modal-xl">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header">
        <h5 class="modal-title text-primary"><i class="fas fa-users"></i> Buscar Cliente para Cobro</h5>
        <button type="button" class="btn-close" onclick="cajaSearchClientModal.hide()"></button>
      </div>
      <div class="modal-body p-4">
        <input type="text" id="caja-search-input" class="form-control form-control-lg mb-3" placeholder="Buscar por Nombre o Identidad...">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-hover align-middle">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th>Identidad</th>
                        <th>Estado Actual</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="caja-search-tbody">
                </tbody>
            </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
    let cajaSearchClientModal;
    let allCajaClients = [];
    let allPlans = [];

    document.addEventListener('DOMContentLoaded', () => {
        cajaSearchClientModal = new bootstrap.Modal(document.getElementById('cajaSearchClientModal'));
        
        document.getElementById('caja-search-input').addEventListener('input', renderCajaSearchClients);

        // Fetch Plans for the select
        fetch('controllers/MembershipController.php?action=getFormData')
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    allPlans = res.plans;
                    const select = document.getElementById('caja-plan-select');
                    select.innerHTML = '';
                    allPlans.forEach(p => {
                        const priceFmt = parseFloat(p.precio).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
                        select.innerHTML += `<option value="${p.id_plan}" data-precio="${p.precio}">${p.nombre_plan} - L ${priceFmt}</option>`;
                    });
                    
                    // Check URL for preselected client
                    const urlParams = new URLSearchParams(window.location.search);
                    const preselectedId = urlParams.get('id_cliente');
                    if (preselectedId) {
                        fetch('controllers/CajaController.php?action=listClientsForPayment')
                            .then(r => r.json())
                            .then(resC => {
                                if(resC.success) {
                                    allCajaClients = resC.data;
                                    const c = allCajaClients.find(client => client.id_cliente == preselectedId);
                                    if (c) {
                                        selectCajaClient(c.id_cliente, c.nombre_completo.replace(/'/g, "\\'"), c.id_plan, c.memb_estado, c.id_membresia);
                                    }
                                }
                            });
                    } else {
                        document.getElementById('caja-cliente-id').value = '';
                        document.getElementById('caja-cliente-name').value = '';
                        document.getElementById('caja-membresia-id').value = '';
                        document.getElementById('caja-membresia-estado').value = '';
                        document.getElementById('caja-plan-details').classList.add('d-none');
                        document.getElementById('caja-client-status').classList.add('d-none');
                        document.getElementById('caja-total-display').textContent = 'L 0.00';
                        document.getElementById('btn-process-payment').disabled = true;
                    }
                }
            });
            
        loadHistorialCaja();
    });

    function toggleCajaConcepto() {
        const concepto = document.getElementById('caja-concepto').value;
        const cont = document.getElementById('caja-mensualidad-container');
        if(concepto === 'mensualidad') {
            cont.classList.remove('d-none');
        } else {
            cont.classList.add('d-none');
        }
    }
    
    function toggleMetodoPago() {
        const metodo = document.getElementById('caja-metodo-pago').value;
        const cont = document.getElementById('caja-banco-container');
        if(metodo === 'Transferencia' || metodo === 'Tarjeta') {
            cont.classList.remove('d-none');
        } else {
            cont.classList.add('d-none');
        }
    }

    function openCajaSearchClient() {
        fetch('controllers/CajaController.php?action=listClientsForPayment')
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    allCajaClients = res.data;
                    renderCajaSearchClients();
                    cajaSearchClientModal.show();
                }
            });
    }

    function renderCajaSearchClients() {
        const q = document.getElementById('caja-search-input').value.toLowerCase();
        const tbody = document.getElementById('caja-search-tbody');
        tbody.innerHTML = '';

        const filtered = allCajaClients.filter(c => 
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

            let badge = '';
            let statusText = 'Sin Membresía';
            let badgeColor = 'secondary';
            let subText = '';
            
            if (c.memb_estado === 'Pendiente') { badge = '<span class="badge bg-warning">Cobro Pendiente</span>'; badgeColor = 'warning'; statusText = 'Cobro Pendiente'; }
            else if (c.memb_estado === 'Activa' && c.dias_restantes > 3) { badge = `<span class="badge bg-success">Activa (${c.dias_restantes} días)</span>`; badgeColor = 'success'; statusText = 'Activa'; subText = `Faltan ${c.dias_restantes} días`; }
            else if (c.memb_estado === 'Activa' && c.dias_restantes <= 3) { badge = `<span class="badge bg-warning">Por Vencer (${c.dias_restantes} días)</span>`; badgeColor = 'warning'; statusText = 'Por Vencer'; subText = `Faltan ${c.dias_restantes} días`; }
            else if (c.memb_estado === 'Vencida') { badge = '<span class="badge bg-danger">Vencida</span>'; badgeColor = 'danger'; statusText = 'Vencida'; subText = `Hace ${Math.abs(c.dias_restantes)} días`;}
            else { badge = '<span class="badge bg-secondary">Sin Membresía</span>'; }

            const imgTag = c.tiene_foto == 1 
                ? `<div class="position-relative d-inline-block" style="width:30px; height:30px; cursor: pointer;" onclick="previewPhoto('${fullPhotoUrl}', '${c.nombre_completo.replace(/'/g, "\\'")}', '${c.identidad || '-'}', '', '${statusText}', '${badgeColor}', '${subText}')">
                       <img src="${photoUrl}" class="rounded-circle object-fit-cover w-100 h-100 shadow-sm">
                   </div>`
                : `<div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width:30px;height:30px;font-size:12px;"><i class="fas fa-user"></i></div>`;

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${imgTag}</td>
                    <td class="fw-bold">${c.nombre_completo}</td>
                    <td>${c.identidad || '-'}</td>
                    <td>${badge}</td>
                    <td><button class="btn btn-sm btn-primary" onclick="selectCajaClient(${c.id_cliente}, '${c.nombre_completo.replace(/'/g, "\\'")}', ${c.id_plan || 'null'}, '${c.memb_estado || ''}', ${c.id_membresia || 'null'})">Seleccionar</button></td>
                </tr>
            `);
        });
    }

    function selectCajaClient(idCliente, nombre, idPlan, estadoMemb, idMembresia) {
        document.getElementById('caja-cliente-id').value = idCliente;
        document.getElementById('caja-cliente-name').value = nombre;
        document.getElementById('caja-membresia-id').value = idMembresia || '';
        document.getElementById('caja-membresia-estado').value = estadoMemb || '';

        const statusDiv = document.getElementById('caja-client-status');
        statusDiv.classList.remove('d-none');
        
        if (estadoMemb === 'Pendiente') {
            statusDiv.innerHTML = '<span class="text-warning fw-bold"><i class="fas fa-exclamation-triangle"></i> Membresía Pendiente de Pago.</span>';
        } else if (estadoMemb === 'Activa' || estadoMemb === 'Vencida') {
            statusDiv.innerHTML = '<span class="text-info fw-bold"><i class="fas fa-info-circle"></i> Renovando o cambiando plan actual.</span>';
        } else {
            statusDiv.innerHTML = '<span class="text-muted"><i class="fas fa-info-circle"></i> Asignando nuevo plan desde Caja.</span>';
        }

        document.getElementById('caja-plan-details').classList.remove('d-none');
        
        if (idPlan) {
            document.getElementById('caja-plan-select').value = idPlan;
        }
        
        document.getElementById('caja-plan-qty').value = 1;
        
        if (cajaSearchClientModal) cajaSearchClientModal.hide();
        calculateCajaTotal();
    }

    function calculateCajaTotal() {
        const select = document.getElementById('caja-plan-select');
        let qty = parseInt(document.getElementById('caja-plan-qty').value);
        if (isNaN(qty) || qty < 1) qty = 1;
        
        if (select.selectedIndex === -1) return;
        
        const option = select.options[select.selectedIndex];
        const precio = parseFloat(option.getAttribute('data-precio')) || 0;
        const total = precio * qty;
        
        const totalFmt = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('caja-total-display').textContent = `L ${totalFmt}`;
        
        document.getElementById('btn-process-payment').disabled = (total <= 0 || !document.getElementById('caja-cliente-id').value);
    }

    function processPayment() {
        const idCliente = document.getElementById('caja-cliente-id').value;
        const idPlan = document.getElementById('caja-plan-select').value;
        const qty = document.getElementById('caja-plan-qty').value;
        const idMembresia = document.getElementById('caja-membresia-id').value;
        const estadoMemb = document.getElementById('caja-membresia-estado').value;
        
        const metodoPago = document.getElementById('caja-metodo-pago').value;
        const banco = document.getElementById('caja-banco').value;
        const referencia = document.getElementById('caja-referencia').value;

        if (!idCliente) return;
        
        if (metodoPago !== 'Efectivo' && (!banco || !referencia.trim())) {
            Swal.fire('Atención', 'Para pagos con Tarjeta o Transferencia, debes especificar el Banco y el No. de Referencia o Voucher.', 'warning');
            return;
        }

        Swal.fire({
            title: '¿Confirmar Cobro?',
            text: "Se procesará el pago y se activará la membresía.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, cobrar'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('id_cliente', idCliente);
                fd.append('id_plan', idPlan);
                fd.append('cantidad', qty);
                fd.append('id_membresia', idMembresia);
                fd.append('estado_memb', estadoMemb);
                fd.append('metodo_pago', metodoPago);
                fd.append('banco', banco);
                fd.append('referencia', referencia);

                fetch('controllers/CajaController.php?action=processMembershipPayment', {
                    method: 'POST',
                    body: fd
                }).then(r => r.json()).then(res => {
                    if(res.success) {
                        Swal.fire('¡Pagado!', 'El cobro se realizó correctamente.', 'success');
                        
                        // Reset form
                        document.getElementById('caja-cliente-id').value = '';
                        document.getElementById('caja-cliente-name').value = '';
                        document.getElementById('caja-plan-details').classList.add('d-none');
                        document.getElementById('caja-client-status').classList.add('d-none');
                        document.getElementById('caja-total-display').textContent = 'L 0.00';
                        document.getElementById('caja-metodo-pago').value = 'Efectivo';
                        document.getElementById('caja-banco').value = '';
                        document.getElementById('caja-referencia').value = '';
                        toggleMetodoPago();
                        document.getElementById('btn-process-payment').disabled = true;
                        
                        loadHistorialCaja();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                });
            }
        });
    }
    
    function loadHistorialCaja() {
        fetch('controllers/CajaController.php?action=getHistorialHoy')
            .then(r => r.json())
            .then(res => {
                const tbody = document.getElementById('caja-historial-tbody');
                if (res.success) {
                    tbody.innerHTML = '';
                    if (res.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay transacciones hoy.</td></tr>';
                    } else {
                        let totalIngresos = 0;
                        let totalEgresos = 0;
                        
                        res.data.forEach(t => {
                            const date = new Date(t.fecha_transaccion);
                            const timeFmt = date.toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit'});
                            
                            const isIngreso = t.tipo_transaccion === 'Ingreso';
                            const badge = isIngreso ? '<span class="badge bg-success">Ingreso</span>' : '<span class="badge bg-danger">Egreso</span>';
                            const colorClass = isIngreso ? 'text-success' : 'text-danger';
                            const sign = isIngreso ? '+' : '-';
                            
                            const amount = parseFloat(t.monto);
                            if (isIngreso) totalIngresos += amount;
                            else totalEgresos += amount;
                            
                            let pagoFmt = t.metodo_pago || 'N/A';
                            if (t.banco && t.referencia) {
                                pagoFmt += `<br><small class="text-muted">${t.banco} (${t.referencia})</small>`;
                            }
                            
                            const valFmt = amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits:2});
                            
                            tbody.innerHTML += `
                                <tr>
                                    <td class="text-muted"><small>${timeFmt}</small></td>
                                    <td class="fw-bold">${t.nombre_cliente || 'General'}</td>
                                    <td>${t.concepto}</td>
                                    <td>${pagoFmt}</td>
                                    <td><small>${t.nombre_usuario || 'Admin'}</small></td>
                                    <td class="text-end fw-bold ${colorClass}">${sign} L ${valFmt}</td>
                                </tr>
                            `;
                        });
                        
                        document.getElementById('caja-resumen-ingresos').innerHTML = '+ L ' + totalIngresos.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
                        document.getElementById('caja-resumen-egresos').innerHTML = '- L ' + totalEgresos.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
                        const balance = totalIngresos - totalEgresos;
                        document.getElementById('caja-resumen-balance').innerHTML = 'L ' + balance.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
                    }
                }
            });
    }
</script>

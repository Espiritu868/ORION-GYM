<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold"><i class="fas fa-chart-line text-primary me-2"></i> Reportes Administrativos</h2>
    
    <div class="d-flex gap-2">
        <input type="date" id="fecha-desde" class="form-control" title="Desde">
        <input type="date" id="fecha-hasta" class="form-control" title="Hasta">
        <button class="btn btn-primary" onclick="cargarReportes()"><i class="fas fa-filter"></i> Filtrar</button>
        <button class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
    </div>
</div>

<div id="reporte-content">
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2" style="letter-spacing: 1px;">Total Ingresos</h6>
                    <h2 class="fw-bold mb-0">L <span id="val-ingresos">0.00</span></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-danger text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2" style="letter-spacing: 1px;">Total Egresos</h6>
                    <h2 class="fw-bold mb-0">L <span id="val-egresos">0.00</span></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-success text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2" style="letter-spacing: 1px;">Balance Neto</h6>
                    <h2 class="fw-bold mb-0">L <span id="val-balance">0.00</span></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white pt-3 pb-2 border-0">
                    <h5 class="fw-bold"><i class="fas fa-users text-info"></i> Rendimiento del Período</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                        <span class="fs-5 text-muted">Nuevas Membresías Vendidas</span>
                        <span class="fs-4 fw-bold" id="val-nuevas-memb">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fs-5 text-muted">Total de Asistencias (Check-Ins)</span>
                        <span class="fs-4 fw-bold" id="val-asistencias">0</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white pt-3 pb-2 border-0">
                    <h5 class="fw-bold"><i class="fas fa-chart-pie text-warning"></i> Estado Actual Global</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                        <span class="fs-5 text-muted">Miembros con Membresía Activa</span>
                        <span class="fs-4 fw-bold text-success" id="val-activos">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fs-5 text-muted">Clientes Inactivos (Bajas)</span>
                        <span class="fs-4 fw-bold text-danger" id="val-inactivos">0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * { visibility: hidden; }
        #reporte-content, #reporte-content * { visibility: visible; }
        #reporte-content { position: absolute; left: 0; top: 0; width: 100%; }
        .sidebar, .topbar { display: none !important; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Set dates to current month by default
        const date = new Date();
        const firstDay = new Date(date.getFullYear(), date.getMonth(), 1).toISOString().split('T')[0];
        const today = date.toISOString().split('T')[0];
        
        document.getElementById('fecha-desde').value = firstDay;
        document.getElementById('fecha-hasta').value = today;
        
        cargarReportes();
    });

    function cargarReportes() {
        const desde = document.getElementById('fecha-desde').value;
        const hasta = document.getElementById('fecha-hasta').value;

        fetch(`controllers/ReportesController.php?action=resumen&desde=${desde}&hasta=${hasta}`)
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    document.getElementById('val-ingresos').textContent = res.ingresos;
                    document.getElementById('val-egresos').textContent = res.egresos;
                    document.getElementById('val-balance').textContent = res.balance;
                    
                    document.getElementById('val-nuevas-memb').textContent = res.nuevas_membresias;
                    document.getElementById('val-asistencias').textContent = res.asistencias;
                    
                    document.getElementById('val-activos').textContent = res.miembros_activos;
                    document.getElementById('val-inactivos').textContent = res.miembros_inactivos;
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            });
    }
</script>

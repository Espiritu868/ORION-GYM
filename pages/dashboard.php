<!-- Se incluye Chart.js para gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center p-3 h-100">
            <div class="card-body">
                <div class="display-4 text-primary mb-2"><i class="fas fa-users"></i></div>
                <h3 class="fw-bold mb-0">154</h3>
                <p class="text-muted">Miembros Totales</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center p-3 h-100">
            <div class="card-body">
                <div class="display-4 text-success mb-2"><i class="fas fa-check-circle"></i></div>
                <h3 class="fw-bold mb-0">120</h3>
                <p class="text-muted">Membresías Activas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center p-3 h-100">
            <div class="card-body">
                <div class="display-4 text-warning mb-2"><i class="fas fa-exclamation-circle"></i></div>
                <h3 class="fw-bold mb-0">34</h3>
                <p class="text-muted">Membresías Vencidas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center p-3 h-100 bg-dark text-white">
            <div class="card-body">
                <div class="display-4 text-info mb-2"><i class="fas fa-wallet"></i></div>
                <h3 class="fw-bold mb-0">L 24,500</h3>
                <p class="text-white-50">Ingresos del Mes</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="fw-bold text-primary mb-4">Flujo de Ingresos (Últimos 7 Días)</h5>
                <canvas id="ingresosChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="fw-bold text-danger mb-4">Membresías Próximas a Vencer</h5>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 mb-2">
                        <div>
                            <h6 class="mb-0 fw-bold">Juan Carlos Reyes</h6>
                            <small class="text-muted">Plan Mensual Básico</small>
                        </div>
                        <span class="badge bg-warning text-dark rounded-pill">Mañana</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 mb-2">
                        <div>
                            <h6 class="mb-0 fw-bold">María Fernanda López</h6>
                            <small class="text-muted">Plan Trimestral</small>
                        </div>
                        <span class="badge bg-danger rounded-pill">Hoy</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 mb-2">
                        <div>
                            <h6 class="mb-0 fw-bold">Pedro Martínez</h6>
                            <small class="text-muted">Plan Mensual Básico</small>
                        </div>
                        <span class="badge bg-secondary rounded-pill">Faltan 3 días</span>
                    </li>
                </ul>
                <div class="text-center mt-3">
                    <a href="index.php?page=membresias" class="btn btn-sm btn-outline-primary w-100">Ver todas las membresías</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('ingresosChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(13, 110, 253, 0.5)'); // primary blue
        gradient.addColorStop(1, 'rgba(13, 110, 253, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Ingresos Diarios (L)',
                    data: [1500, 2300, 800, 3100, 1200, 4500, 500],
                    borderColor: '#0d6efd',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0d6efd',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5] }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>

<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="text-primary fw-bold"><i class="fas fa-id-card"></i> Asignación de Membresías</h4>
        <p class="text-muted">Inscriba clientes en planes de gimnasio o renueve suscripciones vencidas.</p>
    </div>
    <div class="col-md-4 text-end">
        <?php if(hasPerm('membresias_create')): ?>
        <button class="btn btn-lg btn-success shadow-sm"><i class="fas fa-plus"></i> Asignar Plan a Cliente</button>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-lg" placeholder="Buscar por DNI o Nombre de Miembro...">
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-lg">
                    <option value="todas">Todas las Membresías</option>
                    <option value="activas" selected>Solo Activas</option>
                    <option value="vencidas">Vencidas / Por vencer</option>
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
            <tbody>
                <!-- DUMMY DATA -->
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Juan Carlos Reyes</h6>
                                <small class="text-muted">ID: 0801-1990-12345</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-dark">Plan Mensual Básico</span></td>
                    <td>
                        <small><i class="fas fa-calendar-alt"></i> 10 Ago 2026<br><i class="fas fa-calendar-check"></i> 10 Sep 2026</small>
                    </td>
                    <td style="width: 25%;">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-bold text-success">Activa</small>
                            <small class="text-muted">Faltan 13 días</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 60%;"></div>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" title="Renovar"><i class="fas fa-sync"></i></button>
                        <button class="btn btn-sm btn-danger" title="Cancelar"><i class="fas fa-times"></i></button>
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">María Fernanda López</h6>
                                <small class="text-muted">ID: 0501-1985-67890</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-dark">Plan Trimestral</span></td>
                    <td>
                        <small><i class="fas fa-calendar-alt"></i> 01 Jun 2026<br><i class="fas fa-calendar-check"></i> 01 Sep 2026</small>
                    </td>
                    <td style="width: 25%;">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-bold text-warning">Por Vencer</small>
                            <small class="text-muted">Faltan 3 días</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 95%;"></div>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" title="Renovar"><i class="fas fa-sync"></i></button>
                        <button class="btn btn-sm btn-danger" title="Cancelar"><i class="fas fa-times"></i></button>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Carlos Antonio Videz</h6>
                                <small class="text-muted">ID: 1804-1999-11223</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-dark">Plan Mensual Básico</span></td>
                    <td>
                        <small><i class="fas fa-calendar-alt"></i> 15 Jul 2026<br><i class="fas fa-calendar-check"></i> 15 Ago 2026</small>
                    </td>
                    <td style="width: 25%;">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-bold text-danger">Vencida</small>
                            <small class="text-muted">Hace 13 días</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: 100%;"></div>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" title="Renovar"><i class="fas fa-sync"></i> Renovar</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

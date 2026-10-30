<?php
$id_cliente = $_GET['id_cliente'] ?? 0;
if(!$id_cliente) {
    echo "<div class='alert alert-danger'>Cliente no especificado.</div>";
    return;
}

$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->prepare("SELECT nombre_completo FROM CLIENTES WHERE id_cliente = :id");
$stmt->execute([':id' => $id_cliente]);
$cliente = $stmt->fetch();

if(!$cliente) {
    echo "<div class='alert alert-danger'>Cliente no encontrado.</div>";
    return;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Constructor de Rutina: <span class="text-warning"><?php echo htmlspecialchars($cliente['nombre_completo']); ?></span></h4>
    <a href="?page=clientes" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Volver a Clientes</a>
</div>

<div class="row" id="app-rutinas">
    <!-- Day Selector -->
    <div class="col-md-3 mb-4">
        <div class="list-group shadow-sm" id="day-selector">
            <button class="list-group-item list-group-item-action active fw-bold" data-dia="1">Lunes</button>
            <button class="list-group-item list-group-item-action fw-bold" data-dia="2">Martes</button>
            <button class="list-group-item list-group-item-action fw-bold" data-dia="3">Miércoles</button>
            <button class="list-group-item list-group-item-action fw-bold" data-dia="4">Jueves</button>
            <button class="list-group-item list-group-item-action fw-bold" data-dia="5">Viernes</button>
            <button class="list-group-item list-group-item-action fw-bold" data-dia="6">Sábado</button>
            <button class="list-group-item list-group-item-action fw-bold" data-dia="7">Domingo</button>
        </div>
    </div>

    <!-- Routine Builder -->
    <div class="col-md-9">
        <div class="card shadow-sm border-0 bg-white text-dark">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold text-dark" id="current-day-label">Rutina del Lunes</h6>
                <button class="btn btn-sm btn-success fw-bold px-3 rounded-pill" id="btn-save-routine"><i class="fas fa-save"></i> Guardar Día</button>
            </div>
            <div class="card-body bg-light text-dark p-2 p-md-4" style="min-height: 500px;">
                <div id="exercises-container" class="mb-4">
                    <!-- Exercises will be injected here -->
                </div>

                <div class="text-center mt-4">
                    <button class="btn btn-primary rounded-pill px-4 fw-bold w-100 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEjercicios" style="max-width: 400px;">
                        <i class="fas fa-plus"></i> Agregar Ejercicio
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ejercicios -->
<div class="modal fade" id="modalEjercicios" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title w-100 text-center fw-bold">Agregar Ejercicio</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div class="p-3 border-bottom sticky-top bg-white shadow-sm">
            <div class="input-group">
                <span class="input-group-text bg-light border-light text-secondary"><i class="fas fa-search"></i></span>
                <input type="text" id="search-ejercicio" class="form-control bg-light border-light" placeholder="Buscar ejercicio...">
            </div>
        </div>
        <div id="lista-ejercicios" class="list-group list-group-flush">
            <!-- Ejercicios injectados via JS -->
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* Light Theme Styles for Routine Builder */
.exercise-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
.exercise-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.exercise-title { font-weight: bold; font-size: 1.1rem; color: #0d6efd; display: flex; align-items: center; }
.exercise-title .icon-circle { background: #e7f1ff; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; margin-right: 10px; color: #0d6efd; }
.descanso-control { display: flex; align-items: center; color: #495057; font-size: 0.85rem; font-weight: 500; cursor: pointer; }

/* Grid Table for Sets */
.sets-grid { display: grid; grid-template-columns: 50px 1fr 60px 100px 40px; gap: 8px; align-items: center; text-align: center; }
.sets-header { font-size: 0.65rem; font-weight: bold; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; padding-bottom: 5px; }
.set-row { margin-bottom: 8px; }
.set-num { background: #f8f9fa; color: #495057; font-size: 0.85rem; font-weight: bold; border-radius: 6px; padding: 4px 0; border: 1px solid #e9ecef; cursor: pointer; width: 100%; transition: all 0.2s; }
.set-num:hover { background: #e9ecef; }
.set-input { background: #fff; border: 1px solid #ced4da; color: #212529; text-align: center; font-weight: bold; font-size: 0.9rem; border-radius: 6px; padding: 4px; width: 100%; transition: all 0.2s; }
.set-input:focus { border-color: #86b7fe; outline: none; box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25); }
.btn-del-set { color: #adb5bd; background: transparent; border: none; font-size: 1.1rem; transition: color 0.2s; }
.btn-del-set:hover { color: #dc3545; }

.btn-add-set-sm { background: #e7f1ff; border: none; color: #0d6efd; width: 100%; padding: 8px; border-radius: 8px; font-weight: bold; font-size: 0.85rem; margin-top: 5px; transition: background 0.2s; }
.btn-add-set-sm:hover { background: #cfe2ff; }

/* Dropdown override */
.dropdown-menu-set { padding: 5px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border: none; }
.dropdown-menu-set .dropdown-item { border-radius: 6px; font-weight: 500; font-size: 0.9rem; }

/* Modal List Styles */
.ejercicio-list-item { background-color: #fff; border-bottom: 1px solid #f8f9fa; cursor: pointer; transition: background 0.2s; display: flex; align-items: center; padding: 15px; }
.ejercicio-list-item:hover { background-color: #f8f9fa; }
.ejercicio-img-placeholder { width: 50px; height: 50px; border-radius: 50%; background-color: #f8f9fa; color: #0d6efd; border: 1px solid #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; margin-right: 15px; overflow: hidden; }
.ejercicio-img-placeholder img { width: 100%; height: 100%; object-fit: cover; }
.ejercicio-info { flex-grow: 1; }
.ejercicio-name { font-weight: bold; font-size: 1rem; color: #212529; margin-bottom: 2px; }
.ejercicio-muscle { font-size: 0.8rem; color: #6c757d; }

/* Responsive Adjustments */
@media (max-width: 768px) {
    #day-selector { flex-direction: row; overflow-x: auto; white-space: nowrap; padding-bottom: 5px; }
    #day-selector button { flex: 0 0 auto; width: auto; border-radius: 20px !important; margin-right: 5px; margin-bottom: 0; }
    .sets-grid { grid-template-columns: 40px 1fr 45px 80px 30px; gap: 3px; }
    .set-input { font-size: 0.75rem; padding: 2px; }
    .set-num { font-size: 0.7rem; padding: 2px 0; }
    .sets-header { font-size: 0.55rem; letter-spacing: 0; }
    .btn-del-set { font-size: 0.9rem; }
    .exercise-title { font-size: 0.95rem; }
    .exercise-title .icon-circle { width: 24px; height: 24px; font-size: 0.7rem; }
    .card-body { padding: 10px !important; }
}
</style>

<script>
const ID_CLIENTE = <?php echo $id_cliente; ?>;
let currentDay = 1;
let catalog = [];
let currentExercises = [];
let isDirty = false;

// Bootstrap modal instance
let modalInstance = null;

// Warn before leaving if unsaved changes
window.addEventListener('beforeunload', function (e) {
    if (isDirty) {
        e.preventDefault();
        e.returnValue = '';
    }
});

document.addEventListener('DOMContentLoaded', async () => {
    modalInstance = new bootstrap.Modal(document.getElementById('modalEjercicios'));

    // Load catalog
    const resCat = await fetch('controllers/RutinaAvanzadaController.php?action=get_catalogo');
    const catData = await resCat.json();
    catalog = catData.data || [];
    
    renderCatalogList(catalog);

    // Search logic
    document.getElementById('search-ejercicio').addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        const filtered = catalog.filter(ej => 
            ej.nombre.toLowerCase().includes(term) || 
            ej.grupo_muscular.toLowerCase().includes(term)
        );
        renderCatalogList(filtered);
    });

    // Day selection logic
    document.querySelectorAll('#day-selector button').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const newDay = parseInt(e.target.getAttribute('data-dia'));
            if (newDay === currentDay) return;

            if (isDirty) {
                Swal.fire({
                    title: '¿Tienes cambios sin guardar!',
                    text: "Si cambias de día, perderás la rutina que estás editando. ¿Deseas descartar los cambios?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, descartar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                }).then((result) => {
                    if (result.isConfirmed) {
                        switchDay(newDay, e.target);
                    }
                });
            } else {
                switchDay(newDay, e.target);
            }
        });
    });

    // Save routine
    document.getElementById('btn-save-routine').addEventListener('click', async () => {
        const btn = document.getElementById('btn-save-routine');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        const payload = {
            id_cliente: ID_CLIENTE,
            dia_semana: currentDay,
            ejercicios: currentExercises
        };

        const res = await fetch('controllers/RutinaAvanzadaController.php?action=save_rutina_dia', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar Día';
        
        if(data.success) {
            isDirty = false;
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Rutina Guardada', showConfirmButton: false, timer: 1500 });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });

    // Initial load
    loadDayRoutine();
});

function switchDay(newDay, targetBtn) {
    document.querySelectorAll('#day-selector button').forEach(b => b.classList.remove('active'));
    targetBtn.classList.add('active');
    currentDay = newDay;
    document.getElementById('current-day-label').innerText = `Rutina del ${targetBtn.innerText}`;
    isDirty = false;
    loadDayRoutine();
}

function renderCatalogList(list) {
    const container = document.getElementById('lista-ejercicios');
    container.innerHTML = '';
    
    if (list.length === 0) {
        container.innerHTML = '<div class="text-center p-4 text-secondary">No se encontraron ejercicios.</div>';
        return;
    }

    list.forEach(ej => {
        const div = document.createElement('div');
        div.className = 'ejercicio-list-item';
        
        const imgHtml = ej.imagen_url 
            ? `<img src="${ej.imagen_url}" alt="${ej.nombre}">` 
            : `<i class="fas fa-dumbbell"></i>`;

        div.innerHTML = `
            <div class="ejercicio-img-placeholder">
                ${imgHtml}
            </div>
            <div class="ejercicio-info">
                <div class="ejercicio-name">${ej.nombre}</div>
                <div class="ejercicio-muscle">${ej.grupo_muscular}</div>
            </div>
            <i class="fas fa-chevron-right text-secondary ms-auto"></i>
        `;

        div.addEventListener('click', () => {
            selectExercise(ej);
        });

        container.appendChild(div);
    });
}

function selectExercise(ejDef) {
    currentExercises.push({
        id_ejercicio: ejDef.id_ejercicio,
        nombre: ejDef.nombre,
        descanso_segundos: 120,
        series: [{ lbs_obj: 0, reps_obj: '10', tipo_serie: 'N' }]
    });
    
    isDirty = true;
    modalInstance.hide();
    
    document.getElementById('search-ejercicio').value = '';
    renderCatalogList(catalog);
    
    renderExercises();
}

async function loadDayRoutine() {
    currentExercises = [];
    renderExercises(); 
    
    const res = await fetch(`controllers/RutinaAvanzadaController.php?action=get_rutina_dia&id_cliente=${ID_CLIENTE}&dia_semana=${currentDay}`);
    const data = await res.json();
    if(data.success && data.data) {
        currentExercises = data.data.map(ej => ({
            id_ejercicio: ej.id_ejercicio,
            nombre: ej.nombre,
            descanso_segundos: ej.descanso_segundos,
            series: ej.series.map(s => ({
                lbs_obj: parseFloat(s.lbs_obj),
                reps_obj: s.reps_obj || '',
                tipo_serie: s.tipo_serie || 'N'
            }))
        }));
    }
    renderExercises();
}

function getSetTypeLabel(type, num) {
    if (type === 'W') return `<span class="text-warning fw-bold" title="Calentamiento">W</span>`;
    if (type === 'D') return `<span class="text-info fw-bold" title="Drop Set">D</span>`;
    if (type === 'F') return `<span class="text-danger fw-bold" title="Al Fallo">F</span>`;
    return `<span class="fw-bold">${num}</span>`;
}

function renderExercises() {
    const container = document.getElementById('exercises-container');
    container.innerHTML = '';

    if (currentExercises.length === 0) {
        container.innerHTML = '<div class="text-center text-secondary py-5"><i class="fas fa-dumbbell fa-3x mb-3 opacity-50"></i><br>No hay ejercicios para este día.</div>';
        return;
    }

    currentExercises.forEach((ej, exIndex) => {
        const div = document.createElement('div');
        div.className = 'exercise-card';
        
        let seriesHtml = `
            <div class="sets-grid sets-header">
                <div>SERIE</div>
                <div class="text-start">ANTERIOR</div>
                <div>LBS</div>
                <div>RANGO REPS</div>
                <div></div>
            </div>
        `;

        let workingSetCount = 0;
        ej.series.forEach((s, sIndex) => {
            let parts = (s.reps_obj || '').toString().split('-');
            let minReps = parts[0] || '';
            let maxReps = parts.length > 1 ? parts[1] : '';
            
            let displayNum = '';
            if (s.tipo_serie === 'N' || s.tipo_serie === 'F') {
                workingSetCount++;
                displayNum = workingSetCount;
            }

            seriesHtml += `
            <div class="sets-grid set-row">
                <div class="dropdown">
                    <button class="set-num dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border:none; padding:4px 0;">
                        ${getSetTypeLabel(s.tipo_serie, displayNum)}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-set">
                        <li><a class="dropdown-item" href="#" onclick="updateSet(${exIndex}, ${sIndex}, 'tipo_serie', 'W'); return false;"><span class="text-warning fw-bold me-2">W</span> Calentamiento</a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateSet(${exIndex}, ${sIndex}, 'tipo_serie', 'N'); return false;"><span class="text-secondary fw-bold me-2">N</span> Normal</a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateSet(${exIndex}, ${sIndex}, 'tipo_serie', 'D'); return false;"><span class="text-info fw-bold me-2">D</span> Drop Set</a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateSet(${exIndex}, ${sIndex}, 'tipo_serie', 'F'); return false;"><span class="text-danger fw-bold me-2">F</span> Al Fallo</a></li>
                    </ul>
                </div>
                
                <div class="text-start text-secondary" style="font-size: 0.75rem;">-</div>
                <div><input type="number" class="set-input" value="${s.lbs_obj}" onchange="updateSet(${exIndex}, ${sIndex}, 'lbs_obj', this.value)"></div>
                
                <div class="d-flex align-items-center">
                    <input type="number" class="set-input me-1" value="${minReps}" onchange="updateReps(${exIndex}, ${sIndex}, 'min', this.value)" placeholder="Min">
                    <span class="text-muted mx-1" style="font-size:0.8rem;">-</span>
                    <input type="number" class="set-input ms-1" value="${maxReps}" onchange="updateReps(${exIndex}, ${sIndex}, 'max', this.value)" placeholder="Max">
                </div>
                
                <div><button class="btn-del-set" onclick="removeSet(${exIndex}, ${sIndex})" title="Eliminar Serie"><i class="fas fa-times"></i></button></div>
            </div>
            `;
        });

        div.innerHTML = `
            <div class="exercise-header">
                <div class="exercise-title">
                    <div class="icon-circle"><i class="fas fa-dumbbell"></i></div>
                    ${ej.nombre}
                </div>
                <button class="btn btn-sm btn-link text-danger p-0 ms-3 text-decoration-none" onclick="removeExercise(${exIndex})" title="Eliminar Ejercicio"><i class="fas fa-trash"></i></button>
            </div>
            
            <div class="descanso-control mb-3">
                <i class="fas fa-stopwatch me-1"></i> Descanso: 
                <input type="number" value="${ej.descanso_segundos}" class="form-control form-control-sm bg-light text-primary border-0 ms-2 text-center" style="width:50px; padding: 2px;" onchange="updateSet(${exIndex}, null, 'descanso_segundos', this.value)">s
            </div>

            <div class="sets-container mb-2">
                ${seriesHtml}
            </div>
            
            <button class="btn-add-set-sm" onclick="addSet(${exIndex})"><i class="fas fa-plus"></i> Agregar Serie</button>
        `;
        container.appendChild(div);
    });
}

function updateReps(exIndex, sIndex, type, value) {
    isDirty = true;
    let current = currentExercises[exIndex].series[sIndex].reps_obj || '';
    let parts = current.toString().split('-');
    let min = parts[0] || '';
    let max = parts[1] || '';
    
    if (type === 'min') min = value;
    if (type === 'max') max = value;
    
    if (max && min) {
        currentExercises[exIndex].series[sIndex].reps_obj = min + '-' + max;
    } else if (min) {
        currentExercises[exIndex].series[sIndex].reps_obj = min;
    } else if (max) {
        currentExercises[exIndex].series[sIndex].reps_obj = max; // fallback
    } else {
        currentExercises[exIndex].series[sIndex].reps_obj = '';
    }
}

function updateSet(exIndex, sIndex, field, value) {
    isDirty = true;
    if (sIndex === null) {
        currentExercises[exIndex][field] = value;
    } else {
        currentExercises[exIndex].series[sIndex][field] = value;
        if (field === 'tipo_serie') {
            renderExercises();
        }
    }
}

function addSet(exIndex) {
    isDirty = true;
    const series = currentExercises[exIndex].series;
    const lastSet = series.length > 0 ? series[series.length - 1] : { lbs_obj: 0, reps_obj: '10', tipo_serie: 'N' };
    series.push({ ...lastSet });
    renderExercises();
}

function removeSet(exIndex, sIndex) {
    isDirty = true;
    currentExercises[exIndex].series.splice(sIndex, 1);
    renderExercises();
}

function removeExercise(exIndex) {
    isDirty = true;
    currentExercises.splice(exIndex, 1);
    renderExercises();
}
</script>

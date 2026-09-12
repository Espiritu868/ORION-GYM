<?php
$id_cliente = $_GET['id_cliente'] ?? 0;
if(!$id_cliente) {
    echo "<div class='alert alert-danger'>Cliente no especificado.</div>";
    return;
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT nombre_completo, identidad FROM CLIENTES WHERE id_cliente = :id");
$stmt->execute([':id' => $id_cliente]);
$cliente = $stmt->fetch();

if(!$cliente) {
    echo "<div class='alert alert-danger'>Cliente no encontrado.</div>";
    return;
}

$stmt_medidas = $conn->prepare("SELECT * FROM MEDIDAS_CLIENTE WHERE id_cliente = :id ORDER BY fecha_evaluacion DESC");
$stmt_medidas->execute([':id' => $id_cliente]);
$medidas = $stmt_medidas->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Progreso de: <span class="text-primary"><?php echo htmlspecialchars($cliente['nombre_completo']); ?></span></h4>
    <a href="?page=clientes" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver a Clientes</a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Registrar Nueva Evaluación</h5>
            </div>
            <div class="card-body">
                <form id="form-medidas">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <div class="mb-3">
                        <label>Peso (kg/lbs)</label>
                        <input type="number" step="0.01" name="peso" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label>Pecho (cm)</label>
                            <input type="number" step="0.01" name="pecho" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label>Cintura (cm)</label>
                            <input type="number" step="0.01" name="cintura" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label>Cadera (cm)</label>
                            <input type="number" step="0.01" name="cadera" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label>Brazo (cm)</label>
                            <input type="number" step="0.01" name="brazo" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label>Pierna (cm)</label>
                            <input type="number" step="0.01" name="pierna" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label>Pantorrilla (cm)</label>
                            <input type="number" step="0.01" name="pantorrilla" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="btn-save-medida">Guardar Evaluación</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Historial de Medidas</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Peso</th>
                            <th>Pecho</th>
                            <th>Cintura</th>
                            <th>Cadera</th>
                            <th>Brazo</th>
                            <th>Pierna</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($medidas as $m): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($m['fecha_evaluacion'])); ?></td>
                            <td><?php echo $m['peso']; ?></td>
                            <td><?php echo $m['pecho']; ?></td>
                            <td><?php echo $m['cintura']; ?></td>
                            <td><?php echo $m['cadera']; ?></td>
                            <td><?php echo $m['brazo']; ?></td>
                            <td><?php echo $m['pierna']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($medidas)): ?>
                        <tr><td colspan="7" class="text-center">No hay registros de progreso.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('form-medidas').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-medida');
    btn.disabled = true;
    
    fetch('controllers/ProgresoController.php?action=guardar_medida', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            Swal.fire('¡Éxito!', res.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', res.message, 'error');
            btn.disabled = false;
        }
    });
});
</script>

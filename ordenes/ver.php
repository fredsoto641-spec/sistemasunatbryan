<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

/* Validar ID */
if (!isset($_GET['id'])) {
    die("Orden no especificada");
}

$id = intval($_GET['id']);
$orden = $conn->query("SELECT * FROM ordenes WHERE id = $id")->fetch_assoc();

if (!$orden) {
    die("Orden no encontrada");
}

/* 📅 Fecha de generación (SIEMPRE existe) */
$fecha_generacion = new DateTime($orden['fecha']);
$hoy = new DateTime();

/* 📅 Fecha de vencimiento (PUEDE SER NULL) */
$fecha_venc = null;
$dias = null;
$esta_vencido = false;

if (!empty($orden['fecha_vencimiento'])) {
    $fecha_venc = new DateTime($orden['fecha_vencimiento']);
    $diff = $hoy->diff($fecha_venc);
    $dias = $diff->days;
    $esta_vencido = $hoy > $fecha_venc;
}

/* 🎨 Estado y color */
if ($orden['estado_credito'] === 'Pagado') {
    $estado = 'Pagado';
    $color  = 'success';
} elseif ($fecha_venc && $esta_vencido) {
    $estado = 'Vencido';
    $color  = 'danger';
} elseif ($fecha_venc && $dias <= 5) {
    $estado = 'Por vencer';
    $color  = 'warning';
} else {
    $estado = 'Pendiente';
    $color  = 'secondary';
}
?>

<div class="card shadow col-md-8 mx-auto">
  <div class="card-header bg-primary text-white">
    📄 Detalle de la Orden de Compra
  </div>

  <div class="card-body">

    <table class="table table-bordered">

      <tr>
        <th width="35%">Orden</th>
        <td><?= htmlspecialchars($orden['orden_codigo']) ?></td>
      </tr>

      <tr>
        <th>Cliente</th>
        <td><?= htmlspecialchars($orden['cliente']) ?></td>
      </tr>

      <!-- FECHA DE GENERACIÓN -->
      <tr>
        <th>Fecha de generación</th>
        <td><?= $fecha_generacion->format('Y-m-d') ?></td>
      </tr>

      <!-- FECHA DE VENCIMIENTO -->
      <tr>
        <th>Fecha de vencimiento</th>
        <td>
          <?= $fecha_venc ? $fecha_venc->format('Y-m-d') : '—' ?>
        </td>
      </tr>

      <!-- DÍAS RESTANTES -->
      <tr>
        <th>Días restantes</th>
        <td>
          <?php if ($estado === 'Pagado'): ?>
            —
          <?php elseif (!$fecha_venc): ?>
            —
          <?php elseif ($esta_vencido): ?>
            <span class="text-danger fw-bold">
              Vencido hace <?= $dias ?> días
            </span>
          <?php else: ?>
            <span class="text-warning fw-bold">
              Faltan <?= $dias ?> días
            </span>
          <?php endif; ?>
        </td>
      </tr>

      <!-- FECHA DE PAGO -->
      <tr>
        <th>Fecha de pago</th>
        <td><?= $orden['fecha_pago'] ?: 'Pendiente' ?></td>
      </tr>

      <!-- ESTADO -->
      <tr>
        <th>Estado del crédito</th>
        <td>
          <span class="badge bg-<?= $color ?>">
            <?= $estado ?>
          </span>
        </td>
      </tr>

      <tr>
        <th>Total</th>
        <td><strong>S/ <?= number_format($orden['total'], 2) ?></strong></td>
      </tr>

    </table>

    <?php if ($estado !== 'Pagado'): ?>
      <a href="pagar_credito.php?id=<?= $orden['id'] ?>"
         class="btn btn-success">
         ✔ Marcar como Pagado
      </a>
    <?php endif; ?>

    <a href="index.php" class="btn btn-secondary ms-2">
      ⬅ Volver
    </a>

  </div>
</div>

<?php include "../includes/footer.php"; ?>

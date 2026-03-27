<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

$id = intval($_GET['id'] ?? 0);

$f = $conn->query("SELECT * FROM facturas WHERE id=$id")->fetch_assoc();
if (!$f) {
    die("Factura no encontrada");
}

/* PRODUCTOS */
$detalle = $conn->query("
  SELECT *
  FROM factura_detalle
  WHERE factura_id = $id
");

/* PAGOS */
$pagos = $conn->query("
  SELECT *
  FROM factura_pagos
  WHERE factura_id = $id
  ORDER BY fecha_pago DESC
");

/* CALCULOS */
$hoy = new DateTime();
$venc = new DateTime($f['fecha_vencimiento']);
$vencido = $hoy > $venc;

/* total real */
$resTotal = $conn->query("
  SELECT IFNULL(SUM(subtotal),0) AS total_real
  FROM factura_detalle
  WHERE factura_id = $id
");
$total = floatval($resTotal->fetch_assoc()['total_real']);

/* pagado */
$resPagado = $conn->query("
  SELECT IFNULL(SUM(monto),0) AS total_pagado
  FROM factura_pagos
  WHERE factura_id = $id
");
$pagado = floatval($resPagado->fetch_assoc()['total_pagado']);

$saldo = $total - $pagado;

/* estado */
if ($saldo <= 0) {
    $estado = 'Pagado';
    $color  = 'success';
} elseif ($vencido) {
    $estado = 'Vencido';
    $color  = 'danger';
} else {
    $estado = 'Pendiente';
    $color  = 'warning';
}
?>

<div class="erp-wrapper col-md-10 mx-auto">

<div class="erp-header">🧾 Detalle de la Factura</div>

<div class="erp-body">

<!-- DATOS FACTURA -->
<div class="erp-section">
<h6 class="section-title">Datos de la factura</h6>

<table class="table table-bordered">
<tr><th>Serie</th><td><?= htmlspecialchars($f['serie']) ?></td></tr>
<tr><th>Correlativo</th><td><?= htmlspecialchars($f['correlativo']) ?></td></tr>
<tr><th>Cliente</th><td><?= htmlspecialchars($f['cliente']) ?></td></tr>
<tr><th>Documento</th><td><?= htmlspecialchars($f['ruc']) ?></td></tr>
<tr><th>Dirección</th><td><?= htmlspecialchars($f['direccion']) ?></td></tr>
<tr><th>Emisión</th><td><?= htmlspecialchars($f['fecha']) ?></td></tr>
<tr><th>Vencimiento</th><td><?= htmlspecialchars($f['fecha_vencimiento']) ?></td></tr>
<tr>
<th>Estado</th>
<td><span class="badge bg-<?= $color ?>"><?= $estado ?></span></td>
</tr>
</table>
</div>

<!-- RESUMEN -->
<div class="erp-section">
<h6 class="section-title">Resumen</h6>
<div class="row">
<div class="col text-primary"><strong>Total:</strong> S/ <?= number_format($total,2) ?></div>
<div class="col text-success"><strong>Pagado:</strong> S/ <?= number_format($pagado,2) ?></div>
<div class="col text-danger"><strong>Saldo:</strong> S/ <?= number_format($saldo,2) ?></div>
</div>
</div>

<!-- PRODUCTOS -->
<div class="erp-section">
<h6 class="section-title">Productos facturados</h6>

<table class="table table-hover">
<thead class="table-light">
<tr>
<th>Producto</th>
<th class="text-center">Cantidad</th>
<th class="text-end">Precio</th>
<th class="text-end">Subtotal</th>
</tr>
</thead>
<tbody>
<?php while ($d = $detalle->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($d['producto']) ?></td>
<td class="text-center"><?= $d['cantidad'] ?></td>
<td class="text-end">S/ <?= number_format($d['precio'],2) ?></td>
<td class="text-end fw-bold">S/ <?= number_format($d['subtotal'],2) ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

<!-- PAGOS -->
<?php if ($pagos->num_rows > 0): ?>
<div class="erp-section">
<h6 class="section-title">Historial de pagos</h6>

<table class="table table-sm table-bordered">
<thead>
<tr>
<th>Fecha</th>
<th>Monto</th>
<th>Método</th>
<th>Banco</th>
<th>Operación</th>
</tr>
</thead>
<tbody>
<?php while($p = $pagos->fetch_assoc()): ?>
<tr>
<td><?= date('d/m/Y H:i', strtotime($p['fecha_pago'])) ?></td>
<td>S/ <?= number_format($p['monto'],2) ?></td>
<td><?= htmlspecialchars($p['metodo_pago']) ?></td>
<td><?= htmlspecialchars($p['banco']) ?></td>
<td><?= htmlspecialchars($p['numero_operacion']) ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
<?php endif; ?>

<div class="erp-actions">
<a href="../pdf/factura.php?id=<?= $f['id'] ?>" target="_blank" class="btn btn-danger">🖨 Imprimir PDF</a>
<a href="index.php" class="btn btn-secondary">⬅ Volver</a>
</div>

</div>
</div>

<?php include "../includes/footer.php"; ?>

<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

if (!isset($_GET['id'])) {
  echo "<div class='alert alert-danger'>❌ Letra no válida</div>";
  include "../includes/footer.php";
  exit;
}

$id = intval($_GET['id']);

$letra = $conn->query("SELECT * FROM letras WHERE id=$id")->fetch_assoc();
if (!$letra) {
  echo "<div class='alert alert-danger'>❌ Letra no encontrada</div>";
  include "../includes/footer.php";
  exit;
}

/* FACTURAS */
$facturas = $conn->query("
  SELECT f.id, f.cliente, f.total, o.orden_codigo
  FROM letras_facturas lf
  JOIN facturas f ON f.id = lf.factura_id
  LEFT JOIN ordenes o ON o.id = f.orden_id
  WHERE lf.letra_id = $id
");

/* ORDENES */
$ordenes = $conn->query("
  SELECT o.orden_codigo, o.total
  FROM letras_ordenes lo
  JOIN ordenes o ON o.id = lo.orden_id
  WHERE lo.letra_id = $id
");
?>

<div class="card shadow col-md-8 mx-auto">
  <div class="card-header bg-dark text-white">📜 Detalle de la Letra</div>

  <div class="card-body">
    <ul class="list-group mb-4">
      <li class="list-group-item"><b>Código:</b> <?= htmlspecialchars($letra['codigo']) ?></li>
      <li class="list-group-item"><b>N°:</b> <?= $letra['numero'] ?></li>
      <li class="list-group-item"><b>Monto:</b> S/ <?= number_format($letra['monto'],2) ?></li>
      <li class="list-group-item"><b>Emisión:</b> <?= $letra['fecha_emision'] ?></li>
      <li class="list-group-item"><b>Vencimiento:</b> <?= $letra['fecha_vencimiento'] ?></li>
      <li class="list-group-item">
        <b>Estado:</b>
        <span class="badge bg-<?= $letra['estado']==='Pendiente'?'warning':'success' ?>">
          <?= $letra['estado'] ?>
        </span>
      </li>
    </ul>

    <h5>Facturas</h5>
    <?php if ($facturas->num_rows > 0): ?>
      <ul>
        <?php while ($f = $facturas->fetch_assoc()): ?>
          <li>
            <strong>Nro de factura:</strong> <?= $f['orden_codigo'] ?? '—' ?>
            | <?= htmlspecialchars($f['cliente']) ?>
            | <strong>S/</strong> <?= number_format($f['total'],2) ?>
          </li>
        <?php endwhile; ?>
      </ul>
    <?php else: ?><p>—</p><?php endif; ?>

    <h5>Órdenes</h5>
    <?php if ($ordenes->num_rows > 0): ?>
      <ul>
        <?php while ($o = $ordenes->fetch_assoc()): ?>
          <li>
            <strong>Nro de orden:</strong> <?= htmlspecialchars($o['orden_codigo']) ?>
            | <strong>S/</strong> <?= number_format($o['total'],2) ?>
          </li>
        <?php endwhile; ?>
      </ul>
    <?php else: ?><p>—</p><?php endif; ?>

    <div class="d-flex gap-2 flex-wrap mt-3">
      <a href="../pdf/letra.php?id=<?= $letra['id'] ?>" target="_blank" class="btn btn-info">🖨 Imprimir</a>

      <?php if ($letra['estado'] !== 'Pagado'): ?>
        <a href="pagar.php?id=<?= $letra['id'] ?>" class="btn btn-success">💰 Pagar</a>
      <?php endif; ?>

      <a href="editar.php?id=<?= $letra['id'] ?>" class="btn btn-warning">✏️ Editar</a>
      <a href="index.php" class="btn btn-secondary">⬅ Volver</a>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>

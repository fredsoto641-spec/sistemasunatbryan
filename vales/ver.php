<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

$id = intval($_GET['id'] ?? 0);

/* ===============================
   VALE + CLIENTE
=============================== */
$vale = $conn->query("
  SELECT 
    v.*,
    c.documento,
    c.nombre
  FROM vales v
  INNER JOIN clientes c ON c.id = v.cliente_id
  WHERE v.id = $id
")->fetch_assoc();

if (!$vale) {
  echo "<div class='alert alert-danger'>Vale no encontrado</div>";
  include "../includes/footer.php";
  exit;
}

/* ===============================
   PRODUCTOS
=============================== */
$productos = $conn->query("
  SELECT producto, cantidad, precio, subtotal
  FROM vale_detalle
  WHERE vale_id = $id
");

/* ===============================
   PAGOS
=============================== */
$pagos = $conn->query("
  SELECT *
  FROM vale_pagos
  WHERE vale_id = $id
  ORDER BY fecha_pago DESC
");
?>

<div class="card shadow col-md-8 mx-auto">

  <div class="card-header bg-primary text-white">
    📄 Detalle del Vale
  </div>

  <div class="card-body">

    <p><strong>Cliente:</strong><br>
      <?= htmlspecialchars($vale['documento']) ?> —
      <?= htmlspecialchars($vale['nombre']) ?>
    </p>

    <p><strong>Documento:</strong>
      <?= htmlspecialchars($vale['tipo_documento']) ?>
      <?= htmlspecialchars($vale['serie']) ?>-<?= htmlspecialchars($vale['correlativo']) ?>
    </p>

    <p><strong>Fecha de emisión:</strong>
      <?= date('d/m/Y', strtotime($vale['fecha_emision'])) ?>
    </p>

    <hr>

    <!-- ================= PRODUCTOS ================= -->
    <?php if ($productos && $productos->num_rows > 0): ?>
      <h6>Productos</h6>

      <table class="table table-bordered table-sm">
        <thead class="table-light">
          <tr>
            <th>Producto</th>
            <th width="80">Cant.</th>
            <th width="120">Precio</th>
            <th width="120">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($p = $productos->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($p['producto']) ?></td>
              <td class="text-center"><?= $p['cantidad'] ?></td>
              <td>S/ <?= number_format($p['precio'],2) ?></td>
              <td class="fw-bold">S/ <?= number_format($p['subtotal'],2) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <hr>

    <!-- ================= TOTALES ================= -->
    <div class="row mb-3">
      <div class="col">
        <strong>Total:</strong><br>
        S/ <?= number_format($vale['monto_total'], 2) ?>
      </div>
      <div class="col text-success">
        <strong>Pagado:</strong><br>
        S/ <?= number_format($vale['monto_pagado'], 2) ?>
      </div>
      <div class="col text-danger">
        <strong>Saldo:</strong><br>
        S/ <?= number_format($vale['saldo'], 2) ?>
      </div>
    </div>

    <p>
      <strong>Estado:</strong>
      <span class="badge bg-<?= $vale['estado']=='Pagado'?'success':'warning' ?>">
        <?= $vale['estado'] ?>
      </span>
    </p>

    <hr>

    <!-- ================= PAGOS ================= -->
    <?php if ($pagos->num_rows > 0): ?>
      <h6>Pagos realizados</h6>

      <table class="table table-bordered table-sm">
        <thead class="table-light">
          <tr>
            <th>Fecha</th>
            <th>Monto</th>
            <th>Método</th>
            <th>Banco</th>
            <th>Operación</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($pg = $pagos->fetch_assoc()): ?>
            <tr>
              <td><?= date('d/m/Y H:i', strtotime($pg['fecha_pago'])) ?></td>
              <td>S/ <?= number_format($pg['monto'],2) ?></td>
              <td><?= htmlspecialchars($pg['metodo_pago']) ?></td>
              <td><?= htmlspecialchars($pg['banco'] ?: '-') ?></td>
              <td><?= htmlspecialchars($pg['numero_operacion'] ?: '-') ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="text-muted">No hay pagos registrados</p>
    <?php endif; ?>

    <a href="index.php" class="btn btn-secondary mt-3">⬅ Volver</a>

  </div>
</div>

<?php include "../includes/footer.php"; ?>

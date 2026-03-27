<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

$id = intval($_GET['id'] ?? 0);

/* ================= PRODUCTO ================= */
$producto = $conn->query("
  SELECT * FROM productos WHERE id = $id
")->fetch_assoc();

if (!$producto) {
  echo "<div class='alert alert-danger'>Producto no encontrado</div>";
  include "../includes/footer.php";
  exit;
}

/* ================= KARDEX ================= */
$movimientos = $conn->query("
  SELECT *
  FROM producto_movimientos
  WHERE producto_id = $id
  ORDER BY fecha ASC, id ASC
");
?>

<div class="erp-wrapper col-md-10 mx-auto">

  <div class="erp-header d-flex justify-content-between align-items-center">
    <span>📊 Kardex del Producto</span>
    <a href="index.php" class="btn btn-secondary btn-sm">⬅ Volver</a>
  </div>

  <div class="erp-body">

    <!-- ================= DATOS PRODUCTO ================= -->
    <div class="erp-section">
      <h6 class="section-title">Producto</h6>

      <p><strong>Código:</strong> <?= htmlspecialchars($producto['codigo']) ?></p>
      <p><strong>Nombre:</strong> <?= htmlspecialchars($producto['nombre']) ?></p>
      <p><strong>Stock actual:</strong> <?= $producto['stock'] ?></p>
      <p><strong>Precio venta:</strong> S/ <?= number_format($producto['precio'],2) ?></p>
    </div>

    <!-- ================= TABLA KARDEX ================= -->
    <div class="erp-section">
      <h6 class="section-title">Movimientos</h6>

      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Fecha</th>
              <th>Documento</th>
              <th>T/M</th>
              <th class="text-end">Costo</th>
              <th class="text-end">Ingreso</th>
              <th class="text-end">Salida</th>
              <th class="text-end">Saldo</th>
              <th>Medida</th>
              <th>Proveedor</th>
            </tr>
          </thead>

          <tbody>
          <?php
          $saldo = 0;
          if ($movimientos->num_rows > 0):
            while ($m = $movimientos->fetch_assoc()):
              if ($m['tipo_movimiento'] === 'Ingreso') {
                $saldo += $m['cantidad'];
                $ingreso = $m['cantidad'];
                $salida = '';
              } else {
                $saldo -= $m['cantidad'];
                $ingreso = '';
                $salida = $m['cantidad'];
              }
          ?>
            <tr>
              <td><?= date('d/m/Y', strtotime($m['fecha'])) ?></td>
              <td><?= htmlspecialchars($m['documento'] ?? '—') ?></td>
              <td>
  <span class="badge bg-<?= $m['tipo_movimiento']=='Ingreso'?'success':'danger' ?>">
    <?= $m['tipo_movimiento'] ?>
  </span>
</td>


              <td class="text-end">
                <?= $m['costo'] ? 'S/ '.number_format($m['costo'],2) : '—' ?>
              </td>
              <td class="text-end"><?= $ingreso ?></td>
              <td class="text-end"><?= $salida ?></td>
              <td class="text-end"><?= $saldo ?></td>
              <td><?= htmlspecialchars($m['medida'] ?? '—') ?></td>
              <td><?= htmlspecialchars($m['proveedor'] ?? '—') ?></td>
            </tr>
          <?php
            endwhile;
          else:
          ?>
            <tr>
              <td colspan="9" class="text-center text-muted">
                No hay movimientos registrados
              </td>
            </tr>
          <?php endif; ?>
          </tbody>

        </table>
      </div>
    </div>

  </div>
</div>

<?php include "../includes/footer.php"; ?>

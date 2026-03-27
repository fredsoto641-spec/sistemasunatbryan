<?php
require_once "../includes/auth.php";
require_once "../config/conexion.php";
require_once "../includes/header.php";

/* ===============================
   PARÁMETROS
=============================== */
$vendedor_id = $_GET['vendedor_id'] ?? null;
$desde = $_GET['desde'] ?? null;
$hasta = $_GET['hasta'] ?? null;

if (!$vendedor_id || !$desde || !$hasta) {
  die("Parámetros incompletos");
}

/* ===============================
   DATOS DEL VENDEDOR
=============================== */
$vendedor = $conn->query("
  SELECT CONCAT(nombres,' ',apellidos) AS nombre
  FROM vendedores
  WHERE id = $vendedor_id
")->fetch_assoc();

if (!$vendedor) {
  die("Vendedor no encontrado");
}

/* ===============================
   PAGOS INDIVIDUALES (DETALLE)
=============================== */
$sql = "
SELECT
  d.fecha,
  d.monto,
  v.costo_transporte
FROM ventas_vendedores_detalle d
INNER JOIN ventas_vendedores v ON v.id = d.venta_id
WHERE v.vendedor_id = $vendedor_id
  AND v.estado = 1
  AND d.fecha BETWEEN '$desde' AND '$hasta'
ORDER BY d.fecha, d.id
";

$pagos = $conn->query($sql);

/* ===============================
   TOTALES
=============================== */
$totalVenta = 0;
$totalMovilidad = 0;
$totalNeto = 0;
?>

<div class="container-fluid">

  <!-- HEADER -->
  <div class="card shadow mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        📋 Detalle de pagos<br>
        <small class="text-light">
          <?= htmlspecialchars($vendedor['nombre']) ?> |
          <?= $desde ?> al <?= $hasta ?>
        </small>
      </div>

      <a href="index.php" class="btn btn-sm btn-secondary">
        ⬅ Volver
      </a>
    </div>
  </div>

  <!-- TABLA -->
  <div class="card shadow">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">
          <thead class="table-light">
            <tr>
              <th>Fecha</th>
              <th>Monto vendido</th>
              <th>Movilidad / Maestro</th>
              <th>Total neto</th>
              <th>35% Vendedor</th>
              <th>65% Tienda</th>
            </tr>
          </thead>
          <tbody>

          <?php if ($pagos && $pagos->num_rows > 0): ?>
            <?php while ($row = $pagos->fetch_assoc()): ?>
              <?php
                $neto = $row['monto'] - $row['costo_transporte'];
                if ($neto < 0) $neto = 0;

                $v35 = $neto * 0.35;
                $t65 = $neto * 0.65;

                $totalVenta += $row['monto'];
                $totalMovilidad += $row['costo_transporte'];
                $totalNeto += $neto;
              ?>
              <tr>
                <td><?= $row['fecha'] ?></td>

                <td>
                  S/ <?= number_format($row['monto'], 2) ?>
                </td>

                <td class="text-danger">
                  S/ <?= number_format($row['costo_transporte'], 2) ?>
                </td>

                <td class="fw-bold text-success">
                  S/ <?= number_format($neto, 2) ?>
                </td>

                <td>
                  S/ <?= number_format($v35, 2) ?>
                </td>

                <td>
                  S/ <?= number_format($t65, 2) ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6">
                No hay pagos registrados
              </td>
            </tr>
          <?php endif; ?>

          </tbody>

          <!-- TOTALES -->
          <tfoot class="table-dark">
            <tr>
              <th>TOTALES</th>
              <th>S/ <?= number_format($totalVenta, 2) ?></th>
              <th>S/ <?= number_format($totalMovilidad, 2) ?></th>
              <th>S/ <?= number_format($totalNeto, 2) ?></th>
              <th>S/ <?= number_format($totalNeto * 0.35, 2) ?></th>
              <th>S/ <?= number_format($totalNeto * 0.65, 2) ?></th>
            </tr>
          </tfoot>

        </table>
      </div>
    </div>
  </div>

</div>

<?php require_once "../includes/footer.php"; ?>

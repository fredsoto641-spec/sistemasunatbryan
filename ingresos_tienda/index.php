<?php
require_once "../includes/auth.php";
require_once "../config/conexion.php";
require_once "../includes/header.php";

/* ===============================
   MES SELECCIONADO
=============================== */
$mes = $_GET['mes'] ?? date('Y-m'); // formato YYYY-MM

$inicioMes = $mes . '-01';
$finMes = date('Y-m-t', strtotime($inicioMes));

/* ===============================
   CONSULTA INGRESOS TIENDA
=============================== */
$sql = "
SELECT
  v.id AS vendedor_id,
  CONCAT(v.nombres,' ',v.apellidos) AS vendedor,

  SUM(vv.total_venta) AS total_vendido,
  SUM(vv.total_neto) AS total_neto,
  SUM(vv.total_neto * 0.65) AS ingreso_tienda

FROM ventas_vendedores vv
INNER JOIN vendedores v ON v.id = vv.vendedor_id

WHERE vv.estado = 1
  AND vv.fecha BETWEEN '$inicioMes' AND '$finMes'

GROUP BY v.id
ORDER BY vendedor
";

$ingresos = $conn->query($sql);
?>

<div class="container-fluid">

  <!-- SELECTOR MES -->
  <div class="card shadow mb-3">
    <div class="card-body">
      <form method="GET" class="row align-items-end">

        <div class="col-md-3">
          <label class="form-label">Seleccionar mes</label>
          <input type="month"
                 name="mes"
                 class="form-control"
                 value="<?= htmlspecialchars($mes) ?>">
        </div>

        <div class="col-md-2">
          <button class="btn btn-primary w-100">
            📅 Ver mes
          </button>
        </div>

      </form>
    </div>
  </div>

  <!-- TABLA -->
  <div class="card shadow">
    <div class="card-header">
      🏪 Ingresos de tienda (65%)  
      <small class="text-light">
        <?= date('F Y', strtotime($inicioMes)) ?>
      </small>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">
          <thead class="table-light">
            <tr>
              <th>Vendedor</th>
              <th>Total vendido</th>
              <th>Total neto</th>
              <th>65% Tienda</th>
            </tr>
          </thead>
          <tbody>

          <?php
          $granTotal = 0;
          ?>

          <?php if ($ingresos && $ingresos->num_rows > 0): ?>
            <?php while ($row = $ingresos->fetch_assoc()): ?>
              <?php $granTotal += $row['ingreso_tienda']; ?>
              <tr>
                <td><?= htmlspecialchars($row['vendedor']) ?></td>

                <td>
                  S/ <?= number_format($row['total_vendido'], 2) ?>
                </td>

                <td>
                  S/ <?= number_format($row['total_neto'], 2) ?>
                </td>

                <td class="fw-bold text-success">
                  S/ <?= number_format($row['ingreso_tienda'], 2) ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4">
                No hay ingresos registrados para este mes
              </td>
            </tr>
          <?php endif; ?>

          </tbody>

          <!-- TOTAL GENERAL -->
          <tfoot class="table-dark">
            <tr>
              <th colspan="3">TOTAL INGRESO TIENDA</th>
              <th>S/ <?= number_format($granTotal, 2) ?></th>
            </tr>
          </tfoot>

        </table>
      </div>
    </div>
  </div>

</div>

<?php require_once "../includes/footer.php"; ?>

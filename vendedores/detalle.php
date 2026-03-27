<?php
require_once "../includes/auth.php";
require_once "../config/conexion.php";
require_once "../includes/header.php";

$vendedor_id = $_GET['vendedor_id'];
$desde = $_GET['desde'];
$hasta = $_GET['hasta'];

$sql = "
SELECT 
  vv.fecha,
  t.nombre_transportista,
  vv.total_venta,
  vv.costo_transporte,
  vv.total_neto
FROM ventas_vendedores vv
JOIN transportistas t ON t.id = vv.transportista_id
WHERE vv.vendedor_id = $vendedor_id
AND vv.fecha BETWEEN '$desde' AND '$hasta'
ORDER BY vv.fecha
";

$ventas = $conn->query($sql);
?>

<div class="container-fluid">
  <div class="card shadow">
    <div class="card-header">
      <h5 class="mb-0">📋 Detalle diario del vendedor</h5>
      <small><?= $desde ?> al <?= $hasta ?></small>
    </div>

    <div class="card-body">
      <table class="table table-bordered table-hover text-center">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Transportista</th>
            <th>Total venta</th>
            <th>Transporte</th>
            <th>Neto</th>
          </tr>
        </thead>
        <tbody>
          <?php while($v = $ventas->fetch_assoc()): ?>
          <tr>
            <td><?= $v['fecha'] ?></td>
            <td><?= $v['nombre_transportista'] ?></td>
            <td>S/ <?= number_format($v['total_venta'], 2) ?></td>
            <td>S/ <?= number_format($v['costo_transporte'], 2) ?></td>
            <td class="fw-bold text-success">
              S/ <?= number_format($v['total_neto'], 2) ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      

      <a href="index.php" class="btn btn-secondary mt-2">⬅ Volver</a>
      
    </div>
  </div>
</div>

<?php require_once "../includes/footer.php"; ?>

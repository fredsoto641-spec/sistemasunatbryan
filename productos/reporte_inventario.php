<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

$inv = $conn->query("
  SELECT 
    p.codigo,
    p.nombre,
    p.stock,
    p.stock_minimo,
    p.precio,

    m.fecha,
    m.documento,
    m.proveedor,
    m.medida,
    m.costo

  FROM productos p
  LEFT JOIN producto_movimientos m
    ON m.producto_id = p.id
   AND m.id = (
     SELECT id FROM producto_movimientos
     WHERE producto_id = p.id
     ORDER BY fecha DESC, id DESC
     LIMIT 1
   )
  ORDER BY p.nombre
");
?>

<div class="erp-wrapper">
  <div class="erp-header">📊 Reporte de Inventario</div>

  <div class="erp-body">

    <table class="table table-bordered table-hover">
      <thead class="table-primary">
        <tr>
          <th>Producto</th>
          <th>Fecha</th>
          <th>Documento</th>
          <th>Proveedor</th>
          <th>Medida</th>
          <th>Stock</th>
          <th>Costo</th>
          <th>Valor</th>
        </tr>
      </thead>

      <tbody>
      <?php while ($i = $inv->fetch_assoc()): ?>
        <tr class="<?= ($i['stock'] <= $i['stock_minimo'] && $i['stock_minimo']>0) ? 'table-danger' : '' ?>">
          <td><?= htmlspecialchars($i['nombre']) ?></td>
          <td><?= $i['fecha'] ? date('d/m/Y', strtotime($i['fecha'])) : '—' ?></td>
          <td><?= htmlspecialchars($i['documento'] ?? '—') ?></td>
          <td><?= htmlspecialchars($i['proveedor'] ?? '—') ?></td>
          <td><?= htmlspecialchars($i['medida'] ?? '—') ?></td>
          <td><?= $i['stock'] ?></td>
          <td>S/ <?= number_format($i['costo'],2) ?></td>
          <td>S/ <?= number_format($i['stock'] * $i['costo'],2) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>

  </div>
</div>

<?php include "../includes/footer.php"; ?>

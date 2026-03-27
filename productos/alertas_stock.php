<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

$alertas = $conn->query("
  SELECT *
  FROM productos
  WHERE stock <= stock_minimo
    AND stock_minimo > 0
  ORDER BY stock
");
?>

<div class="erp-wrapper">
  <div class="erp-header bg-danger text-white">🚨 Stock Bajo</div>

  <div class="erp-body">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Stock</th>
          <th>Mínimo</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php while ($a = $alertas->fetch_assoc()): ?>
        <tr>
          <td><?= $a['nombre'] ?></td>
          <td class="text-danger"><?= $a['stock'] ?></td>
          <td><?= $a['stock_minimo'] ?></td>
          <td>
            <a href="editar.php?id=<?= $a['id'] ?>" class="btn btn-warning btn-sm">
              Reabastecer
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include "../includes/footer.php"; ?>

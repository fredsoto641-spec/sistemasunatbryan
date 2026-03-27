<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

/* ================= PRODUCTOS A REABASTECER ================= */
$productos = $conn->query("
  SELECT 
    p.id,
    p.codigo,
    p.nombre,
    p.stock,
    p.stock_minimo,

    m.medida,
    m.proveedor
  FROM productos p
  LEFT JOIN producto_movimientos m
    ON m.producto_id = p.id
   AND m.id = (
     SELECT id FROM producto_movimientos
     WHERE producto_id = p.id
     ORDER BY fecha DESC, id DESC
     LIMIT 1
   )
  WHERE p.stock_minimo > 0
    AND p.stock <= p.stock_minimo
  ORDER BY p.nombre
");
?>

<div class="erp-wrapper">

  <div class="erp-header">
    🧠 Sugerencia Automática de Compra
  </div>

  <div class="erp-body">

    <div class="table-responsive">
      <table class="table table-hover align-middle erp-table">

        <thead>
          <tr>
            <th>Código</th>
            <th>Producto</th>
            <th>Stock</th>
            <th>Mínimo</th>
            <th>Sugerido</th>
            <th>Medida</th>
            <th>Proveedor</th>
            <th>Estado</th>
          </tr>
        </thead>

        <tbody>
        <?php if ($productos->num_rows > 0): ?>
          <?php while ($p = $productos->fetch_assoc()): ?>

            <?php
              $sugerido = max(0, ($p['stock_minimo'] * 2) - $p['stock']);

              if ($p['stock'] <= ($p['stock_minimo'] * 0.5)) {
                $estado = 'danger';
                $texto  = 'Crítico';
              } else {
                $estado = 'warning';
                $texto  = 'Reponer';
              }
            ?>

            <tr>
              <td><?= htmlspecialchars($p['codigo']) ?></td>
              <td><?= htmlspecialchars($p['nombre']) ?></td>
              <td><?= $p['stock'] ?></td>
              <td><?= $p['stock_minimo'] ?></td>

              <td class="fw-bold">
                <?= $sugerido ?>
              </td>

              <td><?= htmlspecialchars($p['medida'] ?? '—') ?></td>
              <td><?= htmlspecialchars($p['proveedor'] ?? '—') ?></td>

              <td>
                <span class="badge bg-<?= $estado ?>">
                  <?= $texto ?>
                </span>
              </td>
            </tr>

          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="text-center text-muted">
              ✅ No hay productos para reabastecer
            </td>
          </tr>
        <?php endif; ?>
        </tbody>

      </table>
    </div>

  </div>
</div>

<?php include "../includes/footer.php"; ?>

<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

/* ================= RANKING SOLO VALES ================= */
$ranking = $conn->query("
  SELECT 
    v.id,
    CONCAT(v.nombres,' ',v.apellidos) AS vendedor,
    COUNT(val.id) AS documentos,
    SUM(val.monto_total) AS total
  FROM vendedores v
  LEFT JOIN vales val ON val.vendedor_id = v.id
  GROUP BY v.id
  ORDER BY total DESC
");

/* ===== OBTENER MAYOR VENTA ===== */
$totales = [];
$data = [];

while ($r = $ranking->fetch_assoc()) {
  $totales[] = $r['total'] ?? 0;
  $data[] = $r;
}

$max = max($totales ?: [0]);
?>

<div class="erp-wrapper">

  <div class="erp-header bg-primary text-white">
    🏆 Ranking de Vendedores (Vales)
  </div>

  <div class="erp-body">

    <div class="table-responsive">
      <table class="table table-hover align-middle erp-table">

        <thead>
          <tr>
            <th>#</th>
            <th>Vendedor</th>
            <th class="text-end">Total vendido</th>
            <th class="text-center">Docs</th>
            <th class="text-center">Estado</th>
          </tr>
        </thead>

        <tbody>
        <?php $i = 1; foreach ($data as $r): 
          $total = $r['total'] ?? 0;
          $ratio = $max > 0 ? ($total / $max) * 100 : 0;

          if ($ratio >= 70) {
            $estado = '🟢';
            $badge  = 'success';
          } elseif ($ratio >= 30) {
            $estado = '🟡';
            $badge  = 'warning';
          } else {
            $estado = '🔴';
            $badge  = 'danger';
          }

          $icono = match($i) {
            1 => '🥇',
            2 => '🥈',
            3 => '🥉',
            default => $i
          };
        ?>
          <tr>
            <td class="fw-bold"><?= $icono ?></td>
            <td><?= htmlspecialchars($r['vendedor']) ?></td>
            <td class="text-end fw-bold">
              S/ <?= number_format($total,2) ?>
            </td>
            <td class="text-center"><?= $r['documentos'] ?></td>
            <td class="text-center">
              <span class="badge bg-<?= $badge ?> fs-6">
                <?= $estado ?>
              </span>
            </td>
          </tr>
        <?php $i++; endforeach; ?>
        </tbody>

      </table>
    </div>

  </div>
</div>

<?php include "../includes/footer.php"; ?>

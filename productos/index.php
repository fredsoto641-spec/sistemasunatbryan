<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

$buscar = $_GET['buscar'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$limit = 50;
$offset = ($page - 1) * $limit;

$where = "";
if ($buscar != "") {
  $buscar = $conn->real_escape_string($buscar);
  $where = "WHERE p.codigo LIKE '%$buscar%' OR p.nombre LIKE '%$buscar%'";
}

/* ================= TOTAL ================= */
$total_sql = "SELECT COUNT(*) as total FROM productos p $where";
$total_res = $conn->query($total_sql);
$total = $total_res->fetch_assoc()['total'];
$total_pages = max(1, ceil($total / $limit));

/* ================= CONSULTA PRINCIPAL ================= */
$sql = "
SELECT 
  p.id,
  p.codigo,
  p.nombre,
  p.precio,
  p.stock,
  p.stock_minimo,
  p.estado,

  mv.fecha     AS ultima_fecha,
  mv.documento AS ultimo_documento,

  IF(mi.costo IS NULL OR mi.costo = 0, p.costo, mi.costo) AS costo,

  mi.cantidad,
  mi.medida,
  mi.proveedor

FROM productos p

LEFT JOIN producto_movimientos mv
  ON mv.id = (
    SELECT id
    FROM producto_movimientos
    WHERE producto_id = p.id
    ORDER BY fecha DESC, id DESC
    LIMIT 1
  )

LEFT JOIN producto_movimientos mi
  ON mi.id = (
    SELECT id
    FROM producto_movimientos
    WHERE producto_id = p.id
      AND tipo_movimiento = 'Ingreso'
    ORDER BY fecha DESC, id DESC
    LIMIT 1
  )

$where
ORDER BY p.nombre
LIMIT $limit OFFSET $offset
";

$productos = $conn->query($sql);
?>

<div class="erp-wrapper">

<div class="erp-header d-flex justify-content-between align-items-center">
  <span>📦 Productos</span>

  <div class="d-flex gap-2 align-items-center">

    <form method="GET" class="d-flex gap-1">
      <input type="hidden" name="page" value="1">
      <input type="text" name="buscar" class="form-control form-control-sm"
             placeholder="Buscar código o producto..."
             value="<?= htmlspecialchars($buscar) ?>">
      <button class="btn btn-info btn-sm">🔍</button>
    </form>

    <a href="crear.php" class="btn btn-success btn-sm">➕ Nuevo</a>
  </div>
</div>

<div class="erp-body">

<div class="table-responsive">
<table class="table table-hover align-middle erp-table">

<thead>
<tr>
  <th>#</th>
  <th>Código</th>
  <th>Producto</th>
  <th>Fecha</th>
  <th>Documento</th>
  <th>Proveedor</th>
  <th class="text-end">Costo</th>
  <th class="text-center">Cant.</th>
  <th>Medida</th>
  <th class="text-center">Stock</th>
  <th class="text-center">Acciones</th>
</tr>
</thead>

<tbody>
<?php $i = $offset + 1; while ($p = $productos->fetch_assoc()): ?>

<tr>
  <td><?= $i++ ?></td>
  <td><?= htmlspecialchars($p['codigo']) ?></td>
  <td><?= htmlspecialchars($p['nombre']) ?></td>

  <td>
    <?= $p['ultima_fecha'] ? date('d/m/Y', strtotime($p['ultima_fecha'])) : '—' ?>
  </td>

  <td><?= htmlspecialchars($p['ultimo_documento'] ?? '—') ?></td>
  <td><?= htmlspecialchars($p['proveedor'] ?? '—') ?></td>

  <td class="text-end">
    <?= 'S/ ' . number_format($p['costo'], 2) ?>
  </td>

  <td class="text-center">
    <?= $p['cantidad'] !== null ? $p['cantidad'] : '—' ?>
  </td>

  <td><?= htmlspecialchars($p['medida'] ?? '—') ?></td>

  <td class="text-center">
    <?php if ($p['stock'] <= 0): ?>
      <span class="badge bg-secondary">SIN STOCK</span>
    <?php elseif ($p['stock_minimo'] > 0 && $p['stock'] <= $p['stock_minimo']): ?>
      <span class="badge bg-danger"><?= $p['stock'] ?> ⚠</span>
    <?php else: ?>
      <span class="badge bg-success"><?= $p['stock'] ?></span>
    <?php endif; ?>
  </td>

  <td class="text-center">
    <a href="kardex.php?id=<?= $p['id'] ?>" class="btn btn-info btn-sm">📊</a>
    <a href="editar.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">✏️</a>
  </td>
</tr>

<?php endwhile; ?>
</tbody>

</table>
</div>

<!-- ================= PAGINACIÓN ================= -->
<nav>
<ul class="pagination justify-content-center mt-3">

<?php
$max_links = 7;
$start = max(1, $page - 3);
$end = min($total_pages, $start + $max_links - 1);
?>

<li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
  <a class="page-link" href="?page=<?= $page-1 ?>&buscar=<?= urlencode($buscar) ?>">«</a>
</li>

<?php for ($i = $start; $i <= $end; $i++): ?>
<li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
  <a class="page-link" href="?page=<?= $i ?>&buscar=<?= urlencode($buscar) ?>">
    <?= $i ?>
  </a>
</li>
<?php endfor; ?>

<li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
  <a class="page-link" href="?page=<?= $page+1 ?>&buscar=<?= urlencode($buscar) ?>">»</a>
</li>

</ul>
</nav>

</div>
</div>

<?php include "../includes/footer.php"; ?>

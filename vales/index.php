<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

/* ===============================
   BUSCADOR
=============================== */
$buscar = $_GET['buscar'] ?? '';
$orden = $_GET['orden'] ?? 'desc';

/* ===============================
   PAGINACIÓN
=============================== */
$porPagina = 50;
$pagina = $_GET['pagina'] ?? 1;
$inicio = ($pagina - 1) * $porPagina;

/* ===============================
   CONSULTA PRINCIPAL
=============================== */
$sql = "
  SELECT v.*, c.documento, c.nombre
  FROM vales v
  LEFT JOIN clientes c ON c.id = v.cliente_id
";

if ($buscar !== '') {
  $buscarSafe = $conn->real_escape_string($buscar);
  $sql .= "
    WHERE
      c.documento LIKE '%$buscarSafe%'
      OR c.nombre LIKE '%$buscarSafe%'
      OR CONCAT(v.serie,'-',v.correlativo) LIKE '%$buscarSafe%'
  ";
}

/* ===============================
   TOTAL REGISTROS
=============================== */
$sqlTotal = "
  SELECT COUNT(*) as total
  FROM vales v
  LEFT JOIN clientes c ON c.id = v.cliente_id
";

if ($buscar !== '') {
  $sqlTotal .= "
    WHERE
      c.documento LIKE '%$buscarSafe%'
      OR c.nombre LIKE '%$buscarSafe%'
      OR CONCAT(v.serie,'-',v.correlativo) LIKE '%$buscarSafe%'
  ";
}

$resTotal = $conn->query($sqlTotal);
$totalRegistros = $resTotal->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $porPagina);

/* ===============================
   ORDEN
=============================== */
if ($orden === 'asc') {
  $sql .= " ORDER BY v.serie ASC, v.correlativo ASC";
} else {
  $sql .= " ORDER BY v.serie DESC, v.correlativo DESC";
}

$sql .= " LIMIT $inicio, $porPagina";

$vales = $conn->query($sql);
?>

<div class="erp-wrapper">

<div class="erp-header d-flex justify-content-between align-items-center">
<span>VALES</span>

<a href="crear.php" class="btn btn-success btn-sm">
GENERAR NUEVO VALE / PROFORMA
</a>
</div>

<div class="erp-body">

<!-- BUSCADOR -->
<form method="GET" class="mb-3">
<div class="input-group">
<input type="text"
       name="buscar"
       value="<?= htmlspecialchars($buscar) ?>"
       class="form-control"
       placeholder="Buscar por documento, cliente o serie…">

<input type="hidden" name="orden" value="<?= $orden ?>">

<button class="btn btn-primary">🔍 Buscar</button>

<button type="submit" name="orden" value="asc" class="btn btn-outline-info">⬆ Serie</button>
<button type="submit" name="orden" value="desc" class="btn btn-outline-dark">⬇ Serie</button>

<?php if ($buscar): ?>
<a href="index.php" class="btn btn-secondary">Limpiar</a>
<?php endif; ?>
</div>
</form>

<div class="table-responsive">
<table class="table table-hover align-middle erp-table">

<thead>
<tr>
<th>#</th>
<th>Documento</th>
<th>Serie</th>
<th>Cliente</th>
<th>Total</th>
<th>Pagado</th>
<th>Vuelto</th>
<th>Saldo</th>
<th>Estado</th>
<th class="text-center">Acciones</th>
</tr>
</thead>

<tbody>
<?php while ($v = $vales->fetch_assoc()): ?>
<tr class="<?= $v['estado'] === 'Anulado' ? 'table-danger' : '' ?>">
<td><?= $v['id'] ?></td>

<td><?= htmlspecialchars($v['tipo_documento']) ?></td>

<td><?= htmlspecialchars($v['serie']) ?>-<?= htmlspecialchars($v['correlativo']) ?></td>

<td>
<strong><?= htmlspecialchars($v['documento']) ?></strong><br>
<small class="text-muted"><?= htmlspecialchars($v['nombre']) ?></small>
</td>

<td class="fw-bold">S/ <?= number_format($v['monto_total'],2) ?></td>

<td class="text-success">S/ <?= number_format($v['monto_pagado'],2) ?></td>

<td class="text-info">S/ <?= number_format($v['vuelto'],2) ?></td>

<td class="<?= $v['saldo'] > 0 ? 'text-danger' : 'text-success' ?>">
S/ <?= number_format($v['saldo'],2) ?>
</td>

<td>
<?php if ($v['estado'] === 'Pagado'): ?>
<span class="badge bg-success">Pagado</span>
<?php elseif ($v['estado'] === 'Anulado'): ?>
<span class="badge bg-danger">Anulado</span>
<?php else: ?>
<span class="badge bg-warning text-dark">Pendiente</span>
<?php endif; ?>
</td>

<td class="text-center">
<div class="btn-group btn-group-sm">

<a href="ver.php?id=<?= $v['id'] ?>" class="btn btn-primary">👁</a>

<?php if ($v['estado'] !== 'Anulado'): ?>
<a href="editar.php?id=<?= (int)$v['id'] ?>" class="btn btn-warning">✏️</a>
<?php endif; ?>

<?php if ($v['estado'] !== 'Anulado'): ?>
<button type="button" class="btn btn-info btn-sm"
        onclick="notaCredito(<?= $v['id'] ?>)">
💳 Nota Crédito
</button>
<?php endif; ?>

<?php if ($v['estado'] !== 'Anulado'): ?>
<button type="button" class="btn btn-danger btn-sm"
        onclick="confirmarAnularVale(<?= $v['id'] ?>)">
🚫 Anular
</button>
<?php else: ?>
<button class="btn btn-secondary" disabled>⛔</button>
<?php endif; ?>

</div>
</td>
</tr>
<?php endwhile; ?>
</tbody>

</table>
</div>

<nav class="mt-3">
<ul class="pagination justify-content-center">
<?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
<li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
<a class="page-link"
   href="?pagina=<?= $i ?>&buscar=<?= urlencode($buscar) ?>&orden=<?= $orden ?>">
<?= $i ?>
</a>
</li>
<?php endfor; ?>
</ul>
</nav>

</div>
</div>

<script>
function confirmarAnularVale(id) {
Swal.fire({
title: '¿Anular vale?',
text: 'Este vale será anulado permanentemente',
icon: 'warning',
showCancelButton: true,
confirmButtonColor: '#d33',
cancelButtonColor: '#6c757d',
confirmButtonText: 'Sí, anular',
cancelButtonText: 'Cancelar'
}).then((result) => {
if (result.isConfirmed) {
fetch('anular.php?id=' + id)
.then(() => {
Swal.fire('Vale anulado','El vale fue anulado correctamente','success')
.then(() => window.location.reload());
});
}
});
}

function notaCredito(id){
Swal.fire({
title: 'Registrar Nota de Crédito',
html: `<input type="number" id="monto" class="swal2-input" placeholder="Monto">`,
input: 'select',
inputOptions: {
'Efectivo':'Efectivo',
'Transferencia':'Transferencia',
'Yape':'Yape',
'Plin':'Plin'
},
showCancelButton:true,
confirmButtonText:'Registrar'
}).then(result=>{
if(result.isConfirmed){
const monto = document.getElementById('monto').value;

fetch('nota_credito.php',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:`vale_id=${id}&monto_pagado=${monto}&metodo=${result.value}`
}).then(()=>{
Swal.fire('Correcto','Nota de crédito registrada','success')
.then(()=>location.reload());
});
}
})
}
</script>

<?php include "../includes/footer.php"; ?>

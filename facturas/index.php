<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

/* ===============================
   BUSCADOR
=============================== */
$buscar = $_GET['buscar'] ?? '';
$order  = $_GET['order'] ?? 'DESC';
$page   = $_GET['page'] ?? 1;

$limit = 50;
$offset = ($page - 1) * $limit;

/* VALIDAR ORDER */
if ($order != 'ASC' && $order != 'DESC') {
  $order = 'DESC';
}

/* TOTAL REGISTROS */
$sqlCount = "SELECT COUNT(*) as total FROM facturas";
if ($buscar !== '') {
  $buscarSafe = $conn->real_escape_string($buscar);
  $sqlCount .= "
    WHERE 
      serie LIKE '%$buscarSafe%'
      OR correlativo LIKE '%$buscarSafe%'
      OR CONCAT(serie,'-',correlativo) LIKE '%$buscarSafe%'
  ";
}
$totalResult = $conn->query($sqlCount);
$totalRow = $totalResult->fetch_assoc();
$totalFacturas = $totalRow['total'];
$totalPaginas = ceil($totalFacturas / $limit);

/* CONSULTA PRINCIPAL */
$sql = "SELECT * FROM facturas";

if ($buscar !== '') {
  $buscarSafe = $conn->real_escape_string($buscar);
  $sql .= "
    WHERE
      serie LIKE '%$buscarSafe%'
      OR correlativo LIKE '%$buscarSafe%'
      OR CONCAT(serie,'-',correlativo) LIKE '%$buscarSafe%'
  ";
}

$sql .= " ORDER BY id $order LIMIT $limit OFFSET $offset";

$facturas = $conn->query($sql);

$hoy = new DateTime();

/* ===============================
   FUNCIÓN: PAGADO DE FACTURA
=============================== */
function obtenerPagadoFactura($conn, $factura_id) {
  $r = $conn->query("
    SELECT IFNULL(SUM(monto),0) AS pagado
    FROM factura_pagos
    WHERE factura_id = $factura_id
  ");
  $d = $r->fetch_assoc();
  return floatval($d['pagado']);
}
?>

<div class="erp-wrapper">

  <div class="erp-header d-flex justify-content-between align-items-center">
    <span>FACTURAS</span>

    <a href="crear.php" class="btn btn-success btn-sm">
      GENERAR NUEVA FACTURA
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
               placeholder="Buscar por documento (ej: 0009-48)">

        <input type="hidden" name="order" value="<?= $order ?>">

        <button class="btn btn-primary">🔍 Buscar</button>

        <?php if ($buscar): ?>
          <a href="index.php" class="btn btn-secondary">Limpiar</a>
        <?php endif; ?>

        <a href="index.php?order=<?= ($order=='ASC'?'DESC':'ASC') ?>"
           class="btn btn-dark">
           Orden <?= ($order=='ASC'?'↓':'↑') ?>
        </a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-hover align-middle erp-table">

        <thead>
          <tr>
            <th>#</th>
            <th>Documento</th>
            <th>Cliente</th>
            <th>Emisión</th>
            <th>Vencimiento</th>
            <th>Días</th>
            <th>Total</th>
            <th>Pagado</th>
            <th>Saldo</th>
            <th>Estado</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>

        <tbody>
        <?php while ($f = $facturas->fetch_assoc()):

          $pagado = obtenerPagadoFactura($conn, $f['id']);
          $saldo  = $f['total'] - $pagado;

          $emision = new DateTime($f['fecha']);
          $venc    = new DateTime($f['fecha_vencimiento']);
          $diff    = $hoy->diff($venc);
          $dias    = $diff->days;
          $vencido = $hoy > $venc;

          $colorEmision = ($emision->format('Y-m-d') === $hoy->format('Y-m-d'))
                          ? 'text-primary fw-bold' : '';

          // PRIORIDAD: PAGADO
          if ($saldo <= 0) {
            $colorVenc = 'text-success fw-bold';
            $diasTxt  = 'Pagado';
            $estado   = 'Pagado';
            $colorEst = 'success';

          } elseif ($vencido) {

            $colorVenc = 'text-danger fw-bold';
            $diasTxt  = "Vencido ($dias)";
            $estado   = 'Vencido';
            $colorEst = 'danger';

          } elseif ($venc->format('Y-m-d') === $hoy->format('Y-m-d')) {

            $colorVenc = 'text-warning fw-bold';
            $diasTxt  = 'Vence hoy';
            $estado   = 'Pendiente';
            $colorEst = 'warning';

          } else {

            $colorVenc = 'text-success';
            $diasTxt  = "Faltan $dias";
            $estado   = 'Pendiente';
            $colorEst = 'warning';
          }
        ?>
          <tr>
            <td><?= $f['id'] ?></td>

            <td>
              <strong>FACTURA</strong><br>
              <small class="text-muted">
                <?= htmlspecialchars($f['serie']) ?> - <?= htmlspecialchars($f['correlativo']) ?>
              </small>
            </td>

            <td>
              <strong><?= htmlspecialchars($f['cliente']) ?></strong><br>
              <small class="text-muted">RUC: <?= htmlspecialchars($f['ruc']) ?></small>
            </td>

            <td class="<?= $colorEmision ?>"><?= $emision->format('d/m/Y') ?></td>
            <td class="<?= $colorVenc ?>"><?= $venc->format('d/m/Y') ?></td>
            <td class="<?= $colorVenc ?>"><?= $diasTxt ?></td>

            <td class="fw-bold">S/ <?= number_format($f['total'], 2) ?></td>
            <td class="text-success">S/ <?= number_format($pagado, 2) ?></td>
            <td class="text-danger">S/ <?= number_format($saldo, 2) ?></td>

            <td>
              <span class="badge bg-<?= $colorEst ?>">
                <?= $estado ?>
              </span>
            </td>

           <td class="text-center">
  <div class="btn-group btn-group-sm">

    <a href="ver.php?id=<?= $f['id'] ?>" class="btn btn-primary">👁</a>

    <button onclick="emitirSunat(<?= $f['id'] ?>)" class="btn btn-success btn-sm">
      S
    </button>

    <!-- NOTA DE CRÉDITO -->
    <button onclick="emitirNotaCreditoFactura(<?= $f['id'] ?>)" 
            class="btn btn-warning btn-sm"
            title="Nota de Crédito">
      NC
    </button>

    <!-- NOTA DE DÉBITO -->
    <button onclick="emitirNotaDebitoFactura(<?= $f['id'] ?>)" 
            class="btn btn-secondary btn-sm"
            title="Nota de Débito">
      ND
    </button>

    <a href="editar.php?id=<?= $f['id'] ?>" class="btn btn-warning">✏️</a>

    <button type="button" class="btn btn-danger"
            onclick="confirmarEliminarFactura(<?= $f['id'] ?>)">🗑</button>

  </div>
</td>

          </tr>
        <?php endwhile; ?>
        </tbody>

      </table>
    </div>

    <!-- PAGINACIÓN -->
    <nav>
      <ul class="pagination justify-content-center mt-3">
        <?php for ($i=1; $i<=$totalPaginas; $i++): ?>
          <li class="page-item <?= ($i==$page?'active':'') ?>">
            <a class="page-link"
               href="?page=<?= $i ?>&order=<?= $order ?>&buscar=<?= $buscar ?>">
               <?= $i ?>
            </a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>

  </div>
</div>

<script>
function confirmarEliminarFactura(id) {
  Swal.fire({
    title: '¿Eliminar factura?',
    text: 'Esta acción no se puede deshacer',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('eliminar.php?id=' + id)
        .then(() => {
          Swal.fire({
            icon: 'success',
            title: 'Factura eliminada',
            text: 'La factura fue eliminada correctamente'
          }).then(() => {
            window.location.reload();
          });
        });
    }
  });
}
</script>
<script>
function emitirSunat(id){
  Swal.fire({
    title: 'Enviar a SUNAT',
    text: '¿Deseas emitir este comprobante?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, enviar'
  }).then((r)=>{
    if(r.isConfirmed){
      fetch('../sunat/emitir_factura_sunat.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'id='+id
      })
      .then(res=>res.text())
      .then(data=>{
        if(data.trim()=="ok"){
          Swal.fire('Éxito','Factura enviada a SUNAT','success')
            .then(()=>location.reload());
        }else{
          Swal.fire('Error',data,'error');
        }
      })
    }
  })
}
</script>
<script>
function emitirNotaCreditoFactura(id){
  Swal.fire({
    title: 'Emitir Nota de Crédito',
    text: '¿Deseas generar nota de crédito de esta factura?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, emitir'
  }).then((r)=>{
    if(r.isConfirmed){
      fetch('../sunat/emitir_nota_credito_factura_sunat.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'id='+id
      })
      .then(res=>res.text())
      .then(data=>{
        if(data.trim()=="ok"){
          Swal.fire('Correcto','Nota de crédito enviada a SUNAT','success')
            .then(()=>location.reload());
        }else{
          Swal.fire('Error',data,'error');
        }
      })
    }
  })
}
</script>

<script>
function emitirNotaDebitoFactura(id){
  Swal.fire({
    title: 'Emitir Nota de Débito',
    text: '¿Deseas generar nota de débito de esta factura?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, emitir'
  }).then((r)=>{
    if(r.isConfirmed){
      fetch('../sunat/emitir_nota_debito_factura_sunat.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'id='+id
      })
      .then(res=>res.text())
      .then(data=>{
        if(data.trim()=="ok"){
          Swal.fire('Correcto','Nota de débito enviada a SUNAT','success')
            .then(()=>location.reload());
        }else{
          Swal.fire('Error',data,'error');
        }
      })
    }
  })
}
</script>


<?php include "../includes/footer.php"; ?>

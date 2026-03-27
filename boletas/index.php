<?php
/* ===============================
   DEBUG + ZONA HORARIA
=============================== */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Lima');

require_once "../config/conexion.php";
require_once "../includes/header.php";

/* ===============================
   BUSCADOR
=============================== */
$buscar = $_GET['buscar'] ?? '';

$sql = "
  SELECT *
  FROM boletas
";

if ($buscar !== '') {
  $buscarSafe = $conn->real_escape_string($buscar);
  $sql .= "
    WHERE
      cliente LIKE '%$buscarSafe%'
      OR documento LIKE '%$buscarSafe%'
  ";
}

$sql .= " ORDER BY id DESC";

$boletas = $conn->query($sql);

if (!$boletas) {
  die("Error en consulta: " . $conn->error);
}
?>

<div class="erp-wrapper">

  <div class="erp-header d-flex justify-content-between align-items-center">
    <span>BOLETAS</span>

    <a href="crear.php" class="btn btn-success btn-sm">
      GENERAR NUEVA BOLETA
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
               placeholder="Buscar por cliente o documento…">
        <button class="btn btn-primary">🔍 Buscar</button>
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
            <th>Cliente</th>
            <th>Emisión</th>
            <th>Total</th>
            <th>Estado</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>

        <tbody>
        <?php while ($b = $boletas->fetch_assoc()): ?>

          <?php
            $emision = new DateTime($b['fecha']);

            if ($b['estado'] === 'Pagado') {
              $estado = 'Pagado';
              $colorEst = 'success';
            } elseif ($b['estado'] === 'Anulado') {
              $estado = 'Anulado';
              $colorEst = 'danger';
            } else {
              $estado = 'Pendiente';
              $colorEst = 'warning';
            }
          ?>

          <tr class="<?= $b['estado'] === 'Anulado' ? 'table-danger' : '' ?>">
            <td><?= $b['id'] ?></td>

            <td><strong>BOLETA</strong></td>

            <td>
              <strong><?= htmlspecialchars($b['cliente'] ?? '') ?></strong><br>
              <small class="text-muted">
                Doc: <?= htmlspecialchars($b['documento'] ?? '') ?>
              </small>
            </td>

            <td><?= $emision->format('d/m/Y') ?></td>

            <td class="fw-bold">
              S/ <?= number_format($b['total'], 2) ?>
            </td>

            <td>
              <span class="badge bg-<?= $colorEst ?>">
                <?= $estado ?>
              </span>
            </td>

            <td class="text-center">
              <div class="btn-group btn-group-sm">

                <!-- ENVIAR BOLETA -->
                <button onclick="emitirBoletaSunat(<?= $b['id'] ?>)" class="btn btn-primary btn-sm">
                  S
                </button>

                <!-- NOTA DE CRÉDITO -->
                <button onclick="emitirNotaCreditoBoleta(<?= $b['id'] ?>)" 
                        class="btn btn-warning btn-sm"
                        title="Nota de Crédito">
                  NC
                </button>

                <!-- NOTA DE DÉBITO -->
                <button onclick="emitirNotaDebitoBoleta(<?= $b['id'] ?>)" 
                        class="btn btn-secondary btn-sm"
                        title="Nota de Débito">
                  ND
                </button>

                <!-- PAGAR -->
                <?php if ($b['estado'] === 'Pendiente'): ?>
                <button type="button"
                        class="btn btn-success"
                        title="Registrar pago"
                        onclick="confirmarPagarBoleta(<?= $b['id'] ?>)">
                  💰
                </button>
                <?php endif; ?>

                <!-- ANULAR -->
                <?php if ($b['estado'] !== 'Anulado'): ?>
                <button type="button"
                        class="btn btn-danger"
                        title="Anular"
                        onclick="confirmarAnularBoleta(<?= $b['id'] ?>)">
                  🚫
                </button>
                <?php endif; ?>

              </div>
            </td>
          </tr>

        <?php endwhile; ?>
        </tbody>

      </table>
    </div>

  </div>
</div>

<!-- ================= PAGAR BOLETA ================= -->
<script>
function confirmarPagarBoleta(id) {
  Swal.fire({
    title: 'Pagar boleta',
    text: 'Seleccione el método de pago',
    icon: 'question',
    input: 'select',
    inputOptions: {
      'Yape': 'Yape',
      'Plin': 'Plin',
      'Efectivo': 'Efectivo',
      'Deposito': 'Depósito',
      'Tarjeta': 'Tarjeta',
      'Transferencia': 'Transferencia'
    },
    inputPlaceholder: 'Seleccione método',
    showCancelButton: true,
    confirmButtonText: 'Pagar',
    cancelButtonText: 'Cancelar',
    inputValidator: (value) => {
      if (!value) {
        return 'Debe seleccionar un método de pago';
      }
    }
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('pagar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&metodo=${encodeURIComponent(result.value)}`
      })
      .then(r => r.json())
      .then(resp => {
        if (resp.ok) {
          Swal.fire('Pagado', resp.msg, 'success')
            .then(() => location.reload());
        } else {
          Swal.fire('Error', resp.msg, 'error');
        }
      });
    }
  });
}
</script>

<!-- ================= ANULAR BOLETA ================= -->
<script>
function confirmarAnularBoleta(id) {
  Swal.fire({
    title: '¿Anular boleta?',
    text: 'La boleta será marcada como ANULADA',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sí, anular',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('anular.php?id=' + id)
        .then(r => r.json())
        .then(resp => {
          if (resp.ok) {
            Swal.fire('Anulada', resp.msg, 'success')
              .then(() => location.reload());
          } else {
            Swal.fire('Error', resp.msg, 'error');
          }
        });
    }
  });
}
</script>

<script>
function emitirBoletaSunat(id){
  Swal.fire({
    title: 'Enviar Boleta a SUNAT',
    text: '¿Deseas emitir esta boleta?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, enviar'
  }).then((r)=>{
    if(r.isConfirmed){
      fetch('../sunat/emitir_boleta_sunat.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'id='+id
      })
      .then(res=>res.text())
      .then(data=>{
        if(data.trim()=="ok"){
          Swal.fire('Éxito','Boleta enviada a SUNAT','success')
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
function emitirNotaCreditoBoleta(id){
  Swal.fire({
    title: 'Emitir Nota de Crédito',
    text: '¿Deseas generar nota de crédito de esta boleta?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, emitir'
  }).then((r)=>{
    if(r.isConfirmed){
      fetch('../sunat/emitir_nota_credito_boleta_sunat.php',{
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
function emitirNotaDebitoBoleta(id){
  Swal.fire({
    title: 'Emitir Nota de Débito',
    text: '¿Deseas generar nota de débito de esta boleta?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, emitir'
  }).then((r)=>{
    if(r.isConfirmed){
      fetch('../sunat/emitir_nota_debito_boleta_sunat.php',{
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

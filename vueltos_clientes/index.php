<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

$sql = "
SELECT v.*, c.razon_social, c.numero_documento
FROM vueltos_clientes v
JOIN clientes c ON c.id = v.cliente_id
ORDER BY v.id DESC
";

$vueltos = $conn->query($sql);
?>

<div class="erp-wrapper">

  <div class="erp-header d-flex justify-content-between">
    <span>💰 Vueltos de Clientes</span>
    <a href="crear.php" class="btn btn-success btn-sm">➕ Nuevo</a>
  </div>

  <div class="erp-body">

    <div class="table-responsive">
      <table class="table table-hover erp-table">

        <thead>
          <tr>
            <th>#</th>
            <th>Cliente</th>
            <th>Monto</th>
            <th>Método</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th>Acción</th>
          </tr>
        </thead>

        <tbody>
        <?php while($v = $vueltos->fetch_assoc()): ?>
          <tr>
            <td><?= $v['id'] ?></td>

            <td>
              <strong><?= $v['razon_social'] ?></strong><br>
              <small><?= $v['numero_documento'] ?></small>
            </td>

            <td>S/ <?= number_format($v['monto'],2) ?></td>

            <td><?= $v['metodo_pago'] ?? '-' ?></td>

            <td>
              <?php if($v['estado']=="Pagado"): ?>
                <span class="badge bg-success">Pagado</span>
              <?php else: ?>
                <span class="badge bg-warning text-dark">Pendiente</span>
              <?php endif; ?>
            </td>

            <td><?= $v['fecha'] ?></td>

            <td>
              <?php if($v['estado']=="Pendiente"): ?>
                <button onclick="pagarVuelto(<?= $v['id'] ?>)" class="btn btn-success btn-sm">
                  💳 Pagar
                </button>
              <?php else: ?>
                <button class="btn btn-secondary btn-sm" disabled>✔</button>
              <?php endif; ?>
            </td>

          </tr>
        <?php endwhile; ?>
        </tbody>

      </table>
    </div>

  </div>
</div>

<script>
function pagarVuelto(id){
  Swal.fire({
    title: 'Pagar vuelto',
    input: 'select',
    inputOptions: {
      'Efectivo': 'Efectivo',
      'Transferencia': 'Transferencia',
      'Yape': 'Yape',
      'Plin': 'Plin'
    },
    inputPlaceholder: 'Seleccione método',
    showCancelButton: true,
    confirmButtonText: 'Pagar'
  }).then((result)=>{
    if(result.isConfirmed){
      fetch('pagar.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'id='+id+'&metodo='+result.value
      }).then(()=>{
        Swal.fire('Pagado','El vuelto fue pagado','success')
        .then(()=>location.reload());
      });
    }
  });
}
</script>

<?php include "../includes/footer.php"; ?>

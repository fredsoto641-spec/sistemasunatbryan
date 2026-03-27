<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

$guias=$conn->query("SELECT * FROM guias_remision ORDER BY id DESC");
?>

<div class="erp-wrapper">
<div class="erp-header d-flex justify-content-between">
<span>GUÍAS DE REMISIÓN</span>
<a href="crear.php" class="btn btn-success btn-sm">Nueva Guía</a>
</div>

<div class="erp-body">
<table class="table table-hover">
<thead>
<tr>
<th>#</th>
<th>Tipo</th>
<th>Serie</th>
<th>Cliente</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php while($g=$guias->fetch_assoc()): ?>
<tr>
<td><?= $g['id'] ?></td>
<td><?= $g['tipo'] ?></td>
<td><?= $g['serie']."-".$g['correlativo'] ?></td>
<td><?= $g['cliente_nombre'] ?></td>
<td><?= $g['estado_sunat'] ?></td>
<td>

<button onclick="emitirGuiaRemitente(<?= $g['id'] ?>)" class="btn btn-primary btn-sm">Emitir</button>
<button onclick="consultarGuia(<?= $g['id'] ?>)" class="btn btn-warning btn-sm">Consultar</button>

<a href="ver_pdf_guia.php?id=<?= $g['id'] ?>" class="btn btn-danger btn-sm">PDF</a>
<a href="ver_xml_guia.php?id=<?= $g['id'] ?>" class="btn btn-secondary btn-sm">XML</a>
<a href="ver_cdr_guia.php?id=<?= $g['id'] ?>" class="btn btn-success btn-sm">CDR</a>

</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>

<script>
function emitirGuiaRemitente(id){
  fetch('emitir_guia_remitente_sunat.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'id=' + id
  })
  .then(r => r.text())
  .then(t => {
    if(t.trim() == "ok"){
      Swal.fire("Enviado","Guía enviada","success")
        .then(()=>location.reload());
    } else {
      Swal.fire("Error", t, "error");
    }
  });
}

function consultarGuia(id){
  fetch('consultar_guia_sunat.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'id=' + id
  })
  .then(r => r.text())
  .then(t => {
    if(t.trim() == "ok"){
      Swal.fire("Actualizado","Guía consultada","success")
        .then(()=>location.reload());
    } else {
      Swal.fire("Error", t, "error");
    }
  });
}
</script>



<?php include "../includes/footer.php"; ?>

<?php
require_once "../config/conexion.php";
require_once "../includes/auth.php";
require_once "../includes/header.php";

/* CONSULTAS */
$clientes = $conn->query("SELECT * FROM clientes ORDER BY nombre ASC");
$transportistas = $conn->query("SELECT * FROM transportistas ORDER BY empresa ASC");

if(isset($_POST['guardar'])){

  $tipo = $_POST['tipo'];

  $cliente_id = $_POST['cliente_id'];
  $documento = $_POST['documento'];
  $cliente = $_POST['cliente'];

  $partida = $_POST['partida'];
  $llegada = $_POST['llegada'];
  $ub_partida = $_POST['ub_partida'];
  $ub_llegada = $_POST['ub_llegada'];

  $conductor = $_POST['conductor'];
  $dni = $_POST['dni'];
  $placa = $_POST['placa'];
  $licencia = $_POST['licencia'];
  $peso = $_POST['peso'];

  $serie = ($tipo=="REMITENTE") ? "TTT1" : "VVV1";

  $q = $conn->query("SELECT IFNULL(MAX(correlativo),0)+1 AS next FROM guias_remision WHERE tipo='$tipo'");
  $row = $q->fetch_assoc();
  $correlativo = $row['next'];

  $conn->query("
    INSERT INTO guias_remision
    (tipo, serie, correlativo, cliente_documento, cliente_nombre,
     direccion_partida, direccion_llegada, ubigeo_partida, ubigeo_llegada,
     placa, conductor_dni, conductor_nombre, conductor_licencia, peso)
    VALUES
    ('$tipo','$serie',$correlativo,'$documento','$cliente',
     '$partida','$llegada','$ub_partida','$ub_llegada',
     '$placa','$dni','$conductor','$licencia','$peso')
  ");

  echo "<script>
    alert('Guía registrada correctamente');
    window.location='index.php';
  </script>";
}
?>

<div class="container-fluid mt-4">
<div class="card shadow">
<div class="card-header">
<h5>📄 Nueva Guía de Remisión</h5>
</div>

<div class="card-body">
<form method="POST">

<!-- Tipo -->
<div class="row mb-3">
  <div class="col-md-4">
    <label>Tipo de Guía</label>
    <select name="tipo" class="form-control">
      <option value="REMITENTE">Guía Remitente</option>
      <option value="TRANSPORTISTA">Guía Transportista</option>
    </select>
  </div>
</div>

<hr>

<h6>📦 Datos del Cliente</h6>
<div class="row">

<div class="col-md-6 mb-3">
<label>Cliente</label>
<select name="cliente_id" id="cliente" class="form-control" onchange="cargarCliente()" required>
<option value="">-- Seleccionar Cliente --</option>
<?php while($c = $clientes->fetch_assoc()){ ?>
<option value="<?= $c['id'] ?>"
  data-doc="<?= $c['documento'] ?>"
  data-nombre="<?= $c['nombre'] ?>"
  data-dir="<?= $c['direccion'] ?>">
  <?= $c['documento'] ?> - <?= $c['nombre'] ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-3 mb-3">
<label>Documento</label>
<input name="documento" id="documento" class="form-control" readonly>
</div>

<div class="col-md-3 mb-3">
<label>Dirección Partida</label>
<input name="partida" id="direccion_cliente" class="form-control" readonly>
</div>

<input type="hidden" name="cliente" id="cliente_nombre">

</div>

<h6>🌍 Ubicación</h6>
<div class="row">

<div class="col-md-6 mb-3">
<label>Dirección Llegada</label>
<input name="llegada" class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Ubigeo Partida</label>
<input name="ub_partida" class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Ubigeo Llegada</label>
<input name="ub_llegada" class="form-control">
</div>

</div>

<h6>🚚 Transporte</h6>
<div class="row">

<div class="col-md-4 mb-3">
<label>Transportista</label>
<select id="transportista" class="form-control" onchange="cargarTransportista()" required>
<option value="">-- Seleccionar Transportista --</option>
<?php while($t = $transportistas->fetch_assoc()){ ?>
<option value="<?= $t['id'] ?>"
  data-conductor="<?= $t['conductor'] ?>"
  data-dni="<?= $t['dni'] ?>"
  data-placa="<?= $t['placa'] ?>"
  data-licencia="<?= $t['licencia'] ?>">
  <?= $t['empresa'] ?> - <?= $t['placa'] ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-4 mb-3">
<label>Conductor</label>
<input name="conductor" id="conductor" class="form-control" readonly>
</div>

<div class="col-md-4 mb-3">
<label>DNI</label>
<input name="dni" id="dni" class="form-control" readonly>
</div>

<div class="col-md-4 mb-3">
<label>Placa</label>
<input name="placa" id="placa" class="form-control" readonly>
</div>

<div class="col-md-4 mb-3">
<label>Licencia</label>
<input name="licencia" id="licencia" class="form-control" readonly>
</div>

<div class="col-md-4 mb-3">
<label>Peso (kg)</label>
<input name="peso" type="number" step="0.01" class="form-control">
</div>

</div>

<div class="text-end mt-3">
<button name="guardar" class="btn btn-success">💾 Guardar Guía</button>
</div>

</form>
</div>
</div>
</div>

<script>
function cargarCliente(){
  let sel = document.getElementById("cliente");
  let opt = sel.options[sel.selectedIndex];

  document.getElementById("documento").value = opt.dataset.doc || "";
  document.getElementById("direccion_cliente").value = opt.dataset.dir || "";
  document.getElementById("cliente_nombre").value = opt.dataset.nombre || "";
}

function cargarTransportista(){
  let sel = document.getElementById("transportista");
  let opt = sel.options[sel.selectedIndex];

  document.getElementById("conductor").value = opt.dataset.conductor || "";
  document.getElementById("dni").value = opt.dataset.dni || "";
  document.getElementById("placa").value = opt.dataset.placa || "";
  document.getElementById("licencia").value = opt.dataset.licencia || "";
}
</script>

<?php require_once "../includes/footer.php"; ?>

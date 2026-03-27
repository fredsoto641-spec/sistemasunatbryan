<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../config/conexion.php";

/* ===============================
   PROTECCIÓN
=============================== */
if (!isset($_SESSION['usuario_id'])) {
  header("Location: /sistema-control-letras/login.php");
  exit;
}

/* ===============================
   CLIENTES ACTIVOS (NUEVA BD)
=============================== */
$clientes = $conn->query("
  SELECT id, documento, nombre
  FROM clientes
  WHERE estado='Activo'
  ORDER BY nombre
");

/* ===============================
   VENDEDORES
=============================== */
$vendedores = $conn->query("
  SELECT id, nombres, apellidos
  FROM vendedores
  WHERE estado = 'Activo'
  ORDER BY apellidos, nombres
");

/* ===============================
   TOKEN CSRF
=============================== */
if (empty($_SESSION['token_vale'])) {
  $_SESSION['token_vale'] = bin2hex(random_bytes(32));
}

$error = "";

/* ===============================
   GUARDAR VALE
=============================== */
if (isset($_POST['guardar'])) {

  if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token_vale']) {
    die("Acción no válida");
  }
  unset($_SESSION['token_vale']);

  $cliente_id     = intval($_POST['cliente_id']);
  $vendedor_id    = intval($_POST['vendedor_id']);
  $tipo_documento = $_POST['tipo_documento'];
  $serie          = $_POST['serie'];
  $correlativo    = $_POST['correlativo'];
  $fecha_emision  = $_POST['fecha_emision'];
  $monto_total    = floatval($_POST['monto_total']);

  $no_pago = isset($_POST['no_pago']);

  if ($no_pago) {
    $monto_pagado = 0;
    $saldo        = $monto_total;
    $estado       = 'Pendiente';
    $tipo_pago    = 'Pendiente';
    $numero_op    = null;
  } else {
    $monto_pagado = floatval($_POST['monto_pagado']);
    $saldo        = $monto_total - $monto_pagado;
    $estado       = $saldo <= 0 ? 'Pagado' : 'Pendiente';
    $tipo_pago    = $_POST['tipo_pago'];
    $numero_op    = $_POST['numero_operacion'];
  }

  if ($saldo < 0) {
    $error = "❌ El monto pagado no puede ser mayor al total";
  } else {

    $conn->query("
      INSERT INTO vales
      (cliente_id, vendedor_id, tipo_documento, serie, correlativo, fecha_emision,
       monto_total, monto_pagado, saldo, tipo_pago, numero_operacion, estado)
      VALUES
      (
        $cliente_id,
        $vendedor_id,
        '$tipo_documento',
        '$serie',
        '$correlativo',
        '$fecha_emision',
        $monto_total,
        $monto_pagado,
        $saldo,
        '$tipo_pago',
        ".($numero_op ? "'$numero_op'" : "NULL").",
        '$estado'
      )
    ");

    $vale_id = $conn->insert_id;

    /* ===============================
       GUARDAR PRODUCTOS
    =============================== */
    if (!empty($_POST['producto'])) {
      foreach ($_POST['producto'] as $i => $prod) {
        if (trim($prod) === '') continue;

        $metraje  = (float)$_POST['cantidad'][$i];
        $precio   = (float)$_POST['precio'][$i];
        $subtotal = $metraje * $precio;

        $conn->query("
          INSERT INTO vale_detalle
          (vale_id, producto, cantidad, precio, subtotal)
          VALUES
          ($vale_id, '$prod', $metraje, $precio, $subtotal)
        ");
      }
    }

    $_SESSION['mensaje'] = "✅ Vale creado correctamente";
    $_SESSION['tipo'] = "success";
    header("Location: index.php");
    exit;
  }
}

require_once "../includes/header.php";
?>

<div class="erp-wrapper col-md-10 mx-auto">

<div class="erp-header">🧾 Nuevo Vale</div>

<div class="erp-body">

<?php if ($error): ?>
  <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="POST" id="formVale">
<input type="hidden" name="token" value="<?= $_SESSION['token_vale'] ?>">

<!-- ================= CLIENTE ================= -->
<div class="erp-section">
<h6 class="section-title">Datos del Cliente</h6>

<input type="text" id="buscar_cliente" class="form-control mb-2"
       placeholder="🔍 Buscar cliente por documento o nombre">

<select name="cliente_id" id="cliente" class="form-control mb-3" required>
<option value="">-- Seleccione cliente --</option>
<?php while ($c = $clientes->fetch_assoc()): ?>
  <option value="<?= $c['id'] ?>"
    data-doc="<?= $c['documento'] ?>"
    data-razon="<?= htmlspecialchars($c['nombre']) ?>">
    <?= $c['documento'] ?> - <?= htmlspecialchars($c['nombre']) ?>
  </option>
<?php endwhile; ?>
</select>

<div class="row">
  <div class="col-md-6 mb-3">
    <label>DNI / RUC</label>
    <input type="text" id="doc_cliente" class="form-control" readonly>
  </div>
  <div class="col-md-6 mb-3">
    <label>Cliente</label>
    <input type="text" id="razon_cliente" class="form-control" readonly>
  </div>
</div>
</div>

<!-- ================= VENDEDOR ================= -->
<div class="erp-section">
<h6 class="section-title">Vendedor</h6>
<select name="vendedor_id" class="form-control" required>
<option value="">-- Seleccione vendedor --</option>
<?php while ($v = $vendedores->fetch_assoc()): ?>
<option value="<?= $v['id'] ?>">
<?= htmlspecialchars($v['apellidos'].' '.$v['nombres']) ?>
</option>
<?php endwhile; ?>
</select>
</div>

<!-- ================= DOCUMENTO ================= -->
<div class="erp-section">
<h6 class="section-title">Documento</h6>
<?php foreach (['Proforma','Vale','Boleta'] as $t): ?>
<label class="me-3">
<input type="radio" name="tipo_documento" value="<?= $t ?>" required> <?= $t ?>
</label>
<?php endforeach; ?>

<div class="row mt-3">
<div class="col-md-4">
<label>Serie</label>
<input type="text" name="serie" class="form-control" required>
</div>
<div class="col-md-4">
<label>Correlativo</label>
<input type="number" name="correlativo" class="form-control" required>
</div>
<div class="col-md-4">
<label>Fecha emisión</label>
<input type="date" name="fecha_emision" class="form-control" required>
</div>
</div>
</div>

<!-- ================= PRODUCTOS ================= -->
<div class="erp-section">
<h6 class="section-title">Productos</h6>

<table class="table table-bordered" id="tablaProductos">
<thead class="table-light">
<tr>
<th>Producto</th>
<th width="100">Cant.</th>
<th width="120">Precio</th>
<th width="120">Subtotal</th>
<th width="40"></th>
</tr>
</thead>
<tbody>
<tr>
<td><input type="text" name="producto[]" class="form-control"></td>
<td><input type="number" name="cantidad[]" class="form-control cantidad" step="0.01" value="0"></td>
<td><input type="number" name="precio[]" class="form-control precio" step="0.01"></td>
<td><input type="text" class="form-control subtotal" readonly></td>
<td><button type="button" class="btn btn-danger btn-sm btnEliminar">✖</button></td>
</tr>
</tbody>
</table>

<button type="button" id="btnAgregarProducto" class="btn btn-secondary btn-sm">➕ Agregar producto</button>
</div>

<!-- ================= MONTO ================= -->
<div class="erp-section">
<h6 class="section-title">Monto</h6>

<div class="row">
<div class="col-md-6">
<label>Monto total</label>
<input type="number" step="0.01" name="monto_total" class="form-control" required>
</div>
<div class="col-md-6">
<label>Monto pagado</label>
<input type="number" step="0.01" name="monto_pagado" class="form-control" value="0">
</div>
</div>

<label class="fw-bold text-danger mt-2">
<input type="checkbox" name="no_pago" id="no_pago"> Aún no pagó
</label>
</div>

<!-- ================= PAGO ================= -->
<div class="erp-section">
<h6 class="section-title">Pago</h6>
<?php foreach (['Efectivo','Yape','Plin','Deposito','Transferencia','Tarjeta'] as $p): ?>
<label class="me-3">
<input type="radio" name="tipo_pago" value="<?= $p ?>"> <?= $p ?>
</label>
<?php endforeach; ?>
<input type="text" name="numero_operacion" class="form-control mt-2" placeholder="N° operación">
</div>

<!-- ================= ACCIONES ================= -->
<div class="erp-actions">
<a href="index.php" class="btn btn-secondary">❌ Cancelar</a>
<button type="submit" name="guardar" class="btn btn-primary">💾 Guardar Vale</button>
</div>

</form>
</div>
</div>

<script>
document.getElementById('cliente').addEventListener('change', function () {
  const o = this.options[this.selectedIndex];
  doc_cliente.value = o.dataset.doc || '';
  razon_cliente.value = o.dataset.razon || '';
});

const buscador = document.getElementById('buscar_cliente');
const selectCliente = document.getElementById('cliente');

buscador.addEventListener('keyup', function () {
  const texto = this.value.toLowerCase();
  Array.from(selectCliente.options).forEach(opt => {
    if (opt.value === "") return;
    opt.style.display = opt.text.toLowerCase().includes(texto) ? '' : 'none';
  });
});

function recalcularTotales() {
  let total = 0;
  document.querySelectorAll('#tablaProductos tbody tr').forEach(tr => {
    const cant = parseFloat(tr.querySelector('.cantidad').value) || 0;
    const precio = parseFloat(tr.querySelector('.precio').value) || 0;
    const subtotal = cant * precio;

    tr.querySelector('.subtotal').value = subtotal.toFixed(2);
    total += subtotal;
  });
  document.querySelector('[name="monto_total"]').value = total.toFixed(2);
}

document.addEventListener('input', function(e) {
  if (e.target.classList.contains('cantidad') ||
      e.target.classList.contains('precio')) {
    recalcularTotales();
  }
});

document.getElementById('btnAgregarProducto').addEventListener('click', function () {
  const fila = `
    <tr>
      <td><input type="text" name="producto[]" class="form-control"></td>
      <td><input type="number" name="cantidad[]" class="form-control cantidad" step="0.01" value="0"></td>
      <td><input type="number" name="precio[]" class="form-control precio" step="0.01"></td>
      <td><input type="text" class="form-control subtotal" readonly></td>
      <td><button type="button" class="btn btn-danger btn-sm btnEliminar">✖</button></td>
    </tr>`;
  document.querySelector('#tablaProductos tbody').insertAdjacentHTML('beforeend', fila);
});

document.addEventListener('click', function (e) {
  if (e.target.classList.contains('btnEliminar')) {
    e.target.closest('tr').remove();
    recalcularTotales();
  }
});

document.getElementById("formVale").addEventListener("keydown", function(e) {
  if (e.key === "Enter") {
    e.preventDefault();
    return false;
  }
});
</script>

<?php include "../includes/footer.php"; ?>

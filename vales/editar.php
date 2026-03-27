<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../config/conexion.php";

/* PROTECCIÓN */
if (!isset($_SESSION['usuario_id'])) {
  header("Location: /sistema-control-letras/login.php");
  exit;
}

$id = intval($_GET['id'] ?? 0);

/* CLIENTES Y VENDEDORES */
$clientes = $conn->query("SELECT id, documento, nombre FROM clientes WHERE estado='Activo'");
$vendedores = $conn->query("SELECT id, nombres, apellidos FROM vendedores WHERE estado='Activo'");

/* VALE + CLIENTE */
$vale = $conn->query("
  SELECT v.*, c.documento, c.nombre
  FROM vales v
  JOIN clientes c ON c.id = v.cliente_id
  WHERE v.id = $id
")->fetch_assoc();

if (!$vale) {
  die("<div class='alert alert-danger'>Vale no encontrado</div>");
}

/* PRODUCTOS */
$productos = $conn->query("
  SELECT producto, cantidad, precio, subtotal
  FROM vale_detalle
  WHERE vale_id = $id
");

/* ===============================
   REGISTRAR PAGO
=============================== */
if (isset($_POST['pagar'])) {

  $monto = floatval($_POST['monto_pago']);
  $metodo = $_POST['metodo_pago'];
  $banco  = $_POST['banco'];
  $numero = $_POST['numero_operacion'];

  if ($monto > 0 && $monto <= $vale['saldo']) {

    $conn->query("
      INSERT INTO vale_pagos
      (vale_id, fecha_pago, monto, metodo_pago, banco, numero_operacion)
      VALUES
      ($id, NOW(), $monto, '$metodo', '$banco', '$numero')
    ");

    $nuevo_pagado = $vale['monto_pagado'] + $monto;
    $nuevo_saldo  = $vale['monto_total'] - $nuevo_pagado;
    $nuevo_estado = $nuevo_saldo <= 0 ? 'Pagado' : 'Pendiente';

    $conn->query("
      UPDATE vales SET
        monto_pagado = $nuevo_pagado,
        saldo = $nuevo_saldo,
        estado = '$nuevo_estado'
      WHERE id = $id
    ");

    $_SESSION['mensaje'] = "✅ Pago registrado";
    $_SESSION['tipo'] = "success";
    header("Location: editar.php?id=$id");
    exit;
  } else {
    $error_pago = "Monto inválido";
  }
}

/* ===============================
   GUARDAR TODO
=============================== */
if (isset($_POST['guardar'])) {

  $cliente_id  = intval($_POST['cliente_id']);
  $vendedor_id = intval($_POST['vendedor_id']);

  $nuevo_total = 0;

  if (!empty($_POST['producto'])) {
    foreach ($_POST['producto'] as $i => $prod) {
      if (trim($prod) == '') continue;
      $cant = floatval($_POST['cantidad'][$i]);
      $precio = floatval($_POST['precio'][$i]);
      $nuevo_total += ($cant * $precio);
    }
  }

  $monto_pagado = $vale['monto_pagado'];
  $nuevo_saldo = $nuevo_total - $monto_pagado;
  $nuevo_estado = $nuevo_saldo <= 0 ? 'Pagado' : 'Pendiente';

  $conn->query("
    UPDATE vales SET
      cliente_id = $cliente_id,
      vendedor_id = $vendedor_id,
      tipo_documento = '{$_POST['tipo_documento']}',
      serie = '{$_POST['serie']}',
      correlativo = '{$_POST['correlativo']}',
      fecha_emision = '{$_POST['fecha_emision']}',
      monto_total = $nuevo_total,
      saldo = $nuevo_saldo,
      estado = '$nuevo_estado'
    WHERE id = $id
  ");

  $conn->query("DELETE FROM vale_detalle WHERE vale_id = $id");

  foreach ($_POST['producto'] as $i => $prod) {
    if (trim($prod) == '') continue;

    $cant = floatval($_POST['cantidad'][$i]);
    $precio = floatval($_POST['precio'][$i]);
    $subtotal = $cant * $precio;

    $conn->query("
      INSERT INTO vale_detalle (vale_id, producto, cantidad, precio, subtotal)
      VALUES ($id, '$prod', $cant, $precio, $subtotal)
    ");
  }

  $_SESSION['mensaje'] = "✏️ Vale actualizado";
  $_SESSION['tipo'] = "success";
  header("Location: index.php");
  exit;
}

/* HISTORIAL PAGOS */
$pagos = $conn->query("
  SELECT * FROM vale_pagos
  WHERE vale_id = $id
  ORDER BY fecha_pago DESC
");

require_once "../includes/header.php";
?>

<div class="card shadow col-md-8 mx-auto">
<div class="card-header bg-warning text-dark">✏️ Editar Vale</div>
<div class="card-body">

<form method="POST" id="formEditar">

<label>Cliente</label>
<select name="cliente_id" class="form-select mb-2">
<?php while($c = $clientes->fetch_assoc()): ?>
<option value="<?= $c['id'] ?>" <?= $vale['cliente_id']==$c['id']?'selected':'' ?>>
<?= $c['documento']." - ".$c['nombre'] ?>
</option>
<?php endwhile; ?>
</select>

<label>Vendedor</label>
<select name="vendedor_id" class="form-select mb-2">
<?php while($v = $vendedores->fetch_assoc()): ?>
<option value="<?= $v['id'] ?>" <?= $vale['vendedor_id']==$v['id']?'selected':'' ?>>
<?= $v['apellidos']." ".$v['nombres'] ?>
</option>
<?php endwhile; ?>
</select>

<hr>

<div class="row mb-3">
<div class="col"><strong>Total:</strong> S/ <span id="total_vivo"><?= number_format($vale['monto_total'],2) ?></span></div>
<div class="col text-success"><strong>Pagado:</strong> S/ <?= number_format($vale['monto_pagado'],2) ?></div>
<div class="col text-danger"><strong>Saldo:</strong> S/ <span id="saldo_vivo"><?= number_format($vale['saldo'],2) ?></span></div>
</div>

<h6>Productos</h6>

<table class="table table-sm table-bordered" id="tablaProductos">
<thead>
<tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th><th></th></tr>
</thead>
<tbody>
<?php while($pr = $productos->fetch_assoc()): ?>
<tr>
<td><input type="text" name="producto[]" value="<?= htmlspecialchars($pr['producto']) ?>" class="form-control"></td>
<td><input type="number" step="0.01" name="cantidad[]" value="<?= $pr['cantidad'] ?>" class="form-control cantidad"></td>
<td><input type="number" step="0.01" name="precio[]" value="<?= $pr['precio'] ?>" class="form-control precio"></td>
<td><input type="text" class="form-control subtotal" value="<?= number_format($pr['subtotal'],2) ?>" readonly></td>
<td><button type="button" class="btn btn-danger btn-sm btnEliminar">✖</button></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<button type="button" id="btnAgregarProducto" class="btn btn-secondary btn-sm">➕ Agregar producto</button>

<hr>

<label>Tipo documento</label>
<select name="tipo_documento" class="form-select mb-2">
<?php foreach(['Proforma','Vale','Factura'] as $t): ?>
<option <?= $vale['tipo_documento']==$t?'selected':'' ?>><?= $t ?></option>
<?php endforeach; ?>
</select>

<div class="row">
<input name="serie" value="<?= $vale['serie'] ?>" class="form-control col me-2">
<input name="correlativo" value="<?= $vale['correlativo'] ?>" class="form-control col">
</div>

<input type="date" name="fecha_emision" value="<?= $vale['fecha_emision'] ?>" class="form-control mt-2">

<button name="guardar" class="btn btn-primary w-100 mt-3">💾 Guardar cambios</button>
</form>

<hr>

<?php if ($vale['estado'] !== 'Pagado'): ?>
<form method="POST" class="mb-4">
<label>Monto a pagar</label>
<input type="number" step="0.01" name="monto_pago" class="form-control mb-2" required>

<label>Método de pago</label>
<select name="metodo_pago" class="form-select mb-2" required>
<?php foreach(['Efectivo','Yape','Plin','Deposito','Transferencia','Tarjeta'] as $m): ?>
<option><?= $m ?></option>
<?php endforeach; ?>
</select>

<label>Banco</label>
<input name="banco" class="form-control mb-2">

<label>N° operación</label>
<input name="numero_operacion" class="form-control mb-2">

<?php if (!empty($error_pago)): ?>
<div class="text-danger"><?= $error_pago ?></div>
<?php endif; ?>

<button name="pagar" class="btn btn-success w-100">💰 Registrar pago</button>
</form>
<?php endif; ?>

<hr>

<h6>Historial de pagos</h6>
<table class="table table-sm table-bordered">
<thead>
<tr><th>Fecha</th><th>Monto</th><th>Método</th><th>Banco</th><th>Operación</th></tr>
</thead>
<tbody>
<?php while($p = $pagos->fetch_assoc()): ?>
<tr>
<td><?= date('d/m/Y H:i', strtotime($p['fecha_pago'])) ?></td>
<td>S/ <?= number_format($p['monto'],2) ?></td>
<td><?= $p['metodo_pago'] ?></td>
<td><?= $p['banco'] ?></td>
<td><?= $p['numero_operacion'] ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>
</div>

<script>
function recalcularTotales() {
let total = 0;
document.querySelectorAll('#tablaProductos tbody tr').forEach(tr => {
const cant = parseFloat(tr.querySelector('.cantidad').value) || 0;
const precio = parseFloat(tr.querySelector('.precio').value) || 0;
const subtotal = cant * precio;
tr.querySelector('.subtotal').value = subtotal.toFixed(2);
total += subtotal;
});

document.getElementById("total_vivo").innerText = total.toFixed(2);
let pagado = <?= $vale['monto_pagado'] ?>;
let saldo = total - pagado;
document.getElementById("saldo_vivo").innerText = saldo.toFixed(2);
}

document.addEventListener('input', function(e){
if(e.target.classList.contains('cantidad') || e.target.classList.contains('precio')){
recalcularTotales();
}
});

document.getElementById('btnAgregarProducto').addEventListener('click', function(){
const fila = `
<tr>
<td><input type="text" name="producto[]" class="form-control"></td>
<td><input type="number" step="0.01" name="cantidad[]" class="form-control cantidad"></td>
<td><input type="number" step="0.01" name="precio[]" class="form-control precio"></td>
<td><input type="text" class="form-control subtotal" readonly></td>
<td><button type="button" class="btn btn-danger btn-sm btnEliminar">✖</button></td>
</tr>`;
document.querySelector('#tablaProductos tbody').insertAdjacentHTML('beforeend', fila);
});

document.addEventListener('click', function(e){
if(e.target.classList.contains('btnEliminar')){
e.target.closest('tr').remove();
recalcularTotales();
}
});
</script>

<?php include "../includes/footer.php"; ?>

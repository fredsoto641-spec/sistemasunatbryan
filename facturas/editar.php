<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../config/conexion.php";
require_once "../includes/header.php";

/* PROTECCIÓN */
if (!isset($_SESSION['usuario_id'])) {
  header("Location: /sistema-control-letras/login.php");
  exit;
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$id = intval($_GET['id'] ?? 0);

/* FACTURA */
$factura = $conn->query("SELECT * FROM facturas WHERE id = $id")->fetch_assoc();
if (!$factura) {
  die("<div class='alert alert-danger'>Factura no encontrada</div>");
}

/* PRODUCTOS */
$productos = $conn->query("
  SELECT id, producto, cantidad, precio, subtotal
  FROM factura_detalle
  WHERE factura_id = $id
");

/* PAGOS */
$resPagado = $conn->query("
  SELECT IFNULL(SUM(monto),0) AS pagado
  FROM factura_pagos
  WHERE factura_id = $id
");
$rowPagado = $resPagado->fetch_assoc();
$monto_pagado = floatval($rowPagado['pagado']);
$saldo = $factura['total'] - $monto_pagado;

/* ===============================
   GUARDAR PRODUCTOS
=============================== */
if (isset($_POST['guardar_productos'])) {

  $ids = $_POST['detalle_id'] ?? [];
  $productosN = $_POST['producto'];
  $cantidades = $_POST['cantidad'];
  $precios = $_POST['precio'];
  $subtotales = $_POST['subtotal'];

  $nuevo_total = 0;

  for ($i=0; $i<count($productosN); $i++) {

    if (empty($productosN[$i])) continue;

    $producto = $conn->real_escape_string($productosN[$i]);
    $cantidad = floatval($cantidades[$i]);
    $precio   = floatval($precios[$i]);
    $subtotal = floatval($subtotales[$i]);

    $nuevo_total += $subtotal;

    if (!empty($ids[$i])) {
      $conn->query("
        UPDATE factura_detalle SET
          producto='$producto',
          cantidad=$cantidad,
          precio=$precio,
          subtotal=$subtotal
        WHERE id={$ids[$i]}
      ");
    } else {
      $conn->query("
        INSERT INTO factura_detalle
        (factura_id, producto, cantidad, precio, subtotal)
        VALUES
        ($id,'$producto',$cantidad,$precio,$subtotal)
      ");
    }
  }

  $nuevo_saldo = $nuevo_total - $monto_pagado;
  $estado = ($nuevo_saldo <= 0) ? 'Pagado' : 'Pendiente';

  $conn->query("
    UPDATE facturas SET
      total=$nuevo_total,
      saldo=$nuevo_saldo,
      estado='$estado'
    WHERE id=$id
  ");

  header("Location: editar.php?id=$id");
  exit;
}

/* ===============================
   REGISTRAR PAGO
=============================== */
if (isset($_POST['pagar'])) {

  $monto  = floatval($_POST['monto_pago']);
  $metodo = $_POST['metodo_pago'];
  $banco  = $_POST['banco'];
  $numero = $_POST['numero_operacion'];

  if ($monto > 0 && $monto <= $saldo) {

    $conn->query("
      INSERT INTO factura_pagos
      (factura_id, fecha_pago, monto, metodo_pago, banco, numero_operacion)
      VALUES
      ($id, NOW(), $monto, '$metodo', '$banco', '$numero')
    ");

    if ($monto == $saldo) {
      $conn->query("UPDATE facturas SET estado='Pagado' WHERE id=$id");
    }

    header("Location: editar.php?id=$id");
    exit;
  } else {
    $error_pago = "Monto inválido";
  }
}

/* HISTORIAL PAGOS */
$pagos = $conn->query("
  SELECT *
  FROM factura_pagos
  WHERE factura_id = $id
  ORDER BY fecha_pago DESC
");
?>

<div class="card shadow col-md-9 mx-auto">
<div class="card-header bg-warning text-dark">✏️ Editar Factura</div>
<div class="card-body">

<strong>Cliente:</strong><br>
<?= htmlspecialchars($factura['cliente']) ?> — <?= htmlspecialchars($factura['ruc']) ?>

<hr>

<div class="row mb-3">
  <div class="col"><strong>Total:</strong> S/ <span id="total_factura"><?= number_format($factura['total'],2) ?></span></div>
  <div class="col text-success"><strong>Pagado:</strong> S/ <?= number_format($monto_pagado,2) ?></div>
  <div class="col text-danger"><strong>Saldo:</strong> S/ <?= number_format($saldo,2) ?></div>
</div>

<?php if ($saldo > 0): ?>
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
<h6>Productos</h6>

<form method="POST">
<table class="table table-sm table-bordered">
<thead>
<tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr>
</thead>
<tbody>

<?php while($pr = $productos->fetch_assoc()): ?>
<tr>
<td>
<input type="hidden" name="detalle_id[]" value="<?= $pr['id'] ?>">
<input name="producto[]" value="<?= htmlspecialchars($pr['producto']) ?>" class="form-control">
</td>
<td><input name="cantidad[]" type="number" value="<?= $pr['cantidad'] ?>" class="form-control"></td>
<td><input name="precio[]" type="number" step="0.01" value="<?= $pr['precio'] ?>" class="form-control"></td>
<td><input name="subtotal[]" type="number" step="0.01" value="<?= $pr['subtotal'] ?>" class="form-control"></td>
</tr>
<?php endwhile; ?>

<tr>
<td><input name="producto[]" class="form-control" placeholder="Nuevo producto"></td>
<td><input name="cantidad[]" type="number" class="form-control"></td>
<td><input name="precio[]" type="number" step="0.01" class="form-control"></td>
<td><input name="subtotal[]" type="number" step="0.01" class="form-control"></td>
</tr>

</tbody>
</table>

<button name="guardar_productos" class="btn btn-warning w-100">💾 Guardar productos</button>
</form>

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
<td><?= htmlspecialchars($p['metodo_pago']) ?></td>
<td><?= htmlspecialchars($p['banco']) ?></td>
<td><?= htmlspecialchars($p['numero_operacion']) ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>
</div>

<script>
function recalcular(){
  let total = 0;
  document.querySelectorAll("tbody tr").forEach(function(row){
    let c = row.querySelector("input[name='cantidad[]']");
    let p = row.querySelector("input[name='precio[]']");
    let s = row.querySelector("input[name='subtotal[]']");
    if(c && p && s){
      let subtotal = (parseFloat(c.value)||0)*(parseFloat(p.value)||0);
      s.value = subtotal.toFixed(2);
      total += subtotal;
    }
  });
  document.getElementById("total_factura").innerText = total.toFixed(2);
}

document.addEventListener("input", function(e){
  if(e.target.name=="cantidad[]" || e.target.name=="precio[]"){
    recalcular();
  }
});
</script>

<?php include "../includes/footer.php"; ?>

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../config/conexion.php";
require_once "../includes/header.php";

/* ===============================
   CLIENTES ACTIVOS
=============================== */
$clientes = $conn->query("
  SELECT id, documento, nombre, direccion
  FROM clientes
  WHERE estado='Activo'
  ORDER BY nombre
");

/* ===============================
   PRODUCTOS ACTIVOS
=============================== */
$productos = $conn->query("
  SELECT id, nombre, precio
  FROM productos
  WHERE estado='Activo'
  ORDER BY nombre
");

$swal = null;

/* ===============================
   GUARDAR FACTURA
=============================== */
if (isset($_POST['guardar'])) {

  $cliente_id = intval($_POST['cliente_id'] ?? 0);

  if ($cliente_id <= 0) {
    $swal = [
      'icon' => 'error',
      'title' => 'Error',
      'text' => 'Debe seleccionar un cliente'
    ];
  } elseif (empty($_POST['producto'])) {
    $swal = [
      'icon' => 'error',
      'title' => 'Error',
      'text' => 'Debe agregar al menos un producto'
    ];
  } else {

    $cli = $conn->query("
      SELECT documento, nombre, direccion
      FROM clientes
      WHERE id = $cliente_id
      LIMIT 1
    ")->fetch_assoc();

    $conn->query("
      INSERT INTO facturas
      (serie, correlativo, cliente, ruc, direccion, fecha, fecha_vencimiento, total)
      VALUES
      (
        '{$_POST['serie']}',
        '{$_POST['correlativo']}',
        '{$cli['nombre']}',
        '{$cli['documento']}',
        '{$cli['direccion']}',
        '{$_POST['fecha']}',
        '{$_POST['fecha_vencimiento']}',
        '{$_POST['total']}'
      )
    ");

    $factura_id = $conn->insert_id;

    foreach ($_POST['producto'] as $i => $prod) {
      if (trim($prod) === '') continue;

     $producto_id = intval($prod);
$cantidad = floatval($_POST['cantidad'][$i]);
$precio   = floatval($_POST['precio'][$i]);
$subtotal = $cantidad * $precio;

/* OBTENER NOMBRE DEL PRODUCTO */
$p = $conn->query("
  SELECT nombre
  FROM productos
  WHERE id = $producto_id
  LIMIT 1
")->fetch_assoc();

$nombre_producto = $conn->real_escape_string($p['nombre']);

$conn->query("
  INSERT INTO factura_detalle
  (factura_id, producto, cantidad, precio, subtotal)
  VALUES
  ($factura_id, '$nombre_producto', $cantidad, $precio, $subtotal)
");


      $conn->query("
        UPDATE productos
        SET stock = stock - $cantidad
        WHERE id = $producto_id
      ");
    }

    $swal = [
      'icon' => 'success',
      'title' => 'Factura registrada',
      'text' => 'La factura se guardó correctamente',
      'redirect' => 'index.php'
    ];
  }
}
?>

<div class="erp-wrapper col-md-11 mx-auto">

<div class="erp-header">🧾 Nueva Facturación</div>

<div class="erp-body">
<form method="POST">

<div class="erp-section">
<h6 class="section-title">Datos Generales</h6>

<div class="row">
  <div class="col-md-3">
    <input name="serie" class="form-control" placeholder="Serie" required>
  </div>
  <div class="col-md-3">
    <input name="correlativo" class="form-control" placeholder="Correlativo" required>
  </div>
  <div class="col-md-3">
    <input type="date" name="fecha" class="form-control" required>
  </div>
  <div class="col-md-3">
    <input type="date" name="fecha_vencimiento" class="form-control" required>
  </div>
</div>
</div>

<div class="erp-section">
<h6 class="section-title">Cliente</h6>

<input type="text" id="buscar_cliente" class="form-control mb-2" placeholder="🔍 Buscar cliente por RUC o nombre">

<select name="cliente_id" id="cliente" class="form-control mb-3" required>
<option value="">-- Seleccione cliente --</option>
<?php while ($c = $clientes->fetch_assoc()): ?>
<option value="<?= $c['id'] ?>"
  data-doc="<?= $c['documento'] ?>"
  data-nombre="<?= htmlspecialchars($c['nombre']) ?>"
  data-dir="<?= htmlspecialchars($c['direccion']) ?>">
  <?= $c['documento'] ?> - <?= htmlspecialchars($c['nombre']) ?>
</option>
<?php endwhile; ?>
</select>

<div class="row">
  <div class="col-md-4">
    <input id="ruc" class="form-control" placeholder="Documento" readonly>
  </div>
  <div class="col-md-4">
    <input id="razon" class="form-control" placeholder="Cliente" readonly>
  </div>
  <div class="col-md-4">
    <input id="direccion" class="form-control" placeholder="Dirección" readonly>
  </div>
</div>
</div>

<div class="erp-section">
<h6 class="section-title">Productos</h6>

<table class="table table-bordered" id="tablaProductos">
<thead class="table-primary">
<tr>
<th>Producto</th>
<th>Cantidad</th>
<th>Precio</th>
<th>Subtotal</th>
<th></th>
</tr>
</thead>
<tbody>
<tr>
<td>
<select name="producto[]" class="form-control producto">
<option value="">-- Seleccione producto --</option>
<?php 
$productos2 = $conn->query("SELECT id, nombre, precio FROM productos WHERE estado='Activo'");
while($p = $productos2->fetch_assoc()):
?>
<option value="<?= $p['id'] ?>" data-precio="<?= $p['precio'] ?>">
<?= $p['nombre'] ?>
</option>
<?php endwhile; ?>
</select>
</td>
<td><input name="cantidad[]" class="form-control cantidad" value="1"></td>
<td><input name="precio[]" class="form-control precio" value="0.00"></td>
<td><input class="form-control subtotal" readonly></td>
<td><button type="button" class="btn btn-danger btn-sm btnEliminar">✖</button></td>
</tr>
</tbody>
</table>

<button type="button" id="btnAgregarProducto" class="btn btn-secondary btn-sm">➕ Agregar producto</button>
</div>

<div class="erp-section text-end">
<h4>Total S/ <span id="total">0.00</span></h4>
<input type="hidden" name="total" id="inputTotal">
</div>

<div class="erp-actions">
<button name="guardar" class="btn btn-success">💾 Guardar</button>
</div>

</form>
</div>
</div>

<script>
document.getElementById('cliente').addEventListener('change', function () {
  const o = this.options[this.selectedIndex];
  ruc.value = o.dataset.doc || '';
  razon.value = o.dataset.nombre || '';
  direccion.value = o.dataset.dir || '';
});

function recalcularTotales() {
  let total = 0;
  document.querySelectorAll('#tablaProductos tbody tr').forEach(tr => {
    const cant = parseFloat(tr.querySelector('.cantidad').value) || 0;
    const prec = parseFloat(tr.querySelector('.precio').value) || 0;
    const sub = cant * prec;

    tr.querySelector('.subtotal').value = sub.toFixed(2);
    total += sub;
  });

  document.getElementById('total').innerText = total.toFixed(2);
  document.getElementById('inputTotal').value = total.toFixed(2);
}

document.addEventListener('input', function (e) {
  if (e.target.classList.contains('cantidad') || e.target.classList.contains('precio')) {
    recalcularTotales();
  }
});

/* AUTOCARGAR PRECIO AL SELECCIONAR PRODUCTO */
document.addEventListener('change', function(e){
  if(e.target.classList.contains('producto')){
    const opt = e.target.options[e.target.selectedIndex];
    const precio = opt ? parseFloat(opt.dataset.precio || 0) : 0;
    const fila = e.target.closest('tr');

    fila.querySelector('.precio').value = precio.toFixed(2);
    fila.querySelector('.cantidad').value = 1;
    recalcularTotales();
  }
});

/* AUTOCARGAR PRIMERA FILA */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.producto').forEach(select => {
    if (select.value) {
      const opt = select.options[select.selectedIndex];
      if (opt && opt.dataset.precio) {
        const fila = select.closest('tr');
        fila.querySelector('.precio').value = parseFloat(opt.dataset.precio).toFixed(2);
      }
    }
  });
  recalcularTotales();
});

document.getElementById('btnAgregarProducto').addEventListener('click', function () {
  const fila = `
  <tr>
    <td>
    <select name="producto[]" class="form-control producto">
    <option value="">-- Seleccione producto --</option>
    <?php 
    $productos3 = $conn->query("SELECT id, nombre, precio FROM productos WHERE estado='Activo'");
    while($p = $productos3->fetch_assoc()):
    ?>
    <option value="<?= $p['id'] ?>" data-precio="<?= $p['precio'] ?>">
    <?= $p['nombre'] ?>
    </option>
    <?php endwhile; ?>
    </select>
    </td>
    <td><input name="cantidad[]" class="form-control cantidad" value="1"></td>
    <td><input name="precio[]" class="form-control precio" value="0.00"></td>
    <td><input class="form-control subtotal" readonly></td>
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
</script>

<?php if ($swal): ?>
<script>
Swal.fire({
  icon: '<?= $swal['icon'] ?>',
  title: '<?= $swal['title'] ?>',
  text: '<?= $swal['text'] ?>'
}).then(() => {
<?php if (!empty($swal['redirect'])): ?>
  window.location = '<?= $swal['redirect'] ?>';
<?php endif; ?>
});
</script>
<?php endif; ?>

<script>
document.querySelector("form").addEventListener("keydown", function(e) {
  if (e.key === "Enter") {
    e.preventDefault();
    return false;
  }
});
</script>

<?php include "../includes/footer.php"; ?>

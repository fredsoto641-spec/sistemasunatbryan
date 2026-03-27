<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once "../config/conexion.php";
require_once "../includes/header.php";

$swal = null;

/* ===== OBTENER PRODUCTOS ===== */
$productos = $conn->query("SELECT id,nombre,precio FROM productos WHERE estado='Activo'");

if(isset($_POST['guardar'])){

  $serie = trim($_POST['serie_manual']);
  $correlativo = trim($_POST['correlativo_manual']);
  $cliente = trim($_POST['cliente_manual']);
  $documento = trim($_POST['documento_manual']);
  $fecha = $_POST['fecha'];

  $documento = str_replace(' ','',$documento);

  if($serie==""){
    $swal=['icon'=>'error','title'=>'Error','text'=>'Ingrese serie'];
  }
  elseif(strtoupper($serie[0])!='B'){
    $swal=['icon'=>'error','title'=>'Serie inválida','text'=>'Debe iniciar con B'];
  }
  elseif($cliente==""){
    $swal=['icon'=>'error','title'=>'Error','text'=>'Ingrese cliente'];
  }
  elseif(!ctype_digit($documento) || strlen($documento)!=8){
    $swal=['icon'=>'error','title'=>'DNI inválido','text'=>'Debe tener 8 dígitos'];
  }
  elseif(empty($_POST['producto_id'])){
    $swal=['icon'=>'error','title'=>'Error','text'=>'Agregue productos'];
  }
  else{

    $total = 0;
    foreach($_POST['cantidad'] as $i=>$c){
      $total += floatval($c) * floatval($_POST['precio'][$i]);
    }

    $gravada = round($total/1.18,2);
    $igv = round($total-$gravada,2);

    $conn->query("
      INSERT INTO boletas
      (serie,correlativo,cliente,documento,fecha,total,gravada,igv,estado)
      VALUES
      ('$serie','$correlativo','$cliente','$documento','$fecha',$total,$gravada,$igv,'Pendiente')
    ");

    $boleta_id = $conn->insert_id;

    foreach($_POST['producto_id'] as $i=>$pid){

      if($pid=="") continue;

      $cantidad = floatval($_POST['cantidad'][$i]);
      $precio = floatval($_POST['precio'][$i]);
      $subtotal = $cantidad*$precio;

      $conn->query("
        INSERT INTO boleta_detalle
        (boleta_id,producto_id,cantidad,precio,subtotal)
        VALUES
        ($boleta_id,$pid,$cantidad,$precio,$subtotal)
      ");
    }

    $swal=[
      'icon'=>'success',
      'title'=>'Boleta registrada',
      'text'=>'Boleta guardada correctamente',
      'redirect'=>'index.php'
    ];
  }
}
?>

<div class="erp-wrapper col-md-11 mx-auto">
  <div class="erp-header">🧾 Nueva Boleta</div>

  <div class="erp-body">
    <form method="POST">

      <div class="erp-section">
        <h6>Datos</h6>
        <div class="row">
          <div class="col-md-4">
            <input name="serie_manual" class="form-control" placeholder="Serie (B001)" required>
          </div>
          <div class="col-md-4">
            <input name="correlativo_manual" class="form-control" placeholder="Correlativo" required>
          </div>
          <div class="col-md-4">
            <input type="date" name="fecha" class="form-control" required>
          </div>
        </div>
      </div>

      <div class="erp-section">
        <h6>Cliente</h6>
        <div class="row">
          <div class="col-md-8">
            <input name="cliente_manual" class="form-control" placeholder="Cliente" required>
          </div>
          <div class="col-md-4">
            <input name="documento_manual" class="form-control" placeholder="DNI" maxlength="8" required>
          </div>
        </div>
      </div>

      <div class="erp-section">
        <h6>Productos</h6>
        <table class="table table-bordered" id="tablaProductos">
          <thead>
            <tr>
              <th>Producto</th>
              <th>Cant</th>
              <th>Precio</th>
              <th>Subtotal</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <select name="producto_id[]" class="form-control producto">
                  <option value="">Seleccione</option>
                  <?php while($p=$productos->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>" data-precio="<?= $p['precio'] ?>">
                      <?= htmlspecialchars($p['nombre']) ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </td>
              <td><input name="cantidad[]" class="form-control cantidad" value="1"></td>
              <td><input name="precio[]" class="form-control precio" value="0.00" readonly></td>
              <td><input class="form-control subtotal" readonly></td>
              <td><button type="button" class="btn btn-danger btnEliminar">✖</button></td>
            </tr>
          </tbody>
        </table>

        <button type="button" id="btnAgregarProducto" class="btn btn-secondary btn-sm">
          ➕ Agregar producto
        </button>
      </div>

      <div class="erp-section text-end">
        <h4>Total S/ <span id="total">0.00</span></h4>
        <input type="hidden" name="total" id="inputTotal">
      </div>

      <div class="erp-actions">
        <button name="guardar" class="btn btn-success">💾 Guardar Boleta</button>
      </div>

    </form>
  </div>
</div>

<script>
function recalcular(){
  let total=0;
  document.querySelectorAll('#tablaProductos tbody tr').forEach(tr=>{
    let c=parseFloat(tr.querySelector('.cantidad').value)||0;
    let p=parseFloat(tr.querySelector('.precio').value)||0;
    let s=c*p;
    tr.querySelector('.subtotal').value=s.toFixed(2);
    total+=s;
  });
  document.getElementById('total').innerText=total.toFixed(2);
  document.getElementById('inputTotal').value=total.toFixed(2);
}

/* AUTOCARGAR PRECIO AL CAMBIAR PRODUCTO */
document.addEventListener('change',e=>{
  if(e.target.classList.contains('producto')){
    let opt = e.target.selectedOptions[0];
    let precio = opt ? parseFloat(opt.dataset.precio || 0) : 0;
    let tr = e.target.closest('tr');
    tr.querySelector('.precio').value = precio.toFixed(2);
    tr.querySelector('.cantidad').value = 1;
    recalcular();
  }
});

/* REACCIONAR A CAMBIO DE CANTIDAD */
document.addEventListener('input',e=>{
  if(e.target.classList.contains('cantidad')){
    recalcular();
  }
});

/* AGREGAR FILA */
document.getElementById('btnAgregarProducto').onclick=()=>{
  document.querySelector('#tablaProductos tbody').insertAdjacentHTML('beforeend',`
    <tr>
      <td>
        <select name="producto_id[]" class="form-control producto">
          <option value="">Seleccione</option>
          <?php
          $productos2 = $conn->query("SELECT id,nombre,precio FROM productos WHERE estado='Activo'");
          while($p=$productos2->fetch_assoc()):
          ?>
          <option value="<?= $p['id'] ?>" data-precio="<?= $p['precio'] ?>">
            <?= htmlspecialchars($p['nombre']) ?>
          </option>
          <?php endwhile; ?>
        </select>
      </td>
      <td><input name="cantidad[]" class="form-control cantidad" value="1"></td>
      <td><input name="precio[]" class="form-control precio" value="0.00" readonly></td>
      <td><input class="form-control subtotal" readonly></td>
      <td><button type="button" class="btn btn-danger btnEliminar">✖</button></td>
    </tr>
  `);
};

/* ELIMINAR FILA */
document.addEventListener('click',e=>{
  if(e.target.classList.contains('btnEliminar')){
    e.target.closest('tr').remove();
    recalcular();
  }
});
</script>

<?php if($swal): ?>
<script>
Swal.fire({
  icon:'<?= $swal['icon'] ?>',
  title:'<?= $swal['title'] ?>',
  text:'<?= $swal['text'] ?>'
}).then(()=>{
<?php if(!empty($swal['redirect'])): ?>
  window.location='<?= $swal['redirect'] ?>';
<?php endif; ?>
});
</script>
<?php endif; ?>

<?php include "../includes/footer.php"; ?>

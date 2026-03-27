<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

$error = "";

if (isset($_POST['guardar'])) {

  // ================= PRODUCTO =================
  $codigo = trim($_POST['codigo']);
  $nombre = trim($_POST['nombre']);
  $precio = floatval($_POST['precio']);
  $stock_minimo = intval($_POST['stock_minimo']);

  // ================= MOVIMIENTO =================
  $fecha     = $_POST['fecha'];
  $documento = trim($_POST['documento']);
  $tipo_mov  = $_POST['tipo_movimiento']; // Ingreso
  $costo     = floatval($_POST['costo']);
  $cantidad  = floatval($_POST['cantidad']);
  $medida    = trim($_POST['medida']);
  $proveedor = trim($_POST['proveedor']);

  // ================= VALIDACIONES =================
  if (
    empty($codigo) || empty($nombre) || empty($fecha) || empty($proveedor) ||
    $precio <= 0 || $costo <= 0 || $cantidad <= 0
  ) {
    $error = "❌ No se puede crear un producto sin stock o con datos inválidos.";
  } else {

    // ================= VALIDAR CODIGO =================
    $stmt = $conn->prepare("SELECT id FROM productos WHERE codigo = ?");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
      $error = "❌ El código $codigo ya existe.";
    } else {

      // ================= TRANSACCION =================
      $conn->begin_transaction();

      try {

        // INSERT PRODUCTO
        $estado = "Activo";
        $stmt1 = $conn->prepare("INSERT INTO productos (codigo,nombre,precio,stock,stock_minimo,estado)
        VALUES (?,?,?,?,?,?)");

        $stmt1->bind_param("ssdiis", $codigo, $nombre, $precio, $cantidad, $stock_minimo, $estado);
        $stmt1->execute();

        $producto_id = $conn->insert_id;

        // INSERT MOVIMIENTO
        $stmt2 = $conn->prepare("INSERT INTO producto_movimientos
        (producto_id,fecha,documento,tipo_movimiento,costo,cantidad,medida,proveedor)
        VALUES (?,?,?,?,?,?,?,?)");

        $stmt2->bind_param("isssddss", $producto_id, $fecha, $documento, $tipo_mov, $costo, $cantidad, $medida, $proveedor);
        $stmt2->execute();

        $conn->commit();
        header("Location: index.php");
        exit;

      } catch (Exception $e) {
        $conn->rollback();
        $error = "❌ Error al guardar: " . $e->getMessage();
      }

    }
  }
}
?>

<div class="erp-wrapper col-md-8 mx-auto">

  <div class="erp-header">
    ➕ Nuevo Producto
  </div>

  <div class="erp-body">

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

      <!-- ================= PRODUCTO ================= -->
      <div class="erp-section">
        <h6 class="section-title">Producto</h6>

        <label>Código</label>
        <input type="text" name="codigo" class="form-control" required>

        <label class="mt-2">Nombre del producto</label>
        <input type="text" name="nombre" class="form-control" required>

        <label class="mt-2">Precio de venta</label>
        <input type="number" step="0.01" name="precio" class="form-control" min="0.01" required>

        <label class="mt-2">Stock mínimo</label>
        <input type="number" name="stock_minimo" class="form-control" value="0">
      </div>

      <!-- ================= INGRESO ================= -->
      <div class="erp-section">
        <h6 class="section-title">Ingreso de producto</h6>

        <div class="row">
          <div class="col-md-3">
            <label>Fecha</label>
            <input type="date" name="fecha" class="form-control" required>
          </div>

          <div class="col-md-3">
            <label>Documento</label>
            <input type="text" name="documento" class="form-control">
          </div>

          <div class="col-md-3">
            <label>T/M</label>
            <select name="tipo_movimiento" class="form-control">
              <option value="Ingreso">Ingreso</option>
            </select>
          </div>

          <div class="col-md-3">
            <label>Costo</label>
            <input type="number" step="0.01" name="costo" class="form-control" min="0.01" required>
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-3">
            <label>Cantidad</label>
            <input type="number" step="0.01" name="cantidad" class="form-control" min="0.01" required>
          </div>

          <div class="col-md-3">
            <label>Medida</label>
            <select name="medida" class="form-control" required>
              <option value="">Seleccione</option>
              <option value="Caja">Caja</option>
              <option value="m2">m²</option>
              <option value="Unidad">Unidad</option>
            </select>
          </div>

          <div class="col-md-6">
            <label>Proveedor</label>
            <input type="text" name="proveedor" class="form-control" required>
          </div>
        </div>
      </div>

      <!-- ================= ACCIONES ================= -->
      <div class="erp-actions">
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
        <button name="guardar" class="btn btn-success">Guardar</button>
      </div>

    </form>

  </div>
</div>

<?php include "../includes/footer.php"; ?>

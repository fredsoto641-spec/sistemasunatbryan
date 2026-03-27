<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once "../config/conexion.php";

$id = intval($_GET['id']);

/* ================= PRODUCTO ================= */
$p = $conn->query("SELECT * FROM productos WHERE id = $id")->fetch_assoc();
if (!$p) die("Producto no encontrado");

/* ================= ÚLTIMO INGRESO ================= */
$ultimo = $conn->query("
  SELECT *
  FROM producto_movimientos
  WHERE producto_id = $id
    AND tipo_movimiento = 'Ingreso'
  ORDER BY fecha DESC, id DESC
  LIMIT 1
")->fetch_assoc();

$error = "";

/* ================= GUARDAR ================= */
if (isset($_POST['guardar'])) {

  $codigo       = $conn->real_escape_string(trim($_POST['codigo']));
  $nombre       = $conn->real_escape_string(trim($_POST['nombre']));
  $precio       = floatval($_POST['precio']);
  $estado       = $_POST['estado'];
  $stock_minimo = intval($_POST['stock_minimo']);

  /* DATOS DE INGRESO */
  $fecha     = !empty($_POST['fecha']) ? $_POST['fecha'] : null;
  $documento = $conn->real_escape_string($_POST['documento'] ?? '');
  $cantidad  = floatval($_POST['cantidad'] ?? 0);
  $medida    = $conn->real_escape_string($_POST['medida'] ?? '');
  $proveedor = $conn->real_escape_string($_POST['proveedor'] ?? '');

  /* COSTO: solo si el usuario escribe algo */
  $costo_ingreso = isset($_POST['costo']) && $_POST['costo'] !== ''
    ? floatval($_POST['costo'])
    : null;

  if ($codigo && $nombre && $precio >= 0) {

    $conn->begin_transaction();

    try {

    /* ACTUALIZAR PRODUCTO */
if ($costo_ingreso !== null) {
  $conn->query("
    UPDATE productos SET
      codigo='$codigo',
      nombre='$nombre',
      costo=$costo_ingreso,
      precio=$precio,
      estado='$estado',
      stock_minimo=$stock_minimo
    WHERE id=$id
  ");
} else {
  $conn->query("
    UPDATE productos SET
      codigo='$codigo',
      nombre='$nombre',
      precio=$precio,
      estado='$estado',
      stock_minimo=$stock_minimo
    WHERE id=$id
  ");
}


      /* SI HAY INGRESO */
      if ($cantidad > 0 && $costo_ingreso !== null) {

        /* INSERT MOVIMIENTO */
        $conn->query("
          INSERT INTO producto_movimientos
          (producto_id, fecha, documento, tipo_movimiento, costo, cantidad, medida, proveedor)
          VALUES
          ($id, ".($fecha ? "'$fecha'" : "NULL").", '$documento', 'Ingreso',
           $costo_ingreso, $cantidad, '$medida', '$proveedor')
        ");

        /* ACTUALIZAR STOCK */
        $conn->query("
          UPDATE productos
          SET stock = stock + $cantidad
          WHERE id=$id
        ");

        /* ACTUALIZAR COSTO DEL PRODUCTO */
        $conn->query("
          UPDATE productos
          SET costo = $costo_ingreso
          WHERE id=$id
        ");
      }

      $conn->commit();
      header("Location: index.php");
      exit;

    } catch (Exception $e) {
      $conn->rollback();
      $error = "❌ Error: " . $e->getMessage();
    }

  } else {
    $error = "❌ Datos inválidos";
  }
}
?>

<?php require_once "../includes/header.php"; ?>

<div class="erp-wrapper col-md-8 mx-auto">

  <div class="erp-header">✏️ Editar Producto</div>

  <div class="erp-body">

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

      <!-- PRODUCTO -->
      <div class="erp-section">
        <h6 class="section-title">Producto</h6>

        <label>Código</label>
        <input type="text" name="codigo" class="form-control"
               value="<?= htmlspecialchars($p['codigo']) ?>" required>

        <label class="mt-2">Nombre</label>
        <input type="text" name="nombre" class="form-control"
               value="<?= htmlspecialchars($p['nombre']) ?>" required>

        <label class="mt-2">Precio de venta</label>
        <input type="number" step="0.01" name="precio" class="form-control"
               value="<?= $p['precio'] ?>" required>

        <label class="mt-2">Stock mínimo</label>
        <input type="number" name="stock_minimo" class="form-control"
               value="<?= $p['stock_minimo'] ?>">

        <label class="mt-2">Estado</label>
        <select name="estado" class="form-control">
          <option <?= $p['estado']=='Activo'?'selected':'' ?>>Activo</option>
          <option <?= $p['estado']=='Inactivo'?'selected':'' ?>>Inactivo</option>
        </select>

        <div class="mt-3">
          <strong>Stock actual:</strong> <?= $p['stock'] ?>
        </div>
      </div>

      <!-- ÚLTIMO INGRESO -->
      <div class="erp-section">
        <h6 class="section-title">Último ingreso</h6>

        <?php if ($ultimo): ?>
          <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($ultimo['fecha'])) ?></p>
          <p><strong>Documento:</strong> <?= htmlspecialchars($ultimo['documento']) ?></p>
          <p><strong>Costo:</strong> S/ <?= number_format($ultimo['costo'],2) ?></p>
          <p><strong>Cantidad:</strong> <?= $ultimo['cantidad'].' '.$ultimo['medida'] ?></p>
          <p><strong>Proveedor:</strong> <?= htmlspecialchars($ultimo['proveedor']) ?></p>
        <?php else: ?>
          <p class="text-muted">No hay ingresos registrados</p>
        <?php endif; ?>
      </div>

      <!-- NUEVO INGRESO -->
      <div class="erp-section">
        <h6 class="section-title">Registrar nuevo ingreso</h6>

        <div class="row">
          <div class="col-md-3">
            <label>Fecha</label>
            <input type="date" name="fecha" class="form-control">
          </div>

          <div class="col-md-3">
            <label>Documento</label>
            <input type="text" name="documento" class="form-control">
          </div>

          <div class="col-md-3">
            <label>Costo</label>
            <input type="number" step="0.01" name="costo" class="form-control">
          </div>

          <div class="col-md-3">
            <label>Cantidad</label>
            <input type="number" step="0.01" name="cantidad" class="form-control">
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-4">
            <label>Medida</label>
            <input type="text" name="medida" class="form-control">
          </div>

          <div class="col-md-8">
            <label>Proveedor</label>
            <input type="text" name="proveedor" class="form-control">
          </div>
        </div>
      </div>

      <!-- ACCIONES -->
      <div class="erp-actions">
        <a href="index.php" class="btn btn-secondary">Volver</a>
        <button name="guardar" class="btn btn-success">Guardar cambios</button>
      </div>

    </form>

  </div>
</div>

<?php include "../includes/footer.php"; ?>

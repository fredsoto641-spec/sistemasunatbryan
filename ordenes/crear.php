<?php
require_once "../config/conexion.php";

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/* =========================
   PROCESAR FORMULARIO
========================= */
if (isset($_POST['guardar'])) {

  $orden_codigo = $conn->real_escape_string($_POST['orden_codigo']);
  $fecha        = $_POST['fecha'];
  $cliente      = $conn->real_escape_string($_POST['cliente']);
  $ruc_cliente  = $conn->real_escape_string($_POST['ruc_cliente']);
  $direccion    = $conn->real_escape_string($_POST['direccion']);
  $telefono     = $conn->real_escape_string($_POST['telefono']);
  $condicion    = $conn->real_escape_string($_POST['condicion_pago']);
  $total        = floatval($_POST['total']);

  $sql = "
    INSERT INTO ordenes
    (orden_codigo, fecha, cliente, ruc_cliente,
     direccion, telefono, condicion_pago, total)
    VALUES
    ('$orden_codigo', '$fecha', '$cliente', '$ruc_cliente',
     '$direccion', '$telefono', '$condicion', $total)
  ";

  if ($conn->query($sql)) {
    $_SESSION['mensaje'] = "✅ Orden de compra creada correctamente";
    $_SESSION['tipo'] = "success";
    header("Location: index.php"); // listado de órdenes
    exit;
  } else {
    $_SESSION['mensaje'] = "❌ Error al guardar la orden";
    $_SESSION['tipo'] = "danger";
    header("Location: crear.php");
    exit;
  }
}

/* =========================
   MOSTRAR FORMULARIO
========================= */
require_once "../includes/header.php";
?>

<!-- MENSAJE FLASH -->
<?php if (isset($_SESSION['mensaje'])): ?>
  <div class="alert alert-<?= $_SESSION['tipo'] ?> alert-dismissible fade show">
    <?= $_SESSION['mensaje'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php
  unset($_SESSION['mensaje'], $_SESSION['tipo']);
endif;
?>

<div class="card shadow col-md-8 mx-auto">
  <div class="card-header bg-primary text-white">
    📄 Nueva Orden de Compra
  </div>

  <div class="card-body">

    <form method="POST">

      <div class="row">
        <div class="col-md-6 mb-3">
          <label>Orden de Compra</label>
          <input type="text" name="orden_codigo" class="form-control" required>
        </div>

        <div class="col-md-6 mb-3">
          <label>Fecha de emisión</label>
          <input type="date" name="fecha" class="form-control" required>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label>Cliente</label>
          <input type="text" name="cliente" class="form-control" required>
        </div>

        <div class="col-md-6 mb-3">
          <label>RUC</label>
          <input type="text" name="ruc_cliente" class="form-control">
        </div>
      </div>

      <div class="mb-3">
        <label>Dirección</label>
        <input type="text" name="direccion" class="form-control">
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label>Teléfono</label>
          <input type="text" name="telefono" class="form-control">
        </div>

        <div class="col-md-6 mb-3">
          <label>Condición de pago</label>
          <input type="text" name="condicion_pago"
                 value="Crédito a 40 días"
                 class="form-control">
        </div>
      </div>

      <div class="mb-3">
        <label>Total</label>
        <input type="number" step="0.01" name="total"
               class="form-control" required>
      </div>

      <button type="submit" name="guardar"
              class="btn btn-success w-100">
        💾 Guardar Orden
      </button>

    </form>

  </div>
</div>

<?php include "../includes/footer.php"; ?>

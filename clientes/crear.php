<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../config/conexion.php";
require_once "../includes/header.php";

/* GUARDAR CLIENTE */
if (isset($_POST['guardar'])) {

  $documento = $_POST['documento'];
  $nombre    = $_POST['nombre'];
  $telefono  = $_POST['telefono'] ?? '';
  $direccion = $_POST['direccion'] ?? '';

  $sql = "
    INSERT INTO clientes
    (documento, nombre, telefono, direccion, estado)
    VALUES
    ('$documento', '$nombre', '$telefono', '$direccion', 'Activo')
  ";

  if ($conn->query($sql)) {
    $_SESSION['mensaje'] = "✅ Cliente registrado correctamente";
    $_SESSION['tipo'] = "success";
    header("Location: index.php");
    exit;
  } else {
    $error = $conn->error;
  }
}
?>

<div class="row justify-content-center">
  <div class="col-md-6">

    <div class="dashboard-card card-cyan">
      <div class="dashboard-icon text-info">👤</div>

      <h5 class="text-center mb-3">Nuevo Cliente</h5>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST">

        <div class="mb-3">
          <label>DNI / RUC</label>
          <input type="text" name="documento" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Nombre / Razón social</label>
          <input type="text" name="nombre" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Teléfono</label>
          <input type="text" name="telefono" class="form-control">
        </div>

        <div class="mb-3">
          <label>Dirección</label>
          <input type="text" name="direccion" class="form-control">
        </div>

        <button name="guardar" class="btn btn-info w-100 text-white">
          💾 Guardar Cliente
        </button>

      </form>
    </div>

  </div>
</div>

<?php include "../includes/footer.php"; ?>

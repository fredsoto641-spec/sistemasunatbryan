<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

$id = intval($_GET['id'] ?? 0);

/* ===============================
   OBTENER CLIENTE
=============================== */
$cliente = $conn->query("SELECT * FROM clientes WHERE id = $id")->fetch_assoc();

if (!$cliente) {
  echo "<div class='alert alert-danger'>Cliente no encontrado</div>";
  include "../includes/footer.php";
  exit;
}

/* ===============================
   GUARDAR CAMBIOS
=============================== */
if (isset($_POST['guardar'])) {

  $documento = $_POST['documento'];
  $nombre    = $_POST['nombre'];
  $telefono  = $_POST['telefono'];
  $direccion = $_POST['direccion'];
  $estado    = $_POST['estado'];

  $sql = "
    UPDATE clientes SET
      documento = '$documento',
      nombre    = '$nombre',
      telefono  = '$telefono',
      direccion = '$direccion',
      estado    = '$estado'
    WHERE id = $id
  ";

  if ($conn->query($sql)) {
    header("Location: index.php");
    exit;
  } else {
    $error = $conn->error;
  }
}
?>

<div class="card shadow col-md-6 mx-auto">
  <div class="card-header bg-warning text-dark">
    ✏️ Editar Cliente
  </div>

  <div class="card-body">

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

      <!-- DOCUMENTO -->
      <div class="mb-3">
        <label>DNI / RUC</label>
        <input type="text"
               name="documento"
               class="form-control"
               value="<?= htmlspecialchars($cliente['documento']) ?>"
               required>
      </div>

      <!-- NOMBRE -->
      <div class="mb-3">
        <label>Nombre / Razón social</label>
        <input type="text"
               name="nombre"
               class="form-control"
               value="<?= htmlspecialchars($cliente['nombre']) ?>"
               required>
      </div>

      <!-- TELEFONO -->
      <div class="mb-3">
        <label>Teléfono</label>
        <input type="text"
               name="telefono"
               class="form-control"
               value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>">
      </div>

      <!-- DIRECCIÓN -->
      <div class="mb-3">
        <label>Dirección</label>
        <input type="text"
               name="direccion"
               class="form-control"
               value="<?= htmlspecialchars($cliente['direccion']) ?>">
      </div>

      <!-- ESTADO -->
      <div class="mb-3">
        <label>Estado</label>
        <select name="estado" class="form-control">
          <option value="Activo" <?= $cliente['estado']=='Activo'?'selected':'' ?>>Activo</option>
          <option value="Inactivo" <?= $cliente['estado']=='Inactivo'?'selected':'' ?>>Inactivo</option>
        </select>
      </div>

      <button name="guardar" class="btn btn-success w-100">
        💾 Guardar cambios
      </button>

    </form>
  </div>
</div>

<?php include "../includes/footer.php"; ?>

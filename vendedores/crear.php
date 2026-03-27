<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

if ($_POST) {
  $nombres   = $_POST['nombres'];
  $apellidos = $_POST['apellidos'];
  $dni       = $_POST['dni'];
  $telefono  = $_POST['telefono'];
  $direccion = $_POST['direccion'];

  $conn->query("
    INSERT INTO vendedores (nombres, apellidos, dni, telefono, direccion)
    VALUES ('$nombres', '$apellidos', '$dni', '$telefono', '$direccion')
  ");

  header("Location: index.php");
  exit;
}
?>

<div class="container-fluid px-4">
  <div class="card shadow-sm">

    <div class="card-header bg-primary text-white">
      Nuevo Vendedor
    </div>

    <div class="card-body">

      <form method="POST">

        <div class="row mb-3">
          <div class="col-md-6">
            <label>Nombres</label>
            <input type="text" name="nombres" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label>Apellidos</label>
            <input type="text" name="apellidos" class="form-control" required>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-4">
            <label>DNI</label>
            <input type="text" name="dni" class="form-control" required>
          </div>

          <div class="col-md-4">
            <label>Teléfono</label>
            <input type="text" name="telefono" class="form-control">
          </div>

          <div class="col-md-4">
            <label>Dirección</label>
            <input type="text" name="direccion" class="form-control">
          </div>
        </div>

        <div class="text-end">
          <button class="btn btn-success">Guardar</button>
          <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>

      </form>

    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>

<?php
require_once "../includes/auth.php";
require_once "../config/conexion.php";
require_once "../includes/header.php";

$id = $_GET['id'] ?? 0;

$res = $conn->query("SELECT * FROM transportistas WHERE id = $id");
$transportista = $res->fetch_assoc();

if (!$transportista) {
  echo "<div class='alert alert-danger'>Transportista no encontrado</div>";
  require_once "../includes/footer.php";
  exit;
}
?>

<div class="container-fluid">
  <div class="card shadow">
    <div class="card-header">
      <h5 class="mb-0">✏️ Editar Transportista</h5>
    </div>

    <div class="card-body">
      <form action="actualizar.php" method="POST">
        <input type="hidden" name="id" value="<?= $transportista['id'] ?>">

        <div class="row">
          <div class="col-md-6 mb-3">
            <label>Nombre del transportista</label>
            <input type="text" name="nombre_transportista" class="form-control"
                   value="<?= $transportista['nombre_transportista'] ?>" required>
          </div>

          <div class="col-md-6 mb-3">
            <label>Nombre del conductor</label>
            <input type="text" name="nombre_conductor" class="form-control"
                   value="<?= $transportista['nombre_conductor'] ?>" required>
          </div>

          <div class="col-md-4 mb-3">
            <label>DNI del conductor</label>
            <input type="text" name="dni_conductor" class="form-control"
                   value="<?= $transportista['dni_conductor'] ?>" required>
          </div>

          <div class="col-md-4 mb-3">
            <label>Placa</label>
            <input type="text" name="placa" class="form-control"
                   value="<?= $transportista['placa'] ?>" required>
          </div>

          <div class="col-md-4 mb-3">
            <label>Licencia de conducir</label>
            <input type="text" name="licencia" class="form-control"
                   value="<?= $transportista['licencia'] ?>" required>
          </div>
        </div>

        <button class="btn btn-primary">Actualizar</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
      </form>
    </div>
  </div>
</div>

<?php require_once "../includes/footer.php"; ?>

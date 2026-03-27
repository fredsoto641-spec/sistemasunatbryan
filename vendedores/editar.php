<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

$id = $_GET['id'];
$v = $conn->query("SELECT * FROM vendedores WHERE id=$id")->fetch_assoc();

if ($_POST) {
  $conn->query("
    UPDATE vendedores SET
      nombres   = '{$_POST['nombres']}',
      apellidos = '{$_POST['apellidos']}',
      dni       = '{$_POST['dni']}',
      telefono  = '{$_POST['telefono']}',
      direccion = '{$_POST['direccion']}'
    WHERE id = $id
  ");

  header("Location: index.php");
  exit;
}
?>

<div class="container-fluid px-4">
  <div class="card shadow-sm">

    <div class="card-header bg-primary text-white">
      Editar Vendedor
    </div>

    <div class="card-body">

      <form method="POST">

        <div class="row mb-3">
          <div class="col-md-6">
            <label>Nombres</label>
            <input type="text" name="nombres" value="<?= $v['nombres'] ?>" class="form-control">
          </div>

          <div class="col-md-6">
            <label>Apellidos</label>
            <input type="text" name="apellidos" value="<?= $v['apellidos'] ?>" class="form-control">
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-4">
            <label>DNI</label>
            <input type="text" name="dni" value="<?= $v['dni'] ?>" class="form-control">
          </div>

          <div class="col-md-4">
            <label>Teléfono</label>
            <input type="text" name="telefono" value="<?= $v['telefono'] ?>" class="form-control">
          </div>

          <div class="col-md-4">
            <label>Dirección</label>
            <input type="text" name="direccion" value="<?= $v['direccion'] ?>" class="form-control">
          </div>
        </div>

        <div class="text-end">
          <button class="btn btn-success">Actualizar</button>
          <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>

      </form>

    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>

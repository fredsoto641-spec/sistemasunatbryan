<?php
require_once "../../includes/auth.php";
require_once "../../config/conexion.php";
require_once "../../includes/header.php";
?>

<div class="container-fluid">

  <div class="card shadow">
    <div class="card-header">
      ➕ Crear nuevo rol
    </div>

    <form method="POST" action="guardar.php">

      <div class="card-body">

        <div class="mb-3">
          <label class="form-label">Nombre del rol</label>
          <input type="text"
                 name="nombre"
                 class="form-control"
                 placeholder="Ej: Vendedor, Admin, Gerencia"
                 required>
        </div>

      </div>

      <div class="card-footer text-end">
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
        <button class="btn btn-primary">Guardar</button>
      </div>

    </form>
  </div>

</div>

<?php require_once "../../includes/footer.php"; ?>

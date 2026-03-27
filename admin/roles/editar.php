<?php
require_once "../../includes/auth.php";
require_once "../../config/conexion.php";
require_once "../../includes/header.php";

$id = $_GET['id'] ?? null;
if (!$id) die("Rol inválido");

/* ROL */
$rol = $conn->query("
  SELECT * FROM roles WHERE id = $id
")->fetch_assoc();

if (!$rol) die("Rol no encontrado");
?>

<div class="container-fluid">

  <div class="card shadow">

    <div class="card-header d-flex justify-content-between align-items-center">
      <span>✏️ Editar rol</span>

      <a href="permisos.php?rol_id=<?= $rol['id'] ?>"
         class="btn btn-sm btn-warning">
        🔐 Asignar permisos
      </a>
    </div>

    <form method="POST" action="actualizar.php">

      <input type="hidden" name="id" value="<?= $rol['id'] ?>">

      <div class="card-body">

        <div class="mb-3">
          <label class="form-label">Nombre del rol</label>
          <input type="text"
                 name="nombre"
                 class="form-control"
                 value="<?= htmlspecialchars($rol['nombre']) ?>"
                 required>
        </div>

      </div>

      <div class="card-footer text-end">
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
        <button class="btn btn-primary">Actualizar</button>
      </div>

    </form>

  </div>

</div>

<?php require_once "../../includes/footer.php"; ?>

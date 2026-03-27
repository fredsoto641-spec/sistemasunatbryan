<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "includes/auth.php";
require_once "config/conexion.php";

/* ===============================
   VARIABLES INICIALES
=============================== */
$id = $_SESSION['usuario_id'];
$mensaje = "";

/* ===============================
   OBTENER DATOS DEL USUARIO
=============================== */
$res = $conn->query("SELECT * FROM usuarios WHERE id = $id");
$user = $res->fetch_assoc();

/* ===============================
   ACTUALIZAR PERFIL
=============================== */
if (isset($_POST['guardar'])) {

  $usuario = trim($_POST['usuario'] ?? '');

  if ($usuario !== '') {

    // ACTUALIZAR USUARIO
    $conn->query("UPDATE usuarios SET usuario='$usuario' WHERE id=$id");
    $_SESSION['usuario_nombre'] = $usuario;

    // SUBIR FOTO
    if (!empty($_FILES['foto']['name'])) {

      if (!is_dir("assets/img/users")) {
        mkdir("assets/img/users", 0777, true);
      }

      $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
      $foto = "user_" . $id . "." . $ext;

      move_uploaded_file(
        $_FILES['foto']['tmp_name'],
        "assets/img/users/$foto"
      );

      $conn->query("UPDATE usuarios SET foto='$foto' WHERE id=$id");
      $_SESSION['usuario_foto'] = $foto;
    }

    $mensaje = "Perfil actualizado correctamente";
  }
}
?>

<?php require_once "includes/header.php"; ?>

<div class="container-fluid px-4">

  <h1 class="mt-4">Mi Perfil</h1>
  <ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Configuración de usuario</li>
  </ol>

  <?php if (!empty($mensaje)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($mensaje) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="row">
    <div class="col-xl-6 col-md-8">

      <div class="card shadow-sm mb-4">

        <div class="card-header bg-primary text-white">
          <i class="fas fa-user me-1"></i>
          Datos del Usuario
        </div>

        <div class="card-body">

          <form method="POST" enctype="multipart/form-data">

            <!-- FOTO -->
            <div class="text-center mb-4">
              <img src="assets/img/users/<?= htmlspecialchars($_SESSION['usuario_foto'] ?? 'default.png') ?>"
                   class="rounded-circle shadow"
                   style="width:100px;height:100px;object-fit:cover">
            </div>

            <div class="row mb-3">
              <div class="col-md-12">
                <label class="form-label">Usuario</label>
                <input type="text"
                       name="usuario"
                       value="<?= htmlspecialchars($user['usuario'] ?? '') ?>"
                       class="form-control"
                       required>
              </div>
            </div>

            <div class="row mb-4">
              <div class="col-md-12">
                <label class="form-label">Foto de perfil</label>
                <input type="file"
                       name="foto"
                       class="form-control"
                       accept="image/*">
              </div>
            </div>

            <div class="d-flex justify-content-end">
              <button name="guardar" class="btn btn-success">
                <i class="fas fa-save"></i> Guardar cambios
              </button>
            </div>

          </form>

        </div>
      </div>

    </div>
  </div>

</div>

<?php require_once "includes/footer.php"; ?>

<?php
session_start();
require_once "config/conexion.php";

if (isset($_SESSION['usuario_id'])) {
  header("Location: includes/dashboard.php");
  exit;
}

$error = "";

if (isset($_POST['login'])) {

  $usuario  = trim($_POST['usuario']);
  $password = trim($_POST['password']);

  // ================= SEGURIDAD (PREPARED STATEMENT)
  $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ? LIMIT 1");
  $stmt->bind_param("s", $usuario);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res && $res->num_rows === 1) {

    $user = $res->fetch_assoc();

    if (password_verify($password, $user['password'])) {

      // ================= SESIONES
      $_SESSION['usuario_id']     = $user['id'];
      $_SESSION['usuario_nombre'] = $user['nombre'] ?? $user['usuario']; // nombre real
      $_SESSION['usuario_foto']   = $user['foto'] ?? 'default.png';
      $_SESSION['rol_id']         = $user['rol_id'];

      // ================= PERMISOS
      $permisos = $conn->query("
        SELECT p.nombre
        FROM permisos p
        INNER JOIN rol_permisos rp ON p.id = rp.permiso_id
        WHERE rp.rol_id = {$user['rol_id']}
      ");

      $_SESSION['permisos'] = [];
      while ($p = $permisos->fetch_assoc()) {
        $_SESSION['permisos'][] = $p['nombre'];
      }

      header("Location: includes/dashboard.php");
      exit;

    } else {
      $error = "Usuario o contraseña incorrectos";
    }

  } else {
    $error = "Usuario o contraseña incorrectos";
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login | Cerámicos Importados</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
  min-height:100vh;
  background: linear-gradient(135deg,#0d6efd,#003c8f);
  display:flex;
  align-items:center;
  justify-content:center;
  font-family:'Segoe UI',sans-serif;
}
.login-wrapper{
  width:100%;
  max-width:1000px;
  background:#fff;
  border-radius:22px;
  overflow:hidden;
  box-shadow:0 25px 60px rgba(0,0,0,.25);
  display:flex;
}
.login-left{width:50%;padding:55px;}
.login-right{width:50%;background:linear-gradient(160deg,#001f3f,#004e92);color:#fff;padding:55px;}
.form-control{height:48px;border-radius:10px;}
.btn-login{height:48px;border-radius:12px;font-weight:600;background:#0d6efd;color:white;}
@media(max-width:900px){.login-right{display:none}.login-left{width:100%}}
</style>
</head>

<body>

<div class="login-wrapper">

  <div class="login-left">
    <img src="assets/img/logo.png" class="logo" alt="Logo" style="max-width:160px">

    <h4 class="mb-2">¡Bienvenido!</h4>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">

      <div class="mb-3">
        <label>Usuario</label>
        <input type="text" name="usuario" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Contraseña</label>
        <input type="password" name="password" id="password" class="form-control" required>
      </div>

      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" onclick="mostrarPassword()">
        <label class="form-check-label">Mostrar contraseña</label>
      </div>

      <button name="login" class="btn btn-login w-100">
        Iniciar sesión
      </button>

    </form>
  </div>

  <div class="login-right">
    <h2>SISTEMA CONTABLE</h2>
    <h5>EN LA NUBE ☁️</h5>
  </div>

</div>

<script>
function mostrarPassword(){
  const input = document.getElementById("password");
  input.type = input.type === "password" ? "text" : "password";
}
</script>

</body>
</html>

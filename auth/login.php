<?php include("../config/conexion.php"); ?>

<h2>Login</h2>
<form method="POST">
Usuario <input name="usuario"><br>
Clave <input type="password" name="clave"><br>
<button>Ingresar</button>
</form>

<?php
if ($_POST) {
  $u = $_POST['usuario'];
  $c = md5($_POST['clave']);

  $q = $conn->query("SELECT * FROM usuarios WHERE usuario='$u' AND clave='$c'");
  if ($q->num_rows > 0) {
    $_SESSION['usuario'] = $u;
    header("Location: ../index.php");
  } else {
    echo "❌ Datos incorrectos";
  }
}
?>

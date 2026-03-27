<?php
require_once "../config/conexion.php";
session_start();

$id = intval($_GET['id']);

$conn->query("
  UPDATE letras
  SET estado='Pagado', pagado=1
  WHERE id=$id
");

$_SESSION['mensaje'] = "✅ Letra pagada correctamente";
$_SESSION['tipo'] = "success";

header("Location: index.php");
exit;

<?php
require_once "../config/conexion.php";

$id = intval($_GET['id']);
$hoy = date('Y-m-d');

$conn->query("
  UPDATE ordenes
  SET estado_credito='Pagado',
      fecha_pago='$hoy'
  WHERE id=$id
");

header("Location: index.php");
exit;

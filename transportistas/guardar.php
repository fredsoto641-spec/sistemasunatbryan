<?php
require_once "../config/conexion.php";

$empresa   = $_POST['empresa'];
$conductor = $_POST['conductor'];
$dni       = $_POST['dni'];
$placa     = $_POST['placa'];
$licencia  = $_POST['licencia'];

$sql = $conn->prepare("
  INSERT INTO transportistas
  (empresa, conductor, dni, placa, licencia)
  VALUES (?, ?, ?, ?, ?)
");

$sql->bind_param(
  "sssss",
  $empresa,
  $conductor,
  $dni,
  $placa,
  $licencia
);

$sql->execute();

header("Location: index.php");
exit;

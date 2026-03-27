<?php
require_once "../config/conexion.php";

$id = $_POST['id'];
$nombre_transportista = $_POST['nombre_transportista'];
$nombre_conductor = $_POST['nombre_conductor'];
$dni_conductor = $_POST['dni_conductor'];
$placa = $_POST['placa'];
$licencia = $_POST['licencia'];

$sql = $conn->prepare("
  UPDATE transportistas SET
    nombre_transportista = ?,
    nombre_conductor = ?,
    dni_conductor = ?,
    placa = ?,
    licencia = ?
  WHERE id = ?
");

$sql->bind_param(
  "sssssi",
  $nombre_transportista,
  $nombre_conductor,
  $dni_conductor,
  $placa,
  $licencia,
  $id
);

$sql->execute();

header("Location: index.php");

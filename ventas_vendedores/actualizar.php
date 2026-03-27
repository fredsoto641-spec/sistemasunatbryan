<?php
require_once "../includes/auth.php";
require_once "../config/conexion.php";

$id = $_POST['id'];
$total = floatval($_POST['total_venta']);
$movilidad = floatval($_POST['costo_transporte']);

$totalNeto = $total - $movilidad;

$stmt = $conn->prepare("
  UPDATE ventas_vendedores
  SET total_venta = ?,
      costo_transporte = ?,
      total_neto = ?
  WHERE id = ?
");

$stmt->bind_param("dddi", $total, $movilidad, $totalNeto, $id);
$stmt->execute();

header("Location: index.php");
exit;

<?php
require_once "../includes/auth.php";
require_once "../config/conexion.php";

header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
if (!$id) {
  echo json_encode(['status'=>'error','message'=>'ID inválido']);
  exit;
}

$stmt = $conn->prepare("
  UPDATE ventas_vendedores
  SET estado = 0
  WHERE id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode(['status'=>'success']);

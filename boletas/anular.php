<?php
require_once "../config/conexion.php";

header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
  echo json_encode([
    'ok' => false,
    'msg' => 'ID inválido'
  ]);
  exit;
}

/* ===============================
   VALIDAR EXISTENCIA
=============================== */
$boleta = $conn->query("
  SELECT estado
  FROM boletas
  WHERE id = $id
  LIMIT 1
")->fetch_assoc();

if (!$boleta) {
  echo json_encode([
    'ok' => false,
    'msg' => 'Boleta no encontrada'
  ]);
  exit;
}

if ($boleta['estado'] === 'Anulado') {
  echo json_encode([
    'ok' => false,
    'msg' => 'La boleta ya está anulada'
  ]);
  exit;
}

/* ===============================
   ANULAR BOLETA
=============================== */
$conn->query("
  UPDATE boletas
  SET estado = 'Anulado'
  WHERE id = $id
");

echo json_encode([
  'ok' => true,
  'msg' => 'Boleta anulada correctamente'
]);

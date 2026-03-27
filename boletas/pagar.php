<?php
require_once "../config/conexion.php";

header('Content-Type: application/json');

$id     = intval($_POST['id'] ?? 0);
$metodo = trim($_POST['metodo'] ?? '');

$metodos_validos = ['Yape','Plin','Efectivo','Deposito','Tarjeta','Transferencia'];

if ($id <= 0) {
  echo json_encode([
    'ok' => false,
    'msg' => 'ID inválido'
  ]);
  exit;
}

if (!in_array($metodo, $metodos_validos)) {
  echo json_encode([
    'ok' => false,
    'msg' => 'Método de pago inválido'
  ]);
  exit;
}

/* Verificar boleta */
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
    'msg' => 'No se puede pagar una boleta anulada'
  ]);
  exit;
}

/* Marcar como pagada + método */
$conn->query("
  UPDATE boletas
  SET estado = 'Pagado',
      metodo_pago = '".$conn->real_escape_string($metodo)."'
  WHERE id = $id
");

echo json_encode([
  'ok' => true,
  'msg' => 'Boleta pagada con ' . $metodo
]);

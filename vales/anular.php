<?php
require_once "../config/conexion.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
  header("Location: index.php?msg=error");
  exit;
}

$res = $conn->query("
  SELECT estado
  FROM vales
  WHERE id = $id
  LIMIT 1
");

if ($res->num_rows === 0) {
  header("Location: index.php?msg=noexiste");
  exit;
}

$vale = $res->fetch_assoc();

if ($vale['estado'] === 'Anulado') {
  header("Location: index.php?msg=yaanulado");
  exit;
}

$conn->query("
  UPDATE vales
  SET estado = 'Anulado',
      fecha_anulacion = NOW()
  WHERE id = $id
");

header("Location: index.php?msg=anulado");
exit;

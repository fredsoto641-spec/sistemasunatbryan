<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once "../config/conexion.php";

// =======================
// RECIBIR ID
// =======================
$id = $_GET['id'] ?? 0;

if ($id == 0) {
  die("ID inválido");
}

// =======================
// OBTENER CDR BASE64
// =======================
$stmt = $conn->prepare("SELECT cdr_sunat FROM guias_remision WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
  die("Guía no encontrada");
}

$row = $res->fetch_assoc();

if (empty($row['cdr_sunat'])) {
  die("La guía aún no tiene CDR generado por SUNAT");
}

// =======================
// MOSTRAR CDR (ZIP)
// =======================
$cdr_base64 = $row['cdr_sunat'];
$cdr = base64_decode($cdr_base64);

header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=cdr_guia_$id.zip");
header("Content-Length: " . strlen($cdr));

echo $cdr;
exit;

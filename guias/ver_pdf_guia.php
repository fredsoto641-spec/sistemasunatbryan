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
// OBTENER PDF BASE64
// =======================
$stmt = $conn->prepare("SELECT pdf_sunat FROM guias_remision WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
  die("Guía no encontrada");
}

$row = $res->fetch_assoc();

if (empty($row['pdf_sunat'])) {
  die("La guía aún no tiene PDF generado por SUNAT");
}

// =======================
// MOSTRAR PDF
// =======================
$pdf_base64 = $row['pdf_sunat'];
$pdf = base64_decode($pdf_base64);

header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=guia_$id.pdf");
header("Content-Length: " . strlen($pdf));

echo $pdf;
exit;

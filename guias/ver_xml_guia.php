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
// OBTENER XML BASE64
// =======================
$stmt = $conn->prepare("SELECT xml_sunat FROM guias_remision WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
  die("Guía no encontrada");
}

$row = $res->fetch_assoc();

if (empty($row['xml_sunat'])) {
  die("La guía aún no tiene XML generado por SUNAT");
}

// =======================
// MOSTRAR XML
// =======================
$xml_base64 = $row['xml_sunat'];
$xml = base64_decode($xml_base64);

header("Content-Type: application/xml");
header("Content-Disposition: inline; filename=guia_$id.xml");
header("Content-Length: " . strlen($xml));

echo $xml;
exit;

<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once "../config/conexion.php";
require_once "config_nubefact.php";

// =======================
// RECIBIR ID
// =======================
$id = $_POST['id'] ?? 0;
if ($id == 0) { echo "ID inválido"; exit; }

// =======================
// OBTENER BOLETA
// =======================
$stmt = $conn->prepare("SELECT * FROM boletas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) { echo "Boleta no encontrada"; exit; }
$b = $res->fetch_assoc();

// =======================
// VALIDAR DNI
// =======================
$dni = trim(str_replace(" ","",$b['documento']));
if(!ctype_digit($dni) || strlen($dni)!=8){
  echo "DNI inválido"; exit;
}

// =======================
// CALCULOS
// =======================
$total = floatval($b['total']);
$gravada = round($total/1.18,2);
$igv = round($total-$gravada,2);

// =======================
// ARMAR JSON
// =======================
$data = [
  "operacion" => "generar_comprobante",
  "tipo_de_comprobante" => "2",
  "serie" => $b['serie'],
  "numero" => intval($b['correlativo']),
  "sunat_transaction" => "1",

  "cliente_tipo_de_documento" => "1",
  "cliente_numero_de_documento" => $dni,
  "cliente_denominacion" => $b['cliente'],
  "cliente_direccion" => "LIMA - PERU",
  "cliente_email" => "noemail@correo.com",

  "fecha_de_emision" => date("d-m-Y", strtotime($b['fecha'])),
  "moneda" => "1",
  "porcentaje_de_igv" => "18.00",

  "total_gravada" => number_format($gravada,2,'.',''),
  "total_igv" => number_format($igv,2,'.',''),
  "total" => number_format($total,2,'.',''),

  "enviar_automaticamente_a_la_sunat" => "true",
  "enviar_automaticamente_al_cliente" => "false",
  "formato_de_pdf" => $formato_pdf,

  "items" => [
    [
      "unidad_de_medida" => "ZZ",
      "codigo" => "001",
      "descripcion" => "VENTA SEGÚN BOLETA ".$b['serie']."-".$b['correlativo'],
      "cantidad" => "1",
      "valor_unitario" => number_format($gravada,2,'.',''),
      "precio_unitario" => number_format($total,2,'.',''),
      "subtotal" => number_format($gravada,2,'.',''),
      "tipo_de_igv" => "1",
      "igv" => number_format($igv,2,'.',''),
      "total" => number_format($total,2,'.',''),
      "anticipo_regularizacion" => "false"
    ]
  ]
];

$json = json_encode($data, JSON_UNESCAPED_UNICODE);

// =======================
// ENVIAR A NUBEFACT
// =======================
$ch = curl_init($ruta_boleta);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Authorization: Token token="'.$token.'"',
  'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST,true);
curl_setopt($ch, CURLOPT_POSTFIELDS,$json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);

$response = curl_exec($ch);

if($response===false){
  echo "Error CURL: ".curl_error($ch);
  exit;
}

curl_close($ch);

// =======================
// RESPUESTA
// =======================
$r = json_decode($response,true);

// =======================
// DETERMINAR ESTADO
// =======================
if (!empty($r['errors'])) {
    $estado = "ERROR";
}
elseif (isset($r['aceptada_por_sunat']) && $r['aceptada_por_sunat'] === true) {
    $estado = "ACEPTADO";
}
else {
    $estado = "PENDIENTE";
}

// =======================
// DATOS A GUARDAR
// =======================
$hash = $r['codigo_hash'] ?? null;
$xml = $r['xml_zip_base64'] ?? null;
$cdr = $r['cdr_zip_base64'] ?? null;
$pdf = $r['pdf_zip_base64'] ?? null;
$enlace = $r['enlace_del_pdf'] ?? null;

// =======================
// GUARDAR EN BD
// =======================
$upd = $conn->prepare("
UPDATE boletas SET
  estado_sunat = ?,
  hash_sunat = ?,
  xml_sunat = ?,
  cdr_sunat = ?,
  pdf_sunat = ?,
  respuesta_sunat = ?
WHERE id = ?
");

$upd->bind_param("ssssssi",$estado,$hash,$xml,$cdr,$pdf,$response,$id);
$upd->execute();

// =======================
// RESPUESTA AL FRONTEND
// =======================
if ($estado == "ACEPTADO") {
  echo "ok";
}
elseif ($estado == "PENDIENTE") {
  echo "pendiente";
}
else {
  echo $r['errors'] ?? "Error al enviar boleta a SUNAT";
}

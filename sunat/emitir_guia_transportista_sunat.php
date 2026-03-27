<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once "../config/conexion.php";
require_once "config_nubefact.php";

/* =========================
   VALIDAR ID
========================= */
$id = $_POST['id'] ?? 0;
if($id == 0){
  die("ID inválido");
}

/* =========================
   OBTENER VENTA
========================= */
$v = $conn->query("SELECT * FROM facturas WHERE id=$id")->fetch_assoc();

if(!$v){
  die("No se encontró la venta");
}

/* =========================
   CORRELATIVO
========================= */
$q = $conn->query("SELECT IFNULL(MAX(correlativo),0)+1 AS next 
                   FROM guias_remision 
                   WHERE tipo='TRANSPORTISTA'");
$row = $q->fetch_assoc();
$correlativo = intval($row['next']);

/* =========================
   DATA PARA NUBEFACT
========================= */
$data = [
 "operacion" => "generar_guia",
 "tipo_de_comprobante" => 7,
 "serie" => "VVV1",
 "numero" => $correlativo,

 "cliente_tipo_de_documento" => 6,
 "cliente_numero_de_documento" => $v['ruc'],
 "cliente_denominacion" => $v['cliente'],
 "cliente_direccion" => $v['direccion'],

 "fecha_de_emision" => date("d-m-Y"),
 "motivo_de_traslado" => "01",
 "peso_bruto_total" => "5",
 "peso_bruto_unidad_de_medida" => "KGM",
 "numero_de_bultos" => "1",
 "tipo_de_transporte" => "02",
 "fecha_de_inicio_de_traslado" => date("d-m-Y"),

 /* === DATOS DEL TRANSPORTISTA === */
 "conductor_documento_tipo" => "1",
 "conductor_documento_numero" => "74091255",
 "conductor_nombre" => "JUAN",
 "conductor_apellidos" => "PEREZ",
 "conductor_numero_licencia" => "Q12345678",

 "transportista_razon_social" => "EMPRESA TRANSPORTES SAC",
 "transportista_documento_tipo" => "6",
 "transportista_documento_numero" => "20123456789",
 "transportista_placa_numero" => "ABC123",

 "punto_de_partida_ubigeo" => "150101",
 "punto_de_partida_direccion" => "ALMACEN LIMA",

 "punto_de_llegada_ubigeo" => "150102",
 "punto_de_llegada_direccion" => $v['direccion'],

 "items" => [
   [
     "unidad_de_medida" => "NIU",
     "codigo" => "001",
     "descripcion" => "TRASLADO DE MERCADERIA",
     "cantidad" => "1"
   ]
 ]
];

/* =========================
   ENVIAR A NUBEFACT
========================= */
$json = json_encode($data, JSON_UNESCAPED_UNICODE);

$ch = curl_init($ruta_guia);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Authorization: Token token="'.$token.'"',
  'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$r = json_decode($response, true);

/* =========================
   ESTADO SUNAT
========================= */
$estado = isset($r['errors']) ? 'ERROR' : 'ENVIADO';

/* =========================
   GUARDAR EN BD
========================= */
$conn->query("
INSERT INTO guias_remision
(venta_id, tipo, serie, correlativo, respuesta_sunat, estado_sunat)
VALUES
($id, 'TRANSPORTISTA', 'VVV1', $correlativo,
 '".$conn->real_escape_string($response)."',
 '$estado')
");

/* =========================
   RESPUESTA FINAL
========================= */
if($estado == "ENVIADO"){
  header("Location: index.php");
  exit;
}else{
  echo "<h3>Error al enviar Guía Transportista a Nubefact</h3>";
  echo "<pre>";
  print_r($r['errors']);
  echo "</pre>";
}

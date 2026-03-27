<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once "../config/conexion.php";
require_once "config_nubefact.php";

$id = $_POST['id'] ?? 0;
if($id == 0) die("ID inválido");

$v = $conn->query("SELECT * FROM facturas WHERE id=$id")->fetch_assoc();
if(!$v) die("No se encontró la venta");

/* CORRELATIVO */
$q = $conn->query("SELECT IFNULL(MAX(correlativo),0)+1 AS next 
                   FROM guias_remision WHERE tipo='REMITENTE'");
$row = $q->fetch_assoc();
$correlativo = intval($row['next']);

/* JSON GENERAR GUIA */
$data = [
 "operacion" => "generar_guia",
 "tipo_de_comprobante" => 7,
 "serie" => "TTT1",
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

 "conductor_documento_tipo" => "1",
 "conductor_documento_numero" => "74091255",
 "conductor_nombre" => "BRYAN",
 "conductor_apellidos" => "VERGARA",
 "conductor_numero_licencia" => "Q12345678",

 "transportista_placa_numero" => "ABC123",

 "punto_de_partida_ubigeo" => "150101",
 "punto_de_partida_direccion" => "ALMACEN LIMA",

 "punto_de_llegada_ubigeo" => "150102",
 "punto_de_llegada_direccion" => $v['direccion'],

 "items" => [
   [
     "unidad_de_medida" => "NIU",
     "descripcion" => "TRASLADO DE MERCADERIA",
     "cantidad" => "1"
   ]
 ]
];

$json = json_encode($data, JSON_UNESCAPED_UNICODE);

/* CURL */
$ch = curl_init($ruta_guia);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Authorization: '.$token,
  'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$r = json_decode($response, true);

/* GUARDAR */
$conn->query("INSERT INTO guias_remision
(venta_id, tipo, serie, correlativo, respuesta_sunat, estado_sunat)
VALUES
($id,'REMITENTE','TTT1',$correlativo,
'".$conn->real_escape_string($response)."','ENVIADO')");

/* SEGUNDO PASO: CONSULTAR GUIA */
sleep(3);

$consulta = [
 "operacion" => "consultar_guia",
 "tipo_de_comprobante" => 7,
 "serie" => "TTT1",
 "numero" => $correlativo
];

$ch = curl_init($ruta_guia);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Authorization: '.$token,
  'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($consulta));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$resp2 = curl_exec($ch);
curl_close($ch);

$r2 = json_decode($resp2, true);

if(isset($r2['aceptada_por_sunat']) && $r2['aceptada_por_sunat'] == true){
    echo "ok";
}else{
    echo "SUNAT ERROR: ";
    print_r($r2);
}

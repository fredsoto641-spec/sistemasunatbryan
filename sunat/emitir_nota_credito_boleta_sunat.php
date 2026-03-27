<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once "../config/conexion.php";
require_once "config_nubefact.php";

// =======================
// RECIBIR ID BOLETA
// =======================
$id = $_POST['id'] ?? 0;
if($id==0){ echo "ID inválido"; exit; }

// =======================
// OBTENER BOLETA
// =======================
$b = $conn->query("SELECT * FROM boletas WHERE id=$id")->fetch_assoc();

// =======================
// VALIDAR DNI
// =======================
$dni = trim($b['documento']);
if(!ctype_digit($dni) || strlen($dni)!=8){
  echo "DNI inválido"; exit;
}

// =======================
// CALCULOS
// =======================
$total = floatval($b['total']);
$gravada = round($total/1.18,2);
$igv = round($total - $gravada,2);

// =======================
// CORRELATIVO NC BOLETA (DESDE BD)
// =======================
$q = $conn->query("SELECT IFNULL(MAX(correlativo),0)+1 AS next FROM notas_credito_boleta");
$row = $q->fetch_assoc();
$correlativo_nc = intval($row['next']);

// =======================
// ARMAR JSON
// =======================
$data = [
  "operacion" => "generar_comprobante",
  "tipo_de_comprobante" => 3, // NOTA DE CREDITO
  "serie" => "BBB1",
  "numero" => $correlativo_nc,
  "sunat_transaction" => 1,

  // Documento que se modifica (boleta)
  "documento_que_se_modifica_tipo"   => 2,
  "documento_que_se_modifica_serie"  => $b['serie'],
  "documento_que_se_modifica_numero" => intval($b['correlativo']),
  "tipo_de_nota_de_credito" => "01", // Anulación / descuento

  // Cliente
  "cliente_tipo_de_documento" => 1,
  "cliente_numero_de_documento" => $dni,
  "cliente_denominacion" => $b['cliente'],
  "cliente_direccion" => "LIMA - PERU",
  "cliente_email" => "",

  "fecha_de_emision" => date("d-m-Y"),
  "moneda" => 1,
  "porcentaje_de_igv" => 18.00,

  "total_gravada" => number_format($gravada,2,'.',''),
  "total_igv"     => number_format($igv,2,'.',''),
  "total"         => number_format($total,2,'.',''),

  "enviar_automaticamente_a_la_sunat" => true,
  "enviar_automaticamente_al_cliente" => false,

  "items" => [
    [
      "unidad_de_medida" => "ZZ",
      "codigo" => "001",
      "descripcion" => "ANULACIÓN DE BOLETA ".$b['serie']."-".$b['correlativo'],
      "cantidad" => 1,
      "valor_unitario" => number_format($gravada,2,'.',''),
      "precio_unitario" => number_format($total,2,'.',''),
      "subtotal" => number_format($gravada,2,'.',''),
      "tipo_de_igv" => 1,
      "igv" => number_format($igv,2,'.',''),
      "total" => number_format($total,2,'.','')
    ]
  ]
];

// =======================
// CONVERTIR A JSON
// =======================
$json = json_encode($data, JSON_UNESCAPED_UNICODE);

// =======================
// ENVIAR A NUBEFACT
// =======================
$ch = curl_init($ruta_boleta);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Authorization: Token token="'.$token.'"',
  'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$r = json_decode($response,true);

// =======================
// GUARDAR BD
// =======================
$estado = (isset($r['aceptada_por_sunat']) && $r['aceptada_por_sunat']) ? 'ACEPTADO':'OBSERVADO';

$conn->query("
INSERT INTO notas_credito_boleta
(boleta_id, serie, correlativo, respuesta_sunat, estado_sunat)
VALUES
($id,'BBB1',$correlativo_nc,'".$conn->real_escape_string($response)."','$estado')
");

// =======================
// RESPUESTA
// =======================
if($estado=="ACEPTADO"){
  echo "ok";
}else{
  echo $r['sunat_description'] ?? "Nota de crédito boleta enviada con observaciones";
}

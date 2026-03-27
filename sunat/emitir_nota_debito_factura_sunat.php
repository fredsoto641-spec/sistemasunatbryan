<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once "../config/conexion.php";
require_once "config_nubefact.php";

$id = $_POST['id'] ?? 0;
if($id==0){ echo "ID inválido"; exit; }

// =======================
// OBTENER FACTURA
// =======================
$f = $conn->query("SELECT * FROM facturas WHERE id=$id")->fetch_assoc();

// =======================
// CALCULOS
// =======================
$total = floatval($f['total']);
$gravada = round($total/1.18,2);
$igv = round($total-$gravada,2);

// =======================
// CORRELATIVO ND
// =======================
$q = $conn->query("SELECT IFNULL(MAX(correlativo),0)+1 AS next FROM notas_debito_factura");
$row = $q->fetch_assoc();
$correlativo_nd = intval($row['next']);

// =======================
// JSON NUBEFACT
// =======================
$data = [
  "operacion" => "generar_comprobante",
  "tipo_de_comprobante" => 4, // NOTA DE DEBITO
  "serie" => "FFF1",
  "numero" => $correlativo_nd,
  "sunat_transaction" => 1,

  // Documento modificado
  "documento_que_se_modifica_tipo"   => 1,
  "documento_que_se_modifica_serie"  => $f['serie'],
  "documento_que_se_modifica_numero" => intval($f['correlativo']),
  "tipo_de_nota_de_debito" => "02", // Intereses / aumento de valor

  // Cliente
  "cliente_tipo_de_documento" => 6,
  "cliente_numero_de_documento" => $f['ruc'],
  "cliente_denominacion" => $f['cliente'],
  "cliente_direccion" => $f['direccion'] ?? "",
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
      "descripcion" => "AUMENTO DE VALOR FACTURA ".$f['serie']."-".$f['correlativo'],
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

$json = json_encode($data, JSON_UNESCAPED_UNICODE);

// =======================
// ENVIAR
// =======================
$ch = curl_init($ruta_factura);
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
INSERT INTO notas_debito_factura
(factura_id, serie, correlativo, respuesta_sunat, estado_sunat)
VALUES
($id,'FFF1',$correlativo_nd,'".$conn->real_escape_string($response)."','$estado')
");

// =======================
// RESPUESTA
// =======================
if($estado=="ACEPTADO"){
  echo "ok";
}else{
  echo $r['sunat_description'] ?? "Nota de débito enviada con observaciones";
}

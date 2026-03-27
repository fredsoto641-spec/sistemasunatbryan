<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require_once "../config/conexion.php";
require_once "config_nubefact.php";

// =======================
// RECIBIR ID FACTURA
// =======================
$id = $_POST['id'] ?? 0;
if ($id == 0) { echo "ID inválido"; exit; }

// =======================
// OBTENER FACTURA
// =======================
$stmt = $conn->prepare("SELECT * FROM facturas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows == 0) { echo "Factura no encontrada"; exit; }
$f = $res->fetch_assoc();

// =======================
// CALCULOS
// =======================
$total   = floatval($f['total']);
$gravada = round($total / 1.18, 2);
$igv     = round($total - $gravada, 2);

// =======================
// OBTENER NUEVO CORRELATIVO NC
// =======================
$q = $conn->query("SELECT IFNULL(MAX(correlativo),0)+1 AS next FROM notas_credito_factura");
$row = $q->fetch_assoc();
$correlativo_nc = intval($row['next']);

// =======================
// ARMAR JSON
// =======================
$data = [
  "operacion" => "generar_comprobante",
  "tipo_de_comprobante" => 3, // NOTA DE CREDITO
  "serie" => "FFF1",
  "numero" => $correlativo_nc,

  // Documento que se modifica
  "documento_que_se_modifica_tipo"   => 1,
  "documento_que_se_modifica_serie"  => $f['serie'],
  "documento_que_se_modifica_numero" => intval($f['correlativo']),
  "tipo_de_nota_de_credito" => "01",

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

  "enviar_automaticamente_a_la_sunat" => $enviar_sunat,
  "enviar_automaticamente_al_cliente" => $enviar_cliente,
  "formato_de_pdf" => $formato_pdf,

  "items" => [
    [
      "unidad_de_medida" => "ZZ",
      "codigo" => "001",
      "descripcion" => "ANULACIÓN DE FACTURA ".$f['serie']."-".$f['correlativo'],
      "cantidad" => 1,
      "valor_unitario" => number_format($gravada,2,'.',''),
      "precio_unitario" => number_format($total,2,'.',''),
      "subtotal" => number_format($gravada,2,'.',''),
      "tipo_de_igv" => 1,
      "igv" => number_format($igv,2,'.',''),
      "total" => number_format($total,2,'.',''),
      "anticipo_regularizacion" => false
    ]
  ]
];

// =======================
// ENVIAR A NUBEFACT
// =======================
$json = json_encode($data, JSON_UNESCAPED_UNICODE);

$ch = curl_init($ruta_factura);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Authorization: Token token="'.$token.'"',
  'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
if ($response === false) {
  echo "Error CURL: ".curl_error($ch);
  exit;
}
curl_close($ch);

$r = json_decode($response, true);

// =======================
// GUARDAR EN BD
// =======================
$estado = (isset($r['aceptada_por_sunat']) && $r['aceptada_por_sunat']) ? 'ACEPTADO' : 'OBSERVADO';

$conn->query("
INSERT INTO notas_credito_factura
(factura_id, serie, correlativo, respuesta_sunat, estado_sunat)
VALUES
($id,'FFF1',$correlativo_nc,'". $conn->real_escape_string($response) ."','$estado')
");

// =======================
// RESPUESTA FRONT
// =======================
if ($estado == "ACEPTADO") {
  echo "ok";
} else {
  echo $r['errors'] ?? $r['sunat_description'] ?? "Nota de crédito enviada con observaciones";
}

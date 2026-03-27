<?php
require_once "../config/conexion.php";
require_once "config_nubefact.php";

// =======================
// RECIBIR ID
// =======================
$id = $_POST['id'] ?? 0;

if ($id == 0) {
    echo "ID inválido";
    exit;
}

// =======================
// OBTENER FACTURA
// =======================
$stmt = $conn->prepare("SELECT * FROM facturas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo "Factura no encontrada";
    exit;
}

$f = $res->fetch_assoc();

// =======================
// CALCULOS
// =======================
$total = floatval($f['total']);
$gravada = round($total / 1.18, 2);
$igv = round($total - $gravada, 2);

// =======================
// ARMAR JSON (ARCHIVO PLANO)
// =======================
$data = [
  "operacion" => "generar_comprobante",
  "tipo_de_comprobante" => 1,
  "serie" => $f['serie'],
  "numero" => intval($f['correlativo']),
  "sunat_transaction" => 1,

  "cliente_tipo_de_documento" => 6,
  "cliente_numero_de_documento" => $f['ruc'],
  "cliente_denominacion" => $f['cliente'],
  "cliente_direccion" => $f['direccion'],
  "cliente_email" => "",

  "fecha_de_emision" => date("d-m-Y", strtotime($f['fecha'])),
  "fecha_de_vencimiento" => $f['fecha_vencimiento'] ? date("d-m-Y", strtotime($f['fecha_vencimiento'])) : "",
  "moneda" => 1,
  "porcentaje_de_igv" => 18.00,

  "total_gravada" => $gravada,
  "total_igv" => $igv,
  "total" => $total,

  "enviar_automaticamente_a_la_sunat" => $enviar_sunat,
  "enviar_automaticamente_al_cliente" => $enviar_cliente,
  "formato_de_pdf" => $formato_pdf,

  "items" => [
    [
      "unidad_de_medida" => "ZZ",
      "codigo" => "001",
      "descripcion" => "VENTA SEGÚN ORDEN " . $f['orden_id'],
      "cantidad" => 1,
      "valor_unitario" => $gravada,
      "precio_unitario" => $total,
      "subtotal" => $gravada,
      "tipo_de_igv" => 1,
      "igv" => $igv,
      "total" => $total,
      "anticipo_regularizacion" => false
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
$ch = curl_init($ruta_factura);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: $token",
  "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if ($response === false) {
    echo "Error CURL: " . curl_error($ch);
    exit;
}

curl_close($ch);

// =======================
// RESPUESTA
// =======================
$r = json_decode($response, true);

// =======================
// GUARDAR EN BD
// =======================
$estado = (isset($r['aceptada_por_sunat']) && $r['aceptada_por_sunat']) ? 'ACEPTADO' : 'RECHAZADO';

$hash = $r['codigo_hash'] ?? null;
$xml = $r['xml_zip_base64'] ?? null;
$cdr = $r['cdr_zip_base64'] ?? null;
$pdf = $r['pdf_zip_base64'] ?? null;

$upd = $conn->prepare("
UPDATE facturas SET
  estado_sunat = ?,
  hash_sunat = ?,
  xml_sunat = ?,
  cdr_sunat = ?,
  pdf_sunat = ?,
  respuesta_sunat = ?
WHERE id = ?
");

$upd->bind_param(
  "ssssssi",
  $estado,
  $hash,
  $xml,
  $cdr,
  $pdf,
  $response,
  $id
);

$upd->execute();

// =======================
// RESPUESTA AL FRONTEND
// =======================
if ($estado == "ACEPTADO") {
    echo "ok";
} else {
    echo $r['errors'] ?? "Error al enviar a SUNAT";
}

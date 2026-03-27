<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once "../config/conexion.php";
require_once "config_nubefact.php";

$id = $_POST['id'] ?? 0;
if($id==0){ echo "ID inválido"; exit; }

// OBTENER GUIA
$stmt = $conn->prepare("SELECT * FROM guias_remision WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$res=$stmt->get_result();
if($res->num_rows==0){ echo "Guía no encontrada"; exit; }

$g = $res->fetch_assoc();

$data = [
 "operacion" => "consultar_guia",
 "tipo_de_comprobante" => ($g['tipo']=='REMITENTE'?7:8),
 "serie" => $g['serie'],
 "numero" => intval($g['correlativo'])
];

$json=json_encode($data,JSON_UNESCAPED_UNICODE);

// ENVIAR
$ch=curl_init($ruta_guia);
curl_setopt($ch,CURLOPT_HTTPHEADER,[
 'Authorization: Token token="'.$token.'"',
 'Content-Type: application/json'
]);
curl_setopt($ch,CURLOPT_POST,true);
curl_setopt($ch,CURLOPT_POSTFIELDS,$json);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

$response=curl_exec($ch);
curl_close($ch);

$r=json_decode($response,true);

if(isset($r['errors'])){
 echo $r['errors'];
 exit;
}

// GUARDAR
$estado = (isset($r['aceptada_por_sunat']) && $r['aceptada_por_sunat']) ? 'ACEPTADO':'EN PROCESO';

$upd=$conn->prepare("
UPDATE guias_remision SET
 estado_sunat=?,
 hash_sunat=?,
 xml_sunat=?,
 cdr_sunat=?,
 pdf_sunat=?,
 respuesta_sunat=?
WHERE id=?
");

$upd->bind_param(
 "ssssssi",
 $estado,
 $r['codigo_hash'],
 $r['xml_zip_base64'],
 $r['cdr_zip_base64'],
 $r['pdf_zip_base64'],
 $response,
 $id
);

$upd->execute();

echo "ok";

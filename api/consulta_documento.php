<?php
header("Content-Type: application/json");

if (!isset($_POST['documento'])) {
    echo json_encode(["success"=>false,"message"=>"Documento no enviado"]);
    exit;
}

$doc = trim($_POST['documento']);
$token = "802a9f87d9a2c233a77ec003a1b2e15c3330620717f879076be6ef2b18343db5";

// FUNCIÓN CURL
function curlGet($url, $token){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode($resp, true);
}

// ==================
// DNI (8 dígitos)
// ==================
if(strlen($doc) == 8){

    // 1. RENIEC
    $dniData = curlGet("https://apiperu.dev/api/dni/$doc", $token);

    if(!$dniData || !$dniData["success"]){
        echo json_encode(["success"=>false,"message"=>"DNI no encontrado"]);
        exit;
    }

    // 2. DNI-RUC
    $url = "https://apiperu.dev/api/dni-ruc";
    $data = json_encode(["dni"=>$doc]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $dniRuc = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $ruc = $dniRuc["data"]["ruc"] ?? null;

    // Si no tiene RUC
    if(!$ruc){
        echo json_encode([
            "success"=>true,
            "tipo"=>"DNI",
            "nombres"=>$dniData["data"]["nombres"],
            "apellidos"=>$dniData["data"]["apellido_paterno"]." ".$dniData["data"]["apellido_materno"],
            "ruc"=>null
        ]);
        exit;
    }

    // 3. SUNAT
    $sunat = curlGet("https://apiperu.dev/api/ruc/$ruc", $token);

    echo json_encode([
        "success"=>true,
        "tipo"=>"RUC10",
        "nombres"=>$dniData["data"]["nombres"],
        "apellidos"=>$dniData["data"]["apellido_paterno"]." ".$dniData["data"]["apellido_materno"],
        "ruc"=>$ruc,
        "razon_social"=>$sunat["data"]["razonSocial"] ?? "No disponible",
        "direccion"=>$sunat["data"]["direccionFiscal"] ?? "No disponible",
        "estado"=>$sunat["data"]["estado"] ?? "No disponible",
        "condicion"=>$sunat["data"]["condicion"] ?? "No disponible"
    ]);
    exit;
}

// ==================
// RUC (11 dígitos)
// ==================
if(strlen($doc) == 11){

    $sunat = curlGet("https://apiperu.dev/api/ruc/$doc", $token);

    if(!$sunat || !$sunat["success"]){
        echo json_encode(["success"=>false,"message"=>"RUC no encontrado"]);
        exit;
    }

    echo json_encode([
        "success"=>true,
        "tipo"=>"RUC20",
        "ruc"=>$doc,
        "razon_social"=>$sunat["data"]["razonSocial"] ?? "No disponible",
        "direccion"=>$sunat["data"]["direccionFiscal"] ?? "No disponible",
        "estado"=>$sunat["data"]["estado"] ?? "No disponible",
        "condicion"=>$sunat["data"]["condicion"] ?? "No disponible"
    ]);
    exit;
}

// ==================
echo json_encode(["success"=>false,"message"=>"Documento inválido"]);

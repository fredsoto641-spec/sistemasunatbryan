<?php
header("Content-Type: application/json");

if (!isset($_POST['ruc'])) {
    echo json_encode(["success"=>false,"message"=>"RUC no enviado"]);
    exit;
}

$ruc = trim($_POST['ruc']);
$token = "802a9f87d9a2c233a77ec003a1b2e15c3330620717f879076be6ef2b18343db5";

$url = "https://apiperu.dev/api/ruc/$ruc";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

if(curl_errno($ch)){
    echo json_encode(["success"=>false,"message"=>"Error CURL"]);
    exit;
}

curl_close($ch);

echo $response;

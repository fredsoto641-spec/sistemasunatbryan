<?php

if (!isset($_POST['dni'])) {
    echo json_encode(["success"=>false,"message"=>"DNI no enviado"]);
    exit;
}

$dni = $_POST['dni'];
$token = "802a9f87d9a2c233a77ec003a1b2e15c3330620717f879076be6ef2b18343db5";

$url = "https://apiperu.dev/api/dni-ruc";

$data = json_encode([
    "dni" => $dni
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json",
    "Content-Type: application/json",
    "Authorization: Bearer $token"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

echo $response;

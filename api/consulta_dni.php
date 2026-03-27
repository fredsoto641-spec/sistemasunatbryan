<?php
header("Content-Type: application/json");

if (!isset($_POST['dni'])) {
    echo json_encode(["success"=>false,"message"=>"DNI no enviado"]);
    exit;
}

$dni = trim($_POST['dni']);
$token = "802a9f87d9a2c233a77ec003a1b2e15c3330620717f879076be6ef2b18343db5";

$url = "https://apiperu.dev/api/dni/$dni";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // si tu hosting falla SSL

$response = curl_exec($ch);
curl_close($ch);

echo $response;

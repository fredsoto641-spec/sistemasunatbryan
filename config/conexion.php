<?php
$conn = new mysqli(
  "localhost",
  "u230373812_sissunat",
  "Admin@2027!",
  "u230373812_sis_sunat" 
);

if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

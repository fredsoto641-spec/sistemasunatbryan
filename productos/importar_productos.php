<?php
require_once "../config/conexion.php";

error_reporting(E_ALL);
ini_set("display_errors", 1);

if (!isset($_FILES['archivo'])) {
  die("No llegó archivo");
}

$archivo = fopen($_FILES['archivo']['tmp_name'], "r");

if (!$archivo) {
  die("No se pudo abrir archivo");
}

// Detectar delimitador
$primera = fgets($archivo);
$delimitador = (substr_count($primera, ";") > substr_count($primera, ",")) ? ";" : ",";
rewind($archivo);

// Saltar cabecera
fgetcsv($archivo, 3000, $delimitador);

$linea = 0;

while (($datos = fgetcsv($archivo, 3000, $delimitador)) !== false) {

  $linea++;

  $codigo = isset($datos[0]) ? trim($datos[0]) : "";
  $nombre = isset($datos[1]) ? trim($datos[1]) : "";

  $precio = isset($datos[2]) ? floatval($datos[2]) : 0;
  $stock = isset($datos[3]) ? intval($datos[3]) : 0;
  $stock_minimo = isset($datos[4]) ? intval($datos[4]) : 0;

  $estado = "Activo";

  echo "Importando línea $linea : $codigo - $nombre <br>";

  $sql = "INSERT INTO productos (codigo,nombre,precio,stock,stock_minimo,estado)
          VALUES (
            '".$conn->real_escape_string($codigo)."',
            '".$conn->real_escape_string($nombre)."',
            '$precio',
            '$stock',
            '$stock_minimo',
            '$estado'
          )";

  if (!$conn->query($sql)) {
    echo "<br><b>❌ ERROR en línea $linea</b>: ".$conn->error;
    echo "<br>Datos: ".implode(" | ", $datos);
    exit;
  }
}

echo "<br><h3>✅ Importación finalizada</h3>";

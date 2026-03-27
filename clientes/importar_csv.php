<?php
require_once "../config/conexion.php";
require_once "../includes/auth.php";

ini_set('memory_limit', '512M');
set_time_limit(0);

/* ===============================
   CONFIG
=============================== */
$LOTE = 1000;

$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$importados = isset($_GET['importados']) ? (int)$_GET['importados'] : 0;
$duplicados = isset($_GET['duplicados']) ? (int)$_GET['duplicados'] : 0;

if (!isset($_FILES['archivo']) && $offset === 0) {
  die("No se recibió archivo");
}

/* ===============================
   MANTENER ARCHIVO ENTRE LOTES
=============================== */
if ($offset === 0) {
  $archivo = $_FILES['archivo']['tmp_name'];
  $_SESSION['csv_import'] = file_get_contents($archivo);
} else {
  $archivo = tempnam(sys_get_temp_dir(), 'csv');
  file_put_contents($archivo, $_SESSION['csv_import']);
}

$handle = fopen($archivo, "r");
if (!$handle) {
  die("No se pudo abrir el archivo");
}

/* ===============================
   DETECTAR SEPARADOR
=============================== */
$linea = fgets($handle);
rewind($handle);
$sep = (substr_count($linea, ';') > substr_count($linea, ',')) ? ';' : ',';

/* ===============================
   PREPARAR QUERIES
=============================== */
$stmtCheck = $conn->prepare("
  SELECT id FROM clientes WHERE numero_documento = ? LIMIT 1
");

$stmtInsert = $conn->prepare("
  INSERT INTO clientes
    (numero_documento, razon_social, telefono, direccion, estado)
  VALUES (?, ?, ?, ?, 'Activo')
");

/* ===============================
   PROCESAR LOTE
=============================== */
$fila = 0;
$procesadas = 0;

while (($data = fgetcsv($handle, 0, $sep)) !== false) {

  if ($fila === 0) { $fila++; continue; } // encabezado
  if ($fila <= $offset) { $fila++; continue; }

  if ($procesadas >= $LOTE) break;

  $documento = trim($data[0] ?? '');
  $razon     = trim($data[1] ?? '');
  $telefono  = trim($data[2] ?? '');
  $direccion = trim($data[3] ?? '');

  if ($documento === '' || $razon === '') {
    $fila++;
    continue;
  }

  $stmtCheck->bind_param("s", $documento);
  $stmtCheck->execute();
  $stmtCheck->store_result();

  if ($stmtCheck->num_rows > 0) {
    $duplicados++;
    $fila++;
    continue;
  }

  $stmtInsert->bind_param("ssss", $documento, $razon, $telefono, $direccion);
  if ($stmtInsert->execute()) {
    $importados++;
  }

  $procesadas++;
  $fila++;
}

fclose($handle);

/* ===============================
   ¿HAY MÁS FILAS?
=============================== */
if ($procesadas === $LOTE) {
  header("Location: importar_csv.php?offset=$fila&importados=$importados&duplicados=$duplicados");
  exit;
}

/* ===============================
   FIN
=============================== */
unset($_SESSION['csv_import']);

header("Location: index.php?importados=$importados&duplicados=$duplicados");
exit;

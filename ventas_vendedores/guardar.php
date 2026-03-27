<?php
require_once "../includes/auth.php";
require_once "../config/conexion.php";

/* ===============================
   DATOS PRINCIPALES
=============================== */
$vendedor_id      = (int) ($_POST['vendedor_id'] ?? 0);
$tipo_documento   = trim($_POST['tipo_documento'] ?? '');
$codigo_documento = trim($_POST['codigo_documento'] ?? '');
$movilidad        = (float) ($_POST['movilidad'] ?? 0);

$fechas = $_POST['fecha'] ?? [];
$montos = $_POST['monto'] ?? [];

if ($vendedor_id <= 0 || empty($tipo_documento) || empty($codigo_documento)) {
  die("Datos obligatorios incompletos");
}

/* ===============================
   FECHA PRINCIPAL (USAMOS LA PRIMERA)
=============================== */
$fecha_principal = $fechas[0] ?? date('Y-m-d');

/* ===============================
   INSERT CABECERA (CORREGIDO)
=============================== */
$conn->query("
  INSERT INTO ventas_vendedores (
    vendedor_id,
    fecha,
    total_venta,
    costo_transporte,
    total_neto,
    tipo_documento,
    codigo_documento,
    estado
  ) VALUES (
    $vendedor_id,
    '$fecha_principal',
    0,
    $movilidad,
    0,
    '$tipo_documento',
    '$codigo_documento',
    1
  )
");

$venta_id = $conn->insert_id;

/* ===============================
   INSERT DETALLE + CÁLCULOS
=============================== */
$total_venta = 0;

for ($i = 0; $i < count($fechas); $i++) {
  $fecha = $fechas[$i];
  $monto = (float) $montos[$i];

  if ($fecha && $monto > 0) {
    $total_venta += $monto;

    $conn->query("
      INSERT INTO ventas_vendedores_detalle (
        venta_id,
        fecha,
        monto
      ) VALUES (
        $venta_id,
        '$fecha',
        $monto
      )
    ");
  }
}

/* ===============================
   ACTUALIZAR TOTALES
=============================== */
$total_neto = $total_venta - $movilidad;
if ($total_neto < 0) $total_neto = 0;

$conn->query("
  UPDATE ventas_vendedores
  SET
    total_venta = $total_venta,
    total_neto  = $total_neto
  WHERE id = $venta_id
");

/* ===============================
   REDIRECCIÓN
=============================== */
header("Location: index.php");
exit;

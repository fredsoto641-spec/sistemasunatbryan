<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../libs/dompdf/autoload.inc.php";

use Dompdf\Dompdf;

$fecha = $_GET['fecha'] ?? date('Y-m-d');

/* ===============================
   HTML BASE
=============================== */
$html = "
<!DOCTYPE html>
<html lang='es'>
<head>
<meta charset='UTF-8'>
<style>
  body { font-family: DejaVu Sans; font-size: 11px; }
  h1 { text-align:center; margin-bottom:4px; }
  h2 { background:#111827; color:#fff; padding:6px; }
  table { width:100%; border-collapse:collapse; margin-bottom:18px; }
  th, td { border:1px solid #333; padding:5px; }
  th { background:#e5e7eb; font-size:10px; }
  .right { text-align:right; }
  .center { text-align:center; }
</style>
</head>
<body>

<h1>REPORTE DIARIO</h1>
<p><b>Fecha:</b> $fecha</p>
";

/* =====================================================
   FACTURAS
===================================================== */
$html .= "
<h2>FACTURAS</h2>
<table>
<tr>
  <th>Documento</th>
  <th>Cliente</th>
  <th>Emisión</th>
  <th>Vencimiento</th>
  <th>Días</th>
  <th>Total</th>
  <th>Pagado</th>
  <th>Saldo</th>
  <th>Método</th>
  <th>Estado</th>
</tr>
";

$res = $conn->query("
  SELECT
    IFNULL(NULLIF(CONCAT(f.serie,'-',f.correlativo) AS documento

    f.cliente,
    f.fecha,
    f.fecha_vencimiento,
    DATEDIFF(f.fecha_vencimiento, f.fecha) AS dias,
    f.total,
    f.monto_pagado,
    f.saldo,
    f.estado,
    (SELECT metodo_pago 
     FROM factura_pagos 
     WHERE factura_id = f.id 
     ORDER BY id DESC 
     LIMIT 1) AS metodo_pago
  FROM facturas f
  WHERE DATE(f.fecha) = '$fecha'
");

if ($res->num_rows == 0) {
  $html .= "<tr><td colspan='10' class='center'>No hay facturas</td></tr>";
} else {
  while ($r = $res->fetch_assoc()) {
    $dias = $r['fecha_vencimiento'] ? $r['dias'] : '-';
    $metodo = ($r['metodo_pago']) ? $r['metodo_pago'] : '-';

    $html .= "
    <tr>
      <td>{$r['documento']}</td>
      <td>{$r['cliente']}</td>
      <td>{$r['fecha']}</td>
      <td>{$r['fecha_vencimiento']}</td>
      <td class='center'>$dias</td>
      <td class='right'>S/ ".number_format($r['total'],2)."</td>
      <td class='right'>S/ ".number_format($r['monto_pagado'],2)."</td>
      <td class='right'>S/ ".number_format($r['saldo'],2)."</td>
      <td class='center'>$metodo</td>
      <td>{$r['estado']}</td>
    </tr>";
  }
}

$html .= "</table>";

/* =====================================================
   BOLETAS
===================================================== */
$html .= "
<h2>BOLETAS</h2>
<table>
<tr>
  <th>Documento</th>
  <th>Cliente</th>
  <th>Emisión</th>
  <th>Días</th>
  <th>Total</th>
  <th>Pagado</th>
  <th>Saldo</th>
  <th>Método</th>
  <th>Estado</th>
</tr>
";

$res = $conn->query("
  SELECT
    CONCAT(b.serie,'-',b.correlativo) AS documento,
    b.documento_cliente AS cliente,
    b.fecha_emision,
    DATEDIFF(CURDATE(), b.fecha_emision) AS dias,
    b.monto_total,
    b.monto_pagado,
    b.saldo,
    b.estado,
    (SELECT metodo_pago 
     FROM boleta_pagos 
     WHERE boleta_id = b.id 
     ORDER BY id DESC 
     LIMIT 1) AS metodo_pago
  FROM boletas b
  WHERE DATE(b.fecha_emision) = '$fecha'
");

if ($res->num_rows == 0) {
  $html .= "<tr><td colspan='9' class='center'>No hay boletas</td></tr>";
} else {
  while ($r = $res->fetch_assoc()) {
    $metodo = ($r['metodo_pago']) ? $r['metodo_pago'] : '-';

    $html .= "
    <tr>
      <td>{$r['documento']}</td>
      <td>{$r['cliente']}</td>
      <td>{$r['fecha_emision']}</td>
      <td class='center'>{$r['dias']}</td>
      <td class='right'>S/ ".number_format($r['monto_total'],2)."</td>
      <td class='right'>S/ ".number_format($r['monto_pagado'],2)."</td>
      <td class='right'>S/ ".number_format($r['saldo'],2)."</td>
      <td class='center'>$metodo</td>
      <td>{$r['estado']}</td>
    </tr>";
  }
}

$html .= "</table>";

/* =====================================================
   VALES
===================================================== */
$html .= "
<h2>VALES</h2>
<table>
<tr>
  <th>Documento</th>
  <th>Cliente</th>
  <th>Emisión</th>
  <th>Días</th>
  <th>Total</th>
  <th>Pagado</th>
  <th>Saldo</th>
  <th>Método</th>
  <th>Estado</th>
</tr>
";

$res = $conn->query("
  SELECT
    CONCAT(v.serie,'-',v.correlativo) AS documento,
    v.cliente AS cliente,
    v.fecha_emision,
    DATEDIFF(CURDATE(), v.fecha_emision) AS dias,
    v.monto_total,
    v.monto_pagado,
    v.saldo,
    v.estado,
    (SELECT metodo_pago 
     FROM vale_pagos 
     WHERE vale_id = v.id 
     ORDER BY id DESC 
     LIMIT 1) AS metodo_pago
  FROM vales v
  WHERE DATE(v.fecha_emision) = '$fecha'
");

if ($res->num_rows == 0) {
  $html .= "<tr><td colspan='9' class='center'>No hay vales</td></tr>";
} else {
  while ($r = $res->fetch_assoc()) {
    $metodo = ($r['metodo_pago']) ? $r['metodo_pago'] : '-';

    $html .= "
    <tr>
      <td>{$r['documento']}</td>
      <td>{$r['cliente']}</td>
      <td>{$r['fecha_emision']}</td>
      <td class='center'>{$r['dias']}</td>
      <td class='right'>S/ ".number_format($r['monto_total'],2)."</td>
      <td class='right'>S/ ".number_format($r['monto_pagado'],2)."</td>
      <td class='right'>S/ ".number_format($r['saldo'],2)."</td>
      <td class='center'>$metodo</td>
      <td>{$r['estado']}</td>
    </tr>";
  }
}

$html .= "
</table>
</body>
</html>
";

/* ===============================
   GENERAR PDF
=============================== */
$dompdf = new Dompdf(['isRemoteEnabled' => true]);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream(\"reporte_diario_$fecha.pdf\", [\"Attachment\" => false]);

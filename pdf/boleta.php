<?php
require_once "../config/conexion.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

/* ===============================
   OBTENER BOLETA
=============================== */
$id = intval($_GET['id'] ?? 0);

$boleta = $conn->query("
  SELECT *
  FROM boletas
  WHERE id = $id
  LIMIT 1
")->fetch_assoc();

$detalles = $conn->query("
  SELECT *
  FROM boleta_detalle
  WHERE boleta_id = $id
");

/* ===============================
   LOGO
=============================== */
$logoFile = __DIR__ . '/../assets/img/logo.png';

if (file_exists($logoFile)) {
  $logoData = base64_encode(file_get_contents($logoFile));
  $logoSrc  = 'data:image/png;base64,' . $logoData;
} else {
  $logoSrc = '';
}

/* ===============================
   DOMPDF
=============================== */
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

/* ===============================
   HTML
=============================== */
$total = 0;

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    color: #111;
  }

  .empresa {
    border-bottom: 3px solid #000;
    padding-bottom: 10px;
    margin-bottom: 15px;
  }

  .empresa img {
    height: 60px;
    margin-bottom: 6px;
  }

  .doc {
    text-align: right;
    font-size: 13px;
  }

  .titulo {
    text-align: center;
    font-size: 18px;
    margin: 18px 0;
    letter-spacing: 1px;
  }

  .info {
    border: 1px solid #ccc;
    padding: 10px;
    margin-bottom: 15px;
    line-height: 1.6;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  th, td {
    border: 1px solid #333;
    padding: 8px;
  }

  th {
    background: #eee;
  }

  .right {
    text-align: right;
  }

  .total {
    text-align: right;
    margin-top: 15px;
  }

  .total span {
    background: #000;
    color: #fff;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: bold;
  }

  .footer {
    margin-top: 30px;
    text-align: center;
    font-size: 11px;
    color: #555;
  }
</style>
</head>
<body>

<table width="100%">
<tr>
<td class="empresa">
';

if ($logoSrc !== '') {
  $html .= '<img src="'.$logoSrc.'"><br>';
}

$html .= '
<strong>ACABADOS ZENDY</strong><br>
Lima - Perú<br>
Tel: 981 157 807
</td>

<td class="doc">
<strong>BOLETA</strong><br>
'.htmlspecialchars($boleta['serie']).'-'.htmlspecialchars($boleta['correlativo']).'
</td>
</tr>
</table>

<div class="titulo">BOLETA DE VENTA</div>

<div class="info">
  <strong>Cliente:</strong> '.htmlspecialchars($boleta['cliente']).'<br>
  <strong>Documento:</strong> '.htmlspecialchars($boleta['documento']).'<br>
  <strong>Fecha:</strong> '.date('d/m/Y', strtotime($boleta['fecha'])).'<br>
  <strong>Método de pago:</strong> 
  <span style="font-weight:bold; text-transform:uppercase;">
    '.htmlspecialchars($boleta['metodo_pago'] ?? '—').'
  </span>
</div>

<table>
<thead>
<tr>
  <th>Producto</th>
  <th>Cantidad</th>
  <th>Precio</th>
  <th>Subtotal</th>
</tr>
</thead>
<tbody>
';

while ($d = $detalles->fetch_assoc()) {
  $total += $d['subtotal'];

  $html .= '
  <tr>
    <td>'.htmlspecialchars($d['producto']).'</td>
    <td class="right">'.number_format($d['cantidad'], 2).'</td>
    <td class="right">S/ '.number_format($d['precio'], 2).'</td>
    <td class="right">S/ '.number_format($d['subtotal'], 2).'</td>
  </tr>';
}

$html .= '
</tbody>
</table>

<div class="total">
  <span>TOTAL: S/ '.number_format($total, 2).'</span>
</div>

<div class="footer">
  Gracias por su preferencia — <strong>Acabados Zendy</strong>
</div>

</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream(
  "boleta_{$boleta['serie']}-{$boleta['correlativo']}.pdf",
  ["Attachment" => false]
);

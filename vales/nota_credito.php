<?php
require_once "../config/conexion.php";

$vale_id = $_POST['vale_id'] ?? 0;
$monto_pagado = $_POST['monto_pagado'] ?? 0;
$metodo = $_POST['metodo'] ?? '';

if ($vale_id == 0 || $monto_pagado <= 0 || $metodo == '') {
  exit("Datos inválidos");
}

$res = $conn->query("SELECT * FROM vales WHERE id = $vale_id");
$vale = $res->fetch_assoc();

if (!$vale) {
  exit("Vale no encontrado");
}

$cliente_id = $vale['cliente_id'];
$monto_pagado_actual = $vale['monto_pagado'];
$saldo_actual = $vale['saldo'];

$vuelto = 0;

if ($monto_pagado > $saldo_actual) {
  $vuelto = $monto_pagado - $saldo_actual;
  $nuevo_pagado = $monto_pagado_actual + $monto_pagado;
  $nuevo_saldo = 0;
} else {
  $vuelto = 0;
  $nuevo_pagado = $monto_pagado_actual + $monto_pagado;
  $nuevo_saldo = $saldo_actual - $monto_pagado;
}

$estado = ($nuevo_saldo <= 0) ? 'Pagado' : 'Pendiente';

$conn->query("UPDATE vales SET
  monto_pagado = $nuevo_pagado,
  saldo = $nuevo_saldo,
  vuelto = vuelto + $vuelto,
  estado = '$estado'
WHERE id = $vale_id");

$conn->query("INSERT INTO notas_credito 
(vale_id, cliente_id, monto_pagado, vuelto, metodo_pago)
VALUES
($vale_id, $cliente_id, $monto_pagado, $vuelto, '$metodo')");

if ($vuelto > 0) {
  $conn->query("INSERT INTO vueltos_clientes (cliente_id, vale_id, monto, estado)
VALUES ($cliente_id, $vale_id, $vuelto, 'Pendiente')");

}

echo "OK";

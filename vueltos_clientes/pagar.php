<?php
require_once "../config/conexion.php";

$id = $_POST['id'];
$metodo = $_POST['metodo'];

/* ===============================
   OBTENER EL VUELTO
=============================== */
$res = $conn->query("SELECT * FROM vueltos_clientes WHERE id = $id");
$vuelto = $res->fetch_assoc();

if (!$vuelto) {
  exit("No existe el vuelto");
}

$vale_id = $vuelto['vale_id'];
$monto = $vuelto['monto'];

/* ===============================
   MARCAR VUELTO COMO PAGADO
=============================== */
$conn->query("UPDATE vueltos_clientes 
SET estado='Pagado', metodo_pago='$metodo'
WHERE id=$id");

/* ===============================
   DESCONTAR VUELTO EN EL VALE
=============================== */
$conn->query("UPDATE vales 
SET vuelto = vuelto - $monto
WHERE id = $vale_id");

echo "OK";

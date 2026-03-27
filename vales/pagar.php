<?php
require_once "../config/conexion.php";
session_start();

$id = intval($_GET['id']);

$conn->query("UPDATE vales SET estado='Pagado' WHERE id=$id");

$_SESSION['mensaje'] = "Vale marcado como PAGADO";
$_SESSION['tipo'] = "success";

header("Location: index.php");
exit;

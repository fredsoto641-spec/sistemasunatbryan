<?php
require_once "../config/conexion.php";
session_start();

$id = intval($_GET['id']);

$conn->query("DELETE FROM letras_facturas WHERE letra_id=$id");
$conn->query("DELETE FROM letras_ordenes WHERE letra_id=$id");
$conn->query("DELETE FROM letras WHERE id=$id");

$_SESSION['mensaje'] = "🗑 Letra eliminada";
$_SESSION['tipo'] = "danger";

header("Location: index.php");
exit;

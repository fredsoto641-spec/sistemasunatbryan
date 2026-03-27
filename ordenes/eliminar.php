<?php
require_once "../config/conexion.php";

$id = intval($_GET['id']);

/* Eliminar primero detalles si existen */
$conn->query("DELETE FROM orden_detalle WHERE orden_id=$id");

/* Eliminar orden */
$conn->query("DELETE FROM ordenes WHERE id=$id");

header("Location: index.php");
exit;

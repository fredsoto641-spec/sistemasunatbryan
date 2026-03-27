<?php
require_once "../config/conexion.php";
require_once "../includes/auth.php";
require_once "../includes/permisos.php";

if (!tienePermiso('vendedores_crear')) {
    echo "No tienes permiso";
    exit;
}

$id = $_POST['id'] ?? 0;

if ($id == 0) {
    echo "ID inválido";
    exit;
}

// Eliminación lógica (más segura que DELETE)
$stmt = $conn->prepare("UPDATE vendedores SET estado = 'Inactivo' WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "ok";
} else {
    echo "No se pudo eliminar";
}

<?php
require_once "../../includes/auth.php";
require_once "../../config/conexion.php";

$rol_id = $_POST['rol_id'] ?? null;
$permisos = $_POST['permisos'] ?? [];

if (!$rol_id) die("Rol inválido");

$conn->begin_transaction();

try {

  // 1️⃣ borrar permisos actuales
  $conn->query("
    DELETE FROM rol_permisos
    WHERE rol_id = $rol_id
  ");

  // 2️⃣ insertar nuevos
  if (!empty($permisos)) {
    $stmt = $conn->prepare("
      INSERT INTO rol_permisos (rol_id, permiso_id)
      VALUES (?, ?)
    ");

    foreach ($permisos as $permiso_id) {
      $stmt->bind_param("ii", $rol_id, $permiso_id);
      $stmt->execute();
    }
  }

  $conn->commit();
  header("Location: index.php");
  exit;

} catch (Exception $e) {
  $conn->rollback();
  die("Error al guardar permisos");
}

<?php
ob_start(); // 🔥 CLAVE PARA HOSTINGER

require_once "../includes/auth.php";
require_once "../config/conexion.php";
require_once "../includes/header.php";

/* ===============================
   VALIDAR ID (HOSTINGER SAFE)
=============================== */
$id = isset($_GET['id']) ? (int) trim($_GET['id']) : 0;

if ($id <= 0) {
  echo "<div class='alert alert-danger m-3'>
          Parámetros incompletos (ID no recibido)
        </div>";
  require_once "../includes/footer.php";
  ob_end_flush();
  exit;
}

/* ===============================
   OBTENER VENTA
=============================== */
$res = $conn->query("
  SELECT
    vv.id,
    vv.fecha,
    vv.total_venta,
    vv.costo_transporte,
    vv.tipo_documento,
    vv.codigo_documento,
    CONCAT(v.nombres,' ',v.apellidos) AS vendedor
  FROM ventas_vendedores vv
  INNER JOIN vendedores v ON v.id = vv.vendedor_id
  WHERE vv.id = $id
  LIMIT 1
");

if (!$res || $res->num_rows === 0) {
  echo "<div class='alert alert-warning m-3'>
          Venta no encontrada
        </div>";
  require_once "../includes/footer.php";
  ob_end_flush();
  exit;
}

$venta = $res->fetch_assoc();
?>

<div class="container-fluid">
  <div class="card shadow">
    <div class="card-header">
      ✏️ Editar venta
    </div>

    <form method="POST" action="actualizar.php">

      <input type="hidden" name="id" value="<?= $venta['id'] ?>">

      <div class="card-body">

        <div class="mb-3">
          <label>Vendedor</label>
          <input type="text"
                 class="form-control"
                 value="<?= htmlspecialchars($venta['vendedor']) ?>"
                 readonly>
        </div>

        <div class="mb-3">
          <label>Documento</label>
          <input type="text"
                 class="form-control"
                 value="<?= htmlspecialchars($venta['tipo_documento'].' - '.$venta['codigo_documento']) ?>"
                 readonly>
        </div>

        <div class="mb-3">
          <label>Fecha</label>
          <input type="date"
                 class="form-control"
                 value="<?= $venta['fecha'] ?>"
                 readonly>
        </div>

        <div class="mb-3">
          <label>Total vendido</label>
          <input type="number"
                 name="total_venta"
                 class="form-control"
                 step="0.01"
                 value="<?= $venta['total_venta'] ?>"
                 required>
        </div>

        <div class="mb-3">
          <label>Movilidad / Maestro</label>
          <input type="number"
                 name="costo_transporte"
                 class="form-control"
                 step="0.01"
                 value="<?= $venta['costo_transporte'] ?>">
        </div>

      </div>

      <div class="card-footer text-end">
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">
          Guardar cambios
        </button>
      </div>

    </form>
  </div>
</div>

<?php
require_once "../includes/footer.php";
ob_end_flush(); // 🔥 CIERRE BUFFER

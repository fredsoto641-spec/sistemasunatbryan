<?php
echo "<div style='background:red;color:white;padding:40px;font-size:30px;text-align:center'>
ARCHIVO CORRECTO
</div>";
exit;

require_once "../config/conexion.php";

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/* =========================
   VALIDAR ID
========================= */
if (!isset($_GET['id'])) {
  $_SESSION['mensaje'] = "❌ Letra no válida";
  $_SESSION['tipo'] = "danger";
  header("Location: index.php");
  exit;
}

$id = intval($_GET['id']);

/* =========================
   TRAER LETRA
========================= */
$letra = $conn->query("SELECT * FROM letras WHERE id = $id")->fetch_assoc();

if (!$letra) {
  $_SESSION['mensaje'] = "❌ Letra no encontrada";
  $_SESSION['tipo'] = "danger";
  header("Location: index.php");
  exit;
}

/* =========================
   FACTURAS Y ORDENES
========================= */
$facturas = $conn->query("
  SELECT f.id, f.cliente, f.total, o.orden_codigo
  FROM facturas f
  LEFT JOIN ordenes o ON o.id = f.orden_id
  ORDER BY f.id DESC
");

$ordenes = $conn->query("
  SELECT id, orden_codigo, total
  FROM ordenes
  ORDER BY id DESC
");

/* =========================
   FACTURAS / ORDENES ACTUALES
========================= */
$facturasActuales = [];
$resF = $conn->query("SELECT factura_id FROM letras_facturas WHERE letra_id = $id");
while ($r = $resF->fetch_assoc()) {
  $facturasActuales[] = $r['factura_id'];
}

$ordenesActuales = [];
$resO = $conn->query("SELECT orden_id FROM letras_ordenes WHERE letra_id = $id");
while ($r = $resO->fetch_assoc()) {
  $ordenesActuales[] = $r['orden_id'];
}

/* =========================
   GUARDAR CAMBIOS
========================= */
if (isset($_POST['guardar'])) {

  $codigo = $conn->real_escape_string($_POST['codigo']);
  $numero = intval($_POST['numero']);
  $monto  = floatval($_POST['monto']);
  $fv     = $_POST['fecha_vencimiento'];

  // actualizar letra
  $conn->query("
    UPDATE letras SET
      codigo = '$codigo',
      numero = $numero,
      monto = $monto,
      fecha_vencimiento = '$fv'
    WHERE id = $id
  ");

  // 🔥 limpiar relaciones
  $conn->query("DELETE FROM letras_facturas WHERE letra_id = $id");
  $conn->query("DELETE FROM letras_ordenes WHERE letra_id = $id");

  // guardar facturas
  if (!empty($_POST['factura_id'])) {
    foreach ($_POST['factura_id'] as $fid) {
      $fid = intval($fid);
      $conn->query("
        INSERT INTO letras_facturas (letra_id, factura_id)
        VALUES ($id, $fid)
      ");
    }
  }

  // guardar ordenes
  if (!empty($_POST['orden_id'])) {
    foreach ($_POST['orden_id'] as $oid) {
      $oid = intval($oid);
      $conn->query("
        INSERT INTO letras_ordenes (letra_id, orden_id)
        VALUES ($id, $oid)
      ");
    }
  }

  $_SESSION['mensaje'] = "✏️ Letra actualizada correctamente";
  $_SESSION['tipo'] = "success";
  header("Location: index.php");
  exit;
}

/* =========================
   MOSTRAR FORMULARIO
========================= */
require_once "../includes/header.php";
?>

<div class="card shadow col-md-8 mx-auto">
  <div class="card-header bg-warning text-dark">
    ✏️ Editar Letra
  </div>

  <div class="card-body">

    <form method="POST">

      <div class="mb-3">
        <label>Código de la letra</label>
        <input type="text" name="codigo" class="form-control"
               value="<?= htmlspecialchars($letra['codigo']) ?>" required>
      </div>

      <div class="mb-3">
        <label>Número de letra</label>
        <input type="number" name="numero" class="form-control"
               value="<?= $letra['numero'] ?>" required>
      </div>

      <div class="mb-3">
        <label>Monto</label>
        <input type="number" step="0.01" name="monto"
               class="form-control"
               value="<?= $letra['monto'] ?>" required>
      </div>

      <div class="mb-3">
        <label>Fecha de vencimiento</label>
        <input type="date" name="fecha_vencimiento"
               class="form-control"
               value="<?= $letra['fecha_vencimiento'] ?>" required>
      </div>

      <hr>

      <!-- FACTURAS -->
      <h6>Facturas asociadas</h6>
      <?php while ($f = $facturas->fetch_assoc()): ?>
        <div class="form-check">
          <input class="form-check-input"
                 type="checkbox"
                 name="factura_id[]"
                 value="<?= $f['id'] ?>"
                 <?= in_array($f['id'], $facturasActuales) ? 'checked' : '' ?>>
          <label class="form-check-label">
            Nro de factura: <?= $f['orden_codigo'] ?? '—' ?>
            | <?= htmlspecialchars($f['cliente']) ?>
            | S/ <?= number_format($f['total'],2) ?>
          </label>
        </div>
      <?php endwhile; ?>

      <hr>

      <!-- ORDENES -->
      <h6>Órdenes asociadas</h6>
      <?php while ($o = $ordenes->fetch_assoc()): ?>
        <div class="form-check">
          <input class="form-check-input"
                 type="checkbox"
                 name="orden_id[]"
                 value="<?= $o['id'] ?>"
                 <?= in_array($o['id'], $ordenesActuales) ? 'checked' : '' ?>>
          <label class="form-check-label">
            Nro de orden: <?= htmlspecialchars($o['orden_codigo']) ?>
            | S/ <?= number_format($o['total'],2) ?>
          </label>
        </div>
      <?php endwhile; ?>

      <button type="submit" name="guardar"
              class="btn btn-success w-100 mt-3">
        💾 Guardar cambios
      </button>

      <a href="index.php"
         class="btn btn-secondary w-100 mt-2">
        ⬅ Volver
      </a>

    </form>

  </div>
</div>

<?php include "../includes/footer.php"; ?>

<?php
require_once "../config/conexion.php";

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/* =========================
   PROCESAR FORMULARIO
========================= */
if (isset($_POST['guardar'])) {

  if (empty($_POST['factura_id']) && empty($_POST['orden_id'])) {
    $_SESSION['mensaje'] = "❌ Debe seleccionar al menos una factura u orden";
    $_SESSION['tipo'] = "danger";
    header("Location: crear.php");
    exit;
  }

  $codigo = $conn->real_escape_string($_POST['codigo']);
  $numero = intval($_POST['numero']);
  $monto  = floatval($_POST['monto']);
  $fe     = $_POST['fecha_emision'];
  $fv     = $_POST['fecha_vencimiento'];

  $conn->query("
    INSERT INTO letras
    (codigo, fecha_emision, numero, monto, fecha_vencimiento, pagado, estado)
    VALUES
    ('$codigo', '$fe', $numero, $monto, '$fv', 0, 'Pendiente')
  ");

  $letra_id = $conn->insert_id;

  /* FACTURAS */
  if (!empty($_POST['factura_id'])) {
    foreach ($_POST['factura_id'] as $fid) {
      $fid = intval($fid);
      $conn->query("
        INSERT INTO letras_facturas (letra_id, factura_id)
        VALUES ($letra_id, $fid)
      ");
    }
  }

  /* ORDENES */
  if (!empty($_POST['orden_id'])) {
    foreach ($_POST['orden_id'] as $oid) {
      $oid = intval($oid);
      $conn->query("
        INSERT INTO letras_ordenes (letra_id, orden_id)
        VALUES ($letra_id, $oid)
      ");
    }
  }

  $_SESSION['mensaje'] = "✅ Letra creada correctamente";
  $_SESSION['tipo'] = "success";
  header("Location: index.php");
  exit;
}

/* =========================
   MOSTRAR FORMULARIO
========================= */
require_once "../includes/header.php";

/* 🔹 FACTURAS + ORDEN DE COMPRA */
$facturas = $conn->query("
  SELECT 
    f.id,
    f.cliente,
    f.total,
    f.fecha,
    o.orden_codigo
  FROM facturas f
  LEFT JOIN ordenes o ON o.id = f.orden_id
  ORDER BY f.id DESC
");

/* 🔹 ORDENES */
$ordenes = $conn->query("
  SELECT id, orden_codigo, total
  FROM ordenes
  ORDER BY id DESC
");
?>

<?php if (isset($_SESSION['mensaje'])): ?>
  <div class="alert alert-<?= $_SESSION['tipo'] ?> alert-dismissible fade show">
    <?= $_SESSION['mensaje'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php
  unset($_SESSION['mensaje'], $_SESSION['tipo']);
endif;
?>

<div class="card shadow col-md-8 mx-auto">
  <div class="card-header bg-primary text-white">
    📜 Nueva Letra
  </div>

  <div class="card-body">

    <form method="POST">

      <!-- CODIGO LETRA -->
      <div class="mb-3">
        <label class="form-label">Código de la letra</label>
        <input type="text" name="codigo" class="form-control" required>
      </div>

      <!-- FACTURAS (CON ORDEN DE COMPRA) -->
      <div class="mb-3">
        <label class="form-label">Facturas</label>
        <?php while ($f = $facturas->fetch_assoc()): ?>
          <div class="form-check">
            <input class="form-check-input"
                   type="checkbox"
                   name="factura_id[]"
                   value="<?= $f['id'] ?>">
            <label class="form-check-label">
              Factura #<?= $f['id'] ?>
              | Orden <?= $f['orden_codigo'] ?? '—' ?>
              | <?= htmlspecialchars($f['cliente']) ?>
              | <?= $f['fecha'] ?>
              | S/ <?= number_format($f['total'],2) ?>
            </label>
          </div>
        <?php endwhile; ?>
      </div>

      <!-- ORDENES -->
      <div class="mb-3">
        <label class="form-label">Órdenes de compra</label>
        <?php while ($o = $ordenes->fetch_assoc()): ?>
          <div class="form-check">
            <input class="form-check-input"
                   type="checkbox"
                   name="orden_id[]"
                   value="<?= $o['id'] ?>">
            <label class="form-check-label">
              <?= htmlspecialchars($o['orden_codigo']) ?>
              | S/ <?= number_format($o['total'],2) ?>
            </label>
          </div>
        <?php endwhile; ?>
      </div>

      <!-- NUMERO LETRA -->
      <div class="mb-3">
        <label class="form-label">Número de letra</label>
        <input type="number" name="numero" class="form-control" value="1" required>
      </div>

      <!-- MONTO -->
      <div class="mb-3">
        <label class="form-label">Monto</label>
        <input type="number" step="0.01" name="monto" class="form-control" required>
      </div>

      <!-- FECHAS -->
      <div class="mb-3">
        <label class="form-label">Fecha de emisión</label>
        <input type="date" name="fecha_emision" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Fecha de vencimiento</label>
        <input type="date" name="fecha_vencimiento" class="form-control" required>
      </div>

      <button type="submit" name="guardar" class="btn btn-success w-100">
        💾 Crear Letra
      </button>

    </form>

  </div>
</div>

<?php include "../includes/footer.php"; ?>

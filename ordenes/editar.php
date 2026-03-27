<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

$id = intval($_GET['id']);
$o = $conn->query("SELECT * FROM ordenes WHERE id=$id")->fetch_assoc();

if (!$o) {
    die("Orden no encontrada");
}
?>

<div class="card shadow col-md-6 mx-auto">
  <div class="card-header bg-warning">
    ✏️ Editar Orden
  </div>

  <div class="card-body">

    <form method="POST">

      <div class="mb-3">
        <label>Número de Orden</label>
        <input name="orden_codigo"
               class="form-control"
               value="<?= $o['orden_codigo'] ?>" required>
      </div>

      <div class="mb-3">
        <label>Cliente</label>
        <input name="cliente"
               class="form-control"
               value="<?= $o['cliente'] ?>" required>
      </div>

      <div class="mb-3">
        <label>Fecha</label>
        <input type="date" name="fecha"
               class="form-control"
               value="<?= $o['fecha'] ?>" required>
      </div>

      <div class="mb-3">
        <label>Total</label>
        <input type="number" step="0.01" name="total"
               class="form-control"
               value="<?= $o['total'] ?>" required>
      </div>

      <button name="guardar" class="btn btn-primary w-100">
        💾 Guardar Cambios
      </button>
    </form>

    <?php
    if (isset($_POST['guardar'])) {

      $sql = "UPDATE ordenes SET
        orden_codigo='{$_POST['orden_codigo']}',
        cliente='{$_POST['cliente']}',
        fecha='{$_POST['fecha']}',
        total='{$_POST['total']}'
        WHERE id=$id";

      if ($conn->query($sql)) {
        echo "<div class='alert alert-success mt-3'>
                ✅ Orden actualizada
              </div>";
      }
    }
    ?>

  </div>
</div>

<?php include "../includes/footer.php"; ?>

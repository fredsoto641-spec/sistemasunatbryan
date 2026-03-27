<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

if ($_POST) {
  $cliente = $_POST['cliente_id'];
  $monto = $_POST['monto'];
  $serie = $_POST['serie'];
  $correlativo = $_POST['correlativo'];

  $conn->query("INSERT INTO vueltos_clientes (cliente_id, monto, serie, correlativo) 
                VALUES ('$cliente','$monto','$serie','$correlativo')");

  header("Location: index.php");
}
?>

<div class="erp-wrapper">
  <div class="erp-header">
    <span>➕ Nuevo Vuelto</span>
  </div>

  <div class="erp-body">

    <form method="POST">

      <label>Cliente</label>
      <select name="cliente_id" class="form-control mb-3" required>
        <option value="">Seleccione cliente</option>
        <?php
        $clientes = $conn->query("SELECT id, razon_social FROM clientes");
        while($c = $clientes->fetch_assoc()):
        ?>
          <option value="<?= $c['id'] ?>">
            <?= $c['razon_social'] ?>
          </option>
        <?php endwhile; ?>
      </select>

      <!-- NUEVO CAMPO SERIE -->
      <label>Serie</label>
      <input type="text" name="serie" class="form-control mb-3" required placeholder="Ej: V001">

      <!-- NUEVO CAMPO CORRELATIVO -->
      <label>Correlativo</label>
      <input type="number" name="correlativo" class="form-control mb-3" required placeholder="Ej: 000123">

      <label>Monto a devolver</label>
      <input type="number" step="0.01" name="monto" class="form-control mb-3" required>

      <button class="btn btn-success">Guardar</button>
      <a href="index.php" class="btn btn-secondary">Volver</a>

    </form>

  </div>
</div>

<?php include "../includes/footer.php"; ?>

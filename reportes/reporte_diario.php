<?php
require_once "../includes/header.php";
?>

<div class="container-fluid p-4">

  <div class="reporte-card mx-auto">
    <h2>📄 Reporte Diario</h2>

    <form action="pdf_reporte_diario.php" method="GET" target="_blank">
      <label for="fecha">Seleccionar fecha</label>
      <input type="date"
             name="fecha"
             id="fecha"
             value="<?= date('Y-m-d') ?>"
             required>

      <button type="submit">
        Generar PDF (Facturas + Boletas + Vales)
      </button>
    </form>
  </div>

</div>

<?php
require_once "../includes/footer.php";
?>

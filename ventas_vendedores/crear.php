<?php
require_once "../includes/auth.php";
require_once "../config/conexion.php";
require_once "../includes/header.php";

/* ===============================
   OBTENER VENDEDORES
=============================== */
$vendedores = $conn->query("
  SELECT id, CONCAT(nombres,' ',apellidos) AS nombre
  FROM vendedores
  ORDER BY nombres
");
?>

<div class="container-fluid">
  <div class="card shadow">
    <div class="card-header">
      ➕ Registrar ventas semanales
    </div>

    <form method="POST" action="guardar.php" id="formVentas">

      <div class="card-body">

        <!-- VENDEDOR -->
        <div class="mb-3">
          <label class="form-label">Vendedor</label>
          <select name="vendedor_id" class="form-select" required>
            <option value="">Seleccione vendedor</option>
            <?php while ($v = $vendedores->fetch_assoc()): ?>
              <option value="<?= $v['id'] ?>">
                <?= htmlspecialchars($v['nombre']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <hr>

        <!-- DOCUMENTO -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Tipo de documento</label>
            <select name="tipo_documento" class="form-select" required>
              <option value="">-- Seleccionar --</option>
              <option value="VALE">Vale</option>
              <option value="FACTURA">Factura</option>
              <option value="BOLETA">Boleta</option>
              <option value="PROFORMA">Proforma</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Código del documento</label>
            <input type="text"
                   name="codigo_documento"
                   class="form-control"
                   placeholder="Ej: F001-123"
                   required
                   maxlength="50">
          </div>
        </div>

        <hr>

        <!-- VENTAS -->
        <h6>Ventas (lunes a sábado)</h6>

        <table class="table table-bordered" id="tablaVentas">
          <thead class="table-light">
            <tr>
              <th>Fecha</th>
              <th>Monto vendido</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <input type="date" name="fecha[]" class="form-control fecha" required>
              </td>
              <td>
                <input type="number" name="monto[]" class="form-control monto" step="0.01" min="0" required>
              </td>
              <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btnEliminar">✖</button>
              </td>
            </tr>
          </tbody>
        </table>

        <button type="button" class="btn btn-secondary btn-sm" id="btnAgregar">
          ➕ Agregar venta
        </button>

        <hr>

        <!-- TOTALES -->
        <div class="row">
          <div class="col-md-4">
            <label>Total vendido</label>
            <input type="text" id="totalVentas" class="form-control" readonly value="0.00">
          </div>

          <div class="col-md-4">
            <label>Movilidad / Maestro</label>
            <input type="number" name="movilidad" id="movilidad" class="form-control" step="0.01" value="0">
          </div>

          <div class="col-md-4">
            <label>Total neto</label>
            <input type="text" id="totalNeto" class="form-control" readonly value="0.00">
          </div>
        </div>

        <hr>

        <!-- PORCENTAJES -->
        <div class="row text-center">
          <div class="col-md-6">
            <h6>Vendedor (35%)</h6>
            <h4 id="montoVendedor">S/ 0.00</h4>
          </div>
          <div class="col-md-6">
            <h6>Tienda (65%)</h6>
            <h4 id="montoTienda">S/ 0.00</h4>
          </div>
        </div>

      </div>

      <div class="card-footer text-end">
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">
          Guardar ventas
        </button>
      </div>

    </form>
  </div>
</div>

<script>
function recalcular() {
  let total = 0;
  document.querySelectorAll('.monto').forEach(i => total += parseFloat(i.value || 0));

  const movilidad = parseFloat(document.getElementById('movilidad').value || 0);
  const neto = total - movilidad;

  document.getElementById('totalVentas').value = total.toFixed(2);
  document.getElementById('totalNeto').value = neto.toFixed(2);
  document.getElementById('montoVendedor').innerText = 'S/ ' + (neto * 0.35).toFixed(2);
  document.getElementById('montoTienda').innerText = 'S/ ' + (neto * 0.65).toFixed(2);
}

document.getElementById('btnAgregar').addEventListener('click', () => {
  const tbody = document.querySelector('#tablaVentas tbody');
  const row = tbody.rows[0].cloneNode(true);
  row.querySelectorAll('input').forEach(i => i.value = '');
  tbody.appendChild(row);
});

document.addEventListener('click', e => {
  if (e.target.classList.contains('btnEliminar')) {
    const filas = document.querySelectorAll('#tablaVentas tbody tr');
    if (filas.length > 1) {
      e.target.closest('tr').remove();
      recalcular();
    }
  }
});

document.addEventListener('input', e => {
  if (e.target.classList.contains('monto') || e.target.id === 'movilidad') {
    recalcular();
  }
});
</script>

<?php require_once "../includes/footer.php"; ?>

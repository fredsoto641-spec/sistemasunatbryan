<?php
require_once "../includes/auth.php";
require_once "../config/conexion.php";
require_once "../includes/header.php";

/* ===============================
   FECHA Y MODO
=============================== */
$fechaBase = $_GET['fecha'] ?? date('Y-m-d');
$modo = $_GET['modo'] ?? 'semana';

if ($modo === 'dia') {
  $desde = $fechaBase;
  $hasta = $fechaBase;
} else {
  $desde = date('Y-m-d', strtotime('monday this week', strtotime($fechaBase)));
  $hasta = date('Y-m-d', strtotime('saturday this week', strtotime($fechaBase)));
}

/* ===============================
   BÚSQUEDA
=============================== */
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$filtroBusqueda = '';

if ($busqueda !== '') {
  $busquedaSafe = $conn->real_escape_string($busqueda);
  $filtroBusqueda = "
    AND (
      CONCAT(v.nombres,' ',v.apellidos) LIKE '%$busquedaSafe%'
      OR vv.codigo_documento LIKE '%$busquedaSafe%'
      OR vv.tipo_documento LIKE '%$busquedaSafe%'
    )
  ";
}

/* ===============================
   CONSULTA
=============================== */
$sql = "
SELECT
  vv.id,
  vv.vendedor_id,
  vv.fecha,
  vv.total_venta,
  vv.costo_transporte,
  vv.total_neto,
  vv.tipo_documento,
  vv.codigo_documento,
  CONCAT(v.nombres,' ',v.apellidos) AS vendedor
FROM ventas_vendedores vv
INNER JOIN vendedores v ON v.id = vv.vendedor_id
WHERE vv.estado = 1
  AND vv.fecha BETWEEN '$desde' AND '$hasta'
  $filtroBusqueda
ORDER BY vv.fecha, vendedor
";

$ventas = $conn->query($sql);
?>

<div class="container-fluid">

  <!-- SELECTOR -->
  <div class="card shadow mb-3">
    <div class="card-body">
      <form method="GET" class="row align-items-end">

        <div class="col-md-3">
          <label class="form-label">Seleccionar fecha</label>
          <input type="date"
                 name="fecha"
                 class="form-control"
                 value="<?= htmlspecialchars($fechaBase) ?>">
        </div>

        <div class="col-md-2">
          <button name="modo" value="semana" class="btn btn-primary w-100">
            📅 Ver semana
          </button>
        </div>

        <div class="col-md-2">
          <button name="modo" value="dia" class="btn btn-secondary w-100">
            📆 Ver día
          </button>
        </div>

        <div class="col-md-3">
          <label class="form-label">Buscar</label>
          <input type="text"
                 name="q"
                 id="busqueda"
                 class="form-control"
                 placeholder="Vendedor o documento"
                 value="<?= htmlspecialchars($busqueda) ?>">
        </div>

        <!-- BOTÓN CORRECTO -->
        <div class="col-md-2 text-end">
          <a href="crear.php" class="btn btn-success w-100">
            ➕ Registrar venta
          </a>
        </div>

      </form>
    </div>
  </div>

  <!-- MENSAJE BÚSQUEDA -->
  <?php if ($busqueda !== ''): ?>
    <div class="alert alert-info py-1 mb-2">
      🔍 Resultados para: <strong><?= htmlspecialchars($busqueda) ?></strong>
    </div>
  <?php endif; ?>

  <!-- TABLA -->
  <div class="card shadow">
    <div class="card-header">
      📊 Ventas
      <small class="text-light">
        <?= $modo === 'dia'
          ? "Día: $desde"
          : "Semana: $desde al $hasta" ?>
      </small>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">
          <thead class="table-light">
            <tr>
              <th>Documento</th>
              <th>Fecha</th>
              <th>Vendedor</th>
              <th>Total vendido</th>
              <th>Movilidad</th>
              <th>Total neto</th>
              <th>35% Vendedor</th>
              <th>65% Tienda</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>

          <?php if ($ventas && $ventas->num_rows > 0): ?>
            <?php while ($row = $ventas->fetch_assoc()): ?>
              <?php
                $vendedor35 = $row['total_neto'] * 0.35;
                $tienda65   = $row['total_neto'] * 0.65;

                $color = 'secondary';
                if ($row['tipo_documento'] === 'FACTURA') $color = 'success';
                if ($row['tipo_documento'] === 'VALE')    $color = 'warning';
                if ($row['tipo_documento'] === 'BOLETA')  $color = 'info';
              ?>
              <tr>

                <td>
                  <span class="badge bg-<?= $color ?>">
                    <?= htmlspecialchars($row['tipo_documento']) ?>
                  </span><br>
                  <small><?= htmlspecialchars($row['codigo_documento']) ?></small>
                </td>

                <td><?= $row['fecha'] ?></td>

                <td><?= htmlspecialchars($row['vendedor']) ?></td>

                <td>S/ <?= number_format($row['total_venta'], 2) ?></td>

                <td class="text-danger">
                  S/ <?= number_format($row['costo_transporte'], 2) ?>
                </td>

                <td class="fw-bold text-success">
                  S/ <?= number_format($row['total_neto'], 2) ?>
                </td>

                <td>S/ <?= number_format($vendedor35, 2) ?></td>

                <td>S/ <?= number_format($tienda65, 2) ?></td>

                <td>
                  <a href="editar.php?id=<?= (int)$row['id'] ?>"
                     class="btn btn-warning btn-sm"
                     title="Editar">✏️</a>

                  <a href="detalle.php?vendedor_id=<?= (int)$row['vendedor_id'] ?>&desde=<?= urlencode($desde) ?>&hasta=<?= urlencode($hasta) ?>"
                     class="btn btn-info btn-sm"
                     title="Ver detalles">👁️</a>

                  <button class="btn btn-danger btn-sm"
                          onclick="anularVenta(<?= (int)$row['id'] ?>)"
                          title="Anular">🗑️</button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="9">No hay ventas registradas</td>
            </tr>
          <?php endif; ?>

          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<script>
/* ===============================
   LIMPIAR BÚSQUEDA AUTOMÁTICO
=============================== */
const inputBusqueda = document.getElementById('busqueda');
if (inputBusqueda) {
  inputBusqueda.addEventListener('input', function () {
    if (this.value.trim() === '') {
      const url = new URL(window.location.href);
      url.searchParams.delete('q');
      window.location.href = url.toString();
    }
  });
}

function anularVenta(id) {
  Swal.fire({
    title: '¿Anular venta?',
    text: 'La venta no se eliminará, solo se marcará como anulada',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    confirmButtonText: 'Sí, anular',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('anular.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
      })
      .then(r => r.json())
      .then(data => {
        if (data.status === 'success') {
          location.reload();
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      });
    }
  });
}
</script>

<?php require_once "../includes/footer.php"; ?>

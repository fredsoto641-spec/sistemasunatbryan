<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";
require_once "../includes/permisos.php";
require_once "../includes/auth.php";

/* ===============================
   PERMISO
=============================== */
if (!tienePermiso('vendedores_ver')) {
  header("Location: ../perfil.php");
  exit;
}

/* ===============================
   VENDEDORES + VENTAS (DESDE VALES)
=============================== */
$vendedores = $conn->query("
  SELECT 
    v.id,
    v.nombres,
    v.apellidos,
    v.dni,
    v.telefono,

    SUM(CASE WHEN l.tipo_documento = 'Proforma' THEN 1 ELSE 0 END) AS proformas,
    SUM(CASE WHEN l.tipo_documento = 'Vale' THEN 1 ELSE 0 END) AS vales,
    SUM(CASE WHEN l.tipo_documento = 'Factura' THEN 1 ELSE 0 END) AS facturas,

    COUNT(l.id) AS total_documentos,
    IFNULL(SUM(l.monto_total),0) AS total_ventas

  FROM vendedores v
  LEFT JOIN vales l 
    ON l.vendedor_id = v.id
   AND l.estado <> 'Anulado'
  WHERE v.estado = 'Activo'
  GROUP BY v.id
  ORDER BY total_ventas DESC
");

/* ===============================
   CALCULAR MAX Y MIN
=============================== */
$ventas = [];

$vendedores->data_seek(0);
while ($row = $vendedores->fetch_assoc()) {
  $ventas[] = $row['total_ventas'];
}

$max = $ventas ? max($ventas) : 0;
$min = $ventas ? min($ventas) : 0;

/* Volver al inicio */
$vendedores->data_seek(0);
?>

<div class="container-fluid px-4">

  <div class="card shadow-sm mb-4">

    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <span>Vendedores / Ventas</span>
      <?php if (tienePermiso('vendedores_crear')): ?>
        <a href="crear.php" class="btn btn-success btn-sm">Nuevo Vendedor</a>
      <?php endif; ?>
    </div>

    <div class="card-body">

      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Vendedor</th>
              <th class="text-center">Proformas</th>
              <th class="text-center">Vales</th>
              <th class="text-center">Facturas</th>
              <th>Total Docs</th>
              <th>Total Vendido</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
          <?php if ($vendedores->num_rows > 0): ?>
            <?php while ($v = $vendedores->fetch_assoc()): ?>

              <?php
              $rowClass = '';

              if ($v['total_ventas'] == $max && $max > 0) {
                $rowClass = 'table-success';
              }

              if ($v['total_ventas'] == $min && $max > 0) {
                $rowClass = 'table-danger';
              }
              ?>

              <tr class="<?= $rowClass ?>">
                <td><?= $v['id'] ?></td>

                <td>
                  <?= htmlspecialchars($v['apellidos'].' '.$v['nombres']) ?>
                  <?php if ($v['total_ventas'] == $max && $max > 0): ?>
                    <span class="badge bg-success ms-1">TOP</span>
                  <?php endif; ?>
                  <?php if ($v['total_ventas'] == $min && $max > 0): ?>
                    <span class="badge bg-danger ms-1">MENOR</span>
                  <?php endif; ?>
                </td>

                <td class="text-center"><?= $v['proformas'] ?></td>
                <td class="text-center"><?= $v['vales'] ?></td>
                <td class="text-center"><?= $v['facturas'] ?></td>
                <td><?= $v['total_documentos'] ?></td>

                <td>
                  S/ <?= number_format($v['total_ventas'], 2) ?>
                </td>

                <td class="text-center">
                  <a href="ver.php?id=<?= $v['id'] ?>" class="btn btn-primary btn-sm" title="Ver">👁</a>

                  <?php if (tienePermiso('vendedores_crear')): ?>
                    <a href="editar.php?id=<?= $v['id'] ?>" class="btn btn-warning btn-sm" title="Editar">✏️</a>

                    <button onclick="eliminarVendedor(<?= $v['id'] ?>)"
                            class="btn btn-danger btn-sm"
                            title="Eliminar">🗑</button>
                  <?php endif; ?>
                </td>

              </tr>

            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center text-muted">
                No hay vendedores registrados
              </td>
            </tr>
          <?php endif; ?>
          </tbody>

        </table>
      </div>

    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function eliminarVendedor(id) {
  Swal.fire({
    title: '¿Eliminar vendedor?',
    text: 'Esta acción no se puede deshacer',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {

      fetch('eliminar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
      })
      .then(response => response.text())
      .then(data => {
        if (data.trim() === 'ok') {
          Swal.fire('Eliminado', 'Vendedor eliminado correctamente', 'success')
            .then(() => location.reload());
        } else {
          Swal.fire('Error', data, 'error');
        }
      });

    }
  });
}
</script>

<?php include "../includes/footer.php"; ?>

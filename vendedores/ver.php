<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";
require_once "../includes/permisos.php";

if (!tienePermiso('vendedores_ver')) {
  header("Location: ../includes/dashboard.php");
  exit;
}

$id = intval($_GET['id'] ?? 0);

/* ===============================
   VENDEDOR
=============================== */
$vendedor = $conn->query("
  SELECT * FROM vendedores WHERE id = $id
")->fetch_assoc();

if (!$vendedor) {
  echo "<div class='alert alert-danger'>Vendedor no encontrado</div>";
  include "../includes/footer.php";
  exit;
}

/* ===============================
   RESUMEN POR TIPO
=============================== */
$resumen = $conn->query("
  SELECT 
    tipo_documento,
    COUNT(*) cantidad,
    IFNULL(SUM(monto_total),0) total
  FROM vales
  WHERE vendedor_id = $id
  GROUP BY tipo_documento
");

/* ===============================
   DOCUMENTOS DEL VENDEDOR
=============================== */
$docs = $conn->query("
  SELECT 
    tipo_documento,
    serie,
    correlativo,
    fecha_emision,
    monto_total,
    estado
  FROM vales
  WHERE vendedor_id = $id
  ORDER BY fecha_emision DESC
");

/* ===============================
   RANKING GENERAL (DESDE VALES)
=============================== */
$ranking = $conn->query("
  SELECT 
    v.id,
    v.nombres,
    v.apellidos,
    COUNT(l.id) AS total_docs,
    IFNULL(SUM(l.monto_total),0) AS total_ventas
  FROM vendedores v
  LEFT JOIN vales l ON l.vendedor_id = v.id
  GROUP BY v.id
  ORDER BY total_ventas DESC
");
?>

<div class="container-fluid px-4">

  <!-- ================= DATOS VENDEDOR ================= -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
      <?= htmlspecialchars($vendedor['apellidos'].' '.$vendedor['nombres']) ?>
    </div>
    <div class="card-body">
      <p><strong>DNI:</strong> <?= htmlspecialchars($vendedor['dni']) ?></p>
      <p><strong>Teléfono:</strong> <?= htmlspecialchars($vendedor['telefono'] ?? '-') ?></p>
      <p><strong>Dirección:</strong> <?= htmlspecialchars($vendedor['direccion'] ?? '-') ?></p>
    </div>
  </div>

  <!-- ================= RESUMEN POR TIPO ================= -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
      Resumen por tipo de documento
    </div>

    <div class="card-body">
      <div class="row text-center">
        <?php while ($r = $resumen->fetch_assoc()): ?>
          <div class="col-md-4">
            <div class="border rounded p-3">
              <h6><?= $r['tipo_documento'] ?></h6>
              <strong><?= $r['cantidad'] ?> docs</strong><br>
              <span>S/ <?= number_format($r['total'], 2) ?></span>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>

  <!-- ================= LISTADO DOCUMENTOS ================= -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
      Documentos realizados
    </div>

    <div class="card-body">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Documento</th>
            <th>Fecha</th>
            <th>Monto</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($docs->num_rows > 0): ?>
          <?php while ($d = $docs->fetch_assoc()): ?>
            <tr>
              <td><?= $d['tipo_documento'].' '.$d['serie'].'-'.$d['correlativo'] ?></td>
              <td><?= date('d/m/Y', strtotime($d['fecha_emision'])) ?></td>
              <td>S/ <?= number_format($d['monto_total'], 2) ?></td>
              <td>
                <span class="badge bg-<?= $d['estado']=='Pagado'?'success':'warning' ?>">
                  <?= $d['estado'] ?>
                </span>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="text-center text-muted">
              Este vendedor no tiene documentos
            </td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ================= RANKING GENERAL ================= -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
      Ranking general de vendedores
    </div>

    <div class="card-body">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Vendedor</th>
            <th>Total documentos</th>
            <th>Total vendido</th>
          </tr>
        </thead>
        <tbody>
        <?php $pos = 1; while ($r = $ranking->fetch_assoc()): ?>
          <tr class="<?= $r['id'] == $id ? 'table-info' : '' ?>">
            <td><?= $pos++ ?></td>
            <td><?= htmlspecialchars($r['apellidos'].' '.$r['nombres']) ?></td>
            <td><?= $r['total_docs'] ?></td>
            <td>S/ <?= number_format($r['total_ventas'], 2) ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include "../includes/footer.php"; ?>

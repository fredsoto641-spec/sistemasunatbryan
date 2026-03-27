<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

/* TRAER ORDENES */
$ordenes = $conn->query("
  SELECT *
  FROM ordenes
  ORDER BY id DESC
");
?>

<!-- AVISO FLASH -->
<?php if (isset($_SESSION['mensaje'])): ?>
  <div class="alert alert-<?= $_SESSION['tipo'] ?> alert-dismissible fade show">
    <?= $_SESSION['mensaje'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php
  unset($_SESSION['mensaje'], $_SESSION['tipo']);
endif;
?>

<div class="container-fluid px-4">

  <div class="card shadow-sm mb-4">

    <!-- 🔵 HEADER AZUL ERP -->
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <span>Órdenes de Compra</span>
      <a href="crear.php" class="btn btn-success btn-sm">
        Nueva Orden
      </a>
    </div>

    <div class="card-body">

      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
          <!-- 🟦 TABLA CLARA -->
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Nro de orden</th>
              <th>Cliente</th>
              <th>Fecha</th>
              <th>Total</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
          <?php if ($ordenes->num_rows > 0): ?>
            <?php while ($o = $ordenes->fetch_assoc()): ?>
              <tr>
                <td><?= $o['id'] ?></td>
                <td><?= htmlspecialchars($o['orden_codigo']) ?></td>
                <td><?= htmlspecialchars($o['cliente']) ?></td>
                <td><?= $o['fecha'] ?></td>
                <td>S/ <?= number_format($o['total'], 2) ?></td>

                <!-- ACCIONES -->
                <td class="text-center">
                  <a href="ver.php?id=<?= $o['id'] ?>"
                     class="btn btn-primary btn-sm"
                     title="Ver detalle">👁</a>

                  <a href="editar.php?id=<?= $o['id'] ?>"
                     class="btn btn-warning btn-sm"
                     title="Editar">✏️</a>

                  <a href="eliminar.php?id=<?= $o['id'] ?>"
                     class="btn btn-danger btn-sm"
                     onclick="return confirm('¿Eliminar esta orden?')"
                     title="Eliminar">🗑</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted">
                No hay órdenes registradas
              </td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

</div>

<?php include "../includes/footer.php"; ?>

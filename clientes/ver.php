<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../config/conexion.php";
require_once "../includes/header.php";

/* VALIDAR ID */
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
  header("Location: index.php");
  exit;
}

/* CLIENTE */
$cliente = $conn->query("
  SELECT *
  FROM clientes
  WHERE id = $id
")->fetch_assoc();

if (!$cliente) {
  header("Location: index.php");
  exit;
}

/* VALES DEL CLIENTE */
$vales = $conn->query("
  SELECT *
  FROM vales
  WHERE cliente_id = $id
  ORDER BY id DESC
");
?>

<div class="card shadow col-md-8 mx-auto mb-4">
  <div class="card-header bg-info text-white">
    👤 Detalle del Cliente
  </div>

  <div class="card-body">

    <p><strong>DNI / RUC:</strong> <?= htmlspecialchars($cliente['documento']) ?></p>
    <p><strong>Nombre / Razón social:</strong> <?= htmlspecialchars($cliente['nombre']) ?></p>

    <p>
      <strong>Estado:</strong>
      <span class="badge bg-<?= $cliente['estado'] === 'Activo' ? 'success' : 'secondary' ?>">
        <?= htmlspecialchars($cliente['estado']) ?>
      </span>
    </p>

    <a href="index.php" class="btn btn-secondary btn-sm">
      ← Volver
    </a>

  </div>
</div>

<!-- ================= VALES DEL CLIENTE ================= -->
<div class="card shadow col-md-10 mx-auto">
  <div class="card-header bg-dark text-white">
    🧾 Vales del Cliente
  </div>

  <div class="card-body">

    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Tipo</th>
          <th>Serie / Correlativo</th>
          <th>Fecha</th>
          <th>Monto</th>
          <th>Estado</th>
          <th class="text-center">Acciones</th>
        </tr>
      </thead>

      <tbody>
      <?php if ($vales->num_rows > 0): ?>
        <?php while ($v = $vales->fetch_assoc()): ?>
          <tr>
            <td><?= $v['id'] ?></td>
            <td><?= htmlspecialchars($v['tipo_documento']) ?></td>
            <td><?= htmlspecialchars($v['serie']) ?>-<?= htmlspecialchars($v['correlativo']) ?></td>
            <td><?= htmlspecialchars($v['fecha_emision']) ?></td>
            <td>S/ <?= number_format($v['monto_total'], 2) ?></td>
            <td>
              <span class="badge bg-<?=
                $v['estado'] === 'Pagado' ? 'success' :
                ($v['estado'] === 'Pendiente' ? 'warning' : 'secondary')
              ?>">
                <?= htmlspecialchars($v['estado']) ?>
              </span>
            </td>
            <td class="text-center">
              <a href="../vales/ver.php?id=<?= (int)$v['id'] ?>"
                 class="btn btn-primary btn-sm"
                 title="Ver vale">👁</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" class="text-center text-muted">
            Este cliente no tiene vales registrados
          </td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>

  </div>
</div>

<?php include "../includes/footer.php"; ?>

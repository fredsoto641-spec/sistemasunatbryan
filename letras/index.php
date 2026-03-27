<?php
require_once "../config/conexion.php";
require_once "../includes/header.php";

$letras = $conn->query("SELECT * FROM letras ORDER BY id DESC");
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

<div class="container-fluid px-4">

  <div class="card shadow-sm mb-4">

    <!-- 🔵 HEADER AZUL ERP -->
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <span>Letras</span>
      <a href="crear.php" class="btn btn-success btn-sm">
        Nueva Letra
      </a>
    </div>

    <div class="card-body">

      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
          <!-- 🟦 TABLA CLARA -->
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Código</th>
              <th>Facturas</th>
              <th>Órdenes</th>
              <th>N° Letras</th>
              <th>Monto total</th>
              <th>Fecha emisión</th>
              <th>Vencimiento</th>
              <th>Estado</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
          <?php while ($l = $letras->fetch_assoc()): ?>
            <tr>
              <td><?= $l['id'] ?></td>
              <td><?= htmlspecialchars($l['codigo'] ?: '—') ?></td>

              <!-- FACTURAS -->
              <td>
                <?php
                $fs = $conn->query("
                  SELECT 
                    f.id AS factura_id,
                    f.total AS total_factura,
                    o.orden_codigo
                  FROM letras_facturas lf
                  JOIN facturas f ON f.id = lf.factura_id
                  LEFT JOIN ordenes o ON o.id = f.orden_id
                  WHERE lf.letra_id = {$l['id']}
                ");
                ?>

                <?php if ($fs->num_rows > 0): ?>
                  <ul class="mb-0">
                    <?php while ($f = $fs->fetch_assoc()): ?>
                      <li>
                        <strong>Nro de factura:</strong>
                        <?= 'FAC-' . str_pad($f['factura_id'], 3, '0', STR_PAD_LEFT) ?>
                        |
                        <strong>Nro de orden:</strong>
                        <?= htmlspecialchars($f['orden_codigo'] ?? '—') ?>
                        |
                        <strong>S/</strong> <?= number_format($f['total_factura'], 2) ?>
                      </li>
                    <?php endwhile; ?>
                  </ul>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>

              <!-- ÓRDENES -->
              <td>
                <?php
                $os = $conn->query("
                  SELECT o.orden_codigo, o.total
                  FROM letras_ordenes lo
                  JOIN ordenes o ON o.id = lo.orden_id
                  WHERE lo.letra_id = {$l['id']}
                ");
                ?>

                <?php if ($os->num_rows > 0): ?>
                  <ul class="mb-0">
                    <?php while ($o = $os->fetch_assoc()): ?>
                      <li>
                        <strong>Nro de orden:</strong>
                        <?= htmlspecialchars($o['orden_codigo']) ?>
                        |
                        <strong>S/</strong> <?= number_format($o['total'], 2) ?>
                      </li>
                    <?php endwhile; ?>
                  </ul>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>

              <td><?= $l['numero'] ?></td>
              <td>S/ <?= number_format($l['monto'], 2) ?></td>

              <td><?= date('d/m/Y', strtotime($l['fecha_emision'])) ?></td>
              <td><?= date('d/m/Y', strtotime($l['fecha_vencimiento'])) ?></td>

              <td>
                <span class="badge bg-<?= $l['estado'] === 'Pendiente' ? 'warning' : 'success' ?>">
                  <?= $l['estado'] ?>
                </span>
              </td>

              <!-- ACCIONES -->
              <td class="text-center">
                <a href="ver.php?id=<?= $l['id'] ?>"
                   class="btn btn-primary btn-sm"
                   title="Ver detalle">👁</a>

                <a href="../pdf/letra.php?id=<?= $l['id'] ?>"
                   target="_blank"
                   class="btn btn-info btn-sm"
                   title="Imprimir">🖨</a>

                <?php if ($l['estado'] !== 'Pagado'): ?>
                  <a href="pagar.php?id=<?= $l['id'] ?>"
                     class="btn btn-success btn-sm"
                     title="Pagar">💰</a>
                <?php endif; ?>

                <a href="editar.php?id=<?= $l['id'] ?>"
                   class="btn btn-warning btn-sm"
                   title="Editar">✏️</a>

                <a href="eliminar.php?id=<?= $l['id'] ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('¿Eliminar esta letra?')"
                   title="Eliminar">🗑</a>
              </td>

            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

</div>

<?php include "../includes/footer.php"; ?>

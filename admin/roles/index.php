<?php
require_once "../../includes/auth.php";
require_once "../../config/conexion.php";
require_once "../../includes/header.php";

/* ===============================
   PERMISO
=============================== */
if (!tienePermiso('roles')) {
  header("Location: ../../includes/dashboard.php");
  exit;
}

/* ===============================
   ROLES
=============================== */
$roles = $conn->query("
  SELECT
    r.id,
    r.nombre,
    COUNT(u.id) AS total_usuarios
  FROM roles r
  LEFT JOIN usuarios u ON u.rol_id = r.id
  GROUP BY r.id
  ORDER BY r.nombre
");
?>

<div class="container-fluid">

  <div class="card shadow">

    <div class="card-header d-flex justify-content-between align-items-center">
      <span>🧑‍💼 Gestión de roles</span>

      <a href="crear.php" class="btn btn-success btn-sm">
        ➕ Nuevo rol
      </a>
    </div>

    <div class="card-body">

      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">

          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Rol</th>
              <th>Usuarios asignados</th>
              <th>Acciones</th>
            </tr>
          </thead>

          <tbody>
          <?php if ($roles && $roles->num_rows > 0): ?>
            <?php while ($r = $roles->fetch_assoc()): ?>
              <tr>
                <td><?= $r['id'] ?></td>

                <td>
                  <strong><?= htmlspecialchars($r['nombre']) ?></strong>
                </td>

                <td>
                  <?= $r['total_usuarios'] ?>
                </td>

                <td>
                  <!-- EDITAR -->
                  <a href="editar.php?id=<?= $r['id'] ?>"
                     class="btn btn-warning btn-sm"
                     title="Editar rol">
                     ✏️
                  </a>

                  <!-- PERMISOS -->
                  <a href="permisos.php?rol_id=<?= $r['id'] ?>"
                     class="btn btn-primary btn-sm"
                     title="Asignar permisos">
                     🔐
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4">
                No hay roles registrados
              </td>
            </tr>
          <?php endif; ?>
          </tbody>

        </table>
      </div>

    </div>
  </div>

</div>

<?php require_once "../../includes/footer.php"; ?>

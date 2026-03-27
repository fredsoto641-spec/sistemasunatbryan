<?php
require_once "../../includes/auth.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once "../../includes/header.php";

/* ===============================
   VALIDAR ROL
=============================== */
$rol_id = $_GET['rol_id'] ?? null;
if (!$rol_id || !is_numeric($rol_id)) {
  die("Rol inválido");
}

/* ===============================
   OBTENER ROL
=============================== */
$rol = $conn->query("
  SELECT id, nombre
  FROM roles
  WHERE id = $rol_id
")->fetch_assoc();

if (!$rol) {
  die("Rol no encontrado");
}

/* ===============================
   PERMISOS + ASIGNADOS
=============================== */
$permisos = $conn->query("
  SELECT
    p.id,
    p.nombre,
    IF(rp.permiso_id IS NULL, 0, 1) AS asignado
  FROM permisos p
  LEFT JOIN rol_permisos rp
    ON rp.permiso_id = p.id
   AND rp.rol_id = $rol_id
  ORDER BY p.nombre
");
?>

<div class="container-fluid">

  <div class="card shadow">

    <div class="card-header d-flex justify-content-between align-items-center">
      <span>
        🔐 Permisos del rol:
        <strong><?= htmlspecialchars($rol['nombre']) ?></strong>
      </span>

      <a href="index.php" class="btn btn-secondary btn-sm">
        ⬅ Volver
      </a>
    </div>

    <form method="POST" action="guardar_permisos.php">

      <input type="hidden" name="rol_id" value="<?= $rol_id ?>">

      <div class="card-body">

        <div class="row">

          <?php if ($permisos && $permisos->num_rows > 0): ?>
            <?php while ($p = $permisos->fetch_assoc()): ?>
              <div class="col-md-4 mb-2">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    name="permisos[]"
                    value="<?= $p['id'] ?>"
                    <?= $p['asignado'] ? 'checked' : '' ?>
                  >
                  <label class="form-check-label">
                    <?= htmlspecialchars($p['nombre']) ?>
                  </label>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="col-12 text-center text-muted">
              No hay permisos registrados
            </div>
          <?php endif; ?>

        </div>

      </div>

      <div class="card-footer text-end">
        <button class="btn btn-primary">
          💾 Guardar permisos
        </button>
      </div>

    </form>

  </div>

</div>

<?php require_once "../../includes/footer.php"; ?>

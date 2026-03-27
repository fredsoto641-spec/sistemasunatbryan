<?php
require_once "../includes/auth.php";
require_once "../config/conexion.php";
require_once "../includes/header.php";

$transportistas = $conn->query("SELECT * FROM transportistas ORDER BY id DESC");
?>

<div class="container-fluid">
  <div class="card shadow">
    <div class="card-header d-flex justify-content-between">
      <h5 class="mb-0">🚚 Transportistas</h5>
      <a href="crear.php" class="btn btn-sm btn-primary">Nuevo</a>
    </div>

    <div class="card-body">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>Empresa</th>
            <th>Conductor</th>
            <th>DNI</th>
            <th>Placa</th>
            <th>Licencia</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($t = $transportistas->fetch_assoc()): ?>
          <tr>
            <td><?= $t['empresa'] ?></td>
            <td><?= $t['conductor'] ?></td>
            <td><?= $t['dni'] ?></td>
            <td><?= $t['placa'] ?></td>
            <td><?= $t['licencia'] ?></td>
            <td>
              <a href="editar.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
              <button class="btn btn-sm btn-danger btnEliminar" data-id="<?= $t['id'] ?>">Eliminar</button>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once "../includes/footer.php"; ?>

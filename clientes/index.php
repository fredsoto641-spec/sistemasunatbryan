<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../config/conexion.php";
require_once "../includes/auth.php";

/* ===============================
   TRAER CLIENTES
=============================== */
$clientes = $conn->query("
  SELECT 
    id,
    documento,
    nombre,
    telefono,
    direccion,
    estado,
    fecha_creacion
  FROM clientes
  ORDER BY id DESC
");
?>

<?php require_once "../includes/header.php"; ?>

<div class="container-fluid px-4">

  <div class="card shadow-sm mb-4">

    <!-- HEADER ERP -->
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <span>Clientes</span>

      <div class="d-flex gap-2">

        <!-- IMPORTAR CSV -->
        <form action="importar_csv.php"
              method="POST"
              enctype="multipart/form-data"
              class="d-inline">
          <input type="file"
                 name="archivo"
                 accept=".csv"
                 required
                 class="form-control form-control-sm d-inline"
                 style="width:220px">
          <button class="btn btn-light btn-sm">
            📥 Importar CSV
          </button>
        </form>

        <a href="crear.php" class="btn btn-success btn-sm">
          ➕ Nuevo Cliente
        </a>
      </div>
    </div>

    <div class="card-body">

      <!-- BUSCADOR -->
      <div class="row mb-3">
        <div class="col-md-4">
          <input type="text"
                 id="buscadorClientes"
                 class="form-control"
                 placeholder="🔍 Buscar por DNI/RUC o Nombre">
        </div>
      </div>

      <!-- MENSAJES -->
      <?php if (isset($_GET['importados'])): ?>
        <div class="alert alert-success">
          ✅ <?= (int)$_GET['importados'] ?> clientes importados correctamente
          <?php if (isset($_GET['duplicados'])): ?>
            <br>⚠️ <?= (int)$_GET['duplicados'] ?> duplicados ignorados
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0" id="tablaClientes">

          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>DNI / RUC</th>
              <th>Nombre / Razón social</th>
              <th>Teléfono</th>
              <th>Dirección</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
          <?php if ($clientes && $clientes->num_rows > 0): ?>
            <?php while ($c = $clientes->fetch_assoc()): ?>
              <tr>
                <td><?= $c['id'] ?></td>
                <td><?= htmlspecialchars($c['documento']) ?></td>
                <td><?= htmlspecialchars($c['nombre']) ?></td>
                <td><?= htmlspecialchars($c['telefono'] ?: '-') ?></td>
                <td><?= htmlspecialchars($c['direccion'] ?: '-') ?></td>
                <td>
                  <span class="badge bg-<?= $c['estado'] === 'Activo' ? 'success' : 'secondary' ?>">
                    <?= htmlspecialchars($c['estado']) ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($c['fecha_creacion']) ?></td>
                <td class="text-center">
                  <a href="ver.php?id=<?= (int)$c['id'] ?>" class="btn btn-primary btn-sm">👁</a>
                  <a href="editar.php?id=<?= (int)$c['id'] ?>" class="btn btn-warning btn-sm">✏️</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center text-muted">
                No hay clientes registrados
              </td>
            </tr>
          <?php endif; ?>
          </tbody>

        </table>

        <!-- PAGINACIÓN -->
        <nav>
          <ul class="pagination justify-content-end mt-3" id="paginacionClientes"></ul>
        </nav>

      </div>

    </div>
  </div>

</div>

<script>
const filasOriginales = Array.from(document.querySelectorAll("#tablaClientes tbody tr"));
const filasPorPagina = 50;
let paginaActual = 1;
let filasFiltradas = [...filasOriginales];

const buscador = document.getElementById("buscadorClientes");
const paginacion = document.getElementById("paginacionClientes");

const maxBotones = 10;

function mostrarPagina(pagina) {
  paginaActual = pagina;
  let inicio = (pagina - 1) * filasPorPagina;
  let fin = inicio + filasPorPagina;

  filasOriginales.forEach(tr => tr.style.display = "none");

  filasFiltradas.forEach((fila, i) => {
    fila.style.display = (i >= inicio && i < fin) ? "" : "none";
  });

  renderPaginacion();
}

function renderPaginacion() {
  paginacion.innerHTML = "";
  const totalPaginas = Math.ceil(filasFiltradas.length / filasPorPagina);

  let inicio = Math.max(1, paginaActual - Math.floor(maxBotones / 2));
  let fin = Math.min(totalPaginas, inicio + maxBotones - 1);

  if (inicio > 1) {
    paginacion.innerHTML += `
      <li class="page-item"><a class="page-link" href="#" onclick="mostrarPagina(1)">1</a></li>
      <li class="page-item disabled"><span class="page-link">...</span></li>
    `;
  }

  for (let i = inicio; i <= fin; i++) {
    paginacion.innerHTML += `
      <li class="page-item ${i===paginaActual?'active':''}">
        <a class="page-link" href="#" onclick="mostrarPagina(${i})">${i}</a>
      </li>
    `;
  }

  if (fin < totalPaginas) {
    paginacion.innerHTML += `
      <li class="page-item disabled"><span class="page-link">...</span></li>
      <li class="page-item"><a class="page-link" href="#" onclick="mostrarPagina(${totalPaginas})">${totalPaginas}</a></li>
    `;
  }
}

buscador.addEventListener("keyup", function() {
  const filtro = this.value.toLowerCase();

  filasFiltradas = filasOriginales.filter(tr => {
    let doc = tr.children[1].innerText.toLowerCase();
    let nombre = tr.children[2].innerText.toLowerCase();
    return doc.includes(filtro) || nombre.includes(filtro);
  });

  mostrarPagina(1);
});

// iniciar en página 1
mostrarPagina(1);
</script>

<?php require_once "../includes/footer.php"; ?>

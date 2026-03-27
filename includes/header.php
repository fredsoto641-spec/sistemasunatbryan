<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/permisos.php";
require_once __DIR__ . "/../config/conexion.php";

/* ===============================
   MENÚ ACTIVO
=============================== */
$uri = $_SERVER['REQUEST_URI'];
function isActive($needle, $uri) {
  return strpos($uri, $needle) !== false ? 'active' : '';
}

/* ===============================
   TÍTULO
=============================== */
$titulo = "Panel";
if (strpos($uri, '/facturas') !== false) $titulo = "Facturas";
elseif (strpos($uri, '/vales') !== false) $titulo = "Vales";
elseif (strpos($uri, '/guias') !== false) $titulo = "Guías de Remisión";
elseif (strpos($uri, '/clientes') !== false) $titulo = "Clientes";
elseif (strpos($uri, '/ventas_vendedores') !== false) $titulo = "Ventas Vendedores";
elseif (strpos($uri, '/transportistas') !== false) $titulo = "Transportistas";
elseif (strpos($uri, '/vendedores') !== false) $titulo = "Vendedores";
elseif (strpos($uri, '/roles') !== false) $titulo = "Roles";
elseif (strpos($uri, '/reportes') !== false) $titulo = "Reportes";
elseif (strpos($uri, '/vueltos_clientes') !== false) $titulo = "Vueltos de Clientes";


/* ===============================
   FOTO DE USUARIO SEGURA
=============================== */
$foto = $_SESSION['usuario_foto'] ?? 'default.png';
$rutaFisica = __DIR__ . "/../assets/img/users/" . $foto;
if (empty($foto) || !file_exists($rutaFisica)) {
  $foto = 'default.png';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= $titulo ?> | Control Financiero</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Tipografía ERP -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/erp.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css?v=<?= time() ?>">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<div class="sidebar">

  <!-- MARCA -->
  <div class="brand">Cerámicos Importados</div>

  <!-- USUARIO -->
  <div class="sidebar-user">
    <div class="avatar">
      <img src="<?= BASE_URL ?>assets/img/users/<?= htmlspecialchars($foto) ?>?v=<?= time() ?>">
    </div>
    <div class="user-info">
      <strong><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></strong>

      <span class="role">Administrador</span>
    </div>
  </div>

  <!-- MENÚ -->
  <div class="sidebar-menu">

    <?php if (tienePermiso('dashboard')): ?>
      <a href="<?= BASE_URL ?>includes/dashboard.php"
         class="<?= isActive('/dashboard.php', $uri) ?>">
        <i class="fa-solid fa-gauge"></i> Dashboard
      </a>
    <?php endif; ?>
    
    <?php if (tienePermiso('reportes')): ?>
      <a href="<?= BASE_URL ?>reportes/reporte_diario.php"
         class="<?= isActive('/reportes/', $uri) ?>">
        <i class="fa-solid fa-file-pdf"></i> Reportes
      </a>
    <?php endif; ?>

    <?php if (tienePermiso('boletas')): ?>
      <a href="<?= BASE_URL ?>boletas/index.php"
         class="<?= isActive('/boletas/', $uri) ?>">
        <i class="fa-solid fa-receipt"></i> Boletas
      </a>
    <?php endif; ?>

   <?php if (tienePermiso('guias')): ?>
  <a href="<?= BASE_URL ?>guias/index.php"
     class="<?= isActive('/guias/', $uri) ?>">
    <i class="fa-solid fa-truck-ramp-box"></i> Guías
  </a>
<?php endif; ?>


    <?php if (tienePermiso('facturas')): ?>
      <a href="<?= BASE_URL ?>facturas/index.php"
         class="<?= isActive('/facturas/', $uri) ?>">
        <i class="fa-solid fa-file-invoice"></i> Facturas
      </a>
    <?php endif; ?>

    
    <?php if (tienePermiso('ventas_vendedores')): ?>
      <a href="<?= BASE_URL ?>ventas_vendedores/index.php"
         class="<?= isActive('/ventas_vendedores/', $uri) ?>">
        <i class="fa-solid fa-chart-line"></i> Ventas Vendedores
      </a>
    <?php endif; ?>

    <?php if (tienePermiso('transportistas')): ?>
      <a href="<?= BASE_URL ?>transportistas/index.php"
         class="<?= isActive('/transportistas/', $uri) ?>">
        <i class="fa-solid fa-truck"></i> Transportistas
      </a>
    <?php endif; ?>

    <?php if (tienePermiso('productos_ver')): ?>
      <a href="<?= BASE_URL ?>productos/index.php"
         class="<?= isActive('/productos/', $uri) ?>">
        <i class="fa-solid fa-cubes"></i> Productos
      </a>
    <?php endif; ?>

    <?php if (tienePermiso('vales')): ?>
      <a href="<?= BASE_URL ?>vales/index.php"
         class="<?= isActive('/vales/', $uri) ?>">
        <i class="fa-solid fa-receipt"></i> Vales
      </a>
    <?php endif; ?>

    <!-- NUEVA SECCIÓN VUELTOS -->
    <?php if (tienePermiso('vueltos_clientes')): ?>
      <a href="<?= BASE_URL ?>vueltos_clientes/index.php"
         class="<?= isActive('/vueltos_clientes/', $uri) ?>">
        <i class="fa-solid fa-coins"></i> Vueltos
      </a>
    <?php endif; ?>

    <?php if (tienePermiso('clientes')): ?>
      <a href="<?= BASE_URL ?>clientes/index.php"
         class="<?= isActive('/clientes/', $uri) ?>">
        <i class="fa-solid fa-people-group"></i> Clientes
      </a>
    <?php endif; ?>

    <?php if (tienePermiso('vendedores')): ?>
      <a href="<?= BASE_URL ?>vendedores/index.php"
         class="<?= isActive('/vendedores/', $uri) ?>">
        <i class="fa-solid fa-id-badge"></i> Vendedores
      </a>
    <?php endif; ?>

    <?php if (tienePermiso('roles')): ?>
      <a href="<?= BASE_URL ?>admin/roles/index.php"
         class="<?= isActive('/roles/', $uri) ?>">
        <i class="fa-solid fa-user-shield"></i> Roles
      </a>
    <?php endif; ?>

    <a href="<?= BASE_URL ?>perfil.php"
       class="<?= isActive('/perfil.php', $uri) ?>">
      <i class="fa-solid fa-user"></i> Mi perfil
    </a>

  </div>

  <!-- SALIR -->
  <a href="<?= BASE_URL ?>logout.php" class="logout">
    <i class="fa-solid fa-right-from-bracket"></i> Salir
  </a>

</div>

<div class="main">

<!-- TOP BAR -->
<div class="d-flex justify-content-between align-items-center p-2 border-bottom bg-white">

  <h5 class="mb-0"><?= $titulo ?></h5>

  <div class="d-flex align-items-center gap-2">

    <input type="text" id="doc_header"
           class="form-control form-control-sm"
           placeholder="DNI o RUC"
           maxlength="11"
           style="width:160px;">

    <button class="btn btn-outline-success btn-sm" onclick="consultarDocumento()">
      <i class="fa-solid fa-magnifying-glass"></i>
    </button>

  </div>
</div>

<script>
function consultarDocumento(){

    let doc = document.getElementById("doc_header").value.trim();

    if(doc.length !== 8 && doc.length !== 11){
        Swal.fire("Error","Ingrese DNI (8) o RUC (11)","error");
        return;
    }

    Swal.fire({
        title: "Consultando...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    // ================= DNI =================
    if(doc.length === 8){

        fetch("<?= BASE_URL ?>api/consulta_dni.php", {
            method: "POST",
            headers: {"Content-Type":"application/x-www-form-urlencoded"},
            body: "dni=" + doc
        })
        .then(res => res.json())
        .then(dataDni => {

            Swal.close();

            console.log("DNI:", dataDni);

            if(!dataDni.success){
                Swal.fire("Error","DNI no encontrado","error");
                return;
            }

            let nombres = dataDni.data.nombres;
            let apeP = dataDni.data.apellido_paterno;
            let apeM = dataDni.data.apellido_materno;
            let nombreCompleto = dataDni.data.nombre_completo;
            let verificacion = dataDni.data.codigo_verificacion;

            Swal.fire({
                title: "Datos RENIEC",
                html: `
                  <b>DNI:</b> ${doc}<br>
                  <b>Nombres:</b> ${nombres}<br>
                  <b>Apellido Paterno:</b> ${apeP}<br>
                  <b>Apellido Materno:</b> ${apeM}<br>
                  <b>Nombre Completo:</b> ${nombreCompleto}<br>
                  <b>Código Verificación:</b> ${verificacion}
                `,
                icon: "success"
            });

        })
        .catch(()=>{
            Swal.fire("Error","No se pudo conectar con RENIEC","error");
        });

        return;
    }

    // ================= RUC =================
    if(doc.length === 11){

        fetch("<?= BASE_URL ?>api/consulta_ruc.php", {
            method: "POST",
            headers: {"Content-Type":"application/x-www-form-urlencoded"},
            body: "ruc=" + doc
        })
        .then(res => res.json())
        .then(dataSunat => {

            Swal.close();

            console.log("SUNAT:", dataSunat);

            if(!dataSunat.success){
                Swal.fire("Error", dataSunat.message ?? "RUC no encontrado", "error");
                return;
            }

            let razon = dataSunat.data.nombre_o_razon_social || "No disponible";

            let direccion = dataSunat.data.direccion_completa 
                          || dataSunat.data.direccion 
                          || "No disponible";

            let estado = dataSunat.data.estado || "No disponible";
            let condicion = dataSunat.data.condicion || "No disponible";

            Swal.fire({
                title: "Datos SUNAT",
                html: `
                  <b>RUC:</b> ${doc}<br>
                  <b>Razón Social:</b> ${razon}<br>
                  <b>Dirección Fiscal:</b> ${direccion}<br>
                  <b>Estado:</b> ${estado}<br>
                  <b>Condición:</b> ${condicion}
                `,
                icon: "success"
            });

        })
        .catch(()=>{
            Swal.fire("Error","No se pudo conectar con SUNAT","error");
        });

    }
}
</script>


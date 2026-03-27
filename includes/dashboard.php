<?php
require_once "../config/conexion.php";
require_once "header.php";
require_once "auth.php";

if (!tienePermiso('dashboard')) {
  header("Location: /sistema-control-letras/perfil.php");
  exit;
}
?>

<div class="erp-wrapper">

  <!-- HEADER -->
  <div class="erp-header">
    PANEL PRINCIPAL LUNA PIZARRO
  </div>

  <div class="erp-body">

    <div class="dashboard-container">

      <div class="dashboard-grid">

        <?php if (tienePermiso('boletas')): ?>
        <div class="dashboard-card">
          <i class="fa-solid fa-receipt"></i>
          <strong>Boletas</strong>
          <small>Gestión de boletas</small>
          <a href="../boletas/index.php"></a>
        </div>
        <?php endif; ?>

        <?php if (tienePermiso('facturas')): ?>
        <div class="dashboard-card">
          <i class="fa-solid fa-file-invoice"></i>
          <strong>Facturas</strong>
          <small>Gestión de facturas</small>
          <a href="../facturas/index.php"></a>
        </div>
        <?php endif; ?>

       

        
        <?php if (tienePermiso('ventas_vendedores')): ?>
        <div class="dashboard-card">
          <i class="fa-solid fa-chart-line"></i>
          <strong>Ventas</strong>
          <small>Ventas vendedores</small>
          <a href="../ventas_vendedores/index.php"></a>
        </div>
        <?php endif; ?>

        <?php if (tienePermiso('transportistas')): ?>
        <div class="dashboard-card">
          <i class="fa-solid fa-truck"></i>
          <strong>Transportistas</strong>
          <small>Gestión de transporte</small>
          <a href="../transportistas/index.php"></a>
        </div>
        <?php endif; ?>

        <?php if (tienePermiso('productos_ver')): ?>
        <div class="dashboard-card">
          <i class="fa-solid fa-box"></i>
          <strong>Productos</strong>
          <small>Inventario</small>
          <a href="../productos/index.php"></a>
        </div>
        <?php endif; ?>

        <?php if (tienePermiso('vales')): ?>
        <div class="dashboard-card">
          <i class="fa-solid fa-ticket"></i>
          <strong>Vales</strong>
          <small>Gestión de vales</small>
          <a href="../vales/index.php"></a>
        </div>
        <?php endif; ?>

        <?php if (tienePermiso('clientes')): ?>
        <div class="dashboard-card">
          <i class="fa-solid fa-users"></i>
          <strong>Clientes</strong>
          <small>Clientes registrados</small>
          <a href="../clientes/index.php"></a>
        </div>
        <?php endif; ?>

        <?php if (tienePermiso('vendedores')): ?>
        <div class="dashboard-card">
          <i class="fa-solid fa-id-badge"></i>
          <strong>Vendedores</strong>
          <small>Personal de ventas</small>
          <a href="../vendedores/index.php"></a>
        </div>
        <?php endif; ?>

        <?php if (tienePermiso('roles')): ?>
        <div class="dashboard-card">
          <i class="fa-solid fa-user-shield"></i>
          <strong>Roles</strong>
          <small>Permisos del sistema</small>
          <a href="../admin/roles/index.php"></a>
        </div>
        <?php endif; ?>

        <?php if (tienePermiso('reportes')): ?>
        <div class="dashboard-card">
          <i class="fa-solid fa-file-pdf"></i>
          <strong>Reportes</strong>
          <small>Reportes PDF</small>
          <a href="../reportes/reporte_diario.php"></a>
        </div>
        <?php endif; ?>

      </div>

    </div>

  </div>
</div>

<?php include "footer.php"; ?>

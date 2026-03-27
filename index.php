<?php
header("Location: includes/dashboard.php");
exit;

require_once "config/conexion.php";
require_once "includes/header.php";
?>

<h3 class="mb-4">Panel principal</h3>

<div class="row g-4">

  <!-- VER ORDENES -->
  <div class="col-md-4">
    <div class="card shadow text-center h-100">
      <div class="card-body">
        <h5>📦 Órdenes</h5>
        <p>Gestionar órdenes de compra</p>
        <a href="ordenes/index.php" class="btn btn-success btn-sm">
          Ver Órdenes
        </a>
      </div>
    </div>
  </div>

  <!-- NUEVA ORDEN -->
  <div class="col-md-4">
    <div class="card shadow text-center h-100">
      <div class="card-body">
        <h5>➕ Nueva Orden</h5>
        <p>Registrar una orden</p>
        <a href="ordenes/crear.php" class="btn btn-primary btn-sm">
          Nueva Orden
        </a>
      </div>
    </div>
  </div>

  <!-- FACTURAS -->
  <div class="col-md-4">
    <div class="card shadow text-center h-100">
      <div class="card-body">
        <h5>🧾 Facturas</h5>
        <p>Gestionar facturas</p>
        <a href="facturas/index.php" class="btn btn-success btn-sm">
          Ver Facturas
        </a>
      </div>
    </div>
  </div>
  <!-- NUEVA FACTURA -->
<div class="col-md-4">
  <div class="card shadow text-center h-100">
    <div class="card-body">
      <h5>➕ Nueva Factura</h5>
      <p>Crear factura manual</p>
      <a href="facturas/crear.php" class="btn btn-success btn-sm">
        Nueva Factura
      </a>
    </div>
  </div>
</div>
<!-- NUEVO CLIENTE -->
<div class="col-md-4">
  <div class="card shadow text-center h-100">
    <div class="card-body">
      <h5>➕ Nuevo cliente</h5>
      <p>Crear cliente manual</p>
      <a href="clientes/crear.php" class="btn btn-warning btn-sm">
        Nuevo cliente
      </a>
    </div>
  </div>
</div>
<!-- NUEVA LETRA -->
<div class="col-md-4">
  <div class="card shadow text-center h-100">
    <div class="card-body">
      <h5>➕ Nueva Letra</h5>
      <p>Crear letra manual</p>
      <a href="letras/crear.php" class="btn btn-warning btn-sm">
        Nueva Letra
      </a>
    </div>
  </div>
</div>
  <!-- LETRAS -->
  <div class="col-md-4">
    <div class="card shadow text-center h-100">
      <div class="card-body">
        <h5>📜 Letras</h5>
        <p>Gestionar letras</p>
        <a href="letras/index.php" class="btn btn-success btn-sm">
          Ver Letras
        </a>
      </div>
    </div>
  </div>

</div>


<?php include "includes/footer.php"; ?>

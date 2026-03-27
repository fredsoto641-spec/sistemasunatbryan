<?php
// includes/permisos.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('tienePermiso')) {

    function tienePermiso($permiso) {

        // 🔐 ADMIN TIENE ACCESO TOTAL
        if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1) {
            return true;
        }

        // 🔒 USUARIOS NORMALES: VALIDAR PERMISOS
        return isset($_SESSION['permisos']) &&
               is_array($_SESSION['permisos']) &&
               in_array($permiso, $_SESSION['permisos']);
    }
}

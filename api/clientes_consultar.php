<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta DNI - RENIEC</title>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap opcional -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white p-5">

<div class="container col-md-6">

    <h3 class="mb-4">Consulta DNI - RENIEC</h3>

    <label>DNI:</label>
    <input type="text" id="dni" class="form-control" maxlength="8" placeholder="Ingrese DNI">

    <button onclick="consultarDni()" class="btn btn-primary mt-3 w-100">
        Consultar RENIEC
    </button>

    <hr>

    <label>RUC:</label>
    <input type="text" id="ruc" class="form-control">

    <label class="mt-2">Nombres:</label>
    <input type="text" id="nombres" class="form-control">

    <label class="mt-2">Apellidos:</label>
    <input type="text" id="apellidos" class="form-control">

</div>

<script>
function consultarDni(){
    let dni = document.getElementById("dni").value;

    if(dni.length !== 8 || isNaN(dni)){
        Swal.fire("Error","Ingrese un DNI válido","error");
        return;
    }

    Swal.fire({
        title: "Consultando RENIEC...",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch("api/consulta_dni_ruc.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "dni=" + dni
    })
    .then(res => res.json())
    .then(data => {
        Swal.close();

        console.log(data);

        if(data.success){

            document.getElementById("ruc").value = data.data.ruc ?? "";
            document.getElementById("nombres").value = data.data.nombres ?? "";
            document.getElementById("apellidos").value =
                (data.data.apellido_paterno ?? "") + " " + (data.data.apellido_materno ?? "");

            Swal.fire("Éxito","Datos encontrados","success");

        } else {
            Swal.fire("Error","DNI no encontrado","error");
        }
    })
    .catch(error=>{
        Swal.fire("Error","No se pudo conectar con RENIEC","error");
        console.log(error);
    });
}
</script>

</body>
</html>

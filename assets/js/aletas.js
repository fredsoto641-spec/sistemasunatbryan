function alertaExito(titulo, texto, redirect = null) {
  Swal.fire({
    icon: 'success',
    title: titulo,
    text: texto,
    confirmButtonColor: '#3085d6'
  }).then(() => {
    if (redirect) window.location = redirect;
  });
}

function alertaError(titulo, texto) {
  Swal.fire({
    icon: 'error',
    title: titulo,
    text: texto,
    confirmButtonColor: '#d33'
  });
}

function alertaConfirmar(texto, callback) {
  Swal.fire({
    title: '¿Estás seguro?',
    text: texto,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Sí, confirmar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) callback();
  });
}

document.addEventListener("DOMContentLoaded", () => {

    // --------------------
    // APROBAR SERVICIO
    // --------------------
    document.querySelectorAll(".btn-approve").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();

            let id = this.dataset.id;

            Swal.fire({
                title: '<h5 class="m-0"><i class="bi bi-check-circle-fill text-success"></i> Confirmar Aprobación</h5>',
                html: `
                    <p class="mt-3">
                        ¿Está seguro de que desea <b>aprobar</b> este servicio?
                        Se hará visible inmediatamente para todos los usuarios.
                    </p>
                `,
                icon: "info",
                showCancelButton: true,
                focusCancel: true,
                confirmButtonText: '<i class="bi bi-check-circle"></i> Sí, Aprobar',
                cancelButtonText: '<i class="bi bi-x-lg"></i> Cancelar',
                customClass: {
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-secondary"
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href =
                        `${BASE_URL}/admin/moderacion-actualizar?accion=aprobar&id=${id}`;
                }
            });
        });
    });



    // --------------------
    // RECHAZAR SERVICIO
    // --------------------
    document.querySelectorAll(".btn-reject").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();

            let id = this.dataset.id;

            Swal.fire({
                title: '<h5 class="m-0"><i class="bi bi-x-circle-fill text-danger"></i> Confirmar Rechazo</h5>',
                html: `
                    <p class="mt-3">
                        ¿Está seguro de que desea <b>rechazar</b> este servicio?
                        El proveedor será notificado.
                    </p>

                    <div class="text-start mt-3">
                        <label class="form-label fw-semibold">Motivo del Rechazo (Obligatorio)</label>
                        <textarea id="motivoRechazoSwal" class="form-control" rows="3"
                            placeholder="Indique claramente por qué se rechaza el servicio."></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-x-circle"></i> Sí, Rechazar',
                cancelButtonText: '<i class="bi bi-x-lg"></i> Cancelar',
                customClass: {
                    confirmButton: "btn btn-danger",
                    cancelButton: "btn btn-secondary"
                },
                buttonsStyling: false,
                preConfirm: () => {
                    const motivo = document.getElementById("motivoRechazoSwal").value.trim();
                    if (!motivo) {
                        Swal.showValidationMessage("Debes escribir un motivo.");
                    }
                    return motivo;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let motivo = encodeURIComponent(result.value);

                    window.location.href =
                        `${BASE_URL}/admin/moderacion-actualizar?accion=rechazar&id=${id}&motivo=${motivo}`;
                }
            });
        });
    });

});

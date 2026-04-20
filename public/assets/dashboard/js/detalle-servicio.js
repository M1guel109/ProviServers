document.addEventListener('DOMContentLoaded', () => {
    // 1. Inicialización de elementos
    const modalEl = document.getElementById('modalDetalleServicio');

    // 2. Delegación de eventos (Un solo listener para todo)
    document.addEventListener("click", (e) => {
        // Detectar clics en botones o elementos dentro de botones
        const btn = e.target.closest('button');
        if (!btn) return;

        // ACCIÓN: VER DETALLE
        if (btn.classList.contains('btn-ver-detalle-servicio')) {
            abrirModalDetalle(btn, modalEl);
        }

        // ACCIÓN: ELIMINAR
        if (btn.classList.contains('btn-eliminar-card')) {
            confirmarAccion(btn.dataset.id, 'eliminar', 
                '¿Eliminar servicio?', 'Esta acción no se puede deshacer.', '#dc3545', 'Sí, eliminar');
        }

        // ACCIÓN: PAUSAR
        if (btn.classList.contains('btn-pausar-servicio')) {
            confirmarAccion(btn.dataset.id, 'pausar', 
                '¿Pausar servicio?', 'El servicio dejará de ser visible para los clientes.', '#6c757d', 'Sí, pausar');
        }

        // ACCIÓN: REANUDAR
        if (btn.classList.contains('btn-reanudar-servicio')) {
            confirmarAccion(btn.dataset.id, 'reanudar', 
                '¿Reanudar servicio?', 'Tu servicio volverá a ser visible para los clientes.', '#198754', 'Sí, reanudar');
        }
    });
});

/**
 * Función centralizada para todas las confirmaciones (SweetAlert)
 */
function confirmarAccion(id, accion, titulo, texto, color, textoConfirmar) {
    Swal.fire({
        title: titulo,
        text: texto,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: color,
        cancelButtonColor: "#6c757d",
        confirmButtonText: textoConfirmar,
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirigimos al controlador con la acción correspondiente
            window.location.href = `${BASE_URL}/proveedor/guardar-servicio?accion=${accion}&id=${id}`;
        }
    });
}

/**
 * Función para poblar el modal de detalle
 */
function abrirModalDetalle(btn, modalEl) {
    const loader = document.getElementById("loader-detalle-servicio");
    const contenido = document.getElementById("contenido-detalle-servicio");

    // Mostrar loader y ocultar contenido temporalmente
    loader?.classList.remove("d-none");
    contenido?.classList.add("d-none");

    // Simular pequeña carga para mejor UX
    setTimeout(() => {
        loader?.classList.add("d-none");
        contenido?.classList.remove("d-none");
    }, 300);

    const base = btn.dataset.baseUrl || BASE_URL;
    const id = btn.dataset.servicioId || "";

    // Poblar campos
    const img = document.getElementById("modal-servicio-img");
    if (img) img.src = btn.dataset.servicioImg || "";

    setText("modal-servicio-nombre", btn.dataset.servicioNombre || "");
    setText("modal-servicio-id", `ID: ${id}`);
    setText("modal-servicio-fecha", btn.dataset.servicioFecha || "");
    setText("modal-servicio-categoria", btn.dataset.servicioCategoria || "");
    setText("modal-servicio-descripcion", btn.dataset.servicioDescripcion || "");
    setText("modal-servicio-estado-texto", btn.dataset.servicioEstadoTexto || "");
    setText("modal-servicio-disponible-texto", btn.dataset.servicioDisponibleTexto || "");

    // Configurar Badges
    const badgeEstado = document.getElementById("modal-servicio-estado");
    if (badgeEstado) {
        badgeEstado.className = "badge " + (btn.dataset.servicioEstadoBadgeclass || "bg-secondary");
        badgeEstado.textContent = btn.dataset.servicioEstadoTexto || "";
    }

    const disp = btn.dataset.servicioDisponible === "1";
    const badgeDisp = document.getElementById("modal-servicio-disponible");
    if (badgeDisp) {
        badgeDisp.className = "badge " + (disp ? "bg-success" : "bg-danger");
        badgeDisp.textContent = disp ? "Disponible" : "No disponible";
    }

    // Link editar
    const linkEditar = document.getElementById("modal-link-editar");
    if (linkEditar) linkEditar.href = `${base}/proveedor/editar-servicio?id=${id}`;

    // Botón eliminar dentro del modal
    const btnEliminarModal = document.getElementById("modal-btn-eliminar");
    if (btnEliminarModal) {
        const nuevoBtn = btnEliminarModal.cloneNode(true);
        btnEliminarModal.parentNode.replaceChild(nuevoBtn, btnEliminarModal);
        nuevoBtn.addEventListener("click", () => {
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
            confirmarAccion(id, 'eliminar', '¿Eliminar?', 'Esta acción no se puede deshacer.', '#dc3545', 'Sí, eliminar');
        });
    }
}

// /**
//  * Helper para asignar texto a elementos
//  */
// function setText(id, text) {
//     const el = document.getElementById(id);
//     if (el) el.textContent = text;
// }
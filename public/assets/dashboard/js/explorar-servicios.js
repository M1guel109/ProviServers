document.addEventListener("DOMContentLoaded", () => {
  // Variable global — guarda el servicio actualmente abierto
  // para pasarlo al modal de solicitar
  let servicioActual = {
    id: null,
    titulo: "",
    precio: 0,
    precioFormateado: "",
  };

  // =========================================================
  // MODAL DETALLE — se abre desde la tarjeta
  // =========================================================
  const modalDetalle = document.getElementById("modalDetalleServicio");

  if (modalDetalle) {
    modalDetalle.addEventListener("show.bs.modal", function (event) {
      const button = event.relatedTarget;
      if (!button) return;

      // ✅ Capturar todos los datos del botón
      const id = button.getAttribute("data-id");
      const titulo = button.getAttribute("data-titulo") || "Sin título";
      const descripcion =
        button.getAttribute("data-descripcion") || "Sin descripción";
      const precio = button.getAttribute("data-precio") || "0";
      const precioRaw = parseFloat(button.getAttribute("data-precio-raw")) || 0;
      const proveedor = button.getAttribute("data-proveedor") || "Proveedor";
      const categoria =
        button.getAttribute("data-categoria") || "Sin categoría";
      const imagen = button.getAttribute("data-imagen");

      // ✅ Guardar en variable global para el siguiente modal
      servicioActual = {
        id: id,
        titulo: titulo,
        precio: precioRaw,
        precioFormateado: "$" + precio,
      };

      // Llenar el modal de detalle
      setText("detalle_titulo", titulo);
      setText("detalle_proveedor", proveedor);
      setText("detalle_descripcion", descripcion); // ✅ Ahora muestra completa
      setText("detalle_categoria", categoria);
      setText("detalle_precio", "$" + precio);

      const img = document.getElementById("detalle_imagen");
      if (img) img.src = imagen;
    });
  }

  // =========================================================
  // MODAL SOLICITAR — se abre desde el modal de detalle
  // =========================================================
  const modalSolicitar = document.getElementById("modalSolicitarServicio");

  if (modalSolicitar) {
    modalSolicitar.addEventListener("show.bs.modal", function () {
      if (!servicioActual.id) {
        console.warn("No hay servicio seleccionado");
        return;
      }

      const elId = document.getElementById("modal_servicio_id");
      const elTitulo = document.getElementById("modal_servicio_titulo");
      const elTituloHid = document.getElementById(
        "modal_servicio_titulo_input",
      );
      const elPrecio = document.getElementById("modal_servicio_precio");

      if (elId) elId.value = servicioActual.id;
      if (elTitulo) elTitulo.textContent = servicioActual.titulo;
      if (elTituloHid) elTituloHid.value = servicioActual.titulo; // ✅ titulo al servidor
      if (elPrecio) elPrecio.textContent = servicioActual.precioFormateado;
    });
  }

  // Helper
  function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text ?? "";
  }
});

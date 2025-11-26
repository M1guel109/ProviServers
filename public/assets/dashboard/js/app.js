const tabla = document.querySelector("#tabla-1 tbody");

let servicios = [];

document.addEventListener("DOMContentLoaded", () => {
    fetch("../../../assets/data/usuarios.json")

        .then(response => response.json())

        .then(data => {
            usuarios = data;

            // Funciones futuras
            mostrarUsuarios(usuarios)
        })

        .catch(error => {
            console.error("Error al cargar el JSON:", error);
        })
})

function mostrarUsuarios(usuarios) {

    if (!tabla) return;

    // Limpiar tabla actual
    tabla.innerHTML = "";

    const estadoClase = {
        "Completado": "completed",
        "En proceso": "in-progress",
        "Pendiente": "pending"
    }

    // Recorrer los usuarios y crear filas dinámicamente
    usuarios.forEach(usuario => {
        const claseEstado = estadoClase[usuario.estado] || "pending";
        const fila = document.createElement("tr");
        fila.innerHTML = `
                <tr>
                    <td>${usuario.id}</td>
                    <td>${usuario.nombre}</td>
                    <td>${usuario.email}</td>
                    <td>${usuario.telefono}</td>
                    <td>${usuario.ubicacion}</td>
                    <td><span class="status-badge status-${usuario.rol.toLowerCase()}">${usuario.rol}</span></td>
                    <td>
                        <div class="action-buttons text-center">
                            <button class="btn-action btn-edit" title="Editar usuario">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn-action btn-delete" title="Eliminar usuario">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </td>
                </tr>
        `;
        tabla.appendChild(fila);
    });
}
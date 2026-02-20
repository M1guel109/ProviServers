// JavaScript para el dashboard de proveedores

document.addEventListener('DOMContentLoaded', function() {
    // Toggle del menú lateral
    const btnToggleMenu = document.getElementById('btn-toggle-menu');
    const sidebar = document.querySelector('.sidebar');
    
    if (btnToggleMenu) {
        btnToggleMenu.addEventListener('click', function() {
            sidebar.classList.toggle('plegado');
        });
    }
    
    // Toggle de submenús
    const toggleSubmenuButtons = document.querySelectorAll('.toggle-submenu');
    
    toggleSubmenuButtons.forEach(button => {
        button.addEventListener('click', function() {
            const submenu = this.parentElement.querySelector('.submenu');
            const hasSubmenu = this.parentElement;
            
            hasSubmenu.classList.toggle('active');
            
            if (hasSubmenu.classList.contains('active')) {
                submenu.style.maxHeight = submenu.scrollHeight + 'px';
            } else {
                submenu.style.maxHeight = '0';
            }
        });
    });
    
    // Inicializar gráficos (ejemplo con ApexCharts)
    if (typeof ApexCharts !== 'undefined') {
        // Gráfica principal
        const chartOptions = {
            series: [{
                name: 'Servicios Completados',
                data: [30, 40, 35, 50, 49, 60, 70, 91, 125, 110, 95, 120]
            }, {
                name: 'Ingresos ($)',
                data: [1200, 1500, 1800, 2000, 2200, 2500, 2800, 3000, 3200, 2900, 3100, 3300]
            }],
            chart: {
                height: 300,
                type: 'line',
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            title: {
                text: '',
                align: 'left'
            },
            grid: {
                row: {
                    colors: ['#f3f3f3', 'transparent'],
                    opacity: 0.5
                },
            },
            xaxis: {
                categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            },
            colors: ['#3498db', '#2ecc71']
        };
        
        const chart = new ApexCharts(document.querySelector("#chart"), chartOptions);
        chart.render();
        
        // Cambiar período de la gráfica
        const periodoSelect = document.getElementById('periodo');
        if (periodoSelect) {
            periodoSelect.addEventListener('change', function() {
                // Aquí podrías actualizar la gráfica según el período seleccionado
                console.log('Período cambiado a:', this.value);
            });
        }
    }
    
    // Ejemplo de funcionalidad para notificaciones
    const notificaciones = document.querySelector('.notificaciones');
    if (notificaciones) {
        notificaciones.addEventListener('click', function() {
            alert('Tienes 3 notificaciones nuevas');
        });
    }
});


  // Helper: setText seguro
  const setText = (id, value) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = value ?? '';
  };

  // Resetea el modal (cuando se cierra)
  const resetModalServicio = () => {
    const loader = document.getElementById('loader-detalle-servicio');
    const cont = document.getElementById('contenido-detalle-servicio');
    if (loader) loader.classList.remove('d-none');
    if (cont) cont.classList.add('d-none');

    // Limpia campos
    setText('modal-servicio-nombre', '');
    setText('modal-servicio-id', '');
    setText('modal-servicio-fecha', '');
    setText('modal-servicio-categoria', '');
    setText('modal-servicio-descripcion', '');
    setText('modal-servicio-estado-texto', '');
    setText('modal-servicio-disponible-texto', '');

    const img = document.getElementById('modal-servicio-img');
    if (img) img.src = '';

    const bEstado = document.getElementById('modal-servicio-estado');
    if (bEstado) { bEstado.className = 'badge'; bEstado.textContent = ''; }

    const bDisp = document.getElementById('modal-servicio-disponible');
    if (bDisp) { bDisp.className = 'badge'; bDisp.textContent = ''; }

    const linkEditar = document.getElementById('modal-link-editar');
    if (linkEditar) linkEditar.href = '#';

    const linkEliminar = document.getElementById('modal-link-eliminar');
    if (linkEliminar) linkEliminar.href = '#';
  };

  document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('modalDetalleServicio');
    if (!modalEl) return;

    // Cuando se cierra el modal, resetea para el siguiente click
    modalEl.addEventListener('hidden.bs.modal', resetModalServicio);

    // Delegación: captura clic en cualquier botón "Ver"
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-ver-detalle-servicio');
      if (!btn) return;

      // Muestra contenido y oculta loader
      const loader = document.getElementById('loader-detalle-servicio');
      const cont = document.getElementById('contenido-detalle-servicio');
      if (loader) loader.classList.add('d-none');
      if (cont) cont.classList.remove('d-none');

      // Dataset (Bootstrap data-*)
      const base = btn.dataset.baseUrl || '';
      const id = btn.dataset.servicioId || '';

      // Pintar campos
      const imgUrl = btn.dataset.servicioImg || '';
      const img = document.getElementById('modal-servicio-img');
      if (img) img.src = imgUrl;

      setText('modal-servicio-nombre', btn.dataset.servicioNombre || '');
      setText('modal-servicio-id', `ID: ${id}`);
      setText('modal-servicio-fecha', btn.dataset.servicioFecha || '');
      setText('modal-servicio-categoria', btn.dataset.servicioCategoria || '');
      setText('modal-servicio-descripcion', btn.dataset.servicioDescripcion || '');
      setText('modal-servicio-estado-texto', btn.dataset.servicioEstadoTexto || '');
      setText('modal-servicio-disponible-texto', btn.dataset.servicioDisponibleTexto || '');

      // Badges superiores (overlay)
      const badgeEstado = document.getElementById('modal-servicio-estado');
      if (badgeEstado) {
        badgeEstado.className = 'badge ' + (btn.dataset.servicioEstadoBadgeclass || 'bg-secondary');
        badgeEstado.textContent = btn.dataset.servicioEstadoTexto || '';
      }

      const disp = btn.dataset.servicioDisponible === '1';
      const badgeDisp = document.getElementById('modal-servicio-disponible');
      if (badgeDisp) {
        badgeDisp.className = 'badge ' + (disp ? 'bg-success' : 'bg-dark');
        badgeDisp.textContent = disp ? 'Disponible' : 'No disponible';
      }

      // Links acciones
      const linkEditar = document.getElementById('modal-link-editar');
      if (linkEditar) linkEditar.href = `${base}/proveedor/editar-servicio?id=${id}`;

      const linkEliminar = document.getElementById('modal-link-eliminar');
      if (linkEliminar) linkEliminar.href = `${base}/proveedor/guardar-servicio?accion=eliminar&id=${id}`;
    });
  });

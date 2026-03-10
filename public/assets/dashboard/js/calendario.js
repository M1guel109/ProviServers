/* ======================================================
   calendario_proveedor.js - Calendario interactivo
   ====================================================== */

document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const calendarDays = document.getElementById('calendarDays');
    const currentMonthSpan = document.getElementById('currentMonth');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const todayBtn = document.getElementById('todayBtn');
    const servicesOfDay = document.getElementById('servicesOfDay');
    
    // Fecha actual
    let currentDate = new Date();
    
    // Datos de ejemplo (simulados)
    const eventos = {
        '2025-12-10': {
            servicios: [
                { hora: '08:00', servicio: 'Reparación de tuberías', cliente: 'Carlos López', precio: 120000 },
                { hora: '14:00', servicio: 'Instalación de grifo', cliente: 'Ana Gómez', precio: 80000 }
            ]
        },
        '2025-12-15': {
            servicios: [
                { hora: '09:30', servicio: 'Limpieza residencial', cliente: 'María Pérez', precio: 150000 }
            ]
        },
        '2025-12-18': {
            servicios: [
                { hora: '10:00', servicio: 'Pintura de habitación', cliente: 'Juan Rodríguez', precio: 200000 },
                { hora: '16:00', servicio: 'Reparación eléctrica', cliente: 'Laura Martínez', precio: 95000 }
            ]
        },
        '2025-12-20': {
            servicios: [
                { hora: '11:00', servicio: 'Mantenimiento general', cliente: 'Pedro Sánchez', precio: 180000 }
            ]
        }
    };
    
    const diasBloqueados = ['2025-12-24', '2025-12-25', '2025-12-31'];
    
    // Inicializar calendario
    renderCalendar(currentDate);
    
    // Event listeners
    if (prevMonthBtn) {
        prevMonthBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar(currentDate);
        });
    }
    
    if (nextMonthBtn) {
        nextMonthBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar(currentDate);
        });
    }
    
    if (todayBtn) {
        todayBtn.addEventListener('click', () => {
            currentDate = new Date();
            renderCalendar(currentDate);
        });
    }
    
    // Función para renderizar el calendario
    function renderCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth();
        
        // Actualizar título del mes
        const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        currentMonthSpan.textContent = `${monthNames[month]} ${year}`;
        
        // Primer día del mes
        const firstDay = new Date(year, month, 1);
        const startingDay = firstDay.getDay(); // 0 = Domingo, 1 = Lunes, etc.
        
        // Último día del mes
        const lastDay = new Date(year, month + 1, 0);
        const totalDays = lastDay.getDate();
        
        // Días del mes anterior para rellenar
        const prevMonthLastDay = new Date(year, month, 0).getDate();
        
        // Limpiar calendario
        calendarDays.innerHTML = '';
        
        // Crear grid de días
        let dayCounter = 1;
        let nextMonthDay = 1;
        
        // 6 filas * 7 columnas = 42 celdas
        for (let i = 0; i < 6; i++) {
            const row = document.createElement('div');
            row.className = 'row g-1 mb-1';
            
            for (let j = 0; j < 7; j++) {
                const col = document.createElement('div');
                col.className = 'col';
                
                const dayCell = document.createElement('div');
                dayCell.className = 'calendar-day p-2 text-center rounded';
                
                let dayNumber;
                let isCurrentMonth = false;
                let dateStr = '';
                
                if (i === 0 && j < startingDay) {
                    // Días del mes anterior
                    dayNumber = prevMonthLastDay - startingDay + j + 1;
                    dayCell.classList.add('text-muted', 'opacity-50');
                    dateStr = `${year}-${String(month).padStart(2, '0')}-${String(dayNumber).padStart(2, '0')}`;
                } else if (dayCounter <= totalDays) {
                    // Días del mes actual
                    dayNumber = dayCounter;
                    dayCounter++;
                    isCurrentMonth = true;
                    dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(dayNumber).padStart(2, '0')}`;
                    
                    // Verificar si es hoy
                    const today = new Date();
                    if (year === today.getFullYear() && month === today.getMonth() && dayNumber === today.getDate()) {
                        dayCell.classList.add('bg-primary', 'text-white');
                    }
                    
                    // Verificar si tiene servicios
                    if (eventos[dateStr]) {
                        dayCell.classList.add('bg-success', 'bg-opacity-25', 'border', 'border-success');
                    }
                    
                    // Verificar si está bloqueado
                    if (diasBloqueados.includes(dateStr)) {
                        dayCell.classList.add('bg-danger', 'bg-opacity-25', 'border', 'border-danger');
                    }
                    
                } else {
                    // Días del próximo mes
                    dayNumber = nextMonthDay;
                    nextMonthDay++;
                    dayCell.classList.add('text-muted', 'opacity-50');
                    dateStr = `${year}-${String(month + 2).padStart(2, '0')}-${String(dayNumber).padStart(2, '0')}`;
                }
                
                dayCell.textContent = dayNumber;
                dayCell.setAttribute('data-date', dateStr);
                
                // Evento al hacer clic en un día
                dayCell.addEventListener('click', function() {
                    // Quitar selección anterior
                    document.querySelectorAll('.calendar-day').forEach(el => {
                        el.classList.remove('border', 'border-primary', 'border-2');
                    });
                    
                    // Agregar selección al día actual
                    this.classList.add('border', 'border-primary', 'border-2');
                    
                    // Mostrar servicios del día
                    const selectedDate = this.getAttribute('data-date');
                    mostrarServiciosDelDia(selectedDate);
                });
                
                col.appendChild(dayCell);
                row.appendChild(col);
            }
            
            calendarDays.appendChild(row);
            
            // Si ya completamos todos los días del mes, terminamos
            if (dayCounter > totalDays && nextMonthDay > 7) {
                break;
            }
        }
    }
    
    // Función para mostrar servicios del día seleccionado
    function mostrarServiciosDelDia(dateStr) {
        if (!servicesOfDay) return;
        
        const servicios = eventos[dateStr];
        
        if (servicios && servicios.servicios.length > 0) {
            let html = '<div class="list-group">';
            
            servicios.servicios.forEach(s => {
                html += `
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 fw-bold">${s.servicio}</h6>
                            <small class="text-primary">$${s.precio.toLocaleString()}</small>
                        </div>
                        <p class="mb-1 small text-muted">
                            <i class="bi bi-clock me-1"></i>${s.hora} 
                            <i class="bi bi-person ms-2 me-1"></i>${s.cliente}
                        </p>
                    </div>
                `;
            });
            
            html += '</div>';
            servicesOfDay.innerHTML = html;
        } else if (diasBloqueados.includes(dateStr)) {
            servicesOfDay.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-calendar-x me-2"></i>
                    <strong>Día bloqueado</strong>
                    <p class="mb-0 small mt-2">No hay servicios programados para este día.</p>
                </div>
            `;
        } else {
            servicesOfDay.innerHTML = `
                <div class="alert alert-light mb-0 text-center">
                    <i class="bi bi-calendar3 display-6 d-block mb-2"></i>
                    <p class="mb-0 text-muted">No hay servicios programados para este día.</p>
                </div>
            `;
        }
    }
    
    // Función para bloquear un día (desde el modal)
    const btnConfirmarBloqueo = document.querySelector('#bloquearDiaModal .btn-danger');
    if (btnConfirmarBloqueo) {
        btnConfirmarBloqueo.addEventListener('click', function() {
            const fechaInput = document.querySelector('#bloquearDiaModal input[type="date"]');
            const motivoTextarea = document.querySelector('#bloquearDiaModal textarea');
            
            if (fechaInput && fechaInput.value) {
                const fecha = fechaInput.value;
                const motivo = motivoTextarea ? motivoTextarea.value : '';
                
                // Aquí iría la lógica para guardar el bloqueo (AJAX)
                console.log('Bloquear día:', fecha, motivo);
                
                // Agregar a la lista de bloqueados (simulado)
                diasBloqueados.push(fecha);
                
                // Recargar calendario
                renderCalendar(currentDate);
                
                // Cerrar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('bloquearDiaModal'));
                if (modal) modal.hide();
                
                // Limpiar campos
                fechaInput.value = '';
                if (motivoTextarea) motivoTextarea.value = '';
                
                // Mostrar mensaje de éxito
                alert('Día bloqueado correctamente (simulado)');
            } else {
                alert('Por favor selecciona una fecha');
            }
        });
    }
});
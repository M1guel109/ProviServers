jsssssssss


        // Datos de eventos de ejemplo
        const eventsData = {
            '2025-12-10': [
                { time: '09:00 AM', title: 'Instalación Eléctrica', description: 'Juan Pérez - Cliente: María González', type: 'servicio', proveedor: 'Juan Pérez', cliente: 'María González', estado: 'confirmado' },
                { time: '10:30 AM', title: 'Vencimiento Membresía Premium', description: 'Roberto Sánchez', type: 'membresia', proveedor: 'Roberto Sánchez', monto: '$150,000', estado: 'pendiente' },
                { time: '02:00 PM', title: 'Verificación Documentos', description: 'Nuevo proveedor: Carlos López', type: 'verificacion', proveedor: 'Carlos López', documentos: ['Cédula', 'RUT'], estado: 'pendiente' }
            ],
            '2025-12-12': [
                { time: '11:00 AM', title: 'Servicio de Plomería', description: 'Ana Martínez - Cliente: Pedro Ruiz', type: 'servicio', proveedor: 'Ana Martínez', cliente: 'Pedro Ruiz', estado: 'en_proceso' },
                { time: '03:00 PM', title: 'Reunión Proveedores', description: 'Presentación nuevas políticas', type: 'evento', participantes: 25, ubicacion: 'Sala Virtual' }
            ],
            '2025-12-15': [
                { time: '09:30 AM', title: 'Vencimiento Membresía Basic', description: 'Sandra López', type: 'membresia', proveedor: 'Sandra López', monto: '$80,000', estado: 'por_vencer' },
                { time: '11:00 AM', title: 'Servicio de Jardinería', description: 'Luis Torres', type: 'servicio', proveedor: 'Luis Torres', cliente: 'Carmen Díaz', estado: 'confirmado' }
            ],
            '2025-12-18': [
                { time: '10:00 AM', title: 'Verificación Documentos', description: 'Miguel Ángel', type: 'verificacion', proveedor: 'Miguel Ángel', documentos: ['Certificado'], estado: 'urgente' }
            ],
            '2025-12-20': [
                { time: '02:00 PM', title: 'Vencimiento Premium', description: 'Patricia Silva', type: 'membresia', proveedor: 'Patricia Silva', monto: '$150,000', estado: 'por_vencer' }
            ],
            '2025-12-22': [
                { time: '09:00 AM', title: 'Servicio de Limpieza', description: 'Rosa Méndez', type: 'servicio', proveedor: 'Rosa Méndez', cliente: 'Empresas XYZ', estado: 'confirmado' },
                { time: '04:00 PM', title: 'Mantenimiento Plataforma', description: 'Actualización sistema', type: 'evento', duracion: '2 horas' }
            ],
            '2025-12-25': [
                { time: 'Todo el día', title: 'Navidad - Plataforma Cerrada', description: 'Día festivo', type: 'evento', estado: 'festivo' }
            ]
        };

        let currentDate = new Date();
        let selectedDate = new Date();
        let currentFilter = 'all';
        
        const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        const dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        function generateCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
            
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrevMonth = new Date(year, month, 0).getDate();
            
            const calendarDays = document.getElementById('calendarDays');
            calendarDays.innerHTML = '';
            
            // Días del mes anterior
            for (let i = firstDay - 1; i >= 0; i--) {
                const day = daysInPrevMonth - i;
                const date = new Date(year, month - 1, day);
                const dayDiv = createDayElement(day, 'other-month', date);
                calendarDays.appendChild(dayDiv);
            }
            
            // Días del mes actual
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                date.setHours(0, 0, 0, 0);
                
                let classes = '';
                if (date.getTime() === today.getTime()) classes += 'today ';
                if (date.getTime() === selectedDate.getTime()) classes += 'selected ';
                
                const dayDiv = createDayElement(day, classes, date);
                calendarDays.appendChild(dayDiv);
            }
            
            // Días del siguiente mes
            const remainingDays = 42 - (firstDay + daysInMonth);
            for (let day = 1; day <= remainingDays; day++) {
                const date = new Date(year, month + 1, day);
                const dayDiv = createDayElement(day, 'other-month', date);
                calendarDays.appendChild(dayDiv);
            }
            
            updateEventsList();
            updateStats();
        }

        function createDayElement(day, classes, date) {
            const dayDiv = document.createElement('div');
            dayDiv.className = `calendar-day ${classes}`;
            
            const dateStr = formatDate(date);
            const events = eventsData[dateStr] || [];
            const filteredEvents = currentFilter === 'all' ? events : events.filter(e => e.type === currentFilter);
            
            dayDiv.innerHTML = `
                <div class="day-number">${day}</div>
                <div class="day-events">
                    ${filteredEvents.slice(0, 6).map(e => `<div class="event-dot ${e.type}" title="${e.title}"></div>`).join('')}
                    ${filteredEvents.length > 6 ? `<div style="font-size:10px;color:#6B7280;margin-top:4px">+${filteredEvents.length - 6} más</div>` : ''}
                </div>
            `;
            
            dayDiv.addEventListener('click', () => {
                selectedDate = new Date(date);
                selectedDate.setHours(0, 0, 0, 0);
                generateCalendar();
            });
            
            return dayDiv;
        }
        
        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
        
        function updateEventsList() {
            const dateStr = formatDate(selectedDate);
            const events = eventsData[dateStr] || [];
            const filteredEvents = currentFilter === 'all' ? events : events.filter(e => e.type === currentFilter);
            
            const dayName = dayNames[selectedDate.getDay()];
            const monthName = monthNames[selectedDate.getMonth()];
            document.getElementById('selectedDate').textContent = 
                `${dayName}, ${selectedDate.getDate()} de ${monthName} ${selectedDate.getFullYear()}`;
            
            const eventsList = document.getElementById('eventsList');
            
            if (filteredEvents.length === 0) {
                eventsList.innerHTML = `
                    <div class="empty-events">
                        <i class="bi bi-calendar-x"></i>
                        <div>No hay eventos para este día</div>
                    </div>
                `;
            } else {
                eventsList.innerHTML = filteredEvents.map(event => `
                    <div class="event-item ${event.type}">
                        <div class="event-time">${event.time}</div>
                        <div class="event-title">${event.title}</div>
                        <div class="event-description">${event.description}</div>
                    </div>
                `).join('');
            }
        }
        
        function updateStats() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            let total = 0, servicios = 0, vencimientos = 0, verificaciones = 0;
            
            for (let day = 1; day <= new Date(year, month + 1, 0).getDate(); day++) {
                const dateStr = formatDate(new Date(year, month, day));
                const events = eventsData[dateStr] || [];
                total += events.length;
                servicios += events.filter(e => e.type === 'servicio').length;
                vencimientos += events.filter(e => e.type === 'membresia').length;
                verificaciones += events.filter(e => e.type === 'verificacion').length;
            }
            
            const statItems = document.querySelectorAll('.stat-value');
            if (statItems[0]) statItems[0].textContent = total;
            if (statItems[1]) statItems[1].textContent = servicios;
            if (statItems[2]) statItems[2].textContent = vencimientos;
            if (statItems[3]) statItems[3].textContent = verificaciones;
        }

        document.getElementById('prevMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            generateCalendar();
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            generateCalendar();
        });

        document.getElementById('todayBtn').addEventListener('click', () => {
            currentDate = new Date();
            selectedDate = new Date();
            currentDate.setHours(0, 0, 0, 0);
            selectedDate.setHours(0, 0, 0, 0);
            generateCalendar();
        });

        // Filtros
        document.querySelectorAll('.filter-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.filter-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.getAttribute('data-filter');
                generateCalendar();
            });
        });

        // Inicializar
        selectedDate.setHours(0, 0, 0, 0);
        currentDate.setHours(0, 0, 0, 0);
        generateCalendar();
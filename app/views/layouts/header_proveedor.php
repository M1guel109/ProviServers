        <header class="barra-superior">
            <!-- Botón para plegar el menú -->
            <button id="btn-toggle-menu" class="btn-toggle">
                <i class="bi bi-list"></i>
            </button>

            <!--Barra Superior -->
            <div class="buscador">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Buscar servicios...">
            </div>
            <div class="acciones-barra">
                <!-- Notificaiones -->
                <div class="notificaciones item-barra">
                    <i class="bi bi-bell-fill"></i>
                    <span class="badge">3</span>
                </div>

                <!-- Idioma -->
                <div class="idioma item-barra">
                    <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/bandera-idioma.png" alt="Foto bandera">
                    <span>Español</span>
                    <i class="bi bi-chevron-down"></i>
                </div>

                <!-- Usuario -->
                <a href="dashboardPerfil.html" class="usuario item-barra">
                    <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/Foto-usuario.png" alt="Foto usuario">
                    <div class="info-usuario">
                        <span class="nombre">Carlos Mendoza</span>
                        <span class="rol">Proveedor</span>
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </a>

            </div>
        </header>
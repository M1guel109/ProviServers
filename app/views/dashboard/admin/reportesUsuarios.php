<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Plataforma de servicios locales</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- tu css -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardReportes.css">
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->


    <!-- AQUI VA EL INCLUDE DEL MENU -->

    <?php
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php'
    ?>


    <main class="contenido">

        <!-- AQUI VA EL INCLUDE DEL HEADER -->
        <?php
        include_once __DIR__ . '/../../layouts/header_administrador.php'
        ?>

        <!--     Secciones -->
        <!-- titulo -->
        <section id="titulo-principal">
            <div class="row">
                <div class="col-md-8">
                    <h1>Reporte y Gestión de Usuarios</h1>
                    <p class="text-muted mb-0">
                        Gestión completa de clientes, proveedores y administradores de la plataforma.
                </div>
                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0"></ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- 2. Zona de Indicadores (KPIs) -->
        <section id="tarjetas-kpis" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

            <!-- KPI 1: Total de Usuarios -->
            <div class="bg-white p-5 rounded-xl shadow-lg border-l-4 border-indigo-500">
                <div class="text-sm font-medium text-gray-500">Total Usuarios Activos</div>
                <div class="text-3xl font-bold text-gray-900 mt-1">12,500</div>
            </div>

            <!-- KPI 2: Proveedores -->
            <div class="bg-white p-5 rounded-xl shadow-lg border-l-4 border-green-500">
                <div class="text-sm font-medium text-gray-500">Total Proveedores</div>
                <div class="text-3xl font-bold text-gray-900 mt-1">2,100</div>
            </div>

            <!-- KPI 3: Clientes -->
            <div class="bg-white p-5 rounded-xl shadow-lg border-l-4 border-blue-500">
                <div class="text-sm font-medium text-gray-500">Total Clientes</div>
                <div class="text-3xl font-bold text-gray-900 mt-1">10,400</div>
            </div>

            <!-- KPI 4: Usuarios Bloqueados -->
            <div class="bg-white p-5 rounded-xl shadow-lg border-l-4 border-red-500">
                <div class="text-sm font-medium text-gray-500">Usuarios Bloqueados</div>
                <div class="text-3xl font-bold text-gray-900 mt-1">85</div>
            </div>
        </section>

        <!-- 3. Zona de Filtros y Acciones -->
        <section id="filtros-y-acciones" class="bg-white p-6 rounded-xl shadow-lg mb-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Filtros de Búsqueda</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Búsqueda General -->
                <div>
                    <label for="busqueda" class="block text-sm font-medium text-gray-700">Nombre, Email o ID</label>
                    <input type="text" id="busqueda" placeholder="Buscar usuario..." class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Tipo de Rol -->
                <div>
                    <label for="rol" class="block text-sm font-medium text-gray-700">Rol</label>
                    <select id="rol" class="mt-1 block w-full p-2 border border-gray-300 rounded-lg bg-white focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="cliente">Cliente</option>
                        <option value="proveedor">Proveedor</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>

                <!-- Estado de Cuenta -->
                <div>
                    <label for="estado_cuenta" class="block text-sm font-medium text-gray-700">Estado de Cuenta</label>
                    <select id="estado_cuenta" class="mt-1 block w-full p-2 border border-gray-300 rounded-lg bg-white focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="activo">Activo</option>
                        <option value="pendiente">Pendiente (Verificación)</option>
                        <option value="bloqueado">Bloqueado</option>
                    </select>
                </div>

                <!-- Verificación (Solo para Proveedores) -->
                <div>
                    <label for="verificacion" class="block text-sm font-medium text-gray-700">Verificación (Proveedor)</label>
                    <select id="verificacion" class="mt-1 block w-full p-2 border border-gray-300 rounded-lg bg-white focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="verificado">Verificado</option>
                        <option value="no_verificado">No Verificado</option>
                        <option value="en_revision">En Revisión</option>
                    </select>
                </div>
            </div>

            <div class="flex space-x-4 mt-6">
                <button class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:bg-blue-700 transition duration-300">
                    Aplicar Filtros
                </button>
                <button class="bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg shadow-md hover:bg-gray-400 transition duration-300">
                    Limpiar Filtros
                </button>
                <button class="bg-green-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:bg-green-700 transition duration-300 ml-auto">
                    Exportar CSV/Excel
                </button>
            </div>
        </section>

        <!-- 4. Tabla de Usuarios -->
        <section id="tabla-usuarios" class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Lista de Usuarios</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Completo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Órdenes/Servicios</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Fila de ejemplo (Proveedor Verificado) -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">USR-001</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Sofía Rodríguez (Proveedor)</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">sofia.r@ejemplo.com</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Proveedor</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo / Verificado</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2022-10-15</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">72 Servicios</td>
                            <td>
                                <div class="action-buttons">
                                    <a href="#" class="btn-action btn-view" title="Ver detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="#" class="btn-action btn-edit" title="Editar usuario">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <a href="#" class="btn-action btn-delete" title="Eliminar usuario">
                                        <i class="bi bi-trash3"></i>
                                    </a>
                                </div>

                            </td>
                        </tr>
                        <!-- Fila de ejemplo (Cliente) -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">USR-002</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Carlos Gómez (Cliente)</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">carlos.g@ejemplo.com</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Cliente</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2023-01-20</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">8 Órdenes</td>
                            <td>
                                <div class="action-buttons">
                                    <a href="#" class="btn-action btn-view" title="Ver detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="#" class="btn-action btn-edit" title="Editar usuario">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <a href="#" class="btn-action btn-delete" title="Eliminar usuario">
                                        <i class="bi bi-trash3"></i>
                                    </a>
                                </div>

                            </td>
                        </tr>
                        <!-- Más filas aquí... -->
                    </tbody>
                </table>
            </div>
        </section>






    </main>


    <footer>
        <!-- Enlaces / Información -->
    </footer>

    <!-- apexcharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- tu javaScript -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
</body>

</html>
# ProviServers 🛠️

**Marketplace de servicios independientes** que conecta proveedores con clientes de forma ágil y segura.

> Proyecto desarrollado en el programa **ADSO (Análisis y Desarrollo de Software)** del SENA.

---

## 📋 Descripción

ProviServers es una plataforma web tipo marketplace que permite a clientes publicar solicitudes de servicios y a proveedores independientes ofertar, gestionar y calificar dichos servicios. El sistema centraliza la comunicación, el seguimiento del estado de las solicitudes y la administración de usuarios en un solo lugar.

---

## ✨ Funcionalidades principales

- **Registro y autenticación** de clientes y proveedores
- **Publicación de solicitudes** de servicios por parte de los clientes
- **Gestión del flujo de solicitudes**: pendiente → aceptada/rechazada → en progreso → completada
- **Sistema de mensajería** con inbox y chat entre clientes y proveedores
- **Notificaciones** en tiempo real para eventos del sistema
- **Membresías y suscripciones** para proveedores con distintos planes
- **Calendario del proveedor** para gestión de disponibilidad
- **Favoritos del cliente** para guardar proveedores preferidos
- **Pagos integrados** con MercadoPago
- **Calificación** de proveedores al finalizar el servicio
- **Generación de PDF** de comprobantes y reportes
- **Internacionalización** (español / inglés)
- **Panel de administración** con gestión de usuarios, servicios contratados, gráficas y reportes
- **Dashboard de proveedor** con historial de servicios y configuración de perfil
- **Dashboard de cliente** con seguimiento de solicitudes activas e historial

---

## 🏗️ Arquitectura

El proyecto sigue el patrón **MVC (Modelo - Vista - Controlador)** implementado en PHP puro, sin frameworks externos.

```
ProviServers/
├── app/
│   ├── controllers/        # Controladores (kebab-case)
│   ├── helpers/            # Funciones auxiliares reutilizables
│   ├── lang/               # Archivos de internacionalización (es / en)
│   ├── models/             # Modelos (PascalCase, coincide con nombre de clase)
│   └── views/              # Vistas organizadas por contexto
│       ├── auth/
│       ├── dashboard/
│       │   ├── admin/
│       │   ├── cliente/
│       │   └── proveedor/
│       ├── layouts/
│       ├── pdf/
│       └── website/
├── config/
│   ├── config.php          # Configuración general de entorno (no versionado)
│   ├── database.php        # Conexión a base de datos
│   ├── mail.php            # Configuración de PHPMailer
│   ├── mercadopago.php     # Credenciales de MercadoPago
│   └── pdf.php             # Configuración de dompdf
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   └── uploads/            # Archivos subidos por usuarios (imágenes, documentos, etc.)
├── vendor/                 # Dependencias externas (PHPMailer, dompdf)
├── index.php               # Punto de entrada / enrutador principal
├── .htaccess               # Rewrite rules para el enrutador
└── README.md
```

### Convenciones de nombrado

| Elemento | Convención | Ejemplo |
|---|---|---|
| Modelos | PascalCase | `Usuario.php`, `Solicitud.php` |
| Controladores | kebab-case | `solicitud-controller.php` |
| Vistas | kebab-case | `dashboard-cliente.php` |
| Assets (CSS/JS/img) | kebab-case | `dashboard-styles.css` |

---

## 🛠️ Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP (MVC puro) |
| Base de datos | MySQL |
| Frontend | HTML5, CSS3, JavaScript, Bootstrap 5 |
| Alertas / Modales | SweetAlert2 |
| Gráficas | ApexCharts |
| Pagos | MercadoPago |
| Email | PHPMailer |
| Generación de PDF | dompdf |
| Control de versiones | Git / GitHub |
| Hosting | Hostinger (shared hosting) |
| Gestión de tareas | Trello |

---

## ⚙️ Instalación y configuración local

### Requisitos previos

- PHP >= 7.4
- MySQL >= 5.7
- Servidor web con soporte `mod_rewrite` (Apache recomendado) o XAMPP/Laragon
### Pasos

**1. Clonar el repositorio**

```bash
git clone https://github.com/<org>/proviservers.git
cd proviservers
```

**2. Obtener la carpeta `vendor/`**

La carpeta `vendor/` no se gestiona con Composer — fue integrada manualmente. Si no viene incluida en el repositorio, solicitarla al equipo y copiarla en la raíz del proyecto.

**3. Configurar la base de datos**

Importar el archivo SQL desde tu gestor de base de datos (phpMyAdmin, TablePlus, DBeaver, etc.) o desde la terminal:

```bash
mysql -u root -p nombre_bd < proviservers.sql
```

> El archivo `.sql` debe solicitarse al equipo de desarrollo, ya que no se versiona en el repositorio.

**4. Crear el archivo de configuración**

Crear manualmente el archivo `config/config.php` con los siguientes valores ajustados a tu entorno local:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'proviservers');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', 'http://localhost/ProviServers/');
```

> ⚠️ `config/config.php` está en `.gitignore` y **nunca debe subirse al repositorio**. Revisar también `config/mail.php` y `config/mercadopago.php` para configurar credenciales de esos servicios.

**5. Configurar el servidor**

Asegúrate de que `mod_rewrite` esté habilitado. El archivo `.htaccess` en la raíz ya está configurado para que todas las peticiones pasen por `index.php`.

**6. Acceder al sistema**

Abre tu navegador en `http://localhost/ProviServers/`

---

## 🔄 Flujo de estados de una solicitud

```
Cliente crea solicitud
        │
        ▼
   [pendiente]
        │
   Proveedor revisa
        │
   ┌────┴────┐
   ▼         ▼
[aceptada] [rechazada]
   │
   ▼
[en progreso]
   │
   ▼
[completada]
   │
   ▼
Cliente califica al proveedor
```

---

## 🌿 Flujo de trabajo Git (Git Flow)

### Ramas principales

| Rama | Propósito |
|---|---|
| `main` | Producción — código desplegado en Hostinger |
| `develop` | Integración — base para nuevas funcionalidades |

### Creación de ramas por tarea

**Features (desde `origin/develop`):**

```bash
git fetch
git checkout -b sp{sprint}/develop/feature/{nombre-issue} origin/develop
# ... trabajar ...
git add .
git commit -m "feat: descripción del cambio"
git push origin sp{sprint}/develop/feature/{nombre-issue}
```

**Hotfixes (directamente sobre `main`):**

```bash
git checkout -b sp{sprint}/main/hotfix/{nombre-issue} origin/main
```

### Convención de commits (Conventional Commits)

```
feat: agregar sección de actividad en modal de usuario admin
fix: corregir case-sensitivity en rutas de modelos en Linux
style: ajustar diseño responsive del dashboard cliente
refactor: reorganizar controlador de solicitudes
chore: actualizar .gitignore para excluir config.php
```

---

## 🚀 Despliegue en producción (Hostinger)

1. Hacer push a `main`:
   ```bash
   git push origin main
   ```
2. Subir manualmente por FTP/File Manager:
   - `config/config.php` (con credenciales de producción)
   - `config/mail.php`, `config/mercadopago.php`, `config/pdf.php`
   - `.htaccess` (si fue modificado)
3. Verificar el sitio en el dominio de Hostinger.

> El repositorio está conectado a la cuenta de GitHub del colaborador principal, que es la vinculada a Hostinger.

---

## 📁 Base de datos

Las tablas principales del sistema son:

| Tabla | Descripción |
|---|---|
| `usuarios` | Clientes, proveedores y administradores |
| `solicitudes` | Solicitudes de servicio creadas por clientes |
| `publicaciones` | Servicios publicados por proveedores |
| `servicios_contratados` | Servicios aceptados por un proveedor |
| `mensajes` | Mensajería entre clientes y proveedores |
| `membresias` / `suscripciones` | Planes y suscripciones activas de proveedores |
| `calificaciones` | Calificaciones al finalizar un servicio |
| `categorias` | Categorías de servicios disponibles |

Las credenciales de columnas internas (ej. `clave`, `documento`, `calificacion`) siguen el esquema original del diseño del equipo.

---

## 👥 Equipo

Proyecto desarrollado como trabajo de grado del programa **ADSO** en el **SENA**.

| Rol | Responsabilidad |
|---|---|
| Desarrollador principal | Arquitectura MVC, backend PHP, despliegue |
| Equipo de apoyo | Diseño, pruebas, gestión de tareas en Trello |

---

## 📄 Licencia

Proyecto académico — SENA ADSO. Todos los derechos reservados al equipo de desarrollo.

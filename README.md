# Sistema de Gestión de Cementerio General

Este proyecto implementa un sistema de gestión completo para el Cementerio General de Quetzaltenango, permitiendo la administración de nichos, contratos, pagos, exhumaciones y personajes históricos mediante dos paneles de usuario: uno administrativo y otro de consulta.

## Tecnologías Utilizadas

-   **PHP**: 8.2.x
-   **Laravel**: 12.0
-   **Filament**: 3.3.x - Framework de administración para Laravel
-   **MariaDB**: 10.4.32 - Sistema de gestión de base de datos
-   **XAMPP**: v3.3.0 - Entorno de desarrollo local
-   **Composer**: 2.6.x - Gestor de dependencias de PHP
-   **Tailwind CSS**: 4.x - Framework CSS para el diseño
-   **Vite**: 6.2.x - Herramienta de compilación de frontend
-   **Alpine.js**: Integrado con Filament - Framework JS para interactividad

## Requisitos del Sistema

-   PHP 8.2 o superior
-   Composer 2.6.x o superior
-   Node.js 20.x y NPM 10.x
-   XAMPP 3.3.0 o superior con MariaDB 10.4.x
-   Espacio en disco: Mínimo 500MB

## Instalación

### 1. Preparar el entorno de desarrollo

1. Instalar XAMPP 3.3.0 desde [xampp-windows.org](https://www.apachefriends.org/download.html)
2. Iniciar los servicios de Apache y MySQL desde XAMPP Control Panel
3. Instalar Composer desde [getcomposer.org](https://getcomposer.org/download/)
4. Instalar Node.js desde [nodejs.org](https://nodejs.org/)

### 2. Configurar la base de datos

1. Abrir phpMyAdmin: http://localhost/phpmyadmin/
2. Crear una nueva base de datos llamada `cemetery_management`
3. Establecer collation como `utf8mb4_unicode_ci`

### 3. Clonar e instalar el proyecto

```bash
# Clonar el repositorio
git clone [url-del-repositorio] cemetery-management
cd cemetery-management

# Instalar dependencias PHP
composer install

# Instalar dependencias JavaScript
npm install

# Copiar el archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Configurar .env con credenciales de base de datos
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=cemetery_management
# DB_USERNAME=root
# DB_PASSWORD=

# Ejecutar migraciones y seeders
php artisan migrate --seed

# Compilar assets
npm run build

# Iniciar el servidor de desarrollo
php artisan serve
```

## Estructura del Proyecto

El proyecto sigue la estructura estándar de Laravel con adiciones específicas para el panel de administración Filament:

```
app/
  ├── Console/Commands/           # Comandos personalizados (ej: actualización de estados)
  ├── Filament/                   # Componentes del panel Filament
  │   ├── Consultation/           # Panel de consulta pública
  │   │   └── Pages/              # Páginas del panel de consulta
  │   ├── Pages/                  # Páginas del panel administrativo
  │   ├── Resources/              # Recursos del panel administrativo (CRUD)
  │   └── Widgets/                # Widgets para dashboards
  ├── Http/
  │   ├── Controllers/            # Controladores de la aplicación
  │   └── Middleware/             # Middlewares personalizados
  ├── Models/                     # Modelos Eloquent
  ├── Observers/                  # Observadores de modelos
  └── Policies/                   # Políticas de autorización

bootstrap/                        # Archivos de arranque de Laravel
config/                           # Archivos de configuración
database/                         # Migraciones y seeders
public/                           # Archivos públicos (CSS, JS compilados)
resources/                        # Recursos (vistas, assets sin compilar)
routes/                           # Definición de rutas
```

## Arquitectura del Sistema

### Modelos y Relaciones

El sistema se basa en varios modelos interrelacionados:

-   **Niche**: Representa un nicho en el cementerio con ubicación específica
-   **Contract**: Gestiona contratos de nichos vinculando personas (fallecidos y responsables)
-   **Person**: Almacena información de personas (tanto fallecidos como responsables)
-   **Deceased**: Contiene información específica de personas fallecidas
-   **Payment**: Administra pagos asociados a contratos
-   **Exhumation**: Gestiona solicitudes y procesos de exhumación
-   **HistoricalFigure**: Identifica personajes históricos con protección especial

### Middleware y Autenticación

El sistema implementa un sistema de control de acceso basado en roles mediante middlewares personalizados:

-   **CheckUserRole**: Verifica el rol del usuario y restringe el acceso según el panel
-   **RedirectBasedOnRole**: Redirige a los usuarios al panel correspondiente según su rol
-   **TrackUserLastLogin**: Registra la última vez que un usuario ingresó al sistema

Los roles disponibles son:

1. **Administrador**: Acceso completo a todas las funcionalidades
2. **Ayudante**: Acceso restringido al panel administrativo
3. **Auditor**: Acceso de solo lectura al panel administrativo
4. **Usuario de Consulta**: Acceso únicamente al panel de consulta

### Rutas y Navegación

El sistema utiliza principalmente dos rutas principales:

1. **/admin**: Panel administrativo para la gestión completa del cementerio

    - Acceso restringido a roles Administrador, Ayudante y Auditor
    - Incluye gestión completa de nichos, contratos, pagos, exhumaciones

2. **/consulta**: Panel de consulta para usuarios finales
    - Acceso para usuarios con rol "Usuario de Consulta"
    - Permite realizar búsquedas de nichos, ver contratos propios, solicitar pagos y exhumaciones

La ruta raíz (/) implementa redirección inteligente según el rol del usuario autenticado.

## Funcionalidades Principales

### Panel Administrativo

-   Gestión completa de nichos, contratos, pagos y exhumaciones
-   Reportes y estadísticas en dashboard
-   Gestión de personajes históricos
-   Procesamiento de pagos y solicitudes de exhumación
-   Actualización automática de estados de contratos

### Panel de Consulta

-   Búsqueda de nichos por código, nombre de fallecido o ubicación
-   Visualización de contratos asociados al usuario
-   Solicitud de boletas de pago para renovación
-   Solicitud de exhumaciones

## Comandos Personalizados

El sistema implementa varios comandos Artisan personalizados:

-   `php artisan app:create-admin-user`: Crea un usuario administrador con un registro de persona
-   `php artisan app:update-contract-statuses`: Actualiza automáticamente los estados de contratos basado en fechas

## Contribución

Para contribuir al proyecto:

1. Crear una rama para la nueva funcionalidad (`git checkout -b feature/nueva-funcionalidad`)
2. Realizar cambios y tests
3. Enviar pull request para revisión

## Licencia

Este proyecto se encuentar licenciado bajo [MIT license](https://opensource.org/licenses/MIT).

## Autor

-   #### Diego José Maldonado Monterroso.
-   #### Carné: 201931811.
-   #### Fecha: jueves 02 de Mayo de 2025.

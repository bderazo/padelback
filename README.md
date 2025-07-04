# Sports Reservations Backend ⚽

**Backend API para gestión de reservas padel.**  
Construido con [Laravel 9.52.20](https://laravel.com/) y PHP 8.0.30.

![Laravel](https://img.shields.io/badge/Laravel-9.52.20-red?logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.0.30-blue?logo=php)
![License](https://img.shields.io/badge/license-MIT-green)

## 🚀 Tecnologías utilizadas

-   ⚙️ **Laravel Framework 9.52.20**
-   🐘 **PHP 8.0.30**
-   🗃️ Motor de base de datos SQL (MySQL, PostgreSQL, etc.)

---

## 📦 Instalación

1. **Clona el repositorio**

    ```bash
    git clone https://github.com/tellxmaster/sports-reservations-backend.git
    cd sports-reservations-backend
    ```

2. **Instala dependencias de PHP**

    ```bash
    composer install
    ```

3. **Copia el archivo `.env`**

    ```bash
    cp .env.example .env
    ```

4. **Configura tu base de datos** en el archivo `.env`.

5. **Genera la clave de la app**

    ```bash
    php artisan key:generate
    ```

6. **Ejecuta migraciones y seeders**

    ```bash
    php artisan migrate:fresh --seed
    ```

7. **Inicia el servidor local**

    ```bash
    php artisan serve
    ```

## 📁 Estructura básica

-   `app/` – Lógica del negocio
-   `routes/api.php` – Rutas de la API REST
-   `database/seeders/` – Datos iniciales para pruebas

## Diagrama BD

## 📝 Licencia

Este proyecto está bajo licencia [MIT](LICENSE).

Desarrollado por [@tellxmaster](https://github.com/tellxmaster)

# MedicinasApp - Aplicación de Adherencia a Tratamientos

**MedicinasApp** es una Aplicación Web Progresiva (PWA) desarrollada en Laravel 12, diseñada para asistir a pacientes en la gestión de sus tratamientos médicos. Permite a los usuarios registrar medicamentos, recibir notificaciones push nativas como recordatorios, gestionar su stock y generar reportes de adherencia en PDF para compartir con profesionales de la salud.

La aplicación está diseñada con un enfoque en la accesibilidad, incluyendo una interfaz de usuario clara, botones grandes y un tema claro/oscuro.

## ✨ Características Principales

* **Gestión de Medicamentos (CRUD):** Registrar medicamentos con foto, dosis (incluyendo unidades, 1/2, 1/4 y gotas) y frecuencia.
* **Calendario Automático:** El sistema genera automáticamente el calendario de tomas futuras basándose en la hora de inicio y la frecuencia.
* **Notificaciones Push (PWA):** Envía alertas de tomas a nivel del sistema operativo (Windows, Android, etc.) que funcionan incluso si el navegador está cerrado.
* **Checklist de Tomas:** Lógica de "ventana de tolerancia" (+/- 30 min) para marcar tomas, con estados visuales (Activa, Omitida, Futura, Completada).
* **Gestión de Stock Avanzada:**
    * **Contable (Pastillas):** Descuenta automáticamente el stock al marcar una toma.
    * **Incontable (Goteros, Inyectables):** Permite al usuario marcar manualmente cuándo se ha agotado una unidad.
* **Reportes en PDF:** Genera y visualiza reportes de adherencia bajo demanda, con estadísticas y el logo de la app.
* **Diseño Accesible:** Interfaz limpia con botones grandes, iconos visuales para dosis y tema claro/oscuro.

## 🛠️ Stack Tecnológico

* **Backend:** PHP 8.3 / Laravel 12
* **Frontend:** Blade / Tailwind CSS / Alpine.js
* **Base de Datos:** MySQL
* **Notificaciones:** PWA (Service Workers) y `laravel-notification-channels/webpush`
* **Reportes:** `barryvdh/laravel-dompdf`

---

## 📋 Requisitos Previos

Asegúrate de tener el siguiente software instalado en tu máquina:

* **Servidor Local:** XAMPP o Laragon (Recomendado).
* **Base de Datos:** Un servidor MySQL.
* **PHP:** v8.2 o superior.
* **Composer:** [GetComposer.org](https://getcomposer.org/)
* **Node.js y npm:** [Nodejs.org](https://nodejs.org/en) (v18+)

---

## 🚀 Guía de Instalación Local

Sigue estos pasos para desplegar el proyecto desde un archivo `.zip` (o clonarlo).

### 1. Preparar el Proyecto

1.  **Descomprimir/Clonar:** Descomprime el `.zip` del proyecto en tu carpeta de desarrollo (ej. `C:\laragon\www\` o `C:\xampp\htdocs\`).
2.  **Abrir Terminal:** Abre una terminal (Git Bash, MINGW64, etc.) y navega a la raíz de la carpeta del proyecto.
    ```bash
    cd C:\laragon\www\medicinas-app
    ```

### 2. Instalar Dependencias

1.  **Dependencias de PHP (Composer):**
    ```bash
    composer install
    ```
2.  **Dependencias de JS (npm):**
    ```bash
    npm install
    ```

### 3. Configuración del Entorno (.env)

1.  **Copiar el Archivo `.env`:**
    ```bash
    cp .env.example .env
    ```
2.  **Generar Clave de la App:**
    ```bash
    php artisan key:generate
    ```
3.  **Configurar la Base de Datos (MySQL):**
    * Abre tu gestor de base de datos (phpMyAdmin, HeidiSQL) y crea una nueva base de datos vacía.
        * Nombre recomendado: `medicinas_app`
    * Abre el archivo `.env` y modifica estas líneas con tus credenciales:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=medicinas_app
    DB_USERNAME=root
    DB_PASSWORD=tu_contraseña_de_mysql (déjalo vacío si no tienes)
    ```

4.  **Configurar Notificaciones Push (VAPID):**
    * Este comando generará las claves `VAPID_PUBLIC_KEY` y `VAPID_PRIVATE_KEY` y las añadirá a tu `.env` automáticamente.
    ```bash
    php artisan webpush:vapid
    ```
    * **¡Solución de Problemas (Windows)!** Si este comando falla con un error de `Unable to create the key`, es un problema conocido de OpenSSL en Windows. Ejecuta esto primero (ajustando la ruta a tu versión de PHP en Laragon/XAMPP) y luego reintenta el comando `webpush:vapid`:
        ```bash
        # Ejemplo para Laragon con PHP 8.3
        export OPENSSL_CONF=C:/laragon/bin/php/php-8.3.13-Win32-vs16-x64/extras/ssl/openssl.cnf
        ```

5.  **Configurar Idioma:**
    * Asegúrate de que estas líneas estén en tu `.env` para que la app esté en español.
    ```env
    APP_LOCALE=es
    APP_FALLBACK_LOCALE=es
    APP_FAKER_LOCALE=es_ES
    ```

### 4. Preparar la Aplicación

1.  **Migrar la Base de Datos:** Este comando creará todas las tablas (`users`, `medications`, `takes`, `push_subscriptions`, etc.).
    ```bash
    php artisan migrate
    ```
2.  **Crear el Enlace Simbólico:** Esto permite que las fotos de los medicamentos (guardadas en `storage/app/public`) sean visibles en la carpeta `public/storage`.
    ```bash
    php artisan storage:link
    ```
3.  **Publicar Idiomas:** Para que las traducciones de Laravel (ej. errores de validación) estén disponibles en español.
    ```bash
    php artisan lang:publish
    ```

---

## 🏃‍♂️ Cómo Ejecutar la Aplicación

Para que todas las características (la app, el frontend y las notificaciones push) funcionen, necesitas tener **3 terminales** abiertas y ejecutando procesos simultáneamente.

### Terminal 1: Servidor de Laravel
Ejecuta el servidor web principal.
```bash
php artisan serve

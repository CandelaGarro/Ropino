# Ropino

Sistema de reservas en PHP con arquitectura MVC sin frameworks para la gestion de:

- alojamientos
- mesas de restaurante
- usuarios con roles `admin` y `cliente`
- reservas con confirmacion/cancelacion
- panel de administracion y reportes

## Requisitos

- XAMPP con:
  - Apache
  - MariaDB / MySQL
  - PHP 8.1 o superior
- Composer
- Navegador web

## Instalacion

### 1. Copiar el proyecto

Coloca la carpeta del proyecto en:

```text
C:\xampp\htdocs\RESTAURANTE-ROPINO
```

### 2. Instalar dependencias

Desde la raiz del proyecto ejecuta:

```bash
composer install
```

El proyecto utiliza:

- `phpmailer/phpmailer`

### 3. Crear la base de datos

Crea una base de datos llamada:

```text
ropino
```

Despues importa el archivo SQL entregado junto al proyecto desde `phpMyAdmin` o desde consola.

Ejemplo en consola:

```bash
mysql -u root -p ropino < ruta\\a\\tu\\archivo.sql
```

Nota:

- La conexion actual esta definida en [database.php](/C:/xampp/htdocs/RESTAURANTE-ROPINO/src/config/database.php)
- Por defecto usa:
  - host: `127.0.0.1`
  - base de datos: `ropino`
  - usuario: `root`
  - password: vacia

Si necesitas cambiar estos valores, edita:

- [database.php](/C:/xampp/htdocs/RESTAURANTE-ROPINO/src/config/database.php)

### 4. Configurar el correo

Copia la plantilla:

- [app.local.example.php](/C:/xampp/htdocs/RESTAURANTE-ROPINO/src/config/app.local.example.php)

y crea este archivo privado fuera de `htdocs`:

```text
C:\xampp\private\RESTAURANTE-ROPINO\app.local.php
```

Contenido esperado:

```php
<?php

define("MAIL_HOST", "smtp.gmail.com");
define("MAIL_PORT", 587);
define("MAIL_USERNAME", "tu-email@gmail.com");
define("MAIL_PASSWORD", "tu-clave-o-app-password");
define("MAIL_ENCRYPTION", "tls");
define("MAIL_FROM_ADDRESS", "tu-email@gmail.com");
define("MAIL_FROM_NAME", "Ropino");
define("MAIL_ADMIN_ADDRESS", "tu-email@gmail.com");
```

Si no configuras el correo, la aplicacion puede funcionar, pero las notificaciones por email no llegaran correctamente.

### 5. Iniciar servicios

En XAMPP arranca:

- Apache
- MySQL

### 6. Abrir la aplicacion

Pagina publica:

[http://localhost/RESTAURANTE-ROPINO/index.php?page=home](http://localhost/RESTAURANTE-ROPINO/index.php?page=home)

Login:

[http://localhost/RESTAURANTE-ROPINO/index.php?page=login](http://localhost/RESTAURANTE-ROPINO/index.php?page=login)

## Crear un administrador

Si la base de datos no incluye un usuario administrador, puedes crearlo por consola:

```bash
php scripts/create_admin.php --nombre="Admin" --email="admin@ropino.com" --password="TuClaveSegura123"
```

Script:

- [create_admin.php](/C:/xampp/htdocs/RESTAURANTE-ROPINO/scripts/create_admin.php)

## Estructura del proyecto

```text
RESTAURANTE-ROPINO/
|- assets/          Recursos estaticos (css, js, imagenes, pdf)
|- src/
|  |- config/       Configuracion base
|  |- controllers/  Controladores
|  |- helpers/      Autenticacion, validacion y correo
|  `- models/       Acceso a datos
|- scripts/         Scripts internos de consola
|- storage/         Cola de correo y almacenamiento interno
|- vendor/          Dependencias Composer
|- views/           Vistas del sistema
|- index.php        Router principal
`- logout.php       Cierre de sesion
```

## Funcionalidades principales

- Registro e inicio de sesion
- Roles `cliente` y `admin`
- Reserva de alojamientos
- Reserva de mesas con control de solapes
- Confirmacion y cancelacion de reservas
- Panel de administracion
- CRUD de mesas
- CRUD de alojamientos
- Reportes operativos
- Envio de correos con cola interna

## Notas tecnicas

- La aplicacion sigue un MVC sencillo sin frameworks.
- Las rutas internas sensibles estan protegidas con `.htaccess`.
- La configuracion privada del correo queda fuera de `htdocs`.
- La cola de correos se guarda en [storage/mail-queue](/C:/xampp/htdocs/RESTAURANTE-ROPINO/storage/mail-queue).

## Problemas comunes

### No conecta con la base de datos

Revisa:

- que `MySQL` este arrancado en XAMPP
- que exista la base de datos `ropino`
- que los datos de [database.php](/C:/xampp/htdocs/RESTAURANTE-ROPINO/src/config/database.php) sean correctos

### No llegan los correos

Revisa:

- que exista `C:\xampp\private\RESTAURANTE-ROPINO\app.local.php`
- que el usuario y password SMTP sean correctos
- que Gmail permita el uso de la app password si se usa esa opcion

### Error al crear admin

Revisa:

- que la base de datos este importada
- que el email no exista ya
- que la contrasena tenga al menos 8 caracteres

## Autor

Trabajo Fin de Grado de DAW sobre un sistema de reservas de mesas y habitaciones desarrollado en PHP.

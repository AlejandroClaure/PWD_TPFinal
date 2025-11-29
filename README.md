# 📱 Celulandia – Sistema de Gestión y Tienda Online
--
Proyecto Final – Programación Web Dinámica
--
Celulandia es una aplicación web desarrollada en PHP bajo arquitectura MVC. Permite gestionar usuarios, roles, productos, compras y un carrito de compras funcional. Incluye autenticación segura, operaciones ABM completas y un panel de administración.

🚀 Características Principales
--

### 🛒 Carrito de Compras

    Agregar, quitar y modificar items.

    Validación de stock en tiempo real.

    Registro de compras e items en base de datos.

## 👤 Gestión de Usuarios

    Registro e inicio de sesión.

    Asignación de roles.

    Habilitar/deshabilitar usuarios.

    ABM completo mediante controladores.

### 🔐 Seguridad y Roles

    Manejo de sesiones (Session.php).

    Autorización basada en roles (admin/cliente).

    Asignación de permisos por usuario.

### 📦 Gestión de Productos

    ABM de productos.

    Control de stock.

    Carga de imágenes en /Archivos.

### 🧱 Arquitectura MVC

    PWD_TPFinal/
    │── Archivos/        # Imágenes y uploads
    │── Control/         # Controladores y acciones
    │── Modelo/          # Clases de negocio y ABM
    │── Vista/           # Interfaz HTML/PHP
    │── util/            # Funciones globales
    │── logs/            # Errores del sistema
    │── configuracion.php
    │── index.php

### 🛠 Tecnologías Utilizadas

    Tecnología	Propósito
    PHP 8+	Backend
    phpMySQL Base de datos
    PDO	Conexión segura
    Bootstrap 5	Estilos y UI
    JavaScript / AJAX	Actualización dinámica
    Composer	Autoloading / phpMailer - domPdf
    reCAPTCHA v2

### 📚 Base de Datos

    Incluye el archivo:

    bdcarritocompras.sql


### Tablas principales:

    usuario

    rol

    usuariorol

    producto

    compra

    compraitem

    compraestado

    compraestado tipo

    menu / menurol (si se incluye sistema de menús)

### ⚙️ Instalación

    1️⃣ Clonar el repositorio
        git clone https://github.com/tuusuario/Celulandia.git

    2️⃣ Mover a XAMPP / Laragon
        /xampp/htdocs/PWD_TPFinal

    3️⃣ Configurar base de datos

        Importar bdcarritocompras.sql desde phpMyAdmin.

    4️⃣ Configurar configuracion.php
        $CONFIG['db'] = [
            'host' => 'localhost',
            'user' => 'root',
            'pass' => '',
            'name' => 'bdcarritocompras'
        ];

    5️⃣ Ejecutar

        Abrir en navegador:

        http://localhost/PWD_TPFinal/

### 🧪 Usuarios de Prueba

    Rol	           Usuario	       Contraseña
    Admin	        admin	        admin1234
    Admin-cliente	pepi	           123

### 📂 Estructura del Proyecto Completa

    PWD_TPFinal/
    ├── Archivos/           # Imágenes cargadas
    ├── Control/
    │   ├── Acciones/       # Acciones directas (headers, JSON, etc.)
    │   ├── Abm*.php        # Controladores ABM
    │   └── Session.php
    ├── Modelo/
    │   ├── Usuario.php
    │   ├── Producto.php
    │   ├── Compra.php
    │   ├── CompraItem.php
    │   └── (otros modelos)
    ├── Vista/
    │   ├── login/
    │   ├── panelAdmin/
    │   ├── productos/
    │   └── carrito/
    ├── util/
    │   ├── funciones.php   # data_submitted(), verEstructura(), etc.
    │   └── helpers.php
    ├── logs/
    ├── bdcarritocompras.sql
    ├── configuracion.php
    └── index.php

### 🔒 Seguridad

    Validación de entradas con data_submitted().

    Consultas preparadas con PDO.

    Control de acceso por rol.

    Manejo correcto de sesiones.

    Redirecciones encapsuladas en acciones (acciones/login, acciones/menus, etc).


### 📄 Licencia

    Uso libre para fines educativos.

### 👩‍💻 Autores

    Alejandro Claure
    Cyntia Nasabun

    Tecnicatura Universitaria en Desarrollo Web - Universidad Nacional del Comahue
    Argentina 🇦🇷 -  2025
<?php
$esAdmin = ($usuarioActual["rol"] ?? "") === "admin";
$saludoTopbar = $esAdmin ? "Admin: " . ($usuarioActual["nombre"] ?? "") : "Bienvenido, " . ($usuarioActual["nombre"] ?? "");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuracion</title>
    <link rel="stylesheet" href="/RESTAURANTE-ROPINO/assets/css/styles.css">
    <script src="/RESTAURANTE-ROPINO/assets/js/script.js"></script>
</head>

<body>
    <div class="app">
        <aside class="sidebar" id="sidebar-navigation">
            <div class="logo">
                <div class="logo-circulo"></div>
                <div class="logo-texto">Ropino</div>
            </div>

            <div class="menu-titulo">MENU</div>

            <nav class="menu">
                <?php if ($esAdmin): ?>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=panel">Inicio</a>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=admin_reservas">Reservas</a>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=admin_habitaciones">Alojamientos</a>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=admin_mesas">Mesas</a>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=reportes">Reportes</a>
                <?php else: ?>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=home">Inicio</a>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=reservas">Mis reservas</a>
                <?php endif; ?>
                <a href="/RESTAURANTE-ROPINO/index.php?page=mis_datos">Mis datos</a>
                <a class="activo" href="/RESTAURANTE-ROPINO/index.php?page=configuracion">Configuracion</a>
            </nav>
        </aside>

        <div class="sidebar-overlay" id="sidebar-overlay" aria-hidden="true"></div>

        <div class="contenido">
            <header class="topbar">
                <button
                    class="menu-toggle"
                    id="menu-toggle"
                    type="button"
                    aria-label="Abrir menu de navegacion"
                    aria-controls="sidebar-navigation"
                    aria-expanded="false">
                    <span class="menu-toggle__lines" aria-hidden="true">
                        <span class="menu-toggle__line"></span>
                        <span class="menu-toggle__line"></span>
                        <span class="menu-toggle__line"></span>
                    </span>
                    <span class="menu-toggle__text">Menu</span>
                </button>

                <div class="usuario-box">
                    <div class="avatar"></div>
                    <span><?= htmlspecialchars($saludoTopbar) ?></span>
                </div>

                <div class="icono-perfil">U</div>

                <form class="logout-form" action="/RESTAURANTE-ROPINO/logout.php" method="POST">
                    <?= csrfInput() ?>
                    <button class="btn-salir" type="submit">Cerrar sesion</button>
                </form>
            </header>

            <main class="main">
                <div class="card-principal">
                    <div class="titulo-seccion">Configuracion</div>
                    <?php renderFlashMessages(); ?>

                    <p class="page-intro">
                        Este apartado te permite reforzar el acceso a tu cuenta cambiando la contrasena
                        cuando lo necesites.
                    </p>

                    <div class="perfil-grid">
                        <div class="perfil-card">
                            <h3>Cuenta</h3>
                            <p><?= htmlspecialchars($usuarioActual["email"]) ?></p>
                        </div>

                        <div class="perfil-card">
                            <h3>Rol</h3>
                            <p><?= htmlspecialchars($rolLabel) ?></p>
                        </div>

                        <div class="perfil-card">
                            <h3>Recomendacion</h3>
                            <p>Usa una contrasena unica y de al menos 8 caracteres.</p>
                        </div>
                    </div>

                    <section class="bloque">
                        <h2>Cambiar contrasena</h2>

                    <form class="form-cuenta" action="/RESTAURANTE-ROPINO/src/controllers/cuentaController.php" method="POST">
                            <?= csrfInput() ?>

                            <label for="password_actual">
                                Contrasena actual
                                <input type="password" id="password_actual" name="password_actual" required>
                            </label>

                            <label for="password_nueva">
                                Nueva contrasena
                                <input type="password" id="password_nueva" name="password_nueva" minlength="8" required>
                            </label>

                            <label for="password_confirmacion">
                                Confirmar nueva contrasena
                                <input type="password" id="password_confirmacion" name="password_confirmacion" minlength="8" required>
                            </label>

                            <button class="auth-btn" type="submit" name="cambiar_password">Actualizar contrasena</button>
                        </form>
                    </section>
                </div>
            </main>
        </div>
    </div>
</body>

</html>

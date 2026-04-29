<?php
$esAdmin = ($usuarioActual["rol"] ?? "") === "admin";
$saludoTopbar = $esAdmin ? "Bienvenid@, " . ($usuarioActual["nombre"] ?? "") : "Bienvenid@, " . ($usuarioActual["nombre"] ?? "");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis datos</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
    <script src="<?= BASE_URL ?>/assets/js/script.js"></script>
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
                    <a href="<?= BASE_URL ?>/index.php?page=panel">Inicio</a>
                    <a href="<?= BASE_URL ?>/index.php?page=admin_reservas">Reservas</a>
                    <a href="<?= BASE_URL ?>/index.php?page=admin_habitaciones">Alojamientos</a>
                    <a href="<?= BASE_URL ?>/index.php?page=admin_mesas">Mesas</a>
                    <a href="<?= BASE_URL ?>/index.php?page=reportes">Reportes</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/index.php?page=home">Inicio</a>
                    <a href="<?= BASE_URL ?>/index.php?page=reservas">Mis reservas</a>
                <?php endif; ?>
                <a class="activo" href="<?= BASE_URL ?>/index.php?page=mis_datos">Mis datos</a>
                <a href="<?= BASE_URL ?>/index.php?page=configuracion">Configuracion</a>
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

                <form class="logout-form" action="<?= BASE_URL ?>/logout.php" method="POST">
                    <?= csrfInput() ?>
                    <button class="btn-salir" type="submit">Cerrar sesion</button>
                </form>
            </header>

            <main class="main">
                <div class="card-principal">
                    <div class="titulo-seccion">Mis datos</div>
                    <?php renderFlashMessages(); ?>

                    <p class="page-intro">
                        Desde aqui puedes mantener actualizada tu informacion de acceso para que el sistema
                        de reservas tenga siempre tus datos correctos.
                    </p>

                    <div class="perfil-grid">
                        <div class="perfil-card">
                            <h3>Rol</h3>
                            <p><?= htmlspecialchars($rolLabel) ?></p>
                        </div>

                        <div class="perfil-card">
                            <h3>Nombre actual</h3>
                            <p><?= htmlspecialchars($usuarioActual["nombre"]) ?></p>
                        </div>

                        <div class="perfil-card">
                            <h3>Email actual</h3>
                            <p><?= htmlspecialchars($usuarioActual["email"]) ?></p>
                        </div>
                    </div>

                    <section class="bloque">
                        <h2>Actualizar perfil</h2>

                    <form class="form-cuenta" action="<?= BASE_URL ?>/src/controllers/cuentaController.php" method="POST">
                            <?= csrfInput() ?>

                            <label for="nombre">
                                Nombre completo
                                <input
                                    type="text"
                                    id="nombre"
                                    name="nombre"
                                    maxlength="80"
                                    value="<?= htmlspecialchars(oldInput("nombre", $usuarioActual["nombre"])) ?>"
                                    required>
                            </label>

                            <label for="email">
                                Correo electronico
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    maxlength="120"
                                    value="<?= htmlspecialchars(oldInput("email", $usuarioActual["email"])) ?>"
                                    required>
                            </label>

                            <button class="auth-btn" type="submit" name="actualizar_datos">Guardar cambios</button>
                        </form>
                    </section>
                </div>
            </main>
        </div>
    </div>
</body>

</html>


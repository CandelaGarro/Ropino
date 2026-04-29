<?php
$usuarioActual = $usuarioActual ?? null;
$rol = $usuarioActual["rol"] ?? "";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casa rural - Ropino</title>
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
                <a href="/RESTAURANTE-ROPINO/index.php?page=home">Inicio</a>

                <?php if ($rol === "cliente"): ?>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=reservas">Mis reservas</a>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=mis_datos">Mis datos</a>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=configuracion">Configuracion</a>
                <?php elseif ($rol === "admin"): ?>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=panel">Panel admin</a>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=admin_reservas">Reservas</a>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=reportes">Reportes</a>
                <?php endif; ?>
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

                <?php if ($usuarioActual): ?>
                    <div class="usuario-box">
                        <div class="avatar"></div>
                        <span><?= htmlspecialchars($usuarioActual["nombre"] ?? "") ?></span>
                    </div>

                    <div class="icono-perfil">U</div>

                    <form class="logout-form" action="/RESTAURANTE-ROPINO/logout.php" method="POST">
                        <?= csrfInput() ?>
                        <button class="btn-salir" type="submit">Cerrar sesion</button>
                    </form>
                <?php else: ?>
                    <div style="margin-left:auto;">
                        <a class="btn-salir" href="/RESTAURANTE-ROPINO/index.php?page=login">Iniciar sesion</a>
                    </div>
                <?php endif; ?>
            </header>

            <main class="main">
                <div class="card-principal">
                    <section class="info-hero info-hero--casa">
                        <div class="home-hero__media" aria-hidden="true">
                            <div class="home-hero__slide active" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi1.png');"></div>
                            <div class="home-hero__slide" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi2.png');"></div>
                            <div class="home-hero__slide" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi3.png');"></div>
                            <div class="home-hero__slide" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi4.png');"></div>
                        </div>

                        <div class="home-hero__overlay">
                            <div class="home-hero__content">
                                <span class="home-badge">Casa rural - Grupos - Entorno natural</span>
                                <h1>Ropino</h1>
                                <p>
                                    Una opcion pensada para grupos que buscan amplitud, privacidad
                                    y una experiencia completa en plena naturaleza.
                                </p>
                            </div>

                            <div class="home-hero__dots" id="home-hero-dots">
                                <button type="button" class="home-hero-dot active" data-index="0" aria-label="Ir a imagen 1"></button>
                                <button type="button" class="home-hero-dot" data-index="1" aria-label="Ir a imagen 2"></button>
                                <button type="button" class="home-hero-dot" data-index="2" aria-label="Ir a imagen 3"></button>
                                <button type="button" class="home-hero-dot" data-index="3" aria-label="Ir a imagen 4"></button>
                            </div>
                        </div>
                    </section>

                    <section class="info-grid">
                        <article class="info-card">
                            <h2>2a planta</h2>
                            <ul class="info-list">
                                <li>Disponemos de 7 habitaciones dobles, 5 de matrimonio con cama de 150 cm y 2 con dos camas de 90 cm.</li>
                                <li>Todas las habitaciones cuentan con aseo individual con ducha, TV plana de 32", calefaccion y aire acondicionado.</li>
                                <li>Conexion wifi gratuita en todas las habitaciones.</li>
                            </ul>
                        </article>

                        <article class="info-card">
                            <h2>1a planta</h2>
                            <ul class="info-list">
                                <li>Para reservas de mas de 14 personas disponemos de 4 apartamentos en planta baja.</li>
                                <li>Cada apartamento tiene 2 habitaciones, una con cama de matrimonio de 150 cm y otra con 2 camas de 90 cm.</li>
                                <li>Tambien incluyen aseo con ducha, salon comedor, terraza exterior, cocina, microondas, lavadora, nevera, TV, calefaccion y aire acondicionado.</li>
                                <li>Equipamiento incluido: toallas, sabanas y menaje de cocina.</li>
                            </ul>
                        </article>
                    </section>

                    <section class="info-grid">
                        <article class="info-card">
                            <h2>Salon mirador</h2>
                            <p>
                                La casa rural para grupos dispone de un salon mirador de 100 metros cuadrados,
                                climatizado y totalmente equipado, con barras americanas, cocinas completas,
                                zona de estar con sofas y zona de comedor.
                            </p>
                        </article>

                        <article class="info-card">
                            <h2>Tarifas y precios</h2>
                            <ul class="info-list">
                                <li>Estancia minima: 2 noches</li>
                                <li>1300 EUR por estancia</li>
                                <li>650 EUR por noche</li>
                                <li>Fianza reembolsable: 300 EUR</li>
                            </ul>
                        </article>
                    </section>

                    <section class="bloque restaurante-galeria-bloque">
                        <h2>Galeria de la casa rural</h2>
                        <p class="page-intro">
                            Imagenes del alojamiento y del espacio pensado para grupos.
                        </p>

                        <div class="slider-comida" id="slider-comida">
                            <button class="slider-comida-btn slider-comida-btn-prev" type="button" id="slider-comida-prev" aria-label="Anterior">
                                &lsaquo;
                            </button>

                            <div class="slider-comida-viewport">
                                <div class="slider-comida-track" id="slider-comida-track">
                                    <div class="slider-comida-slide active">
                                        <img src="/RESTAURANTE-ROPINO/assets/img/salon/salon1.png" alt="Casa rural 1">
                                    </div>
                                    <div class="slider-comida-slide">
                                        <img src="/RESTAURANTE-ROPINO/assets/img/salon/salon2.png" alt="Casa rural 2">
                                    </div>
                                    <div class="slider-comida-slide">
                                        <img src="/RESTAURANTE-ROPINO/assets/img/salon/salon3.png" alt="Casa rural 3">
                                    </div>
                                    <div class="slider-comida-slide">
                                        <img src="/RESTAURANTE-ROPINO/assets/img/salon/salon6.png" alt="Casa rural 4">
                                    </div>
                                </div>
                            </div>

                            <button class="slider-comida-btn slider-comida-btn-next" type="button" id="slider-comida-next" aria-label="Siguiente">
                                &rsaquo;
                            </button>
                        </div>

                        <div class="slider-comida-dots" id="slider-comida-dots">
                            <button type="button" class="slider-dot active" data-index="0" aria-label="Ir a imagen 1"></button>
                            <button type="button" class="slider-dot" data-index="1" aria-label="Ir a imagen 2"></button>
                            <button type="button" class="slider-dot" data-index="2" aria-label="Ir a imagen 3"></button>
                            <button type="button" class="slider-dot" data-index="3" aria-label="Ir a imagen 4"></button>
                        </div>
                    </section>

                    <section class="info-cta">
                        <h2>Quieres reservar la casa rural?</h2>
                        <p>
                            Accede al sistema y elige la opcion de alojamiento que mejor encaje con tu grupo.
                        </p>

                        <div class="info-cta__actions">
                            <?php if ($rol === "cliente"): ?>
                                <a class="btn-reservar" href="/RESTAURANTE-ROPINO/index.php?page=reservas">Reservar alojamiento</a>
                            <?php else: ?>
                                <a class="btn-reservar" href="/RESTAURANTE-ROPINO/index.php?page=login">Iniciar sesion</a>
                            <?php endif; ?>

                            <a class="home-btn-secondary info-btn-dark" href="/RESTAURANTE-ROPINO/index.php?page=home">Volver al inicio</a>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>
</body>

</html>

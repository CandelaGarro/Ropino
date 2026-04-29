<?php
$usuarioActual = $usuarioActual ?? null;
$rol = $usuarioActual["rol"] ?? "";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apartamentos - Ropino</title>
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
                    <section class="info-hero info-hero--apartamentos">
                        <div class="home-hero__media" aria-hidden="true">
                            <div class="home-hero__slide active" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi1.png');"></div>
                            <div class="home-hero__slide" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi2.png');"></div>
                            <div class="home-hero__slide" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi3.png');"></div>
                            <div class="home-hero__slide" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi4.png');"></div>
                        </div>

                        <div class="home-hero__overlay">
                            <div class="home-hero__content">
                                <span class="home-badge">Apartamentos - 4 personas - Entorno natural</span>
                                <h1>Ropino</h1>
                                <p>
                                    Apartamentos independientes pensados para estancias comodas,
                                    tranquilas y practicas, perfectos para parejas, familias o
                                    pequenos grupos que quieran disfrutar de la zona con mas independencia.
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
                            <h2>Que ofrecen</h2>
                            <ul class="info-list">
                                <li>En la primera planta disponemos de 4 apartamentos con capacidad para familias o pequenos grupos.</li>
                                <li>Cada apartamento cuenta con dos habitaciones: una con cama de matrimonio de 150 cm y otra con dos camas individuales de 90 cm.</li>
                                <li>Incluyen aseo con ducha, salon comedor amplio y cocina equipada con microondas, lavadora y nevera.</li>
                                <li>Tambien disponen de terraza exterior, television plana, calefaccion y aire acondicionado.</li>
                            </ul>
                        </article>

                        <article class="info-card">
                            <h2>Equipamiento incluido</h2>
                            <ul class="info-list">
                                <li>Toallas y sabanas limpias para la estancia.</li>
                                <li>Menaje completo de cocina.</li>
                                <li>Conexion wifi gratuita en todos los apartamentos.</li>
                            </ul>
                        </article>
                    </section>

                    <section class="info-grid">
                        <article class="info-card">
                            <h2>Tarifas y precios</h2>
                            <ul class="info-list">
                                <li>Estancia minima: 2 noches</li>
                                <li>280 EUR por estancia</li>
                                <li>140 EUR por noche</li>
                                <li>Fianza reembolsable: 100 EUR</li>
                            </ul>
                        </article>
                    </section>

                    <section class="bloque restaurante-galeria-bloque">
                        <h2>Galeria de apartamentos</h2>
                        <p class="page-intro">
                            Una seleccion de imagenes del alojamiento y del entorno.
                        </p>

                        <div class="slider-comida" id="slider-comida">
                            <button class="slider-comida-btn slider-comida-btn-prev" type="button" id="slider-comida-prev" aria-label="Anterior">
                                &lsaquo;
                            </button>

                            <div class="slider-comida-viewport">
                                <div class="slider-comida-track" id="slider-comida-track">
                                    <div class="slider-comida-slide active">
                                        <img src="/RESTAURANTE-ROPINO/assets/img/apartamentos/ap1.png" alt="Apartamento o entorno 1">
                                    </div>
                                    <div class="slider-comida-slide">
                                        <img src="/RESTAURANTE-ROPINO/assets/img/apartamentos/ap2.png" alt="Apartamento o entorno 2">
                                    </div>
                                    <div class="slider-comida-slide">
                                        <img src="/RESTAURANTE-ROPINO/assets/img/apartamentos/ap4.png" alt="Apartamento o entorno 3">
                                    </div>
                                    <div class="slider-comida-slide">
                                        <img src="/RESTAURANTE-ROPINO/assets/img/apartamentos/ap5.png" alt="Apartamento o entorno 4">
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
                        <h2>Quieres reservar apartamento?</h2>
                        <p>
                            Accede al sistema y selecciona el apartamento que mejor encaje con tu estancia.
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

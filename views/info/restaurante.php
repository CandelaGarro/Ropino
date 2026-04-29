<?php
$usuarioActual = $usuarioActual ?? null;
$rol = $usuarioActual["rol"] ?? "";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurante - Ropino</title>
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
                    <section class="info-hero info-hero--restaurante">
                        <div class="home-hero__media" aria-hidden="true">
                            <div class="home-hero__slide active" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi1.png');"></div>
                            <div class="home-hero__slide" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi2.png');"></div>
                            <div class="home-hero__slide" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi3.png');"></div>
                            <div class="home-hero__slide" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi4.png');"></div>
                        </div>

                        <div class="home-hero__overlay">
                            <div class="home-hero__content">
                                <span class="home-badge">Salon - Terraza - Cocina tradicional</span>
                                <h1>Ropino</h1>
                                <p>
                                    Disfruta de la cocina de Ropino en un entorno natural, con
                                    platos tradicionales, carnes, pescados, postres caseros y
                                    una carta completa para compartir en familia o en grupo.
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
                            <h2>La experiencia</h2>
                            <p>
                                En el restaurante de Ropino puedes disfrutar de una comida tranquila
                                en un espacio agradable, ideal tanto para clientes alojados como para
                                quienes quieran venir solo a comer.
                            </p>
                        </article>

                        <article class="info-card">
                            <h2>Que encontraras</h2>
                            <ul class="info-list">
                                <li>Entrantes y platos tipicos</li>
                                <li>Carnes a la brasa</li>
                                <li>Pescados</li>
                                <li>Vinos, bebidas y cafes</li>
                                <li>Postres caseros y helados</li>
                            </ul>
                        </article>
                    </section>

                    <section class="bloque carta-pdf-bloque">
                        <h2>Carta del restaurante</h2>
                        <p class="page-intro">
                            Consulta la carta completa de Ropino o descargala en PDF.
                        </p>

                        <div class="carta-pdf-acciones">
                            <a
                                class="btn-reservar"
                                href="/RESTAURANTE-ROPINO/assets/docs/CartaRopino.pdf"
                                download="Carta-Ropino.pdf">
                                Descargar carta
                            </a>

                            <a
                                class="home-btn-secondary"
                                href="/RESTAURANTE-ROPINO/assets/docs/CartaRopino.pdf"
                                target="_blank"
                                rel="noopener noreferrer">
                                Abrir en grande
                            </a>
                        </div>

                        <div class="pdf-carta-wrap">
                            <object
                                data="/RESTAURANTE-ROPINO/assets/docs/CartaRopino.pdf"
                                type="application/pdf"
                                class="pdf-carta">
                                <p>
                                    No se ha podido mostrar la carta en esta pantalla.
                                    <a href="/RESTAURANTE-ROPINO/assets/docs/CartaRopino.pdf" target="_blank" rel="noopener noreferrer">
                                        Abrir carta
                                    </a>
                                </p>
                            </object>
                        </div>
                    </section>

                    <section class="bloque restaurante-galeria-bloque">
                        <h2>Galeria de comida</h2>
                        <p class="page-intro">
                            Una seleccion de imagenes de platos y presentaciones del restaurante.
                        </p>

                        <div class="slider-comida" id="slider-comida">
                            <button class="slider-comida-btn slider-comida-btn-prev" type="button" id="slider-comida-prev" aria-label="Anterior">
                                &lsaquo;
                            </button>

                            <div class="slider-comida-viewport">
                                <div class="slider-comida-track" id="slider-comida-track">
                                    <div class="slider-comida-slide active">
                                        <img src="/RESTAURANTE-ROPINO/assets/img/comida/comida1.png" alt="Plato 1 de Ropino">
                                    </div>
                                    <div class="slider-comida-slide">
                                        <img src="/RESTAURANTE-ROPINO/assets/img/comida/comida2.png" alt="Plato 2 de Ropino">
                                    </div>
                                    <div class="slider-comida-slide">
                                        <img src="/RESTAURANTE-ROPINO/assets/img/comida/comida3.png" alt="Plato 3 de Ropino">
                                    </div>
                                    <div class="slider-comida-slide">
                                        <img src="/RESTAURANTE-ROPINO/assets/img/comida/comida4.png" alt="Plato 4 de Ropino">
                                    </div>
                                    <div class="slider-comida-slide">
                                        <img src="/RESTAURANTE-ROPINO/assets/img/comida/comida5.png" alt="Plato 5 de Ropino">
                                    </div>
                                    <div class="slider-comida-slide">
                                        <img src="/RESTAURANTE-ROPINO/assets/img/comida/comida6.png" alt="Plato 6 de Ropino">
                                    </div>
                                    <div class="slider-comida-slide">
                                        <img src="/RESTAURANTE-ROPINO/assets/img/comida/comida7.png" alt="Plato 7 de Ropino">
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
                            <button type="button" class="slider-dot" data-index="4" aria-label="Ir a imagen 5"></button>
                            <button type="button" class="slider-dot" data-index="5" aria-label="Ir a imagen 6"></button>
                            <button type="button" class="slider-dot" data-index="6" aria-label="Ir a imagen 7"></button>
                        </div>
                    </section>

                    <section class="bloque restaurante-galeria-bloque">
                        <h2>Espacios del restaurante</h2>
                        <p class="page-intro">
                            Algunas imagenes del entorno y del propio restaurante.
                        </p>

                        <div class="galeria-comida-grid">
                            <figure class="galeria-comida-card">
                                <img src="/RESTAURANTE-ROPINO/assets/img/restaurante/rest1.jpeg" alt="Espacio del restaurante 1">
                            </figure>
                            <figure class="galeria-comida-card">
                                <img src="/RESTAURANTE-ROPINO/assets/img/restaurante/rest2.jpeg" alt="Espacio del restaurante 2">
                            </figure>
                            <figure class="galeria-comida-card">
                                <img src="/RESTAURANTE-ROPINO/assets/img/restaurante/rest3.jpeg" alt="Espacio del restaurante 3">
                            </figure>
                            <figure class="galeria-comida-card">
                                <img src="/RESTAURANTE-ROPINO/assets/img/restaurante/rest4.jpeg" alt="Espacio del restaurante 4">
                            </figure>
                        </div>
                    </section>

                    <section class="info-cta">
                        <h2>Quieres reservar mesa?</h2>
                        <p>
                            Accede al sistema y realiza tu reserva indicando comensales, fecha, hora y ubicacion.
                        </p>

                        <div class="info-cta__actions">
                            <?php if ($rol === "cliente"): ?>
                                <a class="btn-reservar" href="/RESTAURANTE-ROPINO/index.php?page=reservas">Reservar mesa</a>
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

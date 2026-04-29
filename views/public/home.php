<?php
$usuarioActual = $usuarioActual ?? null;
$rol = $usuarioActual["rol"] ?? "";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ropino</title>
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
                    <a href="/RESTAURANTE-ROPINO/index.php?page=admin_reservas">Reservas</a>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=admin_habitaciones">Alojamientos</a>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=admin_mesas">Mesas</a>
                    <a class="activo" href="/RESTAURANTE-ROPINO/index.php?page=reportes">Reportes</a>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=mis_datos">Mis datos</a>
                    <a href="/RESTAURANTE-ROPINO/index.php?page=configuracion">Configuracion</a>
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
                        <span>Bienvenid@: <?= htmlspecialchars($usuarioActual["nombre"]) ?></span>
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
                    <div class="titulo-seccion">Ropino</div>

                    <section class="home-hero" id="home-hero">
                        <div class="home-hero__media" aria-hidden="true">
                            <div class="home-hero__slide active" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi1.png');"></div>
                            <div class="home-hero__slide" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi2.png');"></div>
                            <div class="home-hero__slide" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi3.png');"></div>
                            <div class="home-hero__slide" style="background-image: url('/RESTAURANTE-ROPINO/assets/img/inicio/ropi4.png');"></div>
                        </div>
                        <div class="home-hero__overlay">
                            <div class="home-hero__content">
                                <span class="home-badge">Casa rural · Apartamentos · Restaurante</span>
                                <h1>Casa rural en El Raso, Candeleda</h1>
                                <p>
                                    Ropino es un hotel rural situado en el sur de la Sierra de Gredos,
                                    pensado para disfrutar de la naturaleza, el alojamiento y el
                                    restaurante en un entorno tranquilo y acogedor.
                                </p>

                                <div class="home-hero__actions">
                                    <a class="btn-reservar" href="/RESTAURANTE-ROPINO/index.php?page=reservas">Reservar</a>

                                    <a class="home-btn-secondary" href="#sobre-ropino">Ver informacion</a>
                                </div>
                            </div>

                            <div class="home-hero__dots" id="home-hero-dots">
                                <button type="button" class="home-hero-dot active" data-index="0" aria-label="Ir a imagen 1"></button>
                                <button type="button" class="home-hero-dot" data-index="1" aria-label="Ir a imagen 2"></button>
                                <button type="button" class="home-hero-dot" data-index="2" aria-label="Ir a imagen 3"></button>
                                <button type="button" class="home-hero-dot" data-index="3" aria-label="Ir a imagen 4"></button>
                            </div>
                        </div>
                    </section>

                    <section id="sobre-ropino" class="bloque">
                        <h2>Sobre Ropino</h2>
                        <p class="home-texto">
                            Ropino se encuentra en El Raso, muy cerca de Candeleda y Madrigal de la Vera,
                            en una zona privilegiada del Valle del Tietar con un entorno natural ideal para
                            desconectar. Se presenta como una casa rural para grupos y familias, a unas
                            dos horas de Madrid.
                        </p>
                    </section>

                    <section class="home-cards-grid">
                        <article class="home-info-card">
                            <img
                                src="/RESTAURANTE-ROPINO/assets/img/salon/salon4.png"
                                alt="Casa rural Ropino"
                                class="home-info-card__img">
                            <div class="home-info-card__body">
                                <h3>Casa rural para grupos</h3>
                                <p>
                                    Alojamiento rural pensado para grupos de amigos o familias, con
                                    salon comun acristalado, capacidad para 14 personas.
                                </p>
                                <ul class="home-info-list">
                                    <li>Estancia minima: 2 noches</li>
                                    <li>1.300 € por estancia</li>
                                    <li>650 €/noche</li>
                                    <li>Fianza reembolsable: 300 €</li>
                                </ul>
                                <a class="btn-ver-mas" href="/RESTAURANTE-ROPINO/index.php?page=casa_rural">
                                    Ver más
                                </a>
                            </div>
                        </article>

                        <article class="home-info-card">
                            <img
                                src="/RESTAURANTE-ROPINO/assets/img/apartamentos/ap3.png"
                                alt="Apartamentos Ropino"
                                class="home-info-card__img">
                            <div class="home-info-card__body">
                                <h3>Apartamentos</h3>
                                <p>
                                    Ropino ofrece tambien apartamentos en el mismo entorno, ideales
                                    para una escapada rural con estancia minima de 2 noches.
                                </p>
                                <ul class="home-info-list">
                                    <li>Estancia minima: 2 noches</li>
                                    <li>280 € por estancia</li>
                                    <li>140 €/noche</li>
                                    <li>Fianza reembolsable: 100 €</li>
                                </ul>
                                <a class="btn-ver-mas" href="/RESTAURANTE-ROPINO/index.php?page=apartamentos">
                                    Ver más
                                </a>
                            </div>
                        </article>

                        <article class="home-info-card">
                            <img
                                src="/RESTAURANTE-ROPINO/assets/img/restaurante/rest4.jpeg"
                                alt="Restaurante Ropino"
                                class="home-info-card__img">
                            <div class="home-info-card__body">
                                <h3>Restaurante</h3>
                                <p>
                                    El restaurante cuenta con un acogedor salon y una amplia terraza,
                                    rodeados de vegetacion y paisaje en la falda de la Sierra de Gredos.
                                </p>
                                <ul class="home-info-list">
                                    <li>Entorno natural</li>
                                    <li>Salon interior</li>
                                    <li>Terraza amplia</li>
                                    <li>Recomendable reservar con antelacion</li>
                                </ul>
                                <a class="btn-ver-mas" href="/RESTAURANTE-ROPINO/index.php?page=restaurante">
                                    Ver más
                                </a>
                            </div>
                        </article>
                    </section>

                    <section class="bloque">
                        <h2>Informacion de interes</h2>
                        <div class="tabla-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Ubicacion</th>
                                        <th>Telefono</th>
                                        <th>Email</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>El Raso, Candeleda (Avila)</td>
                                        <td>920 389 788</td>
                                        <td>ropinorestaurante@gmail.com</td>
                                        <td>Precios con IVA incluido. El alojamiento y los apartamentos no incluyen comidas.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
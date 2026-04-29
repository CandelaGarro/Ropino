<!DOCTYPE html>
<?php
/** @var array<string, mixed> $usuarioActual */
/** @var array<int, array<string, mixed>> $reservasPendientes */
/** @var array<string, mixed> $panelOperativo */
/** @var string $fechaResumenTexto */
$usuarioActual = is_array($usuarioActual ?? null) ? $usuarioActual : [];
$reservasPendientes = is_array($reservasPendientes ?? null) ? $reservasPendientes : [];
$panelOperativo = array_merge(
    [
        "total_dia" => 0,
        "mesas_dia" => 0,
        "habitaciones_dia" => 0,
        "agenda_dia" => [],
    ],
    is_array($panelOperativo ?? null) ? $panelOperativo : []
);
$fechaResumenTexto = isset($fechaResumenTexto) ? (string) $fechaResumenTexto : "";
?>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
    <script src="<?= BASE_URL ?>/assets/js/script.js"></script>
    <title>Inicio admin</title>
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
                <a class="activo" href="<?= BASE_URL ?>/index.php?page=panel">Inicio</a>
                <a href="<?= BASE_URL ?>/index.php?page=admin_reservas">Reservas</a>
                <a href="<?= BASE_URL ?>/index.php?page=admin_habitaciones">Alojamientos</a>
                <a href="<?= BASE_URL ?>/index.php?page=admin_mesas">Mesas</a>
                <a href="<?= BASE_URL ?>/index.php?page=reportes">Reportes</a>
                <a href="<?= BASE_URL ?>/index.php?page=mis_datos">Mis datos</a>
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
                    <span>Bienvenid@, <?= htmlspecialchars($usuarioActual["nombre"]) ?></span>
                </div>

                <div class="icono-perfil">U</div>

                <form class="logout-form" action="<?= BASE_URL ?>/logout.php" method="POST">
                    <?= csrfInput() ?>
                    <button class="btn-salir" type="submit">Cerrar sesion</button>
                </form>
            </header>

            <main class="main">
                <div class="card-principal">
                    <div class="titulo-seccion">Resumen</div>
                    <?php renderFlashMessages(); ?>

                    <p class="page-intro">
                        Mostrando el resumen operativo de hoy, <?= htmlspecialchars($fechaResumenTexto) ?>.
                    </p>

                    <div class="resumen-grid">
                        <div class="resumen-card">
                            <h3>Reservas del dia</h3>
                            <p><?= $panelOperativo["total_dia"] ?></p>
                        </div>

                        <div class="resumen-card">
                            <h3>Mesas del dia</h3>
                            <p><?= $panelOperativo["mesas_dia"] ?></p>
                        </div>

                        <div class="resumen-card">
                            <h3>Alojamientos del dia</h3>
                            <p><?= $panelOperativo["habitaciones_dia"] ?></p>
                        </div>
                    </div>

                    <div class="bloque">
                        <h2>Reservas pendientes por confirmar</h2>

                        <div class="tabla-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Tipo</th>
                                        <th>Detalle</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($reservasPendientes)): ?>
                                        <tr>
                                            <td colspan="6">No hay reservas pendientes de confirmacion.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($reservasPendientes as $reserva): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($reserva["nombre_usuario"]) ?></td>
                                                <td><?= htmlspecialchars($reserva["tipo_reserva"]) ?></td>
                                                <td><?= htmlspecialchars($reserva["detalle_reserva"]) ?></td>
                                                <td><?= htmlspecialchars($reserva["fecha_inicio"]) ?></td>
                                                <td><?= htmlspecialchars($reserva["hora_reserva"] ?? "-") ?></td>
                                                <td>
                                                    <div class="acciones">
                                    <form action="<?= BASE_URL ?>/src/controllers/adminReservaController.php" method="POST" style="display:inline;">
                                                            <?= csrfInput() ?>
                                                            <input type="hidden" name="id_reserva" value="<?= htmlspecialchars($reserva["id_reserva"]) ?>">
                                                            <input type="hidden" name="return_page" value="panel">
                                                            <button class="accion-link accion-verde" type="submit" name="confirmar_reserva_admin" onclick="return confirm('Â¿Confirmar esta reserva?')">
                                                                Confirmar
                                                            </button>
                                                        </form>

                                    <form action="<?= BASE_URL ?>/src/controllers/adminReservaController.php" method="POST" style="display:inline;">
                                                            <?= csrfInput() ?>
                                                            <input type="hidden" name="id_reserva" value="<?= htmlspecialchars($reserva["id_reserva"]) ?>">
                                                            <input type="hidden" name="return_page" value="panel">
                                                            <button class="accion-link accion-roja" type="submit" name="cancelar_reserva_admin" onclick="return confirm('Â¿Cancelar esta reserva?')">
                                                                Cancelar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <h2>Reservas del dia</h2>
                    <div class="tabla-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Tipo</th>
                                    <th>Detalle</th>
                                    <th>Hora</th>
                                    <th>Estado real</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($panelOperativo["agenda_dia"])): ?>
                                    <tr>
                                        <td colspan="6">No hay reservas para la fecha seleccionada.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($panelOperativo["agenda_dia"] as $reserva): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($reserva["nombre_usuario"]) ?></td>
                                            <td><?= htmlspecialchars($reserva["tipo_reserva"]) ?></td>
                                            <td><?= htmlspecialchars($reserva["detalle_reserva"]) ?></td>
                                            <td><?= htmlspecialchars($reserva["hora_reserva"] ?? "-") ?></td>
                                            <td>
                                                <span class="<?= htmlspecialchars($reserva["estado_css"]) ?>">
                                                    <?= htmlspecialchars($reserva["estado_mostrado"]) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="acciones">
                                                    <a class="accion-link accion-verde" href="<?= BASE_URL ?>/index.php?page=admin_reservas">
                                                        Ver
                                                    </a>

                                                    <?php if (!empty($reserva["cancelable_admin"])): ?>
                                            <form action="<?= BASE_URL ?>/src/controllers/adminReservaController.php" method="POST" style="display:inline;">
                                                            <?= csrfInput() ?>
                                                            <input type="hidden" name="id_reserva" value="<?= htmlspecialchars($reserva["id_reserva"]) ?>">
                                                            <button class="accion-link accion-roja" type="submit" name="cancelar_reserva_admin" onclick="return confirm('Cancelar esta reserva?')">
                                                                X
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span>-</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>

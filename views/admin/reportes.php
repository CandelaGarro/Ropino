<?php
require_once __DIR__ . "/../../src/helpers/auth.php";
require_once __DIR__ . "/../../src/models/reserva.php";
require_once __DIR__ . "/../../src/helpers/validation.php";

$usuarioActual = requireAuth("admin");

$reservaModel = new Reserva();

$fecha = $_GET["fecha"] ?? date("Y-m-d");
if (!validateDateValue($fecha)) {
    $fecha = date("Y-m-d");
}

$reservasDia = $reservaModel->obtenerPorFecha($fecha);
$mesasDia = $reservaModel->obtenerMesasPorFecha($fecha);
$habitacionesDia = $reservaModel->obtenerHabitacionesPorFecha($fecha);

$totalReservasDia = count($reservasDia);
$totalMesasDia = count($mesasDia);
$totalHabitacionesDia = count($habitacionesDia);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes</title>
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
                <a href="/RESTAURANTE-ROPINO/index.php?page=panel">Inicio</a>
                <a href="/RESTAURANTE-ROPINO/index.php?page=admin_reservas">Reservas</a>
                <a href="/RESTAURANTE-ROPINO/index.php?page=admin_habitaciones">Alojamientos</a>
                <a href="/RESTAURANTE-ROPINO/index.php?page=admin_mesas">Mesas</a>
                <a class="activo" href="/RESTAURANTE-ROPINO/index.php?page=reportes">Reportes</a>
                <a href="/RESTAURANTE-ROPINO/index.php?page=mis_datos">Mis datos</a>
                <a href="/RESTAURANTE-ROPINO/index.php?page=configuracion">Configuracion</a>
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
                    <span>Admin: <?= htmlspecialchars($usuarioActual["nombre"]) ?></span>
                </div>
                <div class="icono-perfil">U</div>
                <form class="logout-form" action="/RESTAURANTE-ROPINO/logout.php" method="POST">
                    <?= csrfInput() ?>
                    <button class="btn-salir" type="submit">Cerrar sesion</button>
                </form>
            </header>

            <main class="main">
                <div class="card-principal">
                    <div class="titulo-seccion">Reportes por fecha</div>
                    <?php renderFlashMessages(); ?>

                    <div class="bloque">
                        <h2>Seleccionar dia</h2>

                        <form class="form-admin" method="GET" action="/RESTAURANTE-ROPINO/index.php">
                            <input type="hidden" name="page" value="reportes">
                            <input type="date" name="fecha" value="<?= htmlspecialchars($fecha) ?>" required>
                            <button type="submit">Consultar</button>
                        </form>
                    </div>

                    <div class="resumen-grid">
                        <div class="resumen-card">
                            <h3>Reservas del dia</h3>
                            <p><?= $totalReservasDia ?></p>
                        </div>

                        <div class="resumen-card">
                            <h3>Mesas ocupadas</h3>
                            <p><?= $totalMesasDia ?></p>
                        </div>

                        <div class="resumen-card">
                            <h3>Alojamientos ocupados</h3>
                            <p><?= $totalHabitacionesDia ?></p>
                        </div>
                    </div>

                    <div class="bloque">
                        <h2>Reservas del dia</h2>

                        <div class="tabla-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Tipo</th>
                                        <th>Detalle</th>
                                        <th>Hora</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($reservasDia)): ?>
                                        <tr>
                                            <td colspan="6">No hay reservas para la fecha seleccionada.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($reservasDia as $reserva): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($reserva["nombre_usuario"]) ?></td>
                                                <td><?= htmlspecialchars($reserva["tipo_reserva"]) ?></td>
                                                <td><?= htmlspecialchars($reserva["detalle_reserva"]) ?></td>
                                                <td><?= htmlspecialchars($reserva["hora_reserva"] ?? "-") ?></td>
                                                <td>
                                                    <?php if (($reserva["estado"] ?? "") === "pendiente"): ?>
                                                        <span class="estado-pendiente">Pendiente</span>
                                                    <?php elseif (($reserva["estado"] ?? "") === "confirmada"): ?>
                                                        <span class="estado-confirmada">Confirmada</span>
                                                    <?php elseif (($reserva["estado"] ?? "") === "cancelada"): ?>
                                                        <span class="estado-cancelada">Cancelada</span>
                                                    <?php else: ?>
                                                        <span><?= htmlspecialchars($reserva["estado"]) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bloque">
                        <h2>Mesas de ese dia</h2>

                        <div class="tabla-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Mesa</th>
                                        <th>Capacidad</th>
                                        <th>Ubicacion</th>
                                        <th>Hora</th>
                                        <th>Cliente</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($mesasDia)): ?>
                                        <tr>
                                            <td colspan="6">No hay mesas reservadas para ese dia.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($mesasDia as $mesa): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($mesa["numero"]) ?></td>
                                                <td><?= htmlspecialchars($mesa["capacidad"]) ?></td>
                                                <td><?= htmlspecialchars($mesa["ubicacion"]) ?></td>
                                                <td><?= htmlspecialchars($mesa["hora_reserva"] ?? "-") ?></td>
                                                <td><?= htmlspecialchars($mesa["nombre_usuario"]) ?></td>
                                                <td><?= htmlspecialchars($mesa["estado"]) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bloque">
                        <h2>Alojamientos de ese dia</h2>

                        <div class="tabla-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Habitacion</th>
                                        <th>Tipo</th>
                                        <th>Precio</th>
                                        <th>Cliente</th>
                                        <th>Entrada</th>
                                        <th>Salida</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($habitacionesDia)): ?>
                                        <tr>
                                            <td colspan="7">No hay alojamientos ocupadas para ese dia.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($habitacionesDia as $habitacion): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($habitacion["nombre"]) ?></td>
                                                <td><?= htmlspecialchars($habitacion["tipo_alojamiento"]) ?></td>
                                                <td><?= htmlspecialchars($habitacion["precio"]) ?> €</td>
                                                <td><?= htmlspecialchars($habitacion["nombre_usuario"]) ?></td>
                                                <td><?= htmlspecialchars($habitacion["fecha_inicio"]) ?></td>
                                                <td><?= htmlspecialchars($habitacion["fecha_fin"]) ?></td>
                                                <td><?= htmlspecialchars($habitacion["estado"]) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>
</body>

</html>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar reservas</title>
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
                <a href="<?= BASE_URL ?>/index.php?page=panel">Inicio</a>
                <a class="activo" href="<?= BASE_URL ?>/index.php?page=admin_reservas">Reservas</a>
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
                    <div class="titulo-seccion">Administrar reservas</div>
                    <?php renderFlashMessages(); ?>

                    <div class="bloque">
                        <h2>Filtros</h2>

                        <form class="form-admin" method="GET" action="<?= BASE_URL ?>/index.php">
                            <input type="hidden" name="page" value="admin_reservas">

                            <input
                                type="text"
                                name="busqueda"
                                placeholder="Buscar por nombre o email"
                                value="<?= htmlspecialchars($busqueda) ?>">

                            <select name="tipo">
                                <option value="">Todos los tipos</option>
                                <option value="habitacion" <?= $filtroTipo === "habitacion" ? "selected" : "" ?>>Alojamiento</option>
                                <option value="mesa" <?= $filtroTipo === "mesa" ? "selected" : "" ?>>Mesa</option>
                            </select>

                            <select name="estado">
                                <option value="">Todos los estados</option>
                                <option value="pendiente" <?= $filtroEstado === "pendiente" ? "selected" : "" ?>>Pendiente</option>
                                <option value="confirmada" <?= $filtroEstado === "confirmada" ? "selected" : "" ?>>Confirmada</option>
                                <option value="cancelada" <?= $filtroEstado === "cancelada" ? "selected" : "" ?>>Cancelada</option>
                            </select>

                            <button type="submit">Filtrar</button>

                            <a class="accion-link accion-editar" href="<?= BASE_URL ?>/index.php?page=admin_reservas" style="text-align:center;">
                                Limpiar
                            </a>
                        </form>
                    </div>

                    <div class="bloque">
                        <h2>Reservas registradas</h2>

                        <div class="tabla-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Email</th>
                                        <th>Tipo</th>
                                        <th>Detalle</th>
                                        <th>Fecha inicio</th>
                                        <th>Fecha fin</th>
                                        <th>Hora</th>
                                        <th>Comensales</th>
                                        <th>Ubicacion</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($reservas)): ?>
                                        <tr>
                                            <td colspan="11">No hay reservas registradas con esos filtros.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($reservas as $reserva): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($reserva["nombre_usuario"]) ?></td>
                                                <td><?= htmlspecialchars($reserva["email_usuario"]) ?></td>
                                                <td>
                                                    <?= ($reserva["tipo_reserva"] ?? "") === "habitacion"
                                                        ? "Alojamiento"
                                                        : (($reserva["tipo_reserva"] ?? "") === "mesa" ? "Mesa" : htmlspecialchars($reserva["tipo_reserva"] ?? "-")) ?>
                                                </td>
                                                <td><?= htmlspecialchars($reserva["detalle_reserva"]) ?></td>
                                                <td><?= htmlspecialchars($reserva["fecha_inicio"]) ?></td>
                                                <td><?= htmlspecialchars($reserva["fecha_fin"]) ?></td>
                                                <td><?= htmlspecialchars($reserva["hora_reserva"] ?? "-") ?></td>
                                                <td><?= htmlspecialchars($reserva["comensales"] ?? "-") ?></td>
                                                <td><?= htmlspecialchars($reserva["ubicacion_preferida"] ?? "-") ?></td>
                                                <td>
                                                    <?php if (($reserva["estado"] ?? "") === "pendiente"): ?>
                                                        <span class="estado-pendiente">Pendiente</span>
                                                    <?php elseif (($reserva["estado"] ?? "") === "confirmada"): ?>
                                                        <span class="estado-confirmada">Confirmada</span>
                                                    <?php elseif (($reserva["estado"] ?? "") === "cancelada"): ?>
                                                        <span class="estado-cancelada">Cancelada</span>
                                                    <?php else: ?>
                                                        <span class="<?= htmlspecialchars($reserva["estado_css"]) ?>">
                                                            <?= htmlspecialchars($reserva["estado_mostrado"]) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="acciones">
                                                        <?php if (($reserva["estado"] ?? "") === "pendiente"): ?>
                                            <form action="<?= BASE_URL ?>/src/controllers/adminReservaController.php" method="POST" style="display:inline;">
                                                                <?= csrfInput() ?>
                                                                <input type="hidden" name="id_reserva" value="<?= htmlspecialchars($reserva["id_reserva"]) ?>">
                                                                <button class="accion-link accion-verde" type="submit" name="confirmar_reserva_admin" onclick="return confirm('Â¿Confirmar esta reserva?')">
                                                                    Confirmar
                                                                </button>
                                                            </form>

                                            <form action="<?= BASE_URL ?>/src/controllers/adminReservaController.php" method="POST" style="display:inline;">
                                                                <?= csrfInput() ?>
                                                                <input type="hidden" name="id_reserva" value="<?= htmlspecialchars($reserva["id_reserva"]) ?>">
                                                                <button class="accion-link accion-roja" type="submit" name="cancelar_reserva_admin" onclick="return confirm('Â¿Cancelar esta reserva?')">
                                                                    Cancelar
                                                                </button>
                                                            </form>

                                                        <?php elseif (($reserva["estado"] ?? "") === "confirmada"): ?>
                                            <form action="<?= BASE_URL ?>/src/controllers/adminReservaController.php" method="POST" style="display:inline;">
                                                                <?= csrfInput() ?>
                                                                <input type="hidden" name="id_reserva" value="<?= htmlspecialchars($reserva["id_reserva"]) ?>">
                                                                <button class="accion-btn" type="submit" name="cancelar_reserva_admin" onclick="return confirm('Â¿Cancelar esta reserva?')">
                                                                    Cancelar
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

                </div>
            </main>
        </div>
    </div>
</body>

</html>


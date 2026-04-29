<!DOCTYPE html>
<?php
/** @var array<string, mixed> $usuarioActual */
/** @var array<string, mixed> $habitacionEditar */
/** @var array<int, array<string, mixed>> $habitaciones */
$usuarioActual = is_array($usuarioActual ?? null) ? $usuarioActual : [];
$habitacionEditar = is_array($habitacionEditar ?? null) ? $habitacionEditar : [];
$habitaciones = is_array($habitaciones ?? null) ? $habitaciones : [];
?>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar alojamientos</title>
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
                <a href="<?= BASE_URL ?>/index.php?page=admin_reservas">Reservas</a>
                <a class="activo" href="<?= BASE_URL ?>/index.php?page=admin_habitaciones">Alojamientos</a>
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
                    <div class="titulo-seccion">Administrar alojamientos</div>
                    <?php renderFlashMessages(); ?>

                    <div class="bloque">
                        <h2><?= $habitacionEditar ? "Editar alojamiento" : "Crear alojamiento" ?></h2>

                        <form class="form-admin" action="<?= BASE_URL ?>/src/controllers/adminHabitacionController.php" method="POST">
                            <?= csrfInput() ?>

                            <?php if ($habitacionEditar): ?>
                                <input type="hidden" name="id_habitacion" value="<?= htmlspecialchars($habitacionEditar["id_habitacion"]) ?>">
                            <?php endif; ?>

                            <input
                                type="text"
                                name="nombre"
                                placeholder="Nombre del alojamiento"
                                value="<?= htmlspecialchars($habitacionEditar["nombre"] ?? oldInput("nombre")) ?>"
                                required>

                            <select name="tipo_alojamiento" required>
                                <option value="">Tipo de alojamiento</option>
                                <option value="completo" <?= (($habitacionEditar["tipo_alojamiento"] ?? oldInput("tipo_alojamiento")) === "completo") ? "selected" : "" ?>>
                                    Casa completa
                                </option>
                                <option value="principal" <?= (($habitacionEditar["tipo_alojamiento"] ?? oldInput("tipo_alojamiento")) === "principal") ? "selected" : "" ?>>
                                    Segunda planta + salon
                                </option>
                                <option value="apartamento" <?= (($habitacionEditar["tipo_alojamiento"] ?? oldInput("tipo_alojamiento")) === "apartamento") ? "selected" : "" ?>>
                                    Apartamento
                                </option>
                            </select>

                            <input
                                type="number"
                                name="capacidad"
                                min="1"
                                max="30"
                                placeholder="Capacidad"
                                value="<?= htmlspecialchars((string) ($habitacionEditar["capacidad"] ?? oldInput("capacidad"))) ?>"
                                required>

                            <input
                                type="number"
                                name="precio"
                                min="0"
                                step="0.01"
                                placeholder="Precio"
                                value="<?= htmlspecialchars((string) ($habitacionEditar["precio"] ?? oldInput("precio"))) ?>"
                                required>

                            <select name="estado" required>
                                <option value="">Estado</option>
                                <option value="disponible" <?= (($habitacionEditar["estado"] ?? oldInput("estado")) === "disponible") ? "selected" : "" ?>>
                                    Disponible
                                </option>
                                <option value="mantenimiento" <?= (($habitacionEditar["estado"] ?? oldInput("estado")) === "mantenimiento") ? "selected" : "" ?>>
                                    Mantenimiento
                                </option>
                                <option value="ocupada" <?= (($habitacionEditar["estado"] ?? oldInput("estado")) === "ocupada") ? "selected" : "" ?>>
                                    Ocupada
                                </option>
                            </select>

                            <?php if ($habitacionEditar): ?>
                                <button type="submit" name="actualizar_habitacion">Guardar cambios</button>
                            <?php else: ?>
                                <button type="submit" name="crear_habitacion">Crear alojamiento</button>
                            <?php endif; ?>

                            <?php if ($habitacionEditar): ?>
                                <a class="accion-link accion-editar" href="<?= BASE_URL ?>/index.php?page=admin_habitaciones" style="text-align:center;">
                                    Cancelar
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="bloque">
                        <h2>Alojamientos registrados</h2>

                        <div class="tabla-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Capacidad</th>
                                        <th>Precio</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($habitaciones)): ?>
                                        <tr>
                                            <td colspan="7">No hay alojamientos registrados.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($habitaciones as $habitacion): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($habitacion["nombre"] ?? "-") ?></td>
                                                <td>
                                                    <?php
                                                    $tipoAlojamiento = $habitacion["tipo_alojamiento"] ?? "";
                                                    if ($tipoAlojamiento === "completo") {
                                                        echo "Casa completa";
                                                    } elseif ($tipoAlojamiento === "principal") {
                                                        echo "Segunda planta + salon";
                                                    } elseif ($tipoAlojamiento === "apartamento") {
                                                        echo "Apartamento";
                                                    } else {
                                                        echo "-";
                                                    }
                                                    ?>
                                                </td>
                                                <td><?= htmlspecialchars((string) ($habitacion["capacidad"] ?? "-")) ?></td>
                                                <td><?= htmlspecialchars((string) ($habitacion["precio"] ?? "-")) ?> EUR</td>
                                                <td>
                                                    <?php if (($habitacion["estado"] ?? "") === "disponible"): ?>
                                                        <span class="estado-confirmada">Disponible</span>
                                                    <?php elseif (($habitacion["estado"] ?? "") === "mantenimiento"): ?>
                                                        <span class="estado-pendiente">Mantenimiento</span>
                                                    <?php else: ?>
                                                        <span class="estado-cancelada"><?= htmlspecialchars($habitacion["estado"] ?? "-") ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="acciones">
                                                        <a class="accion-link accion-editar" href="<?= BASE_URL ?>/index.php?page=admin_habitaciones&editar=<?= htmlspecialchars($habitacion["id_habitacion"]) ?>">
                                                            Editar
                                                        </a>

                                                        <form action="<?= BASE_URL ?>/src/controllers/adminHabitacionController.php" method="POST" style="display:inline;">
                                                            <?= csrfInput() ?>
                                                            <input type="hidden" name="id_habitacion" value="<?= htmlspecialchars($habitacion["id_habitacion"]) ?>">
                                                            <button class="accion-btn" type="submit" name="eliminar_habitacion" onclick="return confirm('Â¿Eliminar este alojamiento?')">
                                                                Eliminar
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

                </div>
            </main>
        </div>
    </div>
</body>

</html>

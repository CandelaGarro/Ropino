<!DOCTYPE html>
<?php
/** @var array<string, mixed> $usuarioActual */
/** @var array<int, array<string, mixed>> $reservas */
/** @var array<int, array<string, mixed>> $habitaciones */
/** @var int $duracionMesaMinutos */
/** @var string $horaMesaFin */
/** @var int $intervaloMesaMinutos */
$usuarioActual = is_array($usuarioActual ?? null) ? $usuarioActual : [];
$reservas = is_array($reservas ?? null) ? $reservas : [];
$habitaciones = is_array($habitaciones ?? null) ? $habitaciones : [];
$duracionMesaMinutos = (int) ($duracionMesaMinutos ?? 0);
$horaMesaFin = isset($horaMesaFin) ? (string) $horaMesaFin : "";
$intervaloMesaMinutos = (int) ($intervaloMesaMinutos ?? 30);
?>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
    <script src="<?= BASE_URL ?>/assets/js/script.js"></script>
    <title>Mis reservas</title>
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
                <a href="<?= BASE_URL ?>/index.php?page=home">Inicio</a>
                <a class="activo" href="<?= BASE_URL ?>/index.php?page=reservas">Mis reservas</a>
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
                    <div class="titulo-seccion">Mis reservas</div>
                    <?php renderFlashMessages(); ?>

                    <div class="bloque">
                        <h2>Crear reserva</h2>
                        <p class="nota-reserva">
                            Las reservas de mesa bloquean la mesa durante <?= htmlspecialchars((string) $duracionMesaMinutos) ?> minutos y solo se pueden cancelar antes de que empiecen.
                        </p>

                <form class="form-reservas" action="<?= BASE_URL ?>/src/controllers/reservaController.php" method="POST">
                            <?= csrfInput() ?>

                            <?php $tipoReservaSeleccionado = oldInput("tipo_reserva"); ?>

                            <select name="tipo_reserva" id="tipo_reserva" required>
                                <option value="">Tipo de reserva</option>
                                <option value="habitacion" <?= $tipoReservaSeleccionado === "habitacion" ? "selected" : "" ?>>Alojamiento</option>
                                <option value="mesa" <?= $tipoReservaSeleccionado === "mesa" ? "selected" : "" ?>>Mesa</option>
                            </select>

                            <!-- BLOQUE HABITACION -->
                            <div id="bloque_habitacion" class="bloque-habitacion bloque-habitacion-grid">
                                <div class="subbloque-alojamiento" style="grid-column: 1 / -1;">
                                    <p class="nota-reserva" style="margin-bottom: 10px;">
                                        Selecciona una o varias opciones de alojamiento.
                                    </p>

                                    <div class="grupo-alojamientos-check">
                                        <?php foreach ($habitaciones as $habitacion): ?>
                                            <?php
                                            $nombre = $habitacion["nombre"] ?? ("Alojamiento " . $habitacion["id_habitacion"]);
                                            $capacidad = $habitacion["capacidad"] ?? null;
                                            $precio = $habitacion["precio"] ?? null;
                                            $tipoAlojamiento = $habitacion["tipo_alojamiento"] ?? "";
                                            ?>
                                            <label class="check-alojamiento">
                                                <input
                                                    type="checkbox"
                                                    name="alojamientos[]"
                                                    value="<?= htmlspecialchars($habitacion["id_habitacion"]) ?>"
                                                    data-tipo="<?= htmlspecialchars($tipoAlojamiento) ?>">
                                                <span>
                                                    <?= htmlspecialchars($nombre) ?>
                                                    <?php if (!empty($capacidad)): ?>
                                                        - <?= htmlspecialchars((string) $capacidad) ?> personas
                                                    <?php endif; ?>
                                                    <?php if ($precio !== null && $precio !== ""): ?>
                                                        - <?= htmlspecialchars((string) $precio) ?> EUR
                                                    <?php endif; ?>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div id="bloque_fecha_inicio_habitacion" class="bloque-habitacion">
                                <input
                                    type="date"
                                    name="fecha_inicio"
                                    id="fecha_inicio"
                                    value="<?= htmlspecialchars(oldInput("fecha_inicio")) ?>"
                                    placeholder="Fecha entrada">
                            </div>

                            <div id="bloque_fecha_fin_habitacion" class="bloque-habitacion">
                                <input
                                    type="date"
                                    name="fecha_fin"
                                    id="fecha_fin"
                                    value="<?= htmlspecialchars(oldInput("fecha_fin")) ?>"
                                    placeholder="Fecha salida">
                            </div>

                            <!-- BLOQUE MESA -->
                            <div id="bloque_mesa" class="bloque-mesa">
                                <input
                                    type="number"
                                    name="comensales"
                                    id="comensales"
                                    min="1"
                                    max="20"
                                    value="<?= htmlspecialchars(oldInput("comensales")) ?>"
                                    placeholder="Comensales">
                            </div>

                            <div id="bloque_fecha_mesa" class="bloque-mesa">
                                <input
                                    type="date"
                                    name="fecha_mesa"
                                    id="fecha_mesa"
                                    value="<?= htmlspecialchars(oldInput("fecha_mesa")) ?>">
                            </div>

                            <div id="bloque_hora_mesa" class="bloque-mesa">
                                <?php
                                $horaMesaSeleccionada = oldInput("hora_mesa");
                                $horaMesaActual = new DateTimeImmutable(Reserva::HORA_MESA_INICIO);
                                $horaMesaFin = new DateTimeImmutable(Reserva::HORA_MESA_FIN);
                                $intervaloMesaMinutos = (int) (Reserva::INTERVALO_MESA_SEGUNDOS / 60);
                                ?>
                                <select name="hora_mesa" id="hora_mesa">
                                    <option value="">Hora</option>
                                    <?php while ($horaMesaActual <= $horaMesaFin): ?>
                                        <?php $horaOpcion = $horaMesaActual->format("H:i"); ?>
                                        <option value="<?= htmlspecialchars($horaOpcion) ?>" <?= $horaMesaSeleccionada === $horaOpcion ? "selected" : "" ?>>
                                            <?= htmlspecialchars($horaOpcion) ?>
                                        </option>
                                        <?php $horaMesaActual = $horaMesaActual->modify("+" . $intervaloMesaMinutos . " minutes"); ?>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div id="bloque_ubicacion_mesa" class="bloque-mesa">
                                <select name="ubicacion_preferida" id="ubicacion_preferida">
                                    <option value="">Ubicacion</option>
                                    <option value="interior" <?= oldInput("ubicacion_preferida") === "interior" ? "selected" : "" ?>>Interior</option>
                                    <option value="terraza" <?= oldInput("ubicacion_preferida") === "terraza" ? "selected" : "" ?>>Terraza</option>
                                </select>
                            </div>

                            <button type="submit" name="crear_reserva">Reservar</button>
                        </form>
                    </div>

                    <div class="bloque">
                        <h2>Reservas registradas</h2>

                        <div class="tabla-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Fecha inicio</th>
                                        <th>Fecha fin</th>
                                        <th>Hora</th>
                                        <th>Comensales</th>
                                        <th>Ubicacion</th>
                                        <th>Estado real</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($reservas)): ?>
                                        <tr>
                                            <td colspan="8">No tienes reservas todavia.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($reservas as $reserva): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($reserva["tipo_reserva"]) ?></td>
                                                <td><?= htmlspecialchars($reserva["fecha_inicio"]) ?></td>
                                                <td><?= htmlspecialchars($reserva["fecha_fin"]) ?></td>
                                                <td><?= htmlspecialchars($reserva["hora_reserva"] ?? "-") ?></td>
                                                <td><?= htmlspecialchars($reserva["comensales"] ?? "-") ?></td>
                                                <td><?= htmlspecialchars($reserva["ubicacion_preferida"] ?? "-") ?></td>
                                                <td>
                                                    <span class="<?= htmlspecialchars($reserva["estado_css"]) ?>">
                                                        <?= htmlspecialchars($reserva["estado_mostrado"]) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($reserva["cancelable_cliente"])): ?>
                                            <form action="<?= BASE_URL ?>/src/controllers/reservaController.php" method="POST" style="display:inline;">
                                                            <?= csrfInput() ?>
                                                            <input type="hidden" name="id_reserva" value="<?= htmlspecialchars($reserva["id_reserva"]) ?>">
                                                            <button class="accion-btn" type="submit" name="cancelar_reserva">Cancelar</button>
                                                        </form>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
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

    <script>
        const tipoReserva = document.getElementById("tipo_reserva");

        const bloquesHabitacion = document.querySelectorAll(".bloque-habitacion");
        const bloquesMesa = document.querySelectorAll(".bloque-mesa");

        const fechaInicio = document.getElementById("fecha_inicio");
        const fechaFin = document.getElementById("fecha_fin");

        const comensales = document.getElementById("comensales");
        const fechaMesa = document.getElementById("fecha_mesa");
        const horaMesa = document.getElementById("hora_mesa");
        const ubicacionPreferida = document.getElementById("ubicacion_preferida");

        const checksAlojamientos = document.querySelectorAll('input[name="alojamientos[]"]');

        function controlarCasaCompleta() {
            const casaCompletaMarcada = Array.from(checksAlojamientos).some(
                check => check.checked && check.dataset.tipo === "completo"
            );

            checksAlojamientos.forEach(check => {
                if (check.dataset.tipo !== "completo") {
                    check.disabled = casaCompletaMarcada;
                    if (casaCompletaMarcada) {
                        check.checked = false;
                    }
                }
            });
        }

        function actualizarFormulario() {
            const tipo = tipoReserva.value;

            if (tipo === "habitacion") {
                bloquesHabitacion.forEach(el => el.style.display = "block");
                bloquesMesa.forEach(el => el.style.display = "none");

                fechaInicio.required = true;
                fechaFin.required = true;

                comensales.required = false;
                fechaMesa.required = false;
                horaMesa.required = false;
                ubicacionPreferida.required = false;

                comensales.value = "";
                fechaMesa.value = "";
                horaMesa.value = "";
                ubicacionPreferida.value = "";

                controlarCasaCompleta();
            } else if (tipo === "mesa") {
                bloquesHabitacion.forEach(el => el.style.display = "none");
                bloquesMesa.forEach(el => el.style.display = "block");

                fechaInicio.required = false;
                fechaFin.required = false;

                checksAlojamientos.forEach(check => {
                    check.checked = false;
                    check.disabled = false;
                });

                comensales.required = true;
                fechaMesa.required = true;
                horaMesa.required = true;
                ubicacionPreferida.required = true;
            } else {
                bloquesHabitacion.forEach(el => el.style.display = "none");
                bloquesMesa.forEach(el => el.style.display = "none");

                fechaInicio.required = false;
                fechaFin.required = false;

                checksAlojamientos.forEach(check => {
                    check.checked = false;
                    check.disabled = false;
                });

                comensales.required = false;
                fechaMesa.required = false;
                horaMesa.required = false;
                ubicacionPreferida.required = false;
            }
        }

        tipoReserva.addEventListener("change", actualizarFormulario);

        checksAlojamientos.forEach(check => {
            check.addEventListener("change", controlarCasaCompleta);
        });

        actualizarFormulario();
    </script>
</body>

</html>

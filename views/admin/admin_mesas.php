<!DOCTYPE html>
<?php
/** @var array<string, mixed> $usuarioActual */
/** @var array<string, mixed> $mesaEditar */
/** @var array<int, array<string, mixed>> $mesas */
$usuarioActual = is_array($usuarioActual ?? null) ? $usuarioActual : [];
$mesaEditar = is_array($mesaEditar ?? null) ? $mesaEditar : [];
$mesas = is_array($mesas ?? null) ? $mesas : [];
?>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
    <script src="<?= BASE_URL ?>/assets/js/script.js"></script>
    <title>Gestionar mesas</title>
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
                <a href="<?= BASE_URL ?>/index.php?page=admin_habitaciones">Alojamientos</a>
                <a class="activo" href="<?= BASE_URL ?>/index.php?page=admin_mesas">Mesas</a>
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
                    <div class="titulo-seccion">Gestionar mesas</div>
                    <?php renderFlashMessages(); ?>

                    <div class="bloque">
                        <h2>Anadir mesa</h2>
                        <form class="form-admin" action="<?= BASE_URL ?>/src/controllers/adminMesaController.php" method="POST">
                            <?= csrfInput() ?>
                            <input type="number" name="numero" placeholder="Numero" value="<?= htmlspecialchars(oldInput("numero")) ?>" min="1" max="9999" required>
                            <input type="number" name="capacidad" min="1" max="20" placeholder="Capacidad" value="<?= htmlspecialchars(oldInput("capacidad")) ?>" required>

                            <select name="ubicacion" required>
                                <option value="">Ubicacion</option>
                                <option value="interior" <?= oldInput("ubicacion") === "interior" ? "selected" : "" ?>>Interior</option>
                                <option value="terraza" <?= oldInput("ubicacion") === "terraza" ? "selected" : "" ?>>Terraza</option>
                            </select>

                            <select name="estado" required>
                                <option value="disponible" <?= oldInput("estado", "disponible") === "disponible" ? "selected" : "" ?>>Disponible</option>
                                <option value="ocupada" <?= oldInput("estado") === "ocupada" ? "selected" : "" ?>>Ocupada</option>
                            </select>

                            <button type="submit" name="crear_mesa">Guardar</button>
                        </form>
                    </div>

                    <?php if ($mesaEditar): ?>
                        <div class="bloque">
                            <h2>Editar mesa</h2>
                        <form class="form-admin" action="<?= BASE_URL ?>/src/controllers/adminMesaController.php" method="POST">
                                <?= csrfInput() ?>
                                <input type="hidden" name="id_mesa" value="<?= $mesaEditar["id_mesa"] ?>">
                                <input type="number" name="numero" value="<?= htmlspecialchars(oldInput("numero", (string) $mesaEditar["numero"])) ?>" min="1" max="9999" required>
                                <input type="number" name="capacidad" min="1" max="20" value="<?= htmlspecialchars(oldInput("capacidad", (string) $mesaEditar["capacidad"])) ?>" required>

                                <select name="ubicacion" required>
                                    <option value="interior" <?= oldInput("ubicacion", $mesaEditar["ubicacion"]) === "interior" ? "selected" : "" ?>>Interior</option>
                                    <option value="terraza" <?= oldInput("ubicacion", $mesaEditar["ubicacion"]) === "terraza" ? "selected" : "" ?>>Terraza</option>
                                </select>

                                <select name="estado" required>
                                    <option value="disponible" <?= oldInput("estado", $mesaEditar["estado"]) === "disponible" ? "selected" : "" ?>>Disponible</option>
                                    <option value="ocupada" <?= oldInput("estado", $mesaEditar["estado"]) === "ocupada" ? "selected" : "" ?>>Ocupada</option>
                                </select>

                                <button type="submit" name="editar_mesa">Actualizar</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <div class="bloque">
                        <h2>Mesas registradas</h2>
                        <div class="tabla-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Numero</th>
                                        <th>Capacidad</th>
                                        <th>Ubicacion</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mesas as $mesa): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($mesa["numero"]) ?></td>
                                            <td><?= htmlspecialchars($mesa["capacidad"]) ?></td>
                                            <td><?= htmlspecialchars($mesa["ubicacion"]) ?></td>
                                            <td><?= htmlspecialchars($mesa["estado"]) ?></td>
                                            <td>
                                                <div class="acciones">
                                                    <a class="accion-link accion-editar" href="<?= BASE_URL ?>/index.php?page=admin_mesas&editar=<?= $mesa["id_mesa"] ?>">Editar</a>

                                                        <form action="<?= BASE_URL ?>/src/controllers/adminMesaController.php" method="POST" style="display:inline;">
                                                        <?= csrfInput() ?>
                                                        <input type="hidden" name="id_mesa" value="<?= htmlspecialchars($mesa["id_mesa"]) ?>">
                                                        <button class="accion-btn accion-eliminar" type="submit" name="eliminar_mesa" value="1" onclick="return confirm('Eliminar esta mesa?')">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
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

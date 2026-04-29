<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ropino</title>
    <link rel="stylesheet" href="/RESTAURANTE-ROPINO/assets/css/styles.css">
    <script src="/RESTAURANTE-ROPINO/assets/js/script.js"></script>
</head>

<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <div class="auth-logo-circulo"></div>
                    <div class="auth-logo-texto">Ropino</div>
                </div>

                <h1>Iniciar sesion</h1>
                <p>Accede a tu cuenta para gestionar tus reservas.</p>
            </div>

            <div class="auth-body">
                <?php renderFlashMessages(); ?>

                <form class="auth-form" action="/RESTAURANTE-ROPINO/src/controllers/usuarioController.php" method="POST">
                    <?= csrfInput() ?>

                    <div>
                        <label for="email">Correo electronico</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars(oldInput("email")) ?>" autocomplete="email" required>
                    </div>

                    <div>
                        <label for="password">Contrasena</label>
                        <input type="password" name="password" id="password" autocomplete="current-password" required>
                    </div>

                    <button class="auth-btn" type="submit" name="login">Entrar</button>
                </form>

                <div class="auth-links">
                    No tienes cuenta?
                    <a href="/RESTAURANTE-ROPINO/index.php?page=registro">Registrate</a>
                </div>

                <div class="auth-note">
                    Restaurante - Hotel Ropino
                </div>
            </div>
        </div>
    </div>
</body>

</html>

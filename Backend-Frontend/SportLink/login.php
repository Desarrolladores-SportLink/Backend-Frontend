<?php
session_start();
require_once __DIR__ . '/includes/helpers.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesion | SportLink</title>
    <link rel="stylesheet" href="styles/app.css">
    <link rel="icon" type="image/png" href="styles/logo%20sin%20fondo.png">
</head>
<body class="auth-wrapper">
    <div class="auth-card">
        <img src="styles/logo%20sin%20fondo.png" class="logo" alt="SportLink">
        <h1>Bienvenido de vuelta</h1>
        <p class="subtitle">Conecta con entrenadores y escuelas deportivas</p>

        <?php if (!empty($_SESSION['mensaje'])): ?>
            <div class="toast toast--info"><?= e($_SESSION['mensaje']) ?></div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <form action="actions/login_action.php" method="POST">
            <div class="form-group">
                <label>Nombre de usuario</label>
                <input type="text" name="username" required autocomplete="username" placeholder="Ej. juanp23">
            </div>
            <div class="form-group">
                <label>Contrasena</label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn--primary btn--block">Entrar</button>
        </form>

        <p class="auth-link">
            &iquest;Aun no tienes cuenta? <a href="registro.php">Crear cuenta</a>
        </p>
    </div>
</body>
</html>

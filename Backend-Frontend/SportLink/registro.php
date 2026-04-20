<?php
session_start();
require_once __DIR__ . '/includes/helpers.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta | SportLink</title>
    <link rel="stylesheet" href="styles/app.css">
    <link rel="icon" type="image/png" href="styles/logo%20sin%20fondo.png">
    <script>
    function actualizarFormulario() {
        const rol = document.getElementById('rol').value;
        document.getElementById('campos-alumno').style.display  = (rol === 'alumno')  ? 'block' : 'none';
        document.getElementById('campos-maestro').style.display = (rol === 'maestro' || rol === 'escuela') ? 'block' : 'none';
        document.getElementById('campos-escuela').style.display = (rol === 'escuela') ? 'block' : 'none';
    }
    </script>
</head>
<body class="auth-wrapper">
    <div class="auth-card">
        <img src="styles/logo%20sin%20fondo.png" class="logo" alt="SportLink">
        <h1>Crea tu cuenta</h1>
        <p class="subtitle">Unete a la comunidad deportiva de SportLink</p>

        <?php if (!empty($_SESSION['mensaje'])): ?>
            <div class="toast toast--error"><?= e($_SESSION['mensaje']) ?></div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <form action="actions/registro_action.php" method="POST">
            <div class="form-group">
                <label>Nombre(s)</label>
                <input type="text" name="nombre" required>
            </div>
            <div class="form-group">
                <label>Apellidos</label>
                <input type="text" name="apellidos" required>
            </div>
            <div class="form-group">
                <label>Nombre de usuario</label>
                <input type="text" name="username" required placeholder="Ej. juanp23">
            </div>
            <div class="form-group">
                <label>Correo electronico</label>
                <input type="email" name="correo" required>
            </div>
            <div class="form-group">
                <label>Contrasena</label>
                <input type="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label>Quiero unirme como</label>
                <select name="rol" id="rol" onchange="actualizarFormulario()" required>
                    <option value="">Selecciona un rol...</option>
                    <option value="alumno">Alumno</option>
                    <option value="maestro">Entrenador / Maestro</option>
                    <option value="escuela">Escuela deportiva</option>
                </select>
            </div>

            <div id="campos-alumno" style="display:none;">
                <div class="form-group">
                    <label>Codigo de estudiante (opcional)</label>
                    <input type="text" name="codigo_estudiante">
                </div>
                <div class="form-group">
                    <label>Edad</label>
                    <input type="number" name="edad" min="6" max="120">
                </div>
            </div>

            <div id="campos-maestro" style="display:none;">
                <div class="form-group">
                    <label>Deporte principal</label>
                    <input type="text" name="deporte" placeholder="Ej. Tenis, Natacion">
                </div>
                <div class="form-group">
                    <label>Telefono de contacto</label>
                    <input type="tel" name="telefono">
                </div>
            </div>

            <div id="campos-escuela" style="display:none;">
                <div class="form-group">
                    <label>Nombre de la escuela</label>
                    <input type="text" name="nombre_escuela" placeholder="Ej. Club Deportivo Aguila">
                </div>
            </div>

            <button type="submit" class="btn btn--primary btn--block">Registrarse</button>
        </form>

        <p class="auth-link">
            &iquest;Ya tienes cuenta? <a href="login.php">Inicia sesion</a>
        </p>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
    <head>
        <title>Registro a SportLink</title>
        <link rel="stylesheet" href="styles/login_style.css">
    </head>

<body>
    <div class="login-container">
    <img src="styles/logo sin fondo.png" class="logo">
    <h2> Crear cuenta</h2>
    <form action="actions/registro_action.php" method="POST">

    <input type="text" name="nombre" placeholder="Nombre" required><br><br>

    <input type="email" name="correo" placeholder="Correo" required><br><br>

    <input type="password" name="password" placeholder="Contraseña" required><br><br>

    <select name="rol" required>
        <option value="">Únete como...</option>
        <option value="alumno">Alumno</option>
        <option value="maestro">Maestro</option>
        <option value="escuela">Escuela</option>
    </select><br><br>

    <button type="submit">Registrarse</button>

</form>
<br>
<a href="login.php">Volver al login</a>

</body>
</html>
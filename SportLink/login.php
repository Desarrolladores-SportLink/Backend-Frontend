<!DOCTYPE html>
<html>
<head>
 <title>Login SportLink</title>   
 <link rel="stylesheet" href="styles/login_style.css">
</head>

<body>
<div class="login-container">

    <img src="styles/logo sin fondo.png" class="logo">

    <h2>Bienvenido a SportLink</h2>
    <?php
session_start();

if (isset($_SESSION['mensaje'])) {
    echo "<div class='toast'>" . $_SESSION['mensaje'] . "</div>";
    unset($_SESSION['mensaje']);
}
?>
    <form action="actions/login_action.php" method="POST">
        <input type="email" name="correo" placeholder="Correo" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Entrar</button>
    </form>
<a href="registro.php" class="create-account">
    <button type="button">Crear cuenta</button>
</a>
</body>

</html>
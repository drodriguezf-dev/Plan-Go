<?php
require_once("../utiles/variables.php");
require_once("../utiles/funciones.php");

//-----------------------------------------------------
// Variables
//-----------------------------------------------------
$errores = [];
$email = isset($_GET['email']) ? $_GET['email'] : "";
$token = isset($_GET['token']) ? $_GET['token'] : "";

// Verificar si el email y token están presentes en la URL
if (empty($email) || empty($token)) {
    header("Location: ../acceso/login.php");
    exit();
}

// Conectar a la base de datos
$conexion = conectarPDO($host, $user, $password, $bbdd);

// Verificar que el email y token son válidos
$consulta = $conexion->prepare("SELECT * FROM usuarios WHERE email = :email AND token = :token");
$consulta->execute([
    "email" => $email,
    "token" => $token
]);
$usuario = $consulta->fetch();

if (!$usuario) {
    header("Location: ../acceso/login.php");
    exit();
}

//-----------------------------------------------------
// Procesar formulario de restablecimiento
//-----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password1 = isset($_POST['password1']) ? $_POST['password1'] : "";
    $password2 = isset($_POST['password2']) ? $_POST['password2'] : "";

    // Validaciones
    if (!validarRequerido($password1) || !validarRequerido($password2)) {
        $errores[] = "Ambos campos de contraseña son obligatorios.";
    } elseif ($password1 !== $password2) {
        $errores[] = "Las contraseñas no coinciden.";
    } elseif (strlen($password1) < 4 || strlen($password1) > 20) {
        $errores[] = "La contraseña debe tener entre 4 y 20 caracteres.";
    }

    // Si no hay errores, actualizar la contraseña
    if (count($errores) === 0) {
        $passwordHash = password_hash($password1, PASSWORD_BCRYPT);
        $update = $conexion->prepare("UPDATE usuarios SET password = :password, activo = 1, token = ''  WHERE email = :email");
        $update->execute([
            "password" => $passwordHash,
            "email" => $email
        ]);

        // Redirigir al login
        header("Location: ../acceso/login.php?restablecido=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>
</head>

<body>
    <h1>Restablecer contraseña</h1>
    <!-- Mostrar errores si los hay -->
    <?php if (count($errores) > 0): ?>
        <ul class="errores">
            <?php foreach ($errores as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- Formulario -->
    <form action="" method="post">
        <p>
            <label>
                Nueva contraseña:
                <input type="password" name="password1" required>
            </label>
        </p>
        <p>
            <label>
                Confirmar contraseña:
                <input type="password" name="password2" required>
            </label>
        </p>
        <p>
            <input type="submit" value="Guardar contraseña">
        </p>
    </form>
</body>

</html>

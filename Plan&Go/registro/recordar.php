<?php
require_once("../utiles/variables.php");
require_once("../utiles/funciones.php");

//-----------------------------------------------------
// Variables
//-----------------------------------------------------
$errores = [];
$email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : "";

// Comprobamos si nos llega el formulario por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //-----------------------------------------------------
    // Validaciones
    //-----------------------------------------------------
    // Email
    if (!validarRequerido($email)) {
        $errores[] = "Campo Email obligatorio.";
    }
    if (!validarEmail($email)) {
        $errores[] = "Campo Email no tiene un formato válido.";
    }

    // Comprobar que el email existe en la base de datos
    if (count($errores) === 0) {
        // Conecta con la base de datos
        $conexion = conectarPDO($host, $user, $password, $bbdd);

        $select = "SELECT token FROM usuarios WHERE email = :email";
        $consulta = $conexion->prepare($select);
        $consulta->execute(["email" => $email]);
        $usuario = $consulta->fetch();

        if ($usuario) {
            //-----------------------------------------------------
            // Actualizar el token en la base de datos
            //-----------------------------------------------------
            $token = bin2hex(openssl_random_pseudo_bytes(16));
            $update = "UPDATE usuarios SET token = :token WHERE email = :email";
            $actualizar = $conexion->prepare($update);
            $actualizar->execute(["token" => $token, "email" => $email]);

            //-----------------------------------------------------
            // Simular el envío del correo electrónico
            //-----------------------------------------------------
            $emailEncode = urlencode($email);
            $tokenEncode = urlencode($token);
            $url = "http://localhost:3000/registro/establecer.php?email=$emailEncode&token=$tokenEncode";

            $headers = [
                "From" => "dwes@php.com",
                "Content-type" => "text/plain; charset=utf-8"
            ];
            $textoEmail = "
            Hola!\n
            Has solicitado restablecer tu contraseña. Pulsa en el enlace a continuación para continuar:\n
            $url
            ";

            mail($email, 'Restablece tu contraseña', $textoEmail, $headers);
            echo "Correo enviado correctamente.";
        } else {
            // Si el email no está registrado
            $errores[] = "El email no está registrado. Verifica los datos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordar contraseña</title>
</head>

<body>
    <h1>Recordar contraseña</h1>
    <!-- Mostramos errores -->
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
                Correo electrónico:
                <input type="text" name="email">
            </label>
        </p>
        <p>
            <input type="submit" value="Recordar">
        </p>
    </form>
</body>

</html>

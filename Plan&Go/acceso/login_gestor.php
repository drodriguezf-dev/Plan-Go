<?php
require_once("../utiles/variables.php");
require_once("../utiles/funciones.php");

//-----------------------------------------------------
// Variables
//-----------------------------------------------------
$errores = [];
$email = isset($_REQUEST["email"]) ? trim($_REQUEST["email"]) : null;
$password2 = isset($_REQUEST["contrasena"]) ? $_REQUEST["contrasena"] : null;

// Comprobamos que nos llega los datos del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //-----------------------------------------------------
    // VALIDAR QUE NO HAY CAMPOS VACÍOS
    //-----------------------------------------------------
    if (empty($email) || empty($password2)) {
        $errores[] = "Debes completar todos los campos.";
    } else {
        try {
            //-----------------------------------------------------
            // COMPROBAR SI EL USUARIO EXISTE
            //-----------------------------------------------------
            // Conecta con base de datos
            $conexion = conectarPDO($host, $user, $password, $bbdd);

            // Prepara SELECT para obtener los datos del usuario
            $select = "SELECT id, nombre, password, perfil_id FROM gestores WHERE email = :email;";
            $consulta = $conexion->prepare($select);
            // Ejecuta consulta
            $consulta->execute(["email" => $email]);
            // Guardo el resultado
            $resultado = $consulta->fetch();

            if (!$resultado) {
                $errores[] = "Este correo no está registrado.";
            } else {
                //-----------------------------------------------------
                // COMPROBAR LA CONTRASEÑA
                //-----------------------------------------------------
                if (password_verify($password2, $resultado["password"])) {
                    // Si son correctos, creamos la sesión
                    session_name("sesion-privada");
                    session_start();
                    $_SESSION["email"] = $email;
                    $_SESSION["nombre"] = $resultado["nombre"]; // Almacenamos el nombre en la sesión
                    $_SESSION["perfil_id"] = $resultado["perfil_id"]; // Almacenamos el perfil_id en la sesión
                    $_SESSION["id"] = $resultado["id"]; // Almacenamos el id en la sesión
                    // Redireccionamos a la página segura
                    header("Location: ../index.php");
                    exit();
                } else {
                    $errores[] = "La contraseña es incorrecta.";
                }
            }
        } catch (PDOException $e) {
            $errores[] = "Error de conexión con la base de datos. Inténtelo más tarde.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold text-gray-700 text-center mb-4">Iniciar sesión (trabajador)</h1>

        <!-- Mostrar errores -->
        <?php if (count($errores) > 0): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>



        <!-- Formulario de login -->
        <form method="post" class="space-y-4">
            <div>
                <input type="text" name="email" placeholder="Email"
                    class="w-full p-3 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <input type="password" name="contrasena" placeholder="Contraseña"
                    class="w-full p-3 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <input type="submit" value="Entrar"
                    class="w-full bg-blue-500 hover:bg-blue-700 text-white p-3 rounded cursor-pointer transition">
            </div>
        </form>

        <!-- Enlaces adicionales -->
        <div class="mt-4 text-center">
            <a href="../index.php" class="text-blue-600 hover:underline">Continuar como invitado</a> |
            <a href="./login.php" class="text-blue-600 hover:underline">Iniciar como usuario</a>
        </div>
    </div>
</body>

</html>
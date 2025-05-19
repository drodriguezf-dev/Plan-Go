<?php
require_once("../utiles/variables.php");
require_once("../utiles/funciones.php");

//-----------------------------------------------------
// Variables
//-----------------------------------------------------
$errores = [];
$nombre = isset($_REQUEST["nombre"]) ? $_REQUEST["nombre"] : "";
$email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : "";
$password2 = isset($_REQUEST["password"]) ? $_REQUEST["password"] : "";
$perfil_id = isset($_REQUEST["perfil_id"]) ? $_REQUEST["perfil_id"] : 4; // Por defecto, demandante

// Comprobamos si nos llega los datos por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //-----------------------------------------------------
    // Validaciones
    //-----------------------------------------------------
    if (!validarRequerido($nombre)) $errores[] = "Campo Nombre obligatorio.";
    if (!validarRequerido($email)) $errores[] = "Campo Email obligatorio.";
    if (!validarEmail($email)) $errores[] = "Campo Email no tiene un formato válido.";
    if (!validarRequerido($password2)) $errores[] = "Campo Contraseña obligatorio.";

    // Verificar que no existe en la base de datos el mismo email
    $conexion = conectarPDO($host, $user, $password, $bbdd);
    $select = "SELECT COUNT(*) as numero FROM usuarios WHERE email = :email";
    $consulta = $conexion->prepare($select);
    $consulta->execute(["email" => $email]);
    $resultado = $consulta->fetch();
    if ($resultado["numero"] > 0) $errores[] = "La dirección de email ya está registrada.";

    //-----------------------------------------------------
    // Crear cuenta
    //-----------------------------------------------------
    if (count($errores) === 0) {
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $insert = "INSERT INTO usuarios (nombre, email, password, activo, token, perfil_id, created_at, updated_at) 
                   VALUES (:nombre, :email, :password, :activo, :token, :perfil_id, :created_at, :updated_at)";
        $consulta = $conexion->prepare($insert);
        $consulta->execute([
            "nombre" => $nombre,
            "email" => $email,
            "password" => password_hash($password2, PASSWORD_BCRYPT),
            "activo" => 0,
            "token" => $token,
            "perfil_id" => $perfil_id,
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s")
        ]);

        // Envío de email con token
        $emailEncode = urlencode($email);
        $tokenEncode = urlencode($token);
        $textoEmail = "Hola $nombre!\n\nGracias por registrarte. Para activar tu cuenta, haz clic en:\n";
        $textoEmail .= "http://localhost:3000/registro/verificar-cuenta.php?email=$emailEncode&token=$tokenEncode";
        mail($email, 'Activa tu cuenta', $textoEmail, ["From" => "dwes@php.com"]);

        header('Location: ../acceso/login.php?registrado=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold text-gray-700 text-center mb-4">Registro</h1>

        <!-- Mostrar errores -->
        <?php if (!empty($errores)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Formulario de registro -->
        <form method="post" class="space-y-4">
            <div>
                <label class="block text-gray-700">Nombre</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" 
                    class="w-full p-3 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-gray-700">Correo electrónico</label>
                <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>"
                    class="w-full p-3 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-gray-700">Contraseña</label>
                <input type="password" name="password"
                    class="w-full p-3 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-gray-700 mb-2">Perfil</label>
                <div class="flex space-x-4">
                    <label class="flex items-center">
                        <input type="radio" name="perfil_id" value="3" <?php echo ($perfil_id == 3) ? 'checked' : ''; ?>
                            class="mr-2">
                        Ofertante
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="perfil_id" value="4" <?php echo ($perfil_id == 4) ? 'checked' : ''; ?>
                            class="mr-2">
                        Demandante
                    </label>
                </div>
            </div>
            <div>
                <input type="submit" value="Registrarse"
                    class="w-full bg-blue-500 hover:bg-blue-700 text-white p-3 rounded cursor-pointer transition">
            </div>
        </form>

        <!-- Enlace de login -->
        <div class="mt-4 text-center">
            <a href="../acceso/login.php" class="text-blue-600 hover:underline">¿Ya tienes cuenta? Inicia sesión</a>
        </div>
        <div class="mt-4 text-center">
            <a href="../index.php" class="text-blue-600 hover:underline">Volver al inicio</a>
        </div>
    </div>
</body>
</html>

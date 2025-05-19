<?php
session_name("sesion-privada");
session_start();

require_once("../utiles/variables.php");
require_once("../utiles/funciones.php");

if (!isset($_SESSION['id']) || $_SESSION['perfil_id'] != 1) {
    header("Location: ../index.php?error=acceso_denegado");
    exit();
}

// Obtener mensajes de error o éxito
$mensaje = "";
$errores = $_SESSION['errores'] ?? [];
unset($_SESSION['errores']);

if (isset($_GET['success']) && $_GET['success'] == 'gestor_creado') {
    $mensaje = "Gestor creado con éxito.";
} elseif (isset($_GET['error']) && $_GET['error'] == 'registro_fallido') {
    $errores[] = "Error al registrar el gestor.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Gestor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <main class="container mx-auto p-6">
        <h3 class="text-3xl font-semibold text-gray-700 text-center mb-6">Crear Gestor</h3>

        <?php if (!empty($errores)): ?>
            <div class="max-w-lg mx-auto bg-red-100 text-red-700 p-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif (!empty($mensaje)): ?>
            <p class="max-w-lg mx-auto text-center font-bold text-lg text-green-600 mb-4 bg-green-100 p-3 rounded">
                <?php echo htmlspecialchars($mensaje); ?>
            </p>
        <?php endif; ?>

        <form action="../funcionalidad/crear_gestor.php" method="POST" class="max-w-lg mx-auto bg-white p-6 shadow-md rounded-lg">
            <label class="block mb-2 font-semibold">Nombre</label>
            <input type="text" name="nombre" class="w-full p-2 border rounded-md mb-4">

            <label class="block mb-2 font-semibold">Correo Electrónico</label>
            <input type="email" name="email" class="w-full p-2 border rounded-md mb-4">

            <label class="block mb-2 font-semibold">Contraseña</label>
            <input type="password" name="password" class="w-full p-2 border rounded-md mb-4">

            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                Crear Gestor
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="../index.php" class="text-blue-600 hover:underline">Volver al inicio</a>
        </div>
    </main>
</body>
</html>

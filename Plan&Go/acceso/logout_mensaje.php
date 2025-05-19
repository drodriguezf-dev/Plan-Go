<?php
session_name("sesion-privada");
session_start();

// Obtener el mensaje de la sesión
$mensaje = isset($_SESSION['mensaje_logout']) ? $_SESSION['mensaje_logout'] : "Redirigiendo...";

// Destruir la sesión después de mostrar el mensaje
session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrando sesión...</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        setTimeout(() => {
            window.location.href = "../index.php";
        }, 1000);
    </script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="bg-blue-500 text-white text-lg font-semibold px-6 py-4 rounded-lg shadow-lg">
        <?php echo htmlspecialchars($mensaje); ?>
    </div>
</body>
</html>

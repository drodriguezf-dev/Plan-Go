<?php
session_name("sesion-privada");
session_start();

require_once("../utiles/variables.php");
require_once("../utiles/funciones.php");

// Verificar si es demandante
if (!isset($_SESSION['perfil_id']) || $_SESSION['perfil_id'] != 4) {
    header("Location: ../index.php?error=acceso_denegado");
    exit();
}

$conexion = conectarPDO($host, $user, $password, $bbdd);
$id_usuario = $_SESSION['id'];

// Consulta para obtener las actividades en las que el usuario está inscrito
$consulta = "SELECT o.id, o.nombre, o.descripcion, o.fecha_actividad, o.aforo
             FROM ofertas o
             INNER JOIN solicitudes s ON o.id = s.oferta_id
             WHERE s.usuario_id = ?";
$stmt = $conexion->prepare($consulta);
$stmt->execute([$id_usuario]);
$actividades_apuntadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Actividades</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex flex-col min-h-screen">
    <nav class="bg-gradient-to-r from-blue-600 to-blue-800 p-4 text-white flex justify-between items-center shadow-md">
        <h1 class="text-2xl font-bold">Plan&Go</h1>
        <div class="space-x-4">
            <a href="../index.php" class="hover:underline">Inicio</a>
            <a href="../acceso/logout.php" class="bg-red-500 px-3 py-1 rounded-md hover:bg-red-700">Cerrar sesión</a>
        </div>
    </nav>

    <main class="container mx-auto p-6 flex-grow">
        <?php if (isset($_GET['mensaje'])): ?>
            <div id="mensaje" class="text-white text-center p-4 font-bold rounded-md mb-4 
        <?php echo (strpos($_GET['mensaje'], 'correctamente') !== false) ? 'bg-green-500' : 'bg-red-500'; ?>">
                <?php echo htmlspecialchars($_GET['mensaje']); ?>
            </div>

            <script>
                // Ocultar el mensaje después de 3 segundos
                setTimeout(() => {
                    document.getElementById('mensaje').style.display = 'none';
                }, 3000);
            </script>
        <?php endif; ?>
        <h3 class="text-3xl font-semibold text-gray-700 text-center mb-6">Mis Actividades</h3>

        <div class="grid md:grid-cols-3 sm:grid-cols-2 gap-6">
            <?php if (!empty($actividades_apuntadas)): ?>
                <?php foreach ($actividades_apuntadas as $actividad): ?>
                    <div class="bg-white shadow-lg rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <h4 class="text-xl font-semibold text-gray-800 mb-2">
                                <?php echo htmlspecialchars($actividad['nombre']); ?>
                            </h4>
                            <p class="text-gray-600 text-sm mb-2">
                                <?php echo htmlspecialchars($actividad['descripcion']); ?>
                            </p>
                            <p class="text-gray-500 text-sm">
                                <strong>Fecha:</strong> <?php echo htmlspecialchars($actividad['fecha_actividad']); ?>
                            </p>
                            <p class="text-gray-500 text-sm mb-4">
                                <strong>Aforo:</strong> <?php echo htmlspecialchars($actividad['aforo']); ?>
                            </p>
                        </div>
                        <form action="../funcionalidad/borrarse.php" method="POST">
                            <input type="hidden" name="oferta_id" value="<?php echo $actividad['id']; ?>">
                            <button type="submit" class="w-full bg-red-500 text-white px-3 py-2 rounded-md hover:bg-red-700">
                                Borrarme
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-600 text-xl col-span-3">No estás inscrito en ninguna actividad.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-gradient-to-r from-gray-800 to-gray-900 text-white text-center p-4 mt-10 w-full">
        <p>&copy; <?php echo date('Y'); ?> Plan&Go. Todos los derechos reservados.</p>
    </footer>
</body>

</html>
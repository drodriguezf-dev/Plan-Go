<?php
session_name("sesion-privada");
session_start();

require_once("../utiles/variables.php");
require_once("../utiles/funciones.php");

// Verificar si es administrador
if (!isset($_SESSION['perfil_id']) || $_SESSION['perfil_id'] != 1) {
    header("Location: ../index.php?error=acceso_denegado");
    exit();
}

$conexion = conectarPDO($host, $user, $password, $bbdd);

// Consulta para obtener todas las ofertas
$consulta = "SELECT id, nombre, descripcion, fecha_actividad, aforo, visada FROM ofertas";
$stmt = $conexion->prepare($consulta);
$stmt->execute();
$ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ofertas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex flex-col min-h-screen">
    <nav class="bg-gradient-to-r from-blue-600 to-blue-800 p-4 text-white flex justify-between items-center shadow-md">
        <h1 class="text-2xl font-bold">Gestión de Ofertas</h1>
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

        <h3 class="text-3xl font-semibold text-gray-700 text-center mb-6">Ofertas Disponibles</h3>

        <div class="grid md:grid-cols-3 sm:grid-cols-2 gap-6">
            <?php if (!empty($ofertas)): ?>
                <?php foreach ($ofertas as $oferta): ?>
                    <div class="bg-white shadow-lg rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <h4 class="text-xl font-semibold text-gray-800 mb-2">
                                <?php echo htmlspecialchars($oferta['nombre']); ?>
                            </h4>
                            <p class="text-gray-600 text-sm mb-2">
                                <?php echo htmlspecialchars($oferta['descripcion']); ?>
                            </p>
                            <p class="text-gray-500 text-sm">
                                <strong>Fecha:</strong> <?php echo htmlspecialchars($oferta['fecha_actividad']); ?>
                            </p>
                            <p class="text-gray-500 text-sm mb-4">
                                <strong>Aforo:</strong> <?php echo htmlspecialchars($oferta['aforo']); ?>
                            </p>

                            <div class="space-y-2 mb-4">
                                <!-- Botones de modificar, visar y eliminar en el mismo contenedor -->
                                <?php if ($oferta['visada'] == 0): ?>
                                    <form action="../funcionalidad/modificar_oferta.php" method="GET" class="w-full">
                                        <input type="hidden" name="oferta_id" value="<?php echo $oferta['id']; ?>">
                                        <button type="submit" class="w-full bg-yellow-500 text-white px-3 py-2 rounded-md hover:bg-yellow-700">
                                            Modificar
                                        </button>
                                    </form>

                                    <form action="../funcionalidad/visar.php" method="POST" class="w-full">
                                        <input type="hidden" name="oferta_id" value="<?php echo $oferta['id']; ?>">
                                        <button type="submit" class="w-full bg-green-500 text-white px-3 py-2 rounded-md hover:bg-green-700">
                                            Visar
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <!-- Botón de eliminar en el mismo contenedor -->
                                <form action="../funcionalidad/borrar_oferta.php" method="POST" class="w-full">
                                    <input type="hidden" name="oferta_id" value="<?php echo $oferta['id']; ?>">
                                    <button type="submit" class="w-full bg-red-500 text-white px-3 py-2 rounded-md hover:bg-red-700">
                                        Eliminar Oferta
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-600 text-xl col-span-3">No hay ofertas disponibles.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-gradient-to-r from-gray-800 to-gray-900 text-white text-center p-4 mt-10 w-full">
        <p>&copy; <?php echo date('Y'); ?> Plan&Go. Todos los derechos reservados.</p>
    </footer>
</body>

</html>

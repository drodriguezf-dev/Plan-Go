<?php
session_name("sesion-privada");
session_start();

require_once("../utiles/variables.php");
require_once("../utiles/funciones.php");

// Verificar si el usuario está autenticado y es ofertante
if (!isset($_SESSION['id']) || $_SESSION['perfil_id'] != 3) {
    header("Location: ../index.php?error=acceso_denegado");
    exit();
}

$id_usuario = $_SESSION['id'];
$mensaje = "";
$errores = [];

// Conectar a la base de datos
$conexion = conectarPDO($host, $user, $password, $bbdd);

// Obtener categorías
$categorias = $conexion->query("SELECT id, categoria FROM categorias")->fetchAll(PDO::FETCH_ASSOC);

// Si se ha enviado el formulario, procesarlo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar cada campo
    if (empty($_POST['categoria_id'])) $errores[] = "Debes seleccionar una categoría.";
    if (empty(trim($_POST['nombre']))) $errores[] = "El nombre de la oferta es obligatorio.";
    if (empty($_POST['fecha_actividad'])) $errores[] = "La fecha y hora de la actividad son obligatorias.";

    // Verificar que la fecha de actividad no esté en el pasado ni dentro de los próximos 6 días
    $fecha_actividad = $_POST['fecha_actividad'];
    $fecha_actual = date("Y-m-d H:i:s"); // Fecha y hora actuales
    $fecha_limite = date("Y-m-d H:i:s", strtotime("+6 days")); // Fecha límite (6 días en el futuro)

    if ($fecha_actividad < $fecha_actual) {
        $errores[] = "La fecha de la actividad no puede ser en el pasado.";
    } elseif ($fecha_actividad < $fecha_limite) {
        $errores[] = "La fecha de la actividad no puede ser creada antes de los próximos 6 días.";
    }

    if (empty($_POST['aforo']) || (int)$_POST['aforo'] <= 0) $errores[] = "El aforo debe ser mayor a 0.";


    // Mostrar errores si existen
    if (!empty($errores)) {
        $mensaje = implode("\n", $errores);
    } else {
        $categoria_id = $_POST['categoria_id'];
        $nombre = trim($_POST['nombre']);
        $aforo = (int)$_POST['aforo'];
        $descripcion = trim($_POST['descripcion'] ?? '');

        try {
            $consulta = $conexion->prepare("
                INSERT INTO ofertas (usuario_id, categoria_id, nombre, descripcion, fecha_actividad, aforo, visada, created_at, updated_at) 
                VALUES (:usuario_id, :categoria_id, :nombre, :descripcion, :fecha_actividad, :aforo, 0, NOW(), NOW())
            ");

            $consulta->execute([
                ":usuario_id" => $id_usuario,
                ":categoria_id" => $categoria_id,
                ":nombre" => $nombre,
                ":descripcion" => $descripcion,
                ":fecha_actividad" => $fecha_actividad,
                ":aforo" => $aforo
            ]);

            $mensaje = "Oferta creada con éxito.";
        } catch (PDOException $e) {
            $mensaje = "Error al crear la oferta.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Oferta</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

    <main class="container mx-auto p-6">
        <h3 class="text-3xl font-semibold text-gray-700 text-center mb-6">Crear Nueva Oferta</h3>

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
        <?php echo nl2br(htmlspecialchars($mensaje)); ?>
    </p>
<?php endif; ?>

        <form action="crear_oferta.php" method="POST" class="max-w-lg mx-auto bg-white p-6 shadow-md rounded-lg">
            <!-- Categoría -->
            <label class="block mb-2 font-semibold">¿Qué categoría será?</label>
            <select name="categoria_id" class="w-full p-2 border rounded-md mb-4">
                <option value="" disabled selected>Selecciona una categoría</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= $categoria['id'] ?>"><?= $categoria['categoria'] ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Nombre -->
            <label class="block mb-2 font-semibold">Nombre de la oferta</label>
            <input type="text" name="nombre" class="w-full p-2 border rounded-md mb-4">

            <!-- Descripción -->
            <label class="block mb-2 font-semibold">Descripción de la oferta</label>
            <textarea name="descripcion" class="w-full p-2 border rounded-md mb-4" rows="4"></textarea>

            <!-- Fecha y hora -->
            <label class="block mb-2 font-semibold">Fecha y hora de la actividad</label>
            <input type="datetime-local" name="fecha_actividad" class="w-full p-2 border rounded-md mb-4">

            <!-- Aforo -->
            <label class="block mb-2 font-semibold">Aforo máximo</label>
            <input type="number" name="aforo" class="w-full p-2 border rounded-md mb-4" min="1">

            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                Crear Oferta
            </button>
        </form>

        <!-- Enlace para volver a inicio -->
        <div class="text-center mt-4">
            <a href="../index.php" class="text-blue-600 hover:underline">Volver al inicio</a>
        </div>
    </main>

</body>
</html>

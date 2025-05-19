<?php
session_name("sesion-privada");
session_start();

require_once("../utiles/variables.php");
require_once("../utiles/funciones.php");

// Verificar si el usuario está autenticado y es ofertante
if (!isset($_SESSION['id']) || $_SESSION['perfil_id'] == 2 || $_SESSION['perfil_id'] == 4) {
    header("Location: ../index.php?error=acceso_denegado");
    exit();
}

$id_usuario = $_SESSION['id'];
$mensaje = "";
$errores = [];
$conexion = conectarPDO($host, $user, $password, $bbdd);

// Obtener categorías
$categorias = $conexion->query("SELECT id, categoria FROM categorias")->fetchAll(PDO::FETCH_ASSOC);

// Verificar si se recibe una oferta para editar
if (!isset($_GET['oferta_id']) || empty($_GET['oferta_id'])) {
    header("Location: ../index.php?error=oferta_no_encontrada");
    exit();
}

$id_oferta = $_GET['oferta_id'];

// Obtener la oferta
if ($_SESSION['perfil_id'] == 1) {
    $consulta = $conexion->prepare("SELECT * FROM ofertas WHERE id = :id");
    $consulta->execute([":id" => $id_oferta]);
    $oferta = $consulta->fetch(PDO::FETCH_ASSOC);
} else {
    $consulta = $conexion->prepare("SELECT * FROM ofertas WHERE id = :id AND usuario_id = :usuario_id");
    $consulta->execute([":id" => $id_oferta, ":usuario_id" => $id_usuario]);
    $oferta = $consulta->fetch(PDO::FETCH_ASSOC);
}
if (!$oferta) {
    header("Location: ../index.php?error=oferta_no_encontrada");
    exit();
}

// Procesar formulario si se envió
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['categoria_id'])) $errores[] = "Debes seleccionar una categoría.";
    if (empty(trim($_POST['nombre']))) $errores[] = "El nombre de la oferta es obligatorio.";
    if (empty($_POST['fecha_actividad'])) $errores[] = "La fecha y hora de la actividad son obligatorias.";
    if (empty($_POST['aforo']) || (int)$_POST['aforo'] <= 0) $errores[] = "El aforo debe ser mayor a 0.";

    if (empty($errores)) {
        try {
            // Verificar si la oferta sigue sin visar antes de actualizar
            $consulta = $conexion->prepare("SELECT visada FROM ofertas WHERE id = :id");
            $consulta->execute([":id" => $id_oferta]);
            $visada = $consulta->fetchColumn();

            if ($visada == 1) {
                $errores[] = "No puedes modificar esta oferta porque ha sido visada por un administrador.";
            } else {
                $consulta = $conexion->prepare("UPDATE ofertas SET categoria_id = :categoria_id, nombre = :nombre, descripcion = :descripcion, fecha_actividad = :fecha_actividad, aforo = :aforo, updated_at = NOW() WHERE id = :id  AND visada = 0");
                $consulta->execute([
                    ":categoria_id" => $_POST['categoria_id'],
                    ":nombre" => trim($_POST['nombre']),
                    ":descripcion" => trim($_POST['descripcion'] ?? ''),
                    ":fecha_actividad" => $_POST['fecha_actividad'],
                    ":aforo" => (int)$_POST['aforo'],
                    ":id" => $id_oferta,
                ]);
                $mensaje = "Oferta modificada con éxito.";
                $oferta = array_merge($oferta, $_POST); // Actualizar valores en el formulario
            }
        } catch (PDOException $e) {
            header("Location: ../index.php?error=error_base_datos");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Oferta</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex flex-col min-h-screen">
    <main class="container mx-auto p-6">
        <h3 class="text-3xl font-semibold text-gray-700 text-center mb-6">Modificar Oferta</h3>

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

        <form action="modificar_oferta.php?oferta_id=<?php echo $id_oferta; ?>" method="POST" class="max-w-lg mx-auto bg-white p-6 shadow-md rounded-lg">
            <label class="block mb-2 font-semibold">Categoría</label>
            <select name="categoria_id" class="w-full p-2 border rounded-md mb-4">
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= $categoria['id'] ?>" <?= ($oferta['categoria_id'] == $categoria['id']) ? 'selected' : '' ?>>
                        <?= $categoria['categoria'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="block mb-2 font-semibold">Nombre</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($oferta['nombre']); ?>" class="w-full p-2 border rounded-md mb-4">

            <label class="block mb-2 font-semibold">Descripción</label>
            <textarea name="descripcion" class="w-full p-2 border rounded-md mb-4" rows="4"><?php echo htmlspecialchars($oferta['descripcion']); ?></textarea>

            <label class="block mb-2 font-semibold">Fecha y hora</label>
            <input type="datetime-local" name="fecha_actividad" value="<?php echo htmlspecialchars($oferta['fecha_actividad']); ?>" class="w-full p-2 border rounded-md mb-4">

            <label class="block mb-2 font-semibold">Aforo</label>
            <input type="number" name="aforo" value="<?php echo htmlspecialchars($oferta['aforo']); ?>" class="w-full p-2 border rounded-md mb-4" min="1">

            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                Modificar Oferta
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="../index.php" class="text-blue-600 hover:underline">Volver al inicio</a>
        </div>
    </main>
</body>

</html>
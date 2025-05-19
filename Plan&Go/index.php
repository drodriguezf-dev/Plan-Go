<?php
session_name("sesion-privada"); // Establece el nombre de la sesión
session_start(); // Inicia la sesión

// Variables iniciales
$iniciado = false;
$ofertante = false;
$demandante = false;
$gestor = false;
$admin = false;
$nombre_usuario = '';
$rol_usuario = '';

// Incluye ficheros de variables y funciones
require_once("./utiles/variables.php");
require_once("./utiles/funciones.php");

// Verifica si el usuario está autenticado
if (isset($_SESSION['id'])) {
    $id_usuario = $_SESSION['id'];
}

if (isset($_SESSION['nombre'])) {
    $nombre_usuario = $_SESSION['nombre'];
}

if (isset($_SESSION['email'])) {
    $iniciado = true;

    if ($_SESSION['perfil_id'] == 3) {
        $ofertante = true;
        $rol_usuario = 'Ofertante';
    } elseif ($_SESSION['perfil_id'] == 4) {
        $demandante = true;
        $rol_usuario = 'Demandante';
    } elseif ($_SESSION['perfil_id'] == 2) {
        $gestor = true;
        $rol_usuario = 'Gestor';
    } elseif ($_SESSION['perfil_id'] == 1) {
        $admin = true;
        $rol_usuario = 'Administrador';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Actividades</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex flex-col min-h-screen">
    <?php
    // Verificar si hay un mensaje en la URL (GET)
    $mensaje = "";
    $tipo_mensaje = ""; // Puede ser "success" o "error"

    if (isset($_GET['success'])) {
        if ($_GET['success'] == 'apuntado') {
            $mensaje = "¡Te has apuntado con éxito!";
            $tipo_mensaje = "success";
        }
    } elseif (isset($_GET['error'])) {
        if ($_GET['error'] == 'aforo_lleno') {
            $mensaje = "No quedan plazas disponibles.";
            $tipo_mensaje = "error";
        } elseif ($_GET['error'] == 'actividad_proxima') {
            $mensaje = "No puedes apuntarte a una actividad que está a punto de comenzar";
            $tipo_mensaje = "error";
        } elseif ($_GET['error'] == 'acceso_denegado') {
            $mensaje = "No posees el rol necesario para estar ahí";
            $tipo_mensaje = "error";
        } elseif ($_GET['error'] == 'oferta_no_encontrada') {
            $mensaje = "Oferta no encontrada";
            $tipo_mensaje = "error";
        } elseif ($_GET['error'] == 'base_datos') {
            $mensaje = "Error en la base de datos";
            $tipo_mensaje = "error";
        }
    }

    // Solo mostramos si hay mensaje
    if ($mensaje !== ""):
    ?>
        <div id="mensaje" class="text-white text-center p-4 font-bold 
        <?php echo ($tipo_mensaje == 'success') ? 'bg-green-500' : 'bg-red-500'; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>

        <script>
            // Hacer que el mensaje desaparezca tras 3 segundos
            setTimeout(() => {
                document.getElementById('mensaje').style.display = 'none';
            }, 3000);
        </script>
    <?php endif; ?>
    <nav class="bg-gradient-to-r from-blue-600 to-blue-800 p-4 text-white flex justify-between items-center shadow-md">
        <h1 class="text-2xl font-bold">Plan&Go</h1>
        <div class="space-x-4">
            <a href="index.php" class="hover:underline">Inicio</a>

            <?php if ($demandante): ?>
                <a href="usuarios/mis_actividades.php" class="hover:underline">Mis actividades</a>
            <?php endif; ?>

            <?php if ($ofertante): ?>
                <a href="usuarios/actividades_creadas.php" class="hover:underline">Mis actividades creadas</a>
            <?php endif; ?>

            <?php if ($gestor): ?>
                <a href="./usuarios/actividades_gestor.php" class="hover:underline">Visar actividades</a>
            <?php endif; ?>

            <?php if ($admin): ?>
                <a href="./usuarios/admin_usuarios.php" class="hover:underline">Gestionar usuarios</a>
            <?php endif; ?>

            <?php if ($admin): ?>
                <a href="./usuarios/admin_ofertas.php" class="hover:underline">Gestionar ofertas</a>
            <?php endif; ?>

            <?php if ($iniciado): ?>
                <a href="acceso/logout.php" class="bg-red-500 px-3 py-1 rounded-md hover:bg-red-700">Cerrar sesión</a>
            <?php else: ?>
                <a href="acceso/login.php" class="bg-green-500 px-3 py-1 rounded-md hover:bg-green-700">Iniciar sesión</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container mx-auto p-6 flex-grow">
        <h3 class="text-3xl font-semibold text-gray-700 text-center mb-6">
            <?php if ($iniciado): ?>
                Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?>
            <?php else: ?>
                Bienvenido, invitado
            <?php endif; ?>
        </h3>

        <?php if ($iniciado): ?>
            <p class="text-xl text-center mb-6">Tu rol es: <?php echo $rol_usuario; ?></p>

            <?php if ($ofertante): ?>
                <div class="text-center mb-6">
                    <a href="funcionalidad/crear_oferta.php" class="inline-block bg-blue-600 text-white text-lg font-bold px-6 py-3 rounded-lg shadow-lg hover:bg-blue-700 transition-transform transform hover:scale-105">
                        Crear nueva oferta
                    </a>
                </div>
            <?php endif; ?>

        <?php endif; ?>

        <?php
        $conexion = conectarPDO($host, $user, $password, $bbdd);
        // Verificar si existe al menos un administrador en la base de datos
        $consulta_admin = "SELECT COUNT(*) FROM gestores WHERE perfil_id = 1";
        $stmt = $conexion->prepare($consulta_admin);
        $stmt->execute();
        $admin_existe = $stmt->fetchColumn();

        if ($admin_existe == 0) {
                $token = bin2hex(openssl_random_pseudo_bytes(16)); // Generar token único
                $nombre_admin = "Admin";
                $email_admin = "admin@email.com";
                $password_admin = password_hash("12345", PASSWORD_BCRYPT); // Hash seguro
        
                $consulta = $conexion->prepare("INSERT INTO gestores 
                    (nombre, email, password, perfil_id, created_at, updated_at) 
                    VALUES (:nombre, :email, :password, 1, :created_at, :updated_at)");
        
                $consulta->execute([
                    ":nombre" => $nombre_admin,
                    ":email" => $email_admin,
                    ":password" => $password_admin,
                    ":created_at" => date("Y-m-d H:i:s"),
                    ":updated_at" => date("Y-m-d H:i:s")
                ]);
    
        }

        // Consulta para obtener las ofertas visadas
        $consulta = "SELECT id, nombre, descripcion, fecha_actividad, aforo FROM ofertas WHERE visada = 1 AND aforo > 0";
        $resultado = resultadoConsulta($conexion, $consulta);

        // Consulta para obtener las ofertas en las que el usuario está apuntado
        if (isset($_SESSION['id'])) {
            $consulta_solicitudes = "SELECT oferta_id FROM solicitudes WHERE usuario_id = ?";
            $stmt = $conexion->prepare($consulta_solicitudes);
            $stmt->execute([$id_usuario]);
            $ofertas_apuntadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        ?>

        <div class="grid md:grid-cols-3 sm:grid-cols-2 gap-6">
            <?php while ($registro = $resultado->fetch(PDO::FETCH_ASSOC)): ?>
                <?php
                // Si es demandante y ya está apuntado a esta actividad, la saltamos
                if ($demandante && in_array($registro['id'], $ofertas_apuntadas)) {
                    continue;
                }
                ?>
                <div class="bg-white shadow-lg rounded-lg p-4 flex flex-col justify-between">
                    <div>
                        <h4 class="text-xl font-semibold text-gray-800 mb-2">
                            <?php echo htmlspecialchars($registro['nombre']); ?>
                        </h4>
                        <p class="text-gray-600 text-sm mb-2">
                            <?php echo htmlspecialchars($registro['descripcion']); ?>
                        </p>
                        <p class="text-gray-500 text-sm">
                            <strong>Fecha:</strong> <?php echo htmlspecialchars($registro['fecha_actividad']); ?>
                        </p>
                        <p class="text-gray-500 text-sm mb-4">
                            <strong>Aforo:</strong> <?php echo htmlspecialchars($registro['aforo']); ?>
                        </p>
                    </div>
                    <?php if ($demandante): ?>
                        <form action="funcionalidad/apuntarse.php" method="POST">
                            <input type="hidden" name="oferta_id" value="<?php echo $registro['id']; ?>">
                            <button type="submit" class="w-full bg-green-500 text-white px-3 py-2 rounded-md hover:bg-green-700">
                                Apuntarme
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <footer class="bg-gradient-to-r from-gray-800 to-gray-900 text-white text-center p-4 mt-10 w-full">
        <p>&copy; <?php echo date('Y'); ?> Plan&Go. Todos los derechos reservados.</p>
    </footer>
</body>

</html>
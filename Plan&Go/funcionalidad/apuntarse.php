<?php
session_name("sesion-privada");
session_start();

require_once("../utiles/variables.php");
require_once("../utiles/funciones.php");

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id']) || $_SESSION['perfil_id'] != 4) {
    header("Location: ../index.php?error=acceso_denegado");
    exit();
}

// Verificar si se recibe una oferta válida
if (!isset($_POST['oferta_id']) || !is_numeric($_POST['oferta_id'])) {
    header("Location: ../index.php?error=datos_invalidos");
    exit();
}

$id_usuario = $_SESSION['id'];
$oferta_id = $_POST['oferta_id'];

try {
    $conexion = conectarPDO($host, $user, $password, $bbdd);

    // Verificar la fecha de la actividad
    $consulta_fecha = $conexion->prepare("SELECT fecha_actividad, aforo FROM ofertas WHERE id = :oferta_id");
    $consulta_fecha->execute([":oferta_id" => $oferta_id]);
    $actividad = $consulta_fecha->fetch(PDO::FETCH_ASSOC);

    if (!$actividad) {
        header("Location: ../index.php?error=actividad_no_encontrada");
        exit();
    }

    $fecha_actividad = $actividad['fecha_actividad'];
    $aforo_actual = $actividad['aforo'];
    $fecha_limite = date('Y-m-d', strtotime('+5 days'));

    // Si la actividad es en menos de 5 días, no permitir la inscripción
    if ($fecha_actividad < $fecha_limite) {
        header("Location: ../index.php?error=actividad_proxima");
        exit();
    }

    // Verificar si hay aforo disponible
    if ($aforo_actual > 0) {
        // Insertar la solicitud
        $consulta = $conexion->prepare("INSERT INTO solicitudes (oferta_id, usuario_id, fecha_solicitud, created_at, updated_at) VALUES (:oferta_id, :usuario_id, NOW(), NOW(), NOW())");
        $consulta->execute([
            ":oferta_id" => $oferta_id,
            ":usuario_id" => $id_usuario
        ]);

        // Reducir el aforo en 1
        $consulta_update = $conexion->prepare("UPDATE ofertas SET aforo = aforo - 1 WHERE id = :oferta_id");
        $consulta_update->execute([":oferta_id" => $oferta_id]);

        header("Location: ../index.php?success=apuntado");
    } else {
        header("Location: ../index.php?error=aforo_lleno");
    }
} catch (PDOException $e) {
    header("Location: ../index.php?error=db");
}
exit();

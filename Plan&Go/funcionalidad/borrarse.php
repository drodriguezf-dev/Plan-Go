<?php
session_name("sesion-privada");
session_start();

require_once("../utiles/variables.php");
require_once("../utiles/funciones.php");

// Verificar si el usuario está autenticado y si se recibió una oferta_id
if (!isset($_SESSION['id']) || !isset($_POST['oferta_id']) || ($_SESSION['perfil_id'] != 4)) {
    header("Location: ../index.php?error=acceso_denegado");
    exit();
}

$id_usuario = $_SESSION['id'];
$oferta_id = $_POST['oferta_id'];

try {
    $conexion = conectarPDO($host, $user, $password, $bbdd);

    // Eliminar la solicitud del usuario
    $consulta = "DELETE FROM solicitudes WHERE usuario_id = ? AND oferta_id = ?";
    $stmt = $conexion->prepare($consulta);
    $stmt->execute([$id_usuario, $oferta_id]);

    if ($stmt->rowCount() > 0) {
        // Aumentar el aforo en 1 al borrarse
        $consulta_aforo = "UPDATE ofertas SET aforo = aforo + 1 WHERE id = ?";
        $stmt_aforo = $conexion->prepare($consulta_aforo);
        $stmt_aforo->execute([$oferta_id]);

        $mensaje = "Te has borrado correctamente de la actividad y el aforo ha sido actualizado.";
    } else {
        $mensaje = "No estabas apuntado a esta actividad.";
    }
} catch (PDOException $e) {
    $mensaje = "Error al borrarse: " . $e->getMessage();
}

// Redirigir pasando el mensaje por la URL
header("Location: ../usuarios/mis_actividades.php?mensaje=" . urlencode($mensaje));
exit();

<?php
session_name("sesion-privada");
session_start();

require_once("../utiles/variables.php");
require_once("../utiles/funciones.php");

// Verificar si el usuario está autenticado y si se recibió una oferta_id
if (!isset($_SESSION['id']) || !isset($_POST['oferta_id']) || $_SESSION['perfil_id'] == 3 || $_SESSION['perfil_id'] == 4) {
    header("Location: ../index.php?error=acceso_denegado");
    exit();
}

$id_usuario = $_SESSION['id'];
$oferta_id = $_POST['oferta_id'];

try {
    $conexion = conectarPDO($host, $user, $password, $bbdd);

    // Visar actividad
    $consulta = "UPDATE ofertas SET visada = 1 WHERE id = :oferta_id";
    $stmt = $conexion->prepare($consulta);
    $stmt->execute([":oferta_id" => $oferta_id]);

    // Redirigir de nuevo al listado
    if ($_SESSION['perfil_id'] == 2) {
        header("Location: ../usuarios/actividades_gestor.php");
        exit();
    } else {
        header("Location: ../usuarios/admin_ofertas.php");
        exit();
    }
} catch (PDOException $e) {
    // En caso de error, redirigir con mensaje de error
    $mensaje_error = "No ha sido posible visar la oferta. Intenta nuevamente.";
    header("Location: ../usuarios/actividades_gestor.php?error=" . urlencode($mensaje_error));
    exit();
}

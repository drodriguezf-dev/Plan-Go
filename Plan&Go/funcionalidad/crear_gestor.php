<?php
session_name("sesion-privada");
session_start();

require_once("../utiles/variables.php");
require_once("../utiles/funciones.php");

// Verificar si el usuario es administrador
if (!isset($_SESSION['id']) || $_SESSION['perfil_id'] != 1) {
    header("Location: ../index.php?error=acceso_denegado");
    exit();
}

$errores = [];
$mensaje = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password2 = $_POST['password'] ?? '';

    if (empty($nombre)) $errores[] = "El nombre es obligatorio.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = "Correo electrónico no válido.";
    if (strlen($password2) < 4) $errores[] = "La contraseña debe tener al menos 5 caracteres.";

    if (empty($errores)) {
        $conexion = conectarPDO($host, $user, $password, $bbdd);
        try {
            $token = bin2hex(openssl_random_pseudo_bytes(16));
            $consulta = $conexion->prepare("INSERT INTO gestores (nombre, email, password, perfil_id, created_at, updated_at) 
            VALUES (:nombre, :email, :password, 2, :created_at, :updated_at)");
            $consulta->execute([
                ":nombre" => $nombre,
                ":email" => $email,
                "password" => password_hash($password2, PASSWORD_BCRYPT),
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ]);
            header("Location: ../usuarios/admin_usuarios.php?success=gestor_creado");
            exit();
        } catch (PDOException $e) {
            header("Location: ../usuarios/admin_usuarios.php?error=registro_fallido");
            exit();
        }
    } else {
        session_start();
        $_SESSION['errores'] = $errores;
        header("Location: ../usuarios/admin_usuarios.php");
        exit();
    }
}

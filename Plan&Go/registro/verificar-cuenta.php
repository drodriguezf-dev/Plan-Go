<?php
require_once("../utiles/variables.php");
require_once("../utiles/funciones.php");
//-----------------------------------------------------
// Variables
//-----------------------------------------------------
$email = isset($_REQUEST["email"]) ? urldecode($_REQUEST["email"]) : "";
$token = isset($_REQUEST["token"]) ? urldecode($_REQUEST["token"]) : "";
//-----------------------------------------------------
// COMPROBAR SI SON CORRECTOS LOS DATOS
//-----------------------------------------------------
// Conecta con base de datos
$conexion = conectarPDO($host, $user, $password, $bbdd);
// Prepara SELECT para obtener la contraseña almacenada del usuario
$select = "SELECT COUNT(*) as numero FROM usuarios WHERE email = :email AND token
= :token AND activo = 0";
$consulta = $conexion->prepare($select);
// Ejecuta consulta
$consulta->execute([
"email" => $email,
"token" => $token
]);
$resultado = $consulta->fetch();
$consulta = null;
// Existe el usuario con el token
if ($resultado["numero"] > 0)
{
//-----------------------------------------------------
// ACTIVAR CUENTA
//-----------------------------------------------------
// Prepara la actualización
$update = "UPDATE usuarios SET activo = 1 WHERE email = :email";
$consulta = $conexion->prepare($update);
// Ejecuta actualización
$consulta->execute([
"email" => $email
]);
//-----------------------------------------------------
// REDIRECCIONAR A IDENTIFICACIÓN
//-----------------------------------------------------
header('Location: ../acceso/login.php?activada=1');
exit();
}
// No es un usuario válido, le enviamos al formulario de identificación
header('Location: ../index.php');
exit();
?>

<?php
session_name("sesion-privada");
session_start();

// Guarda el mensaje en la sesión antes de destruirla
$_SESSION['mensaje_logout'] = "Cerrando sesión...";

// Redirige a una página intermedia
header("Location: logout_mensaje.php");
exit();
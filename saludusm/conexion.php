<?php
$host = "127.0.0.1";   // mejor que "localhost" para evitar problemas de socket
$usuario = "root";     // o el usuario que hayas creado
$password = "prueba123";        // pon aquí la contraseña si tu root la tiene
$base_datos = "salud_usm";
$puerto = 3399;        // puerto que XAMPP muestra para MySQL

$conexion = new mysqli($host, $usuario, $password, $base_datos, $puerto);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>

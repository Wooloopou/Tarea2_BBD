<?php

require_once "conexion.php";

$password = "password";

$hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "UPDATE Usuario
        SET Password_hash = ?
        WHERE Usuario IN ('paciente1', 'medico1', 'admin')";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $hash);

if ($stmt->execute()) {
    echo "Contraseñas actualizadas correctamente.<br>";
    echo "Usuarios afectados: " . $stmt->affected_rows . "<br>";
    echo "Hash generado correctamente.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conexion->close();

?>
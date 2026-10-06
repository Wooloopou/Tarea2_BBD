<?php

session_start();

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Paciente") {
    header("Location: ../login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel del Paciente - SaludUSM</title>
</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Panel del Paciente</h2>

    <p>
        Bienvenido,
        <strong><?php echo htmlspecialchars($_SESSION["usuario"]); ?></strong>
    </p>

    <hr>

    <h3>Opciones</h3>

    <ul>
        <li>
            <a href="agendar.php">Agendar cita</a>
        </li>

        <li>
            <a href="mis_citas.php">Mis citas</a>
        </li>

        <li>
            <a href="historial.php">Historial de atenciones</a>
        </li>

        <li>
            <a href="recetas.php">Mis recetas</a>
        </li>

        <li>
            <a href="mis_datos.php">Mis datos personales</a>
        </li>
    </ul>
    <br>

    <a href="../logout.php">Cerrar sesión</a>

</body>

</html>
<?php

session_start();

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Medico") {
    header("Location: ../login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel del Médico - SaludUSM</title>
</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Panel del Médico</h2>

    <p>
        Bienvenido,
        <strong><?php echo htmlspecialchars($_SESSION["usuario"]); ?></strong>
    </p>

    <hr>

    <h3>Opciones</h3>

    <ul>
        <li>
            <a href="agenda.php">Mi agenda</a>
        </li>

        <li>
            <a href="agenda.php">Gestionar estado de citas</a>
        </li>

        <li>
            <a href="registrar_atencion.php">Registrar atención</a>
        </li>
        <li>
            <a href="mis_datos.php">Mis datos personales</a>
        </li>
    </ul>

    <br>

    <a href="../logout.php">Cerrar sesión</a>

</body>

</html>
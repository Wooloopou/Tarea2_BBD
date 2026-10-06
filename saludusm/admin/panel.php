<?php

session_start();

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Administrador") {
    header("Location: ../login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel del Administrador - SaludUSM</title>
</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Panel del Administrador</h2>

    <p>
        Bienvenido,
        <strong><?php echo htmlspecialchars($_SESSION["usuario"]); ?></strong>
    </p>

    <hr>

    <h3>Opciones</h3>

    <ul>
        <li>
            <a href="centros.php">Gestionar centros médicos</a>
        </li>
        <li>
            <a href="medicos.php">Gestionar médicos</a>
        </li>
        <li>
            <a href="especialidades.php">Gestionar especialidades</a>
        </li>
        <li>
            <a href="medico_centro.php">
                Asignar médicos a centros médicos
            </a>
        </li>
        <li>
            <a href="medico_especialidad.php">
                Asignar especialidades a médicos
            </a>
        </li>
        <li>
            <a href="mis_datos.php">Mis datos personales</a>
        </li>
    </ul>

    <br>

    <a href="../logout.php">Cerrar sesión</a>

</body>

</html>
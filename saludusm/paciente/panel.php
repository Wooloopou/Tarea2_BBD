<?php

session_start();

if (
    !isset($_SESSION["id_usuario"]) ||
    $_SESSION["rol"] !== "Paciente"
) {

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

    <strong>
        <?php echo htmlspecialchars($_SESSION["usuario"]); ?>
    </strong>

</p>


<hr>


<h3>Opciones</h3>

<table border="1">

    <tr>
        <th>Función</th>
        <th>Acceso</th>
    </tr>

    <tr>

        <td>Buscar médicos</td>

        <td>
            <a href="../Pag_principal.php">
                Ir
            </a>
        </td>

    </tr>

    <tr>

        <td>Agendar cita</td>

        <td>
            <a href="agendar.php">
                Ir
            </a>
        </td>

    </tr>

    <tr>

        <td>Mis citas</td>

        <td>
            <a href="mis_citas.php">
                Ir
            </a>
        </td>

    </tr>

</table>


<br>


<a href="../logout.php">
    Cerrar sesión
</a>

</body>

</html>
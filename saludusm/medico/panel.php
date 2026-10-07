<?php

session_start();

if (
    !isset($_SESSION["id_usuario"]) ||
    $_SESSION["rol"] !== "Medico"
) {

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

        <td>Mi agenda</td>

        <td>

            <a href="agenda.php">
                Ir
            </a>

        </td>

    </tr>


    <tr>

        <td>Registrar atención</td>

        <td>

            <a href="agenda.php">
                Ir desde agenda
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
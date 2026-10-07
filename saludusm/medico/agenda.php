<?php

session_start();

require_once "../conexion.php";


if (
    !isset($_SESSION["id_usuario"]) ||
    $_SESSION["rol"] !== "Medico"
) {

    header("Location: ../login.php");
    exit();

}


$rut_medico = $_SESSION["rut_medico"];


/*
 * Si no se selecciona una fecha,
 * se utiliza el día actual.
 */

$fecha = isset($_GET["fecha"])
    ? $_GET["fecha"]
    : date("Y-m-d");


/* ======================================
   OBTENER CITAS DEL MÉDICO
   ====================================== */

$sql = "

    SELECT

        c.Id_cita,

        TIME(c.Fecha_hora) AS Hora,

        p.Nombre_completo AS Paciente,

        ps.Descripcion AS Prevision,

        cm.Nombre AS Centro,

        ec.Descripcion AS Estado

    FROM Cita c

    JOIN Paciente p
        ON c.Rut_paciente = p.Rut_paciente

    JOIN Prevision_Salud ps
        ON p.Id_prevision = ps.Id_prevision

    JOIN Centro_medico cm
        ON c.Codigo_centro = cm.Codigo_interno

    JOIN Estado_cita ec
        ON c.Estado_cita = ec.Id_estado

    WHERE c.Rut_medico = ?

      AND DATE(c.Fecha_hora) = ?

    ORDER BY c.Fecha_hora ASC

";


$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "ss",
    $rut_medico,
    $fecha
);

$stmt->execute();

$resultado = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Agenda médica - SaludUSM</title>

</head>

<body>

<h1>SaludUSM</h1>

<h2>Agenda del médico</h2>


<p>

    Médico:

    <strong>
        <?php echo htmlspecialchars($_SESSION["usuario"]); ?>
    </strong>

</p>


<form method="GET">

    <label>

        Seleccione el día:

    </label>


    <input
        type="date"
        name="fecha"
        value="<?php echo htmlspecialchars($fecha); ?>"
        required
    >


    <button type="submit">

        Ver agenda

    </button>

</form>


<br>


<h3>
    Citas del <?php echo htmlspecialchars($fecha); ?>
</h3>


<table border="1">

    <tr>

        <th>Hora</th>

        <th>Paciente</th>

        <th>Previsión</th>

        <th>Centro</th>

        <th>Estado</th>

        <th>Atención</th>

    </tr>


    <?php if ($resultado->num_rows > 0): ?>


        <?php while ($fila = $resultado->fetch_assoc()): ?>


            <tr>

                <td>

                    <?php
                    echo htmlspecialchars(
                        $fila["Hora"]
                    );
                    ?>

                </td>


                <td>

                    <?php
                    echo htmlspecialchars(
                        $fila["Paciente"]
                    );
                    ?>

                </td>


                <td>

                    <?php
                    echo htmlspecialchars(
                        $fila["Prevision"]
                    );
                    ?>

                </td>


                <td>

                    <?php
                    echo htmlspecialchars(
                        $fila["Centro"]
                    );
                    ?>

                </td>


                <td>

                    <?php
                    echo htmlspecialchars(
                        $fila["Estado"]
                    );
                    ?>

                </td>


                <td>

                    <a
                        href="registrar_atencion.php?id_cita=<?php echo $fila["Id_cita"]; ?>"
                    >

                        Registrar atención

                    </a>

                </td>

            </tr>


        <?php endwhile; ?>


    <?php else: ?>


        <tr>

            <td colspan="6">

                No hay citas para este día.

            </td>

        </tr>


    <?php endif; ?>


</table>


<br>


<a href="panel.php">
    Volver al panel
</a>


<br><br>


<a href="../Pag_principal.php">
    Página principal
</a>


</body>

</html>
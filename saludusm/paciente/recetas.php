<?php

session_start();
require_once "../conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Paciente") {
    header("Location: ../login.php");
    exit();
}

$rut_paciente = $_SESSION["rut_paciente"];

/*
 * Obtener las recetas del paciente.
 */
$sql = "SELECT
            r.Id_receta,
            c.Id_cita,
            c.Fecha_hora,
            m.Nombre_completo AS Medico,
            r.Id_atencion,
            dr.Id_detalle,
            dr.Medicamento,
            dr.Dosis,
            dr.Cantidad_dias
        FROM Receta r
        INNER JOIN Atencion a
            ON r.Id_atencion = a.Id_atencion
        INNER JOIN Cita c
            ON a.Id_cita = c.Id_cita
        INNER JOIN Medico m
            ON c.Rut_medico = m.Rut_medico
        INNER JOIN Detalle_Receta dr
            ON r.Id_receta = dr.Id_receta
        WHERE c.Rut_paciente = ?
        ORDER BY c.Fecha_hora DESC, r.Id_receta DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $rut_paciente);
$stmt->execute();

$recetas = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mis recetas - SaludUSM</title>
</head>

<body>

<h1>SaludUSM</h1>

<h2>Mis recetas</h2>

<?php if ($recetas->num_rows === 0): ?>

    <p>No tiene recetas registradas.</p>

<?php else: ?>

    <table border="1" cellpadding="8">

        <tr>
            <th>ID Receta</th>
            <th>ID Cita</th>
            <th>Fecha</th>
            <th>Médico</th>
            <th>Medicamento</th>
            <th>Dosis</th>
            <th>Días</th>
        </tr>

        <?php while ($receta = $recetas->fetch_assoc()): ?>

            <tr>

                <td>
                    <?php echo $receta["Id_receta"]; ?>
                </td>

                <td>
                    <?php echo $receta["Id_cita"]; ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($receta["Fecha_hora"]); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($receta["Medico"]); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($receta["Medicamento"]); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($receta["Dosis"]); ?>
                </td>

                <td>
                    <?php echo $receta["Cantidad_dias"]; ?>
                </td>

            </tr>

        <?php endwhile; ?>

    </table>

<?php endif; ?>

<br>

<a href="panel.php">Volver al panel</a>

</body>

</html>
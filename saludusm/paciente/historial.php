<?php

session_start();
require_once "../conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Paciente") {
    header("Location: ../login.php");
    exit();
}

$rut_paciente = $_SESSION["rut_paciente"];

/*
 * Obtener las atenciones del paciente.
 * Solo se muestran citas que tienen una atención registrada.
 */
$sql = "SELECT
            a.Id_atencion,
            c.Id_cita,
            c.Fecha_hora,
            m.Nombre_completo AS Medico,
            e.Tipo_especialidad AS Especialidad,
            a.Motivo_consulta,
            a.Observaciones_clinicas
        FROM Atencion a
        INNER JOIN Cita c
            ON a.Id_cita = c.Id_cita
        INNER JOIN Medico m
            ON c.Rut_medico = m.Rut_medico
        INNER JOIN Especialidad e
            ON c.Id_especialidad = e.Id_especialidad
        WHERE c.Rut_paciente = ?
        ORDER BY c.Fecha_hora DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $rut_paciente);
$stmt->execute();

$atenciones = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Historial de atenciones - SaludUSM</title>
</head>

<body>

<h1>SaludUSM</h1>

<h2>Historial de atenciones</h2>

<?php if ($atenciones->num_rows === 0): ?>

    <p>No tiene atenciones registradas.</p>

<?php else: ?>

    <table border="1" cellpadding="8">

        <tr>
            <th>ID Atención</th>
            <th>ID Cita</th>
            <th>Fecha y hora</th>
            <th>Médico</th>
            <th>Especialidad</th>
            <th>Motivo de consulta</th>
            <th>Observaciones</th>
        </tr>

        <?php while ($atencion = $atenciones->fetch_assoc()): ?>

            <tr>

                <td>
                    <?php echo $atencion["Id_atencion"]; ?>
                </td>

                <td>
                    <?php echo $atencion["Id_cita"]; ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($atencion["Fecha_hora"]); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($atencion["Medico"]); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($atencion["Especialidad"]); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($atencion["Motivo_consulta"]); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($atencion["Observaciones_clinicas"]); ?>
                </td>

            </tr>

        <?php endwhile; ?>

    </table>

<?php endif; ?>

<br>

<a href="panel.php">Volver al panel</a>

</body>

</html>

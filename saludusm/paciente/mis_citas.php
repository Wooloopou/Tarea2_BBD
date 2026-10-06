<?php

session_start();
require_once "../conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Paciente") {
    header("Location: ../login.php");
    exit();
}

$rut_paciente = $_SESSION["rut_paciente"];
$mensaje = "";


/* =========================
   CANCELAR CITA
   ========================= */

if (isset($_GET["cancelar"])) {

    $id_cita = intval($_GET["cancelar"]);

    $sql = "UPDATE Cita
            SET Estado_cita = 5
            WHERE Id_cita = ?
            AND Rut_paciente = ?
            AND Estado_cita IN (1, 2)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("is", $id_cita, $rut_paciente);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $mensaje = "La cita fue cancelada correctamente.";
    } else {
        $mensaje = "No se puede cancelar esta cita.";
    }
}


/* =========================
   REPROGRAMAR CITA
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_cita = intval($_POST["id_cita"]);
    $nueva_fecha = $_POST["nueva_fecha"];

    if (empty($nueva_fecha)) {

        $mensaje = "Debe seleccionar una nueva fecha y hora.";

    } else {

        /* Obtener médico y centro de la cita */
        $sql = "SELECT Rut_medico, Codigo_centro
                FROM Cita
                WHERE Id_cita = ?
                AND Rut_paciente = ?
                AND Estado_cita IN (1, 2)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("is", $id_cita, $rut_paciente);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {

            $mensaje = "La cita no existe o no puede ser reprogramada.";

        } else {

            $cita = $resultado->fetch_assoc();

            $rut_medico = $cita["Rut_medico"];
            $codigo_centro = $cita["Codigo_centro"];


            /* Verificar horario del médico */
            $sql = "SELECT Id_cita
                    FROM Cita
                    WHERE Rut_medico = ?
                    AND Codigo_centro = ?
                    AND Fecha_hora = ?
                    AND Id_cita <> ?";

            $stmt = $conexion->prepare($sql);

            $stmt->bind_param(
                "sssi",
                $rut_medico,
                $codigo_centro,
                $nueva_fecha,
                $id_cita
            );

            $stmt->execute();

            if ($stmt->get_result()->num_rows > 0) {

                $mensaje = "El médico ya tiene una cita en ese horario.";

            } else {

                /* Verificar horario del paciente */
                $sql = "SELECT Id_cita
                        FROM Cita
                        WHERE Rut_paciente = ?
                        AND Fecha_hora = ?
                        AND Id_cita <> ?";

                $stmt = $conexion->prepare($sql);

                $stmt->bind_param(
                    "ssi",
                    $rut_paciente,
                    $nueva_fecha,
                    $id_cita
                );

                $stmt->execute();

                if ($stmt->get_result()->num_rows > 0) {

                    $mensaje = "Ya tiene otra cita en ese horario.";

                } else {

                    /* Actualizar cita */
                    $sql = "UPDATE Cita
                            SET Fecha_hora = ?
                            WHERE Id_cita = ?
                            AND Rut_paciente = ?";

                    $stmt = $conexion->prepare($sql);

                    $stmt->bind_param(
                        "sis",
                        $nueva_fecha,
                        $id_cita,
                        $rut_paciente
                    );

                    if ($stmt->execute()) {
                        $mensaje = "La cita fue reprogramada correctamente.";
                    } else {
                        $mensaje = "No fue posible reprogramar la cita.";
                    }
                }
            }
        }
    }
}


/* =========================
   OBTENER CITAS DEL PACIENTE
   ========================= */

$sql = "SELECT
            c.Id_cita,
            c.Fecha_hora,
            m.Nombre_completo AS Medico,
            e.Tipo_especialidad AS Especialidad,
            cm.Nombre AS Centro,
            ec.Descripcion AS Estado
        FROM Cita c
        INNER JOIN Medico m
            ON c.Rut_medico = m.Rut_medico
        INNER JOIN Especialidad e
            ON c.Id_especialidad = e.Id_especialidad
        INNER JOIN Centro_medico cm
            ON c.Codigo_centro = cm.Codigo_interno
        INNER JOIN Estado_cita ec
            ON c.Estado_cita = ec.Id_estado
        WHERE c.Rut_paciente = ?
        ORDER BY c.Fecha_hora DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $rut_paciente);
$stmt->execute();

$citas = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Mis citas - SaludUSM</title>

</head>

<body>

<h1>SaludUSM</h1>

<h2>Mis citas</h2>


<?php if (!empty($mensaje)): ?>

    <p>
        <strong>
            <?php echo htmlspecialchars($mensaje); ?>
        </strong>
    </p>

<?php endif; ?>


<table border="1" cellpadding="8">

    <tr>

        <th>ID</th>
        <th>Fecha y hora</th>
        <th>Médico</th>
        <th>Especialidad</th>
        <th>Centro</th>
        <th>Estado</th>
        <th>Acciones</th>

    </tr>


    <?php while ($cita = $citas->fetch_assoc()): ?>

        <tr>

            <td>
                <?php echo $cita["Id_cita"]; ?>
            </td>

            <td>
                <?php echo htmlspecialchars($cita["Fecha_hora"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($cita["Medico"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($cita["Especialidad"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($cita["Centro"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($cita["Estado"]); ?>
            </td>

            <td>

                <?php if (
                    $cita["Estado"] === "Reservada" ||
                    $cita["Estado"] === "Confirmada"
                ): ?>

                    <form method="POST">

                        <input
                            type="hidden"
                            name="id_cita"
                            value="<?php echo $cita["Id_cita"]; ?>"
                        >

                        <input
                            type="datetime-local"
                            name="nueva_fecha"
                            required
                        >

                        <button type="submit">
                            Reprogramar
                        </button>

                    </form>

                    <br>

                    <a
                        href="mis_citas.php?cancelar=<?php echo $cita["Id_cita"]; ?>"
                        onclick="return confirm('¿Está seguro de cancelar esta cita?');"
                    >
                        Cancelar
                    </a>

                <?php else: ?>

                    No disponible

                <?php endif; ?>

            </td>

        </tr>

    <?php endwhile; ?>

</table>


<br>

<a href="panel.php">
    Volver al panel
</a>

</body>

</html>
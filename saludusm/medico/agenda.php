<?php

session_start();
require_once "../conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Medico") {
    header("Location: ../login.php");
    exit();
}

$rut_medico = $_SESSION["rut_medico"];
$mensaje = "";


/* =========================
   ACTUALIZAR ESTADO
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_cita = intval($_POST["id_cita"]);
    $nuevo_estado = intval($_POST["estado"]);

    /*
     * El médico solamente puede modificar
     * sus propias citas.
     */
    $sql = "UPDATE Cita
            SET Estado_cita = ?
            WHERE Id_cita = ?
            AND Rut_medico = ?";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "iis",
        $nuevo_estado,
        $id_cita,
        $rut_medico
    );

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $mensaje = "Estado de la cita actualizado correctamente.";
    } else {
        $mensaje = "No fue posible actualizar la cita.";
    }
}


/* =========================
   OBTENER AGENDA
   ========================= */

$sql = "SELECT
            c.Id_cita,
            c.Fecha_hora,
            p.Nombre_completo AS Paciente,
            e.Tipo_especialidad AS Especialidad,
            cm.Nombre AS Centro,
            ec.Id_estado,
            ec.Descripcion AS Estado
        FROM Cita c
        INNER JOIN Paciente p
            ON c.Rut_paciente = p.Rut_paciente
        INNER JOIN Especialidad e
            ON c.Id_especialidad = e.Id_especialidad
        INNER JOIN Centro_medico cm
            ON c.Codigo_centro = cm.Codigo_interno
        INNER JOIN Estado_cita ec
            ON c.Estado_cita = ec.Id_estado
        WHERE c.Rut_medico = ?
        ORDER BY c.Fecha_hora ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $rut_medico);
$stmt->execute();

$agenda = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Mi agenda - SaludUSM</title>

</head>

<body>

<h1>SaludUSM</h1>

<h2>Mi agenda</h2>

<?php if (!empty($mensaje)): ?>

    <p>
        <strong>
            <?php echo htmlspecialchars($mensaje); ?>
        </strong>
    </p>

<?php endif; ?>


<?php if ($agenda->num_rows === 0): ?>

    <p>No tiene citas registradas.</p>

<?php else: ?>

<table border="1" cellpadding="8">

    <tr>

        <th>ID</th>
        <th>Fecha y hora</th>
        <th>Paciente</th>
        <th>Especialidad</th>
        <th>Centro</th>
        <th>Estado</th>
        <th>Actualizar estado</th>

    </tr>


    <?php while ($cita = $agenda->fetch_assoc()): ?>

        <tr>

            <td>
                <?php echo $cita["Id_cita"]; ?>
            </td>

            <td>
                <?php echo htmlspecialchars($cita["Fecha_hora"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($cita["Paciente"]); ?>
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

                <form method="POST">

                    <input
                        type="hidden"
                        name="id_cita"
                        value="<?php echo $cita["Id_cita"]; ?>"
                    >

                    <select name="estado">

                        <option
                            value="1"
                            <?php if ($cita["Id_estado"] == 1) echo "selected"; ?>
                        >
                            Reservada
                        </option>

                        <option
                            value="2"
                            <?php if ($cita["Id_estado"] == 2) echo "selected"; ?>
                        >
                            Confirmada
                        </option>

                        <option
                            value="3"
                            <?php if ($cita["Id_estado"] == 3) echo "selected"; ?>
                        >
                            Atendida
                        </option>

                        <option
                            value="4"
                            <?php if ($cita["Id_estado"] == 4) echo "selected"; ?>
                        >
                            No Asistió
                        </option>

                        <option
                            value="5"
                            <?php if ($cita["Id_estado"] == 5) echo "selected"; ?>
                        >
                            Cancelada
                        </option>

                    </select>

                    <button type="submit">
                        Actualizar
                    </button>

                </form>

            </td>

        </tr>

    <?php endwhile; ?>

</table>

<?php endif; ?>


<br>

<a href="panel.php">
    Volver al panel
</a>

<br><br>

<a href="../logout.php">
    Cerrar sesión
</a>

</body>

</html>

<?php

session_start();
require_once "../conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Paciente") {
    header("Location: ../login.php");
    exit();
}

$rut_paciente = $_SESSION["rut_paciente"];
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fecha_hora = $_POST["fecha_hora"];
    $rut_medico = $_POST["rut_medico"];
    $codigo_centro = $_POST["codigo_centro"];
    $id_especialidad = $_POST["id_especialidad"];

    if (empty($fecha_hora) || empty($rut_medico) ||
        empty($codigo_centro) || empty($id_especialidad)) {

        $mensaje = "Debe completar todos los campos.";

    } else {

        /*
         * Verificar que el médico tenga esa especialidad
         * y atienda en ese centro.
         */
        $sql = "SELECT 1
                FROM Medico_Especialidad
                WHERE Rut_medico = ?
                AND Id_especialidad = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("si", $rut_medico, $id_especialidad);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {

            $mensaje = "El médico no tiene esa especialidad.";

        } else {

            $sql = "SELECT 1
                    FROM Medico_Centro
                    WHERE Rut_medico = ?
                    AND Codigo_interno = ?";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ss", $rut_medico, $codigo_centro);
            $stmt->execute();

            if ($stmt->get_result()->num_rows === 0) {

                $mensaje = "El médico no atiende en ese centro.";

            } else {

                /*
                 * Verificar que el paciente no tenga
                 * otra cita en el mismo horario.
                 */
                $sql = "SELECT Id_cita
                        FROM Cita
                        WHERE Rut_paciente = ?
                        AND Fecha_hora = ?";

                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("ss", $rut_paciente, $fecha_hora);
                $stmt->execute();

                if ($stmt->get_result()->num_rows > 0) {

                    $mensaje = "Ya tiene una cita en ese horario.";

                } else {

                    /*
                     * Verificar disponibilidad del médico.
                     */
                    $sql = "SELECT Id_cita
                            FROM Cita
                            WHERE Rut_medico = ?
                            AND Codigo_centro = ?
                            AND Fecha_hora = ?";

                    $stmt = $conexion->prepare($sql);
                    $stmt->bind_param(
                        "sss",
                        $rut_medico,
                        $codigo_centro,
                        $fecha_hora
                    );

                    $stmt->execute();

                    if ($stmt->get_result()->num_rows > 0) {

                        $mensaje = "El médico ya tiene una cita en ese horario.";

                    } else {

                        $estado = 1;

                        $sql = "INSERT INTO Cita
                                (
                                    Fecha_hora,
                                    Rut_paciente,
                                    Rut_medico,
                                    Codigo_centro,
                                    Id_especialidad,
                                    Estado_cita
                                )
                                VALUES (?, ?, ?, ?, ?, ?)";

                        $stmt = $conexion->prepare($sql);

                        $stmt->bind_param(
                            "ssssii",
                            $fecha_hora,
                            $rut_paciente,
                            $rut_medico,
                            $codigo_centro,
                            $id_especialidad,
                            $estado
                        );

                        if ($stmt->execute()) {
                            $mensaje = "Cita agendada correctamente.";
                        } else {
                            $mensaje = "No fue posible agendar la cita.";
                        }
                    }
                }
            }
        }
    }
}

$especialidades = $conexion->query(
    "SELECT Id_especialidad, Tipo_especialidad
     FROM Especialidad
     ORDER BY Tipo_especialidad"
);

$medicos = $conexion->query(
    "SELECT Rut_medico, Nombre_completo
     FROM Medico
     ORDER BY Nombre_completo"
);

$centros = $conexion->query(
    "SELECT Codigo_interno, Nombre
     FROM Centro_medico
     ORDER BY Nombre"
);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Agendar cita - SaludUSM</title>
</head>

<body>

<h1>SaludUSM</h1>

<h2>Agendar cita</h2>

<?php if (!empty($mensaje)): ?>

    <p>
        <?php echo htmlspecialchars($mensaje); ?>
    </p>

<?php endif; ?>

<form method="POST">

    <label>Especialidad:</label><br>

    <select name="id_especialidad" required>

        <option value="">Seleccione</option>

        <?php while ($e = $especialidades->fetch_assoc()): ?>

            <option value="<?php echo $e["Id_especialidad"]; ?>">
                <?php echo htmlspecialchars($e["Tipo_especialidad"]); ?>
            </option>

        <?php endwhile; ?>

    </select>

    <br><br>

    <label>Médico:</label><br>

    <select name="rut_medico" required>

        <option value="">Seleccione</option>

        <?php while ($m = $medicos->fetch_assoc()): ?>

            <option value="<?php echo htmlspecialchars($m["Rut_medico"]); ?>">
                <?php echo htmlspecialchars($m["Nombre_completo"]); ?>
            </option>

        <?php endwhile; ?>

    </select>

    <br><br>

    <label>Centro médico:</label><br>

    <select name="codigo_centro" required>

        <option value="">Seleccione</option>

        <?php while ($c = $centros->fetch_assoc()): ?>

            <option value="<?php echo htmlspecialchars($c["Codigo_interno"]); ?>">
                <?php echo htmlspecialchars($c["Nombre"]); ?>
            </option>

        <?php endwhile; ?>

    </select>

    <br><br>

    <label>Fecha y hora:</label><br>

    <input
        type="datetime-local"
        name="fecha_hora"
        required
    >

    <br><br>

    <button type="submit">
        Agendar cita
    </button>

</form>

<br>

<a href="panel.php">Volver al panel</a>

</body>

</html>
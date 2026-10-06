<?php

session_start();
require_once "../conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Administrador") {
    header("Location: ../login.php");
    exit();
}

$mensaje = "";


/* =========================
   ASIGNAR ESPECIALIDAD
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["asignar"])) {

    $rut_medico = trim($_POST["rut_medico"]);
    $id_especialidad = intval($_POST["id_especialidad"]);

    $sql = "INSERT INTO Medico_Especialidad
            (Rut_medico, Id_especialidad)
            VALUES (?, ?)";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "si",
        $rut_medico,
        $id_especialidad
    );

    if ($stmt->execute()) {

        $mensaje = "Especialidad asignada correctamente.";

    } else {

        $mensaje = "El médico ya tiene asignada esa especialidad.";
    }
}


/* =========================
   QUITAR ESPECIALIDAD
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["quitar"])) {

    $rut_medico = trim($_POST["rut_medico"]);
    $id_especialidad = intval($_POST["id_especialidad"]);

    $sql = "DELETE FROM Medico_Especialidad
            WHERE Rut_medico = ?
            AND Id_especialidad = ?";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "si",
        $rut_medico,
        $id_especialidad
    );

    if ($stmt->execute()) {

        $mensaje = "Especialidad quitada correctamente.";

    } else {

        $mensaje = "No fue posible quitar la especialidad.";
    }
}


/* =========================
   MÉDICOS
   ========================= */

$medicos = $conexion->query(
    "SELECT Rut_medico, Nombre_completo
     FROM Medico
     ORDER BY Nombre_completo"
);


/* =========================
   ESPECIALIDADES
   ========================= */

$especialidades = $conexion->query(
    "SELECT Id_especialidad, Tipo_especialidad
     FROM Especialidad
     ORDER BY Tipo_especialidad"
);


/* =========================
   ASIGNACIONES
   ========================= */

$asignaciones = $conexion->query(
    "SELECT
        me.Rut_medico,
        m.Nombre_completo AS Medico,
        me.Id_especialidad,
        e.Tipo_especialidad AS Especialidad
     FROM Medico_Especialidad me
     INNER JOIN Medico m
        ON me.Rut_medico = m.Rut_medico
     INNER JOIN Especialidad e
        ON me.Id_especialidad = e.Id_especialidad
     ORDER BY m.Nombre_completo, e.Tipo_especialidad"
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Asignar especialidades - SaludUSM</title>

</head>

<body>

<h1>SaludUSM</h1>

<h2>Asignar especialidades a médicos</h2>


<?php if (!empty($mensaje)): ?>

    <p>
        <strong>
            <?php echo htmlspecialchars($mensaje); ?>
        </strong>
    </p>

<?php endif; ?>


<h3>Nueva asignación</h3>

<form method="POST">

    <input
        type="hidden"
        name="asignar"
        value="1"
    >

    <label>Médico:</label><br>

    <select name="rut_medico" required>

        <option value="">
            Seleccione un médico
        </option>

        <?php while ($medico = $medicos->fetch_assoc()): ?>

            <option value="<?php echo htmlspecialchars($medico["Rut_medico"]); ?>">

                <?php
                echo htmlspecialchars(
                    $medico["Nombre_completo"]
                    . " - "
                    . $medico["Rut_medico"]
                );
                ?>

            </option>

        <?php endwhile; ?>

    </select>

    <br><br>


    <label>Especialidad:</label><br>

    <select name="id_especialidad" required>

        <option value="">
            Seleccione una especialidad
        </option>

        <?php while ($especialidad = $especialidades->fetch_assoc()): ?>

            <option value="<?php echo $especialidad["Id_especialidad"]; ?>">

                <?php echo htmlspecialchars($especialidad["Tipo_especialidad"]); ?>

            </option>

        <?php endwhile; ?>

    </select>

    <br><br>

    <button type="submit">
        Asignar especialidad
    </button>

</form>


<hr>


<h3>Asignaciones actuales</h3>

<table border="1" cellpadding="8">

    <tr>

        <th>RUT médico</th>
        <th>Médico</th>
        <th>Especialidad</th>
        <th>Acción</th>

    </tr>


    <?php while ($asignacion = $asignaciones->fetch_assoc()): ?>

        <tr>

            <td>
                <?php echo htmlspecialchars($asignacion["Rut_medico"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($asignacion["Medico"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($asignacion["Especialidad"]); ?>
            </td>

            <td>

                <form method="POST">

                    <input
                        type="hidden"
                        name="quitar"
                        value="1"
                    >

                    <input
                        type="hidden"
                        name="rut_medico"
                        value="<?php echo htmlspecialchars($asignacion["Rut_medico"]); ?>"
                    >

                    <input
                        type="hidden"
                        name="id_especialidad"
                        value="<?php echo $asignacion["Id_especialidad"]; ?>"
                    >

                    <button
                        type="submit"
                        onclick="return confirm('¿Quitar esta especialidad del médico?');"
                    >
                        Quitar
                    </button>

                </form>

            </td>

        </tr>

    <?php endwhile; ?>

</table>


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
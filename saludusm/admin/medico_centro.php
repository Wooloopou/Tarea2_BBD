<?php

session_start();
require_once "../conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Administrador") {
    header("Location: ../login.php");
    exit();
}

$mensaje = "";


/* =========================
   ASIGNAR MÉDICO A CENTRO
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["asignar"])) {

    $rut_medico = trim($_POST["rut_medico"]);
    $codigo_centro = trim($_POST["codigo_centro"]);

    if (empty($rut_medico) || empty($codigo_centro)) {

        $mensaje = "Debe seleccionar un médico y un centro.";

    } else {

        $sql = "INSERT INTO Medico_Centro
                (Rut_medico, Codigo_interno)
                VALUES (?, ?)";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "ss",
            $rut_medico,
            $codigo_centro
        );

        if ($stmt->execute()) {

            $mensaje = "Médico asignado al centro correctamente.";

        } else {

            $mensaje = "El médico ya está asignado a ese centro.";
        }
    }
}


/* =========================
   QUITAR ASIGNACIÓN
   ========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["quitar"])
) {

    $rut_medico = trim($_POST["rut_medico"]);
    $codigo_centro = trim($_POST["codigo_centro"]);

    /*
     * No permitimos quitar la asignación si existen
     * citas de ese médico en ese centro.
     */
    $sql = "SELECT Id_cita
            FROM Cita
            WHERE Rut_medico = ?
            AND Codigo_centro = ?
            LIMIT 1";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "ss",
        $rut_medico,
        $codigo_centro
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        $mensaje = "No se puede quitar la asignación porque el médico tiene citas registradas en ese centro.";

    } else {

        $sql = "DELETE FROM Medico_Centro
                WHERE Rut_medico = ?
                AND Codigo_interno = ?";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "ss",
            $rut_medico,
            $codigo_centro
        );

        if ($stmt->execute()) {

            $mensaje = "Asignación eliminada correctamente.";

        } else {

            $mensaje = "No fue posible eliminar la asignación.";
        }
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
   CENTROS
   ========================= */

$centros = $conexion->query(
    "SELECT Codigo_interno, Nombre
     FROM Centro_medico
     ORDER BY Nombre"
);


/* =========================
   ASIGNACIONES
   ========================= */

$asignaciones = $conexion->query(
    "SELECT
        mc.Rut_medico,
        m.Nombre_completo AS Medico,
        mc.Codigo_interno,
        c.Nombre AS Centro
     FROM Medico_Centro mc
     INNER JOIN Medico m
        ON mc.Rut_medico = m.Rut_medico
     INNER JOIN Centro_medico c
        ON mc.Codigo_interno = c.Codigo_interno
     ORDER BY m.Nombre_completo, c.Nombre"
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Asignar médicos a centros - SaludUSM</title>

</head>

<body>

<h1>SaludUSM</h1>

<h2>Asignar médicos a centros médicos</h2>


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


    <label>Centro médico:</label><br>

    <select name="codigo_centro" required>

        <option value="">
            Seleccione un centro
        </option>

        <?php while ($centro = $centros->fetch_assoc()): ?>

            <option value="<?php echo htmlspecialchars($centro["Codigo_interno"]); ?>">

                <?php echo htmlspecialchars($centro["Nombre"]); ?>

            </option>

        <?php endwhile; ?>

    </select>

    <br><br>

    <button type="submit">
        Asignar médico
    </button>

</form>


<hr>


<h3>Asignaciones actuales</h3>

<table border="1" cellpadding="8">

    <tr>

        <th>RUT médico</th>
        <th>Médico</th>
        <th>Código centro</th>
        <th>Centro médico</th>
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
                <?php echo htmlspecialchars($asignacion["Codigo_interno"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($asignacion["Centro"]); ?>
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
                        name="codigo_centro"
                        value="<?php echo htmlspecialchars($asignacion["Codigo_interno"]); ?>"
                    >

                    <button
                        type="submit"
                        onclick="return confirm('¿Quitar esta asignación?');"
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
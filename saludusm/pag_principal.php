<?php

session_start();
require_once "conexion.php";

$busqueda = "";
$resultado_medicos = null;

if (isset($_GET["buscar"])) {

    $busqueda = trim($_GET["buscar"]);
    $texto = "%" . $busqueda . "%";

    $sql = "
        SELECT DISTINCT
            m.Rut_medico,
            m.Nombre_completo AS Medico,
            e.Tipo_especialidad AS Especialidad,
            cm.Nombre AS Centro
        FROM Medico m
        JOIN Medico_Especialidad me
            ON m.Rut_medico = me.Rut_medico
        JOIN Especialidad e
            ON me.Id_especialidad = e.Id_especialidad
        JOIN Medico_Centro mc
            ON m.Rut_medico = mc.Rut_medico
        JOIN Centro_medico cm
            ON mc.Codigo_interno = cm.Codigo_interno
        WHERE m.Nombre_completo LIKE ?
           OR e.Tipo_especialidad LIKE ?
        ORDER BY m.Nombre_completo
    ";

    $stmt = $conexion->prepare($sql);

    if ($stmt) {

        $stmt->bind_param("ss", $texto, $texto);
        $stmt->execute();

        $resultado_medicos = $stmt->get_result();

    } else {

        die("Error en la búsqueda: " . $conexion->error);

    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>SaludUSM</title>

</head>

<body>

<h1>SaludUSM</h1>

<?php if (isset($_SESSION["id_usuario"])): ?>

    <p>
        Bienvenido,
        <strong>
            <?php echo htmlspecialchars($_SESSION["usuario"]); ?>
        </strong>
    </p>

<?php endif; ?>


<h2>Buscar médicos</h2>

<form method="GET">

    <label>
        Nombre del médico o especialidad:
    </label>

    <br><br>

    <input
        type="text"
        name="buscar"
        value="<?php echo htmlspecialchars($busqueda); ?>"
        placeholder="Ej: cardiología"
    >

    <button type="submit">
        Buscar
    </button>

</form>


<?php if ($resultado_medicos !== null): ?>

    <h2>Resultados</h2>

    <?php if ($resultado_medicos->num_rows > 0): ?>

        <table border="1">

            <tr>
                <th>Médico</th>
                <th>Especialidad</th>
                <th>Centro médico</th>
            </tr>

            <?php while ($fila = $resultado_medicos->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($fila["Medico"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($fila["Especialidad"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($fila["Centro"]); ?>
                    </td>

                </tr>

            <?php endwhile; ?>

        </table>

    <?php else: ?>

        <p>
            No se encontraron médicos.
        </p>

    <?php endif; ?>

<?php endif; ?>


<hr>


<h2>Acceso</h2>


<?php if (!isset($_SESSION["id_usuario"])): ?>

    <a href="login.php">
        Iniciar sesión
    </a>

    <br><br>

    <a href="registro.php">
        Registrarse
    </a>

<?php else: ?>


    <?php if ($_SESSION["rol"] === "Paciente"): ?>

        <a href="paciente/panel.php">
            Panel del paciente
        </a>

    <?php elseif ($_SESSION["rol"] === "Medico"): ?>

        <a href="medico/panel.php">
            Panel del médico
        </a>

    <?php elseif ($_SESSION["rol"] === "Administrador"): ?>

        <a href="admin/panel.php">
            Panel del administrador
        </a>

    <?php endif; ?>


    <br><br>

    <a href="logout.php">
        Cerrar sesión
    </a>

<?php endif; ?>


</body>

</html>
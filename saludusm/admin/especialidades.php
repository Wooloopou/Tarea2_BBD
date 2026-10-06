<?php

session_start();
require_once "../conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Administrador") {
    header("Location: ../login.php");
    exit();
}

$mensaje = "";


/* =========================
   AGREGAR ESPECIALIDAD
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["agregar"])) {

    $especialidad = trim($_POST["especialidad"]);

    if (empty($especialidad)) {

        $mensaje = "Debe ingresar una especialidad.";

    } else {

        $sql = "INSERT INTO Especialidad (Tipo_especialidad)
                VALUES (?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $especialidad);

        if ($stmt->execute()) {
            $mensaje = "Especialidad agregada correctamente.";
        } else {
            $mensaje = "No fue posible agregar la especialidad.";
        }
    }
}


/* =========================
   MODIFICAR ESPECIALIDAD
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["modificar"])) {

    $id = intval($_POST["id"]);
    $especialidad = trim($_POST["especialidad"]);

    if (empty($especialidad)) {

        $mensaje = "Debe ingresar una especialidad.";

    } else {

        $sql = "UPDATE Especialidad
                SET Tipo_especialidad = ?
                WHERE Id_especialidad = ?";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "si",
            $especialidad,
            $id
        );

        if ($stmt->execute()) {
            $mensaje = "Especialidad modificada correctamente.";
        } else {
            $mensaje = "No fue posible modificar la especialidad.";
        }
    }
}


/* =========================
   ELIMINAR ESPECIALIDAD
   ========================= */

if (isset($_GET["eliminar"])) {

    $id = intval($_GET["eliminar"]);

    $sql = "DELETE FROM Especialidad
            WHERE Id_especialidad = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {

        $mensaje = "Especialidad eliminada correctamente.";

    } else {

        $mensaje = "No se puede eliminar esta especialidad porque tiene información asociada.";
    }
}


/* =========================
   LISTAR ESPECIALIDADES
   ========================= */

$resultado = $conexion->query(
    "SELECT Id_especialidad,
            Tipo_especialidad
     FROM Especialidad
     ORDER BY Id_especialidad"
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Gestionar especialidades - SaludUSM</title>

</head>

<body>

<h1>SaludUSM</h1>

<h2>Gestionar especialidades</h2>


<?php if (!empty($mensaje)): ?>

    <p>
        <strong>
            <?php echo htmlspecialchars($mensaje); ?>
        </strong>
    </p>

<?php endif; ?>


<h3>Agregar especialidad</h3>

<form method="POST">

    <input
        type="hidden"
        name="agregar"
        value="1"
    >

    <label>Especialidad:</label><br>

    <input
        type="text"
        name="especialidad"
        required
    >

    <br><br>

    <button type="submit">
        Agregar especialidad
    </button>

</form>


<hr>


<h3>Especialidades registradas</h3>

<table border="1" cellpadding="8">

    <tr>

        <th>ID</th>
        <th>Especialidad</th>
        <th>Acciones</th>

    </tr>


    <?php while ($fila = $resultado->fetch_assoc()): ?>

        <tr>

            <form method="POST">

                <td>

                    <?php echo $fila["Id_especialidad"]; ?>

                    <input
                        type="hidden"
                        name="id"
                        value="<?php echo $fila["Id_especialidad"]; ?>"
                    >

                </td>


                <td>

                    <input
                        type="text"
                        name="especialidad"
                        value="<?php echo htmlspecialchars($fila["Tipo_especialidad"]); ?>"
                        required
                    >

                </td>


                <td>

                    <input
                        type="hidden"
                        name="modificar"
                        value="1"
                    >

                    <button type="submit">
                        Modificar
                    </button>

                    <a
                        href="especialidades.php?eliminar=<?php echo $fila["Id_especialidad"]; ?>"
                        onclick="return confirm('¿Está seguro de eliminar esta especialidad?');"
                    >
                        Eliminar
                    </a>

                </td>

            </form>

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
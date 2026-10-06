<?php

session_start();
require_once "../conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Administrador") {
    header("Location: ../login.php");
    exit();
}

$mensaje = "";


/* =========================
   AGREGAR CENTRO
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["agregar"])) {

    $codigo = trim($_POST["codigo"]);
    $nombre = trim($_POST["nombre"]);
    $comuna = trim($_POST["comuna"]);
    $region = trim($_POST["region"]);

    if (
        empty($codigo) ||
        empty($nombre) ||
        empty($comuna) ||
        empty($region)
    ) {

        $mensaje = "Debe completar todos los campos.";

    } else {

        $sql = "INSERT INTO Centro_medico
                (Codigo_interno, Nombre, Comuna, Region)
                VALUES (?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "ssss",
            $codigo,
            $nombre,
            $comuna,
            $region
        );

        if ($stmt->execute()) {

            $mensaje = "Centro médico agregado correctamente.";

        } else {

            $mensaje = "No fue posible agregar el centro. Verifique que el código no esté repetido.";
        }
    }
}


/* =========================
   MODIFICAR CENTRO
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["modificar"])) {

    $codigo = trim($_POST["codigo"]);
    $nombre = trim($_POST["nombre"]);
    $comuna = trim($_POST["comuna"]);
    $region = trim($_POST["region"]);

    $sql = "UPDATE Centro_medico
            SET Nombre = ?,
                Comuna = ?,
                Region = ?
            WHERE Codigo_interno = ?";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "ssss",
        $nombre,
        $comuna,
        $region,
        $codigo
    );

    if ($stmt->execute()) {

        $mensaje = "Centro médico modificado correctamente.";

    } else {

        $mensaje = "No fue posible modificar el centro.";
    }
}


/* =========================
   ELIMINAR CENTRO
   ========================= */

if (isset($_GET["eliminar"])) {

    $codigo = $_GET["eliminar"];

    $sql = "DELETE FROM Centro_medico
            WHERE Codigo_interno = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $codigo);

    if ($stmt->execute()) {

        $mensaje = "Centro médico eliminado correctamente.";

    } else {

        $mensaje = "No se puede eliminar este centro porque tiene información asociada.";
    }
}


/* =========================
   OBTENER CENTROS
   ========================= */

$resultado = $conexion->query(
    "SELECT Codigo_interno, Nombre, Comuna, Region
     FROM Centro_medico
     ORDER BY Nombre"
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Centros médicos - SaludUSM</title>

</head>

<body>

<h1>SaludUSM</h1>

<h2>Gestionar centros médicos</h2>


<?php if (!empty($mensaje)): ?>

    <p>
        <strong>
            <?php echo htmlspecialchars($mensaje); ?>
        </strong>
    </p>

<?php endif; ?>


<h3>Agregar centro médico</h3>

<form method="POST">

    <input type="hidden" name="agregar" value="1">

    <label>Código interno:</label><br>

    <input
        type="text"
        name="codigo"
        required
    >

    <br><br>

    <label>Nombre:</label><br>

    <input
        type="text"
        name="nombre"
        required
    >

    <br><br>

    <label>Comuna:</label><br>

    <input
        type="text"
        name="comuna"
        required
    >

    <br><br>

    <label>Región:</label><br>

    <input
        type="text"
        name="region"
        required
    >

    <br><br>

    <button type="submit">
        Agregar centro
    </button>

</form>


<hr>


<h3>Centros registrados</h3>

<table border="1" cellpadding="8">

    <tr>

        <th>Código</th>
        <th>Nombre</th>
        <th>Comuna</th>
        <th>Región</th>
        <th>Acciones</th>

    </tr>


    <?php while ($centro = $resultado->fetch_assoc()): ?>

        <tr>

            <form method="POST">

                <td>

                    <input
                        type="text"
                        name="codigo"
                        value="<?php echo htmlspecialchars($centro["Codigo_interno"]); ?>"
                        readonly
                    >

                </td>

                <td>

                    <input
                        type="text"
                        name="nombre"
                        value="<?php echo htmlspecialchars($centro["Nombre"]); ?>"
                        required
                    >

                </td>

                <td>

                    <input
                        type="text"
                        name="comuna"
                        value="<?php echo htmlspecialchars($centro["Comuna"]); ?>"
                        required
                    >

                </td>

                <td>

                    <input
                        type="text"
                        name="region"
                        value="<?php echo htmlspecialchars($centro["Region"]); ?>"
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
                        href="centros.php?eliminar=<?php echo urlencode($centro["Codigo_interno"]); ?>"
                        onclick="return confirm('¿Está seguro de eliminar este centro?');"
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
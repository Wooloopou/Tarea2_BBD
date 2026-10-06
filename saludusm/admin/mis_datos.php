<?php

session_start();
require_once "../conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Administrador") {
    header("Location: ../login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$mensaje = "";


/* ACTUALIZAR DATOS */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);

    if (empty($email)) {

        $mensaje = "Debe completar el correo electrónico.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $mensaje = "El correo electrónico no es válido.";

    } else {

        $sql = "SELECT Id_usuario
                FROM Usuario
                WHERE Email = ?
                AND Id_usuario <> ?";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "si",
            $email,
            $id_usuario
        );

        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {

            $mensaje = "El correo ya está registrado por otro usuario.";

        } else {

            $sql = "UPDATE Usuario
                    SET Email = ?
                    WHERE Id_usuario = ?";

            $stmt = $conexion->prepare($sql);

            $stmt->bind_param(
                "si",
                $email,
                $id_usuario
            );

            if ($stmt->execute()) {

                $mensaje = "Datos actualizados correctamente.";

            } else {

                $mensaje = "No fue posible actualizar los datos.";
            }
        }
    }
}


/* OBTENER DATOS */

$sql = "SELECT
            Usuario,
            Email,
            Rol
        FROM Usuario
        WHERE Id_usuario = ?";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $id_usuario
);

$stmt->execute();

$datos = $stmt->get_result()->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Mis datos - SaludUSM</title>

</head>

<body>

<h1>SaludUSM</h1>

<h2>Mis datos personales</h2>


<?php if (!empty($mensaje)): ?>

    <p>
        <strong>
            <?php echo htmlspecialchars($mensaje); ?>
        </strong>
    </p>

<?php endif; ?>


<form method="POST">

    <label>Usuario:</label><br>

    <input
        type="text"
        value="<?php echo htmlspecialchars($datos["Usuario"]); ?>"
        disabled
    >

    <br><br>


    <label>Rol:</label><br>

    <input
        type="text"
        value="<?php echo htmlspecialchars($datos["Rol"]); ?>"
        disabled
    >

    <br><br>


    <label>Correo electrónico:</label><br>

    <input
        type="email"
        name="email"
        value="<?php echo htmlspecialchars($datos["Email"]); ?>"
        required
    >

    <br><br>


    <button type="submit">
        Guardar cambios
    </button>

</form>


<br>

<a href="panel.php">
    Volver al panel
</a>

</body>

</html>
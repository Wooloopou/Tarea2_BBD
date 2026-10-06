<?php

session_start();
require_once "../conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Medico") {
    header("Location: ../login.php");
    exit();
}

$rut_medico = $_SESSION["rut_medico"];
$mensaje = "";


/* ACTUALIZAR DATOS */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);
    $email = trim($_POST["email"]);

    if (empty($nombre) || empty($email)) {

        $mensaje = "Debe completar todos los campos.";

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
            $_SESSION["id_usuario"]
        );

        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {

            $mensaje = "El correo ya está registrado por otro usuario.";

        } else {

            $conexion->begin_transaction();

            try {

                /* Actualizar médico */

                $sql = "UPDATE Medico
                        SET Nombre_completo = ?,
                            E_mail_institucional = ?
                        WHERE Rut_medico = ?";

                $stmt = $conexion->prepare($sql);

                $stmt->bind_param(
                    "sss",
                    $nombre,
                    $email,
                    $rut_medico
                );

                $stmt->execute();


                /* Actualizar correo del usuario */

                $sql = "UPDATE Usuario
                        SET Email = ?
                        WHERE Id_usuario = ?";

                $stmt = $conexion->prepare($sql);

                $stmt->bind_param(
                    "si",
                    $email,
                    $_SESSION["id_usuario"]
                );

                $stmt->execute();

                $conexion->commit();

                $mensaje = "Datos actualizados correctamente.";

            } catch (Exception $e) {

                $conexion->rollback();

                $mensaje = "No fue posible actualizar los datos.";
            }
        }
    }
}


/* OBTENER DATOS */

$sql = "SELECT
            m.Rut_medico,
            m.Nombre_completo,
            m.E_mail_institucional
        FROM Medico m
        WHERE m.Rut_medico = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $rut_medico);
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

    <label>RUT:</label><br>

    <input
        type="text"
        value="<?php echo htmlspecialchars($datos["Rut_medico"]); ?>"
        disabled
    >

    <br><br>

    <label>Nombre completo:</label><br>

    <input
        type="text"
        name="nombre"
        value="<?php echo htmlspecialchars($datos["Nombre_completo"]); ?>"
        required
    >

    <br><br>

    <label>Correo institucional:</label><br>

    <input
        type="email"
        name="email"
        value="<?php echo htmlspecialchars($datos["E_mail_institucional"]); ?>"
        required
    >

    <br><br>

    <button type="submit">
        Guardar cambios
    </button>

</form>

<br>

<a href="panel.php">Volver al panel</a>

</body>

</html>
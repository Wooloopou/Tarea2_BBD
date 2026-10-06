<?php

session_start();
require_once "../conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Paciente") {
    header("Location: ../login.php");
    exit();
}

$rut_paciente = $_SESSION["rut_paciente"];
$mensaje = "";


/* ACTUALIZAR DATOS */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);
    $fecha_nacimiento = $_POST["fecha_nacimiento"];
    $sexo = $_POST["sexo"];
    $prevision = $_POST["prevision"];
    $telefono = trim($_POST["telefono"]);
    $comuna = trim($_POST["comuna"]);
    $email = trim($_POST["email"]);

    if (
        empty($nombre) ||
        empty($fecha_nacimiento) ||
        empty($sexo) ||
        empty($prevision) ||
        empty($telefono) ||
        empty($comuna) ||
        empty($email)
    ) {

        $mensaje = "Debe completar todos los campos.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $mensaje = "El correo electrónico no es válido.";

    } else {

        /* Verificar que el correo no pertenezca a otro usuario */

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

                /* Actualizar paciente */

                $sql = "UPDATE Paciente
                        SET Nombre_completo = ?,
                            Fecha_nacimiento = ?,
                            Id_sexo = ?,
                            Id_prevision = ?,
                            Telefono_contacto = ?,
                            Comuna_residencia = ?
                        WHERE Rut_paciente = ?";

                $stmt = $conexion->prepare($sql);

                $stmt->bind_param(
                    "ssiisss",
                    $nombre,
                    $fecha_nacimiento,
                    $sexo,
                    $prevision,
                    $telefono,
                    $comuna,
                    $rut_paciente
                );

                $stmt->execute();


                /* Actualizar correo */

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


/* OBTENER DATOS ACTUALES */

$sql = "SELECT
            p.Rut_paciente,
            p.Nombre_completo,
            p.Fecha_nacimiento,
            p.Id_sexo,
            p.Id_prevision,
            p.Telefono_contacto,
            p.Comuna_residencia,
            u.Email
        FROM Paciente p
        INNER JOIN Usuario u
            ON p.Rut_paciente = u.Rut_paciente
        WHERE p.Rut_paciente = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $rut_paciente);
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
        value="<?php echo htmlspecialchars($datos["Rut_paciente"]); ?>"
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


    <label>Fecha de nacimiento:</label><br>

    <input
        type="date"
        name="fecha_nacimiento"
        value="<?php echo htmlspecialchars($datos["Fecha_nacimiento"]); ?>"
        required
    >

    <br><br>


    <label>Sexo:</label><br>

    <select name="sexo" required>

        <option value="1" <?php if ($datos["Id_sexo"] == 1) echo "selected"; ?>>
            Femenino
        </option>

        <option value="2" <?php if ($datos["Id_sexo"] == 2) echo "selected"; ?>>
            Masculino
        </option>

        <option value="3" <?php if ($datos["Id_sexo"] == 3) echo "selected"; ?>>
            Otro
        </option>

    </select>

    <br><br>


    <label>Previsión:</label><br>

    <select name="prevision" required>

        <option value="1" <?php if ($datos["Id_prevision"] == 1) echo "selected"; ?>>
            Fonasa
        </option>

        <option value="2" <?php if ($datos["Id_prevision"] == 2) echo "selected"; ?>>
            Isapre
        </option>

        <option value="3" <?php if ($datos["Id_prevision"] == 3) echo "selected"; ?>>
            Particular
        </option>

    </select>

    <br><br>


    <label>Teléfono:</label><br>

    <input
        type="text"
        name="telefono"
        value="<?php echo htmlspecialchars($datos["Telefono_contacto"]); ?>"
        required
    >

    <br><br>


    <label>Comuna:</label><br>

    <input
        type="text"
        name="comuna"
        value="<?php echo htmlspecialchars($datos["Comuna_residencia"]); ?>"
        required
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


    <br><br>

    <a href="eliminar_cuenta.php"
    onclick="return confirm('¿Está seguro de eliminar su cuenta? Esta acción no se puede deshacer.');">
        Eliminar mi cuenta
    </a>

</form>

<br>

<a href="panel.php">
    Volver al panel
</a>

</body>

</html>
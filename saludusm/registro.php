<?php

session_start();
require_once "conexion.php";

$mensaje = "";
$exito = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $rut = trim($_POST["rut"]);
    $nombre = trim($_POST["nombre"]);
    $fecha_nacimiento = $_POST["fecha_nacimiento"];
    $sexo = $_POST["sexo"];
    $prevision = $_POST["prevision"];
    $telefono = trim($_POST["telefono"]);
    $comuna = trim($_POST["comuna"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $password_confirmar = $_POST["password_confirmar"];

    /* Validar RUT */
    if (!preg_match('/^[0-9]{7,8}-[0-9Kk]$/', $rut)) {

        $mensaje = "El RUT debe tener el formato XXXXXXXX-X.";

    }

    /* Validar campos obligatorios */
    elseif (
        empty($nombre) ||
        empty($fecha_nacimiento) ||
        empty($sexo) ||
        empty($prevision) ||
        empty($telefono) ||
        empty($comuna) ||
        empty($email) ||
        empty($password)
    ) {

        $mensaje = "Debe completar todos los campos.";

    }

    /* Validar correo */
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $mensaje = "El correo electrónico no es válido.";

    }

    /* Validar contraseña */
    elseif (strlen($password) < 8) {

        $mensaje = "La contraseña debe tener al menos 8 caracteres.";

    }

    elseif ($password !== $password_confirmar) {

        $mensaje = "Las contraseñas no coinciden.";

    }

    else {

        /* Verificar si el RUT ya existe */
        $sql = "SELECT Rut_paciente
                FROM Paciente
                WHERE Rut_paciente = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $rut);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {

            $mensaje = "El RUT ya está registrado.";

        } else {

            /* Verificar si el correo ya existe */
            $sql = "SELECT Id_usuario
                    FROM Usuario
                    WHERE Email = ?";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();

            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {

                $mensaje = "El correo electrónico ya está registrado.";

            } else {

                $conexion->begin_transaction();

                try {

                    /* Crear paciente */
                    $sql = "INSERT INTO Paciente
                    (
                        Rut_paciente,
                        Nombre_completo,
                        Fecha_nacimiento,
                        Id_sexo,
                        Id_prevision,
                        Telefono_contacto,
                        Comuna_residencia
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

                    $stmt = $conexion->prepare($sql);

                    $stmt->bind_param(
                        "sssisss",
                        $rut,
                        $nombre,
                        $fecha_nacimiento,
                        $sexo,
                        $prevision,
                        $telefono,
                        $comuna
                    );

                    $stmt->execute();


                    /* Crear usuario */
                    $usuario = $rut;
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $rol = "Paciente";

                    $sql = "INSERT INTO Usuario
                    (
                        Usuario,
                        Email,
                        Password_hash,
                        Rol,
                        Rut_paciente,
                        Rut_medico
                    )
                    VALUES (?, ?, ?, ?, ?, NULL)";

                    $stmt = $conexion->prepare($sql);

                    $stmt->bind_param(
                        "sssss",
                        $usuario,
                        $email,
                        $hash,
                        $rol,
                        $rut
                    );

                    $stmt->execute();

                    $conexion->commit();

                    $exito = "Registro exitoso. Su usuario para iniciar sesión es su RUT.";

                } catch (Exception $e) {

                    $conexion->rollback();

                    $mensaje = "No fue posible completar el registro.";
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Registro de Paciente - SaludUSM</title>

</head>

<body>

<h1>SaludUSM</h1>

<h2>Registro de Paciente</h2>


<?php if (!empty($mensaje)): ?>

    <p>
        <?php echo htmlspecialchars($mensaje); ?>
    </p>

<?php endif; ?>


<?php if (!empty($exito)): ?>

    <p>
        <?php echo htmlspecialchars($exito); ?>
    </p>

    <p>
        <a href="login.php">
            Ir al inicio de sesión
        </a>
    </p>

<?php else: ?>


<form method="POST" action="registro.php">

    <label>RUT:</label><br>

    <input
        type="text"
        name="rut"
        placeholder="12345678-9"
        required
    >

    <br><br>


    <label>Nombre completo:</label><br>

    <input
        type="text"
        name="nombre"
        required
    >

    <br><br>


    <label>Fecha de nacimiento:</label><br>

    <input
        type="date"
        name="fecha_nacimiento"
        required
    >

    <br><br>


    <label>Sexo:</label><br>

    <select name="sexo" required>

        <option value="">
            Seleccione
        </option>

        <option value="1">
            Femenino
        </option>

        <option value="2">
            Masculino
        </option>

        <option value="3">
            Otro
        </option>

    </select>

    <br><br>


    <label>Previsión de salud:</label><br>

    <select name="prevision" required>

        <option value="">
            Seleccione
        </option>

        <option value="1">
            Fonasa
        </option>

        <option value="2">
            Isapre
        </option>

        <option value="3">
            Particular
        </option>

    </select>

    <br><br>


    <label>Teléfono:</label><br>

    <input
        type="text"
        name="telefono"
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


    <label>Correo electrónico:</label><br>

    <input
        type="email"
        name="email"
        required
    >

    <br><br>


    <label>Contraseña:</label><br>

    <input
        type="password"
        name="password"
        required
    >

    <br><br>


    <label>Confirmar contraseña:</label><br>

    <input
        type="password"
        name="password_confirmar"
        required
    >

    <br><br>


    <button type="submit">
        Registrarse
    </button>

</form>

<?php endif; ?>


<br>

<a href="login.php">
    Volver al inicio de sesión
</a>

</body>

</html>
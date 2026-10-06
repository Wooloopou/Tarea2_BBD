<?php

session_start();
require_once "../conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Administrador") {
    header("Location: ../login.php");
    exit();
}

$mensaje = "";


/* =========================
   AGREGAR MÉDICO
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["agregar"])) {

    $rut = trim($_POST["rut"]);
    $nombre = trim($_POST["nombre"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (
        empty($rut) ||
        empty($nombre) ||
        empty($email) ||
        empty($password)
    ) {

        $mensaje = "Debe completar todos los campos.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $mensaje = "El correo electrónico no es válido.";

    } elseif (strlen($password) < 8) {

        $mensaje = "La contraseña debe tener al menos 8 caracteres.";

    } else {

        $conexion->begin_transaction();

        try {

            /* Crear médico */

            $sql = "INSERT INTO Medico
                    (Rut_medico, Nombre_completo, E_mail_institucional)
                    VALUES (?, ?, ?)";

            $stmt = $conexion->prepare($sql);

            $stmt->bind_param(
                "sss",
                $rut,
                $nombre,
                $email
            );

            $stmt->execute();


            /* Crear usuario del médico */

            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $usuario = $rut;
            $rol = "Medico";

            $sql = "INSERT INTO Usuario
                    (
                        Usuario,
                        Password_hash,
                        Rol,
                        Rut_paciente,
                        Rut_medico,
                        Email
                    )
                    VALUES (?, ?, ?, NULL, ?, ?)";

            $stmt = $conexion->prepare($sql);

            $stmt->bind_param(
                "sssss",
                $usuario,
                $password_hash,
                $rol,
                $rut,
                $email
            );

            $stmt->execute();

            $conexion->commit();

            $mensaje = "Médico y usuario creados correctamente.";

        } catch (Exception $e) {

            $conexion->rollback();

            $mensaje = "No fue posible crear el médico. Verifique que el RUT o correo no estén registrados.";
        }
    }
}

/* =========================
   MODIFICAR MÉDICO
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["modificar"])) {

    $rut = trim($_POST["rut"]);
    $nombre = trim($_POST["nombre"]);
    $email = trim($_POST["email"]);

    if (empty($nombre) || empty($email)) {

        $mensaje = "Debe completar todos los campos.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $mensaje = "El correo electrónico no es válido.";

    } else {

        $sql = "UPDATE Medico
                SET Nombre_completo = ?,
                    E_mail_institucional = ?
                WHERE Rut_medico = ?";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "sss",
            $nombre,
            $email,
            $rut
        );

        if ($stmt->execute()) {

            $mensaje = "Médico modificado correctamente.";

        } else {

            $mensaje = "No fue posible modificar el médico.";
        }
    }
}


/* =========================
   ELIMINAR MÉDICO
   ========================= */

if (isset($_GET["eliminar"])) {

    $rut = $_GET["eliminar"];

    $sql = "DELETE FROM Medico
            WHERE Rut_medico = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $rut);

    if ($stmt->execute()) {

        $mensaje = "Médico eliminado correctamente.";

    } else {

        $mensaje = "No se puede eliminar este médico porque tiene información asociada.";
    }
}


/* =========================
   LISTAR MÉDICOS
   ========================= */

$resultado = $conexion->query(
    "SELECT Rut_medico,
            Nombre_completo,
            E_mail_institucional
     FROM Medico
     ORDER BY Nombre_completo"
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Gestionar médicos - SaludUSM</title>

</head>

<body>

<h1>SaludUSM</h1>

<h2>Gestionar médicos</h2>


<?php if (!empty($mensaje)): ?>

    <p>
        <strong>
            <?php echo htmlspecialchars($mensaje); ?>
        </strong>
    </p>

<?php endif; ?>


<h3>Agregar médico</h3>

<form method="POST">

    <input
        type="hidden"
        name="agregar"
        value="1"
    >

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


    <label>Correo institucional:</label><br>

    <input
        type="email"
        name="email"
        required
    >
    <br><br>

    <label>Contraseña para el médico:</label><br>

    <input
            type="password"
            name="password"
         minlength="8"
         required
    >

    <br><br>


    <button type="submit">
        Agregar médico
    </button>

</form>


<hr>


<h3>Médicos registrados</h3>

<table border="1" cellpadding="8">

    <tr>

        <th>RUT</th>
        <th>Nombre</th>
        <th>Correo institucional</th>
        <th>Acciones</th>

    </tr>


    <?php while ($medico = $resultado->fetch_assoc()): ?>

        <tr>

            <form method="POST">

                <td>

                    <input
                        type="text"
                        name="rut"
                        value="<?php echo htmlspecialchars($medico["Rut_medico"]); ?>"
                        readonly
                    >

                </td>


                <td>

                    <input
                        type="text"
                        name="nombre"
                        value="<?php echo htmlspecialchars($medico["Nombre_completo"]); ?>"
                        required
                    >

                </td>


                <td>

                    <input
                        type="email"
                        name="email"
                        value="<?php echo htmlspecialchars($medico["E_mail_institucional"]); ?>"
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
                        href="medicos.php?eliminar=<?php echo urlencode($medico["Rut_medico"]); ?>"
                        onclick="return confirm('¿Está seguro de eliminar este médico?');"
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
<?php

session_start();
require_once "conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario = trim($_POST["usuario"]);
    $password = $_POST["password"];

    if (empty($usuario) || empty($password)) {
        $mensaje = "Debe ingresar usuario y contraseña.";
    } else {

        $sql = "SELECT Id_usuario, Usuario, Password_hash, Rol, Rut_paciente, Rut_medico
                FROM Usuario
                WHERE Usuario = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows == 1) {

            $datos = $resultado->fetch_assoc();

            if (password_verify($password, $datos["Password_hash"])) {

                $_SESSION["id_usuario"] = $datos["Id_usuario"];
                $_SESSION["usuario"] = $datos["Usuario"];
                $_SESSION["rol"] = $datos["Rol"];
                $_SESSION["rut_paciente"] = $datos["Rut_paciente"];
                $_SESSION["rut_medico"] = $datos["Rut_medico"];

                if ($datos["Rol"] == "Paciente") {
                    header("Location: paciente/panel.php");
                    exit();
                }

                if ($datos["Rol"] == "Medico") {
                    header("Location: medico/panel.php");
                    exit();
                }

                if ($datos["Rol"] == "Administrador") {
                    header("Location: admin/panel.php");
                    exit();
                }

            } else {
                $mensaje = "Usuario o contraseña incorrectos.";
            }

        } else {
            $mensaje = "Usuario o contraseña incorrectos.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login - SaludUSM</title>
</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Iniciar sesión</h2>

    <?php if (!empty($mensaje)): ?>
        <p><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <form method="POST" action="">

        <label>Usuario:</label><br>
        <input type="text" name="usuario" required>

        <br><br>

        <label>Contraseña:</label><br>
        <input type="password" name="password" required>

        <br><br>

        <button type="submit">Iniciar sesión</button>

        <br><br>

        <p>
            ¿Eres paciente?
            <a href="registro.php">Regístrate aquí</a>
        </p>

    </form>

</body>

</html>
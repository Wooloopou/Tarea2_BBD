<?php

session_start();
require_once "../conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Medico") {
    header("Location: ../login.php");
    exit();
}

$rut_medico = $_SESSION["rut_medico"];
$mensaje = "";


/* =========================
   GUARDAR ATENCIÓN
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_cita = intval($_POST["id_cita"]);
    $motivo = trim($_POST["motivo"]);
    $observaciones = trim($_POST["observaciones"]);

    $diagnosticos = $_POST["diagnosticos"] ?? [];

    $medicamentos = $_POST["medicamento"] ?? [];
    $dosis = $_POST["dosis"] ?? [];
    $dias = $_POST["dias"] ?? [];

    if ($id_cita <= 0 || empty($motivo) || empty($diagnosticos)) {

        $mensaje = "Debe completar el motivo y seleccionar al menos un diagnóstico.";

    } else {

        /* Verificar que la cita pertenezca al médico y esté ATENDIDA */

        $sql = "SELECT Id_cita
                FROM Cita
                WHERE Id_cita = ?
                AND Rut_medico = ?
                AND Estado_cita = 3";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("is", $id_cita, $rut_medico);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {

            $mensaje = "La cita no existe, no pertenece a este médico o no está en estado Atendida.";

        } else {

            /* Verificar que no tenga una atención registrada */

            $sql = "SELECT Id_atencion
                    FROM Atencion
                    WHERE Id_cita = ?";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $id_cita);
            $stmt->execute();

            if ($stmt->get_result()->num_rows > 0) {

                $mensaje = "Esta cita ya tiene una atención registrada.";

            } else {

                $conexion->begin_transaction();

                try {

                    /* =========================
                       CREAR ATENCIÓN
                       ========================= */

                    $sql = "INSERT INTO Atencion
                            (
                                Id_cita,
                                Motivo_consulta,
                                Observaciones_clinicas
                            )
                            VALUES (?, ?, ?)";

                    $stmt = $conexion->prepare($sql);

                    $stmt->bind_param(
                        "iss",
                        $id_cita,
                        $motivo,
                        $observaciones
                    );

                    $stmt->execute();

                    $id_atencion = $conexion->insert_id;


                    /* =========================
                       REGISTRAR DIAGNÓSTICOS
                       ========================= */

                    $sql = "INSERT INTO Atencion_Diagnostico
                            (
                                Id_atencion,
                                Codigo_diagnostico
                            )
                            VALUES (?, ?)";

                    $stmt_diag = $conexion->prepare($sql);

                    foreach ($diagnosticos as $diagnostico) {

                        $stmt_diag->bind_param(
                            "is",
                            $id_atencion,
                            $diagnostico
                        );

                        $stmt_diag->execute();
                    }


                    /* =========================
                       CREAR RECETA
                       ========================= */

                    $hay_medicamentos = false;

                    foreach ($medicamentos as $medicamento) {

                        if (!empty(trim($medicamento))) {
                            $hay_medicamentos = true;
                            break;
                        }
                    }

                    if ($hay_medicamentos) {

                        $sql = "INSERT INTO Receta
                                (Id_atencion)
                                VALUES (?)";

                        $stmt = $conexion->prepare($sql);

                        $stmt->bind_param(
                            "i",
                            $id_atencion
                        );

                        $stmt->execute();

                        $id_receta = $conexion->insert_id;


                        /* =========================
                           DETALLE DE RECETA
                           ========================= */

                        $sql = "INSERT INTO Detalle_Receta
                                (
                                    Id_receta,
                                    Medicamento,
                                    Dosis,
                                    Cantidad_dias
                                )
                                VALUES (?, ?, ?, ?)";

                        $stmt_detalle = $conexion->prepare($sql);

                        for ($i = 0; $i < count($medicamentos); $i++) {

                            if (empty(trim($medicamentos[$i]))) {
                                continue;
                            }

                            $medicamento = trim($medicamentos[$i]);
                            $dosis_actual = trim($dosis[$i] ?? "");
                            $dias_actual = intval($dias[$i] ?? 0);

                            $stmt_detalle->bind_param(
                                "issi",
                                $id_receta,
                                $medicamento,
                                $dosis_actual,
                                $dias_actual
                            );

                            $stmt_detalle->execute();
                        }
                    }


                    $conexion->commit();

                    $mensaje = "Atención registrada correctamente.";

                } catch (Exception $e) {

                    $conexion->rollback();

                    $mensaje = "No fue posible registrar la atención.";
                }
            }
        }
    }
}


/* =========================
   CITAS ATENDIDAS SIN ATENCIÓN
   ========================= */

$sql = "SELECT
            c.Id_cita,
            c.Fecha_hora,
            p.Nombre_completo AS Paciente,
            e.Tipo_especialidad AS Especialidad
        FROM Cita c
        INNER JOIN Paciente p
            ON c.Rut_paciente = p.Rut_paciente
        INNER JOIN Especialidad e
            ON c.Id_especialidad = e.Id_especialidad
        LEFT JOIN Atencion a
            ON c.Id_cita = a.Id_cita
        WHERE c.Rut_medico = ?
        AND c.Estado_cita = 3
        AND a.Id_atencion IS NULL
        ORDER BY c.Fecha_hora DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $rut_medico);
$stmt->execute();

$citas = $stmt->get_result();


/* =========================
   DIAGNÓSTICOS
   ========================= */

$diagnosticos_bd = $conexion->query(
    "SELECT Codigo, Descripcion
     FROM Diagnostico
     ORDER BY Codigo"
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Registrar atención - SaludUSM</title>

</head>

<body>

<h1>SaludUSM</h1>

<h2>Registrar atención</h2>


<?php if (!empty($mensaje)): ?>

    <p>
        <strong>
            <?php echo htmlspecialchars($mensaje); ?>
        </strong>
    </p>

<?php endif; ?>


<?php if ($citas->num_rows === 0): ?>

    <p>
        No existen citas atendidas pendientes de registrar.
    </p>

<?php else: ?>


<form method="POST">


    <label>Cita:</label><br>

    <select name="id_cita" required>

        <option value="">
            Seleccione una cita
        </option>

        <?php while ($cita = $citas->fetch_assoc()): ?>

            <option value="<?php echo $cita["Id_cita"]; ?>">

                <?php
                echo "Cita #" .
                     $cita["Id_cita"] .
                     " - " .
                     $cita["Fecha_hora"] .
                     " - " .
                     $cita["Paciente"] .
                     " - " .
                     $cita["Especialidad"];
                ?>

            </option>

        <?php endwhile; ?>

    </select>


    <br><br>


    <label>Motivo de consulta:</label><br>

    <textarea
        name="motivo"
        rows="4"
        cols="50"
        required
    ></textarea>


    <br><br>


    <label>Observaciones clínicas:</label><br>

    <textarea
        name="observaciones"
        rows="5"
        cols="50"
    ></textarea>


    <br><br>


    <label>Diagnóstico(s):</label><br>

    <select
        name="diagnosticos[]"
        multiple
        size="6"
        required
    >

        <?php while ($diagnostico = $diagnosticos_bd->fetch_assoc()): ?>

            <option value="<?php echo htmlspecialchars($diagnostico["Codigo"]); ?>">

                <?php
                echo htmlspecialchars(
                    $diagnostico["Codigo"] .
                    " - " .
                    $diagnostico["Descripcion"]
                );
                ?>

            </option>

        <?php endwhile; ?>

    </select>

    <p>
        Puede seleccionar uno o más diagnósticos.
    </p>


    <h3>Receta</h3>

    <p>
        La receta es opcional.
    </p>


    <label>Medicamento 1:</label><br>

    <input
        type="text"
        name="medicamento[]"
    >

    <br>

    <label>Dosis:</label><br>

    <input
        type="text"
        name="dosis[]"
    >

    <br>

    <label>Cantidad de días:</label><br>

    <input
        type="number"
        name="dias[]"
        min="1"
    >


    <br><br>


    <label>Medicamento 2:</label><br>

    <input
        type="text"
        name="medicamento[]"
    >

    <br>

    <label>Dosis:</label><br>

    <input
        type="text"
        name="dosis[]"
    >

    <br>

    <label>Cantidad de días:</label><br>

    <input
        type="number"
        name="dias[]"
        min="1"
    >


    <br><br>


    <label>Medicamento 3:</label><br>

    <input
        type="text"
        name="medicamento[]"
    >

    <br>

    <label>Dosis:</label><br>

    <input
        type="text"
        name="dosis[]"
    >

    <br>

    <label>Cantidad de días:</label><br>

    <input
        type="number"
        name="dias[]"
        min="1"
    >


    <br><br>


    <button type="submit">
        Registrar atención
    </button>

</form>

<?php endif; ?>


<br>

<a href="panel.php">
    Volver al panel
</a>

</body>

</html>
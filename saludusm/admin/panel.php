<?php

session_start();

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Administrador") {
    header("Location: ../login.php");
    exit();
}

require_once "../conexion.php";


// ============================================================
// 1. ESTADÍSTICAS POR CENTRO MÉDICO
// ============================================================

$sql_centros = "
    SELECT
        cm.Nombre AS Centro,
        COUNT(c.Id_cita) AS Total_citas,
        ROUND(
            100.0 * SUM(
                CASE
                    WHEN ec.Descripcion = 'No Asistió' THEN 1
                    ELSE 0
                END
            ) / NULLIF(COUNT(c.Id_cita), 0),
            2
        ) AS Porcentaje_inasistencia
    FROM Centro_medico cm
    LEFT JOIN Cita c
        ON cm.Codigo_interno = c.Codigo_centro
    LEFT JOIN Estado_cita ec
        ON c.Estado_cita = ec.Id_estado
    GROUP BY cm.Codigo_interno, cm.Nombre
    ORDER BY cm.Nombre;
";

$resultado_centros = $conexion->query($sql_centros);

if (!$resultado_centros) {
    die("Error al obtener las estadísticas: " . $conexion->error);
}


// ============================================================
// 2. CINCO DIAGNÓSTICOS MÁS FRECUENTES
// ============================================================

$sql_diagnosticos = "
    SELECT
        d.Codigo,
        d.Descripcion,
        COUNT(*) AS Cantidad
    FROM Atencion_Diagnostico ad
    JOIN Diagnostico d
        ON ad.Codigo_diagnostico = d.Codigo
    GROUP BY d.Codigo, d.Descripcion
    ORDER BY Cantidad DESC
    LIMIT 5;
";

$resultado_diagnosticos = $conexion->query($sql_diagnosticos);

if (!$resultado_diagnosticos) {
    die("Error al obtener los diagnósticos: " . $conexion->error);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel de gestión</title>
</head>

<body>

<h1>Panel de gestión - Administrador</h1>


<!-- =========================================================
     TABLA DE CITAS POR CENTRO
     ========================================================= -->

<h2>Citas por centro médico</h2>

<table border="1">

    <thead>
        <tr>
            <th>Centro médico</th>
            <th>Total de citas</th>
            <th>Porcentaje de inasistencia</th>
        </tr>
    </thead>

    <tbody>

        <?php while ($fila = $resultado_centros->fetch_assoc()): ?>

            <tr>
                <td>
                    <?php echo htmlspecialchars($fila["Centro"]); ?>
                </td>

                <td>
                    <?php echo $fila["Total_citas"]; ?>
                </td>

                <td>
                    <?php echo $fila["Porcentaje_inasistencia"]; ?>%
                </td>
            </tr>

        <?php endwhile; ?>

    </tbody>

</table>


<br>


<!-- =========================================================
     TABLA TOP 5 DIAGNÓSTICOS
     ========================================================= -->

<h2>5 diagnósticos más frecuentes de la red</h2>

<table border="1">

    <thead>
        <tr>
            <th>Posición</th>
            <th>Código</th>
            <th>Diagnóstico</th>
            <th>Cantidad</th>
        </tr>
    </thead>

    <tbody>

        <?php
        $posicion = 1;

        while ($fila = $resultado_diagnosticos->fetch_assoc()):
        ?>

            <tr>

                <td>
                    <?php echo $posicion; ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($fila["Codigo"]); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($fila["Descripcion"]); ?>
                </td>

                <td>
                    <?php echo $fila["Cantidad"]; ?>
                </td>

            </tr>

        <?php
            $posicion++;
        endwhile;
        ?>

    </tbody>

</table>

</body>
</html>
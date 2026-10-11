<?php
require_once "../conexion.php";
$rut_medico = $_GET["rut_medico"];

$sql = "SELECT c.Codigo_interno, c.Nombre
        FROM Centro_medico c
        INNER JOIN Medico_Centro mc ON c.Codigo_interno = mc.Codigo_interno
        WHERE mc.Rut_medico = ?
        ORDER BY c.Nombre";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $rut_medico);
$stmt->execute();
$result = $stmt->get_result();

echo '<option value="">Seleccione</option>';
while ($c = $result->fetch_assoc()) {
    echo '<option value="'.$c["Codigo_interno"].'">'.htmlspecialchars($c["Nombre"]).'</option>';
}

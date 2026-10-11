<?php
require_once "../conexion.php";
$id_especialidad = $_GET["id_especialidad"];

$sql = "SELECT m.Rut_medico, m.Nombre_completo
        FROM Medico m
        INNER JOIN Medico_Especialidad me ON m.Rut_medico = me.Rut_medico
        WHERE me.Id_especialidad = ?
        ORDER BY m.Nombre_completo";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_especialidad);
$stmt->execute();
$result = $stmt->get_result();

echo '<option value="">Seleccione</option>';
while ($m = $result->fetch_assoc()) {
    echo '<option value="'.$m["Rut_medico"].'">'.htmlspecialchars($m["Nombre_completo"]).'</option>';
}

<?php

session_start();
require_once "../conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== "Paciente") {
    header("Location: ../login.php");
    exit();
}

$rut_paciente = $_SESSION["rut_paciente"];
$id_usuario = $_SESSION["id_usuario"];

$conexion->begin_transaction();

try {

    /*
     * Primero eliminamos las citas del paciente.
     *
     * Las relaciones dependientes se eliminan por CASCADE.
     */
    $sql = "DELETE FROM Cita
            WHERE Rut_paciente = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $rut_paciente);
    $stmt->execute();


    /*
     * Eliminar usuario.
     */
    $sql = "DELETE FROM Usuario
            WHERE Id_usuario = ?
            AND Rut_paciente = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("is", $id_usuario, $rut_paciente);
    $stmt->execute();


    /*
     * Eliminar paciente.
     */
    $sql = "DELETE FROM Paciente
            WHERE Rut_paciente = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $rut_paciente);
    $stmt->execute();

    $conexion->commit();

    session_unset();
    session_destroy();

    header("Location: ../login.php?eliminado=1");
    exit();

} catch (Exception $e) {

    $conexion->rollback();

    die("No fue posible eliminar la cuenta.");
}

?>
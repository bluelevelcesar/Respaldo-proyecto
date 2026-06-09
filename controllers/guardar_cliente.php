<?php
include '/../config/conexion.php';

$nombre    = $_POST['nombre'];
$dni       = $_POST['dni'];
$email     = $_POST['email'];
$telefono  = $_POST['telefono'];
$direccion = $_POST['direccion']; // <-- NUEVO

// Asegúrate de agregar la columna 'direccion' en tu consulta SQL
$sql = "INSERT INTO clientes (nombre, dni, email, telefono, direccion, estado) 
        VALUES ('$nombre', '$dni', '$email', '$telefono', '$direccion', 'Activo')";

if (mysqli_query($conexion, $sql)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($conexion)]);
}
?>
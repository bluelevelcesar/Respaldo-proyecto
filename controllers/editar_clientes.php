<?php
include '/../config/conexion.php';

// Recibir datos
$id        = $_POST['id'];
$nombre    = $_POST['nombre'];
$dni       = $_POST['dni'];
$email     = $_POST['email'];
$telefono  = $_POST['telefono'];
$direccion = $_POST['direccion'];

// ACTUALIZACIÓN: Nos aseguramos de que el estado siga siendo 'Activo'
$sql = "UPDATE clientes SET 
        nombre = '$nombre', 
        dni = '$dni', 
        email = '$email', 
        telefono = '$telefono', 
        direccion = '$direccion',
        estado = 'Activo' 
        WHERE id = '$id'";

if (mysqli_query($conexion, $sql)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($conexion)]);
}

mysqli_close($conexion);
?>
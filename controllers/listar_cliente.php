<?php
include '/../config/conexion.php';

// Solo traemos a los clientes cuyo estado NO sea 'Inactivo'
$sql = "SELECT id, nombre, dni, email, telefono, direccion, estado 
        FROM clientes 
        WHERE estado = 'Activo' 
        ORDER BY id DESC";

$resultado = mysqli_query($conexion, $sql);

$clientes = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $clientes[] = $fila;
}

header('Content-Type: application/json');
echo json_encode($clientes);

mysqli_close($conexion);
?>
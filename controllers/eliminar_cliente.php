<?php
// funciones/eliminar_cliente.php

// 1. Incluimos la conexión a la base de datos
include '/../config/conexion.php';

// 2. Verificamos que se haya recibido el ID por el método POST
if (isset($_POST['id'])) {
    
    // Limpiamos el ID para evitar inyecciones básicas
    $id = mysqli_real_escape_string($conexion, $_POST['id']);

    /**
     * BAJA LÓGICA: 
     * No usamos DELETE para no romper registros históricos.
     * Simplemente cambiamos el estado a 'Inactivo'.
     */
    $sql = "UPDATE clientes SET estado = 'Inactivo' WHERE id = '$id'";

    if (mysqli_query($conexion, $sql)) {
        // Si sale bien, enviamos respuesta de éxito
        echo json_encode(["status" => "success", "message" => "Cliente desactivado correctamente"]);
    } else {
        // Si hay error en la consulta
        echo json_encode(["status" => "error", "message" => "Error al actualizar: " . mysqli_error($conexion)]);
    }

} else {
    // Si no se envió el ID
    echo json_encode(["status" => "error", "message" => "ID no proporcionado"]);
}

// 3. Cerramos la conexión
mysqli_close($conexion);
?>
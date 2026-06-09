<?php
// actualizar_hashes.php - Ejecutar UNA SOLA VEZ para convertir contraseñas a hash
include __DIR__ . '/config/conexion.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Actualizando contraseñas a hash</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
<h1>Actualizando contraseñas a hash</h1>
<ul>";

$sql = "SELECT id_usuario, contrasena, usuario, email FROM usuario";
$result = $conexion->query($sql);
$actualizados = 0;
$totales = 0;

while ($usuario = $result->fetch_assoc()) {
    $totales++;
    $pass = $usuario['contrasena'];
    
    // Si la contraseña NO empieza con $2y$ (no está hasheada)
    if (strpos($pass, '$2y$') !== 0) {
        $nuevo_hash = password_hash($pass, PASSWORD_DEFAULT);
        $update = $conexion->prepare("UPDATE usuario SET contrasena = ? WHERE id_usuario = ?");
        $update->bind_param("si", $nuevo_hash, $usuario['id_usuario']);
        
        if ($update->execute()) {
            echo "<li class='success'>✅ Usuario: <strong>" . htmlspecialchars($usuario['usuario']) . "</strong> (" . htmlspecialchars($usuario['email']) . ") - Contraseña actualizada a hash</li>";
            $actualizados++;
        } else {
            echo "<li class='error'>❌ Error al actualizar usuario: " . htmlspecialchars($usuario['usuario']) . "</li>";
        }
        $update->close();
    } else {
        echo "<li class='info'>ℹ️ Usuario: <strong>" . htmlspecialchars($usuario['usuario']) . "</strong> - Ya tiene hash correcto</li>";
    }
}

echo "<li><strong>Resumen: $actualizados de $totales usuarios actualizados a hash</strong></li>";
echo "</ul></body></html>";

$conexion->close();
?>
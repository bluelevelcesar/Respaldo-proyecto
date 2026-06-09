<?php
// funciones/cambiar_contra.php
session_start();
header('Content-Type: application/json');

// Agregar esto para depuración (muestra errores)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'mensaje' => 'No autorizado']);
    exit;
}

// Verificar que el archivo conexion.php existe
$conexion_path = __DIR__ . '/../config/conexion.php';
if (!file_exists($conexion_path)) {
    echo json_encode(['success' => false, 'mensaje' => 'Error: No se encuentra conexion.php']);
    exit;
}

include $conexion_path;

$password_actual = $_POST['password_actual'] ?? '';
$password_nueva = $_POST['password_nueva'] ?? '';
$password_confirmar = $_POST['password_confirmar'] ?? '';

if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
    echo json_encode(['success' => false, 'mensaje' => 'Todos los campos son obligatorios']);
    exit;
}

if (strlen($password_nueva) < 4) {
    echo json_encode(['success' => false, 'mensaje' => 'La nueva contraseña debe tener al menos 4 caracteres']);
    exit;
}

if ($password_nueva !== $password_confirmar) {
    echo json_encode(['success' => false, 'mensaje' => 'Las contraseñas nuevas no coinciden']);
    exit;
}

// Verificar conexión a BD
if (!$conexion) {
    echo json_encode(['success' => false, 'mensaje' => 'Error de conexión a la base de datos']);
    exit;
}

// Verificar contraseña actual usando password_verify (HASH)
$sql = "SELECT contrasena FROM usuario WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'mensaje' => 'Error en la consulta SQL']);
    exit;
}

$stmt->bind_param("i", $_SESSION['id_usuario']);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    echo json_encode(['success' => false, 'mensaje' => 'Usuario no encontrado']);
    exit;
}

// ✅ Verificar contraseña actual con password_verify
if (!password_verify($password_actual, $usuario['contrasena'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Contraseña actual incorrecta']);
    exit;
}

// ✅ Generar NUEVO HASH para la nueva contraseña
$password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);

// Actualizar contraseña en la base de datos
$sql = "UPDATE usuario SET contrasena = ? WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("si", $password_hash, $_SESSION['id_usuario']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Contraseña actualizada correctamente']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar la contraseña: ' . $stmt->error]);
}

$stmt->close();
$conexion->close();
?>
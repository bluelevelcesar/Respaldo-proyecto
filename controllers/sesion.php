<?php
// controllers/sesion.php - PROCESADOR ÚNICO CON HASH
session_start();
header('Content-Type: application/json');

// Desactivar errores HTML para que solo salga JSON
error_reporting(0);
ini_set('display_errors', 0);

// Verificar conexión
$conexion_path = __DIR__ . '/../config/conexion.php';

if (!file_exists($conexion_path)) {
    echo json_encode(["success" => false, "msg" => "Error: Archivo conexion.php no encontrado"]);
    exit;
}

require_once $conexion_path;

// Verificar conexión a BD
if (!$conexion) {
    echo json_encode(["success" => false, "msg" => "Error de conexión a la base de datos"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "msg" => "Método no permitido"]);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {
    echo json_encode(["success" => false, "msg" => "Complete todos los campos"]);
    exit;
}

// Buscar usuario
$stmt = $conexion->prepare("SELECT * FROM usuario WHERE email = ? OR usuario = ?");
$stmt->bind_param("ss", $email, $email);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) {
    echo json_encode(["success" => false, "msg" => "Usuario no encontrado"]);
    exit;
}

// Verificar contraseña
$password_correcta = password_verify($password, $usuario['contrasena']);

// Si la contraseña está en texto plano, convertir a hash
if (!$password_correcta && $password === $usuario['contrasena']) {
    $password_correcta = true;
    $nuevo_hash = password_hash($password, PASSWORD_DEFAULT);
    $update_stmt = $conexion->prepare("UPDATE usuario SET contrasena = ? WHERE id_usuario = ?");
    $update_stmt->bind_param("si", $nuevo_hash, $usuario['id_usuario']);
    $update_stmt->execute();
    $update_stmt->close();
}

if (!$password_correcta) {
    echo json_encode(["success" => false, "msg" => "Contraseña incorrecta"]);
    exit;
}

// Guardar sesión
$_SESSION['id_usuario'] = $usuario['id_usuario'];
$_SESSION['email'] = $usuario['email'];
$_SESSION['id_rol'] = $usuario['id_rol'];
$_SESSION['usuario'] = $usuario['usuario'];

// Mapeo de roles
$roles = [
    1 => 'admin',
    2 => 'cobrador',
    3 => 'tecnico',
    4 => 'admin_red'
];
$rol = $roles[$usuario['id_rol']] ?? 'otro';

echo json_encode(["success" => true, "rol" => $rol, "usuario" => $usuario['usuario']]);
?>
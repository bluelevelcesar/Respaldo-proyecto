<?php
// controllers/servicios_controller.php
session_start();
header('Content-Type: application/json');

error_reporting(0);
ini_set('display_errors', 0);

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'mensaje' => 'No autorizado']);
    exit;
}

include __DIR__ . '/../config/conexion.php';

if (!$conexion) {
    echo json_encode(['success' => false, 'mensaje' => 'Error de conexión a BD']);
    exit;
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

// LISTAR SERVICIOS (cambiar 'servicios' a 'servicio')
if ($accion === 'listar') {
    $result = $conexion->query("SELECT * FROM servicio ORDER BY id_servicio ASC");
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

// CREAR SERVICIO
if ($accion === 'crear') {
    $nombre = $_POST['nombre'] ?? '';
    $velocidad = $_POST['velocidad'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    
    if (empty($nombre) || empty($velocidad) || $precio <= 0) {
        echo json_encode(['success' => false, 'mensaje' => 'Complete todos los campos']);
        exit;
    }
    
    $stmt = $conexion->prepare("INSERT INTO servicio (nombre, velocidad, precio) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $nombre, $velocidad, $precio);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'mensaje' => 'Servicio creado']);
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'Error al crear']);
    }
    exit;
}

// EDITAR SERVICIO
if ($accion === 'editar') {
    $id = $_POST['id'] ?? 0;
    $nombre = $_POST['nombre'] ?? '';
    $velocidad = $_POST['velocidad'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    
    if ($id <= 0 || empty($nombre) || empty($velocidad) || $precio <= 0) {
        echo json_encode(['success' => false, 'mensaje' => 'Complete todos los campos']);
        exit;
    }
    
    $stmt = $conexion->prepare("UPDATE servicio SET nombre=?, velocidad=?, precio=? WHERE id_servicio=?");
    $stmt->bind_param("ssdi", $nombre, $velocidad, $precio, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'mensaje' => 'Servicio actualizado']);
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar']);
    }
    exit;
}

// ELIMINAR SERVICIO
if ($accion === 'eliminar') {
    $id = $_POST['id'] ?? 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'mensaje' => 'ID no válido']);
        exit;
    }
    
    $stmt = $conexion->prepare("DELETE FROM servicio WHERE id_servicio = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'mensaje' => 'Servicio eliminado']);
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'Error al eliminar']);
    }
    exit;
}

echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
?>
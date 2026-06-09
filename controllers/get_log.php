<?php
// funciones/get_log.php
session_start();
header('Content-Type: application/json');

include __DIR__ . '/../config/conexion.php';
include __DIR__ . '/funcion_log.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$log = new SistemaLog($conexion);

// Obtener logs recientes
$logsRecientes = $log->obtenerLogsRecientes(50);
$estadisticas = $log->getEstadisticas();

// Contar clientes (tabla clientes)
$totalClientes = 0;
$result = $conexion->query("SELECT COUNT(*) as total FROM clientes");
if ($result) {
    $totalClientes = $result->fetch_assoc()['total'];
}

echo json_encode([
    'success' => true,
    'logs' => $logsRecientes,
    'estadisticas' => $estadisticas,
    'totalClientes' => $totalClientes
]);
?>
<?php
// controllers/get_estadisticas.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

include __DIR__ . '/../config/conexion.php';

// Clientes que pagaron
$sql_pagaron = "SELECT COUNT(DISTINCT id_cliente) as total FROM pagos WHERE mes = MONTH(CURDATE()) AND anio = YEAR(CURDATE()) AND estado = 'pagado'";
$result = $conexion->query($sql_pagaron);
$pagaron = $result ? $result->fetch_assoc()['total'] : 0;

// Clientes que NO pagaron
$sql_no_pagaron = "SELECT COUNT(DISTINCT id_cliente) as total FROM pagos WHERE mes = MONTH(CURDATE()) AND anio = YEAR(CURDATE()) AND estado = 'pendiente'";
$result = $conexion->query($sql_no_pagaron);
$no_pagaron = $result ? $result->fetch_assoc()['total'] : 0;

// Clientes cortados
$sql_cortados = "SELECT COUNT(*) as total FROM clientes WHERE estado = 'Inactivo' OR estado = 'Cortado'";
$result = $conexion->query($sql_cortados);
$cortados = $result ? $result->fetch_assoc()['total'] : 0;

// Ganancias mensuales últimos 6 meses
$ganancias_mensuales = [];
$meses = [];
for ($i = 5; $i >= 0; $i--) {
    $mes_num = date('n', strtotime("-$i months"));
    $anio = date('Y', strtotime("-$i months"));
    $meses[] = date('M', strtotime("-$i months"));
    
    $sql_ganancia = "SELECT SUM(monto) as total FROM pagos WHERE mes = $mes_num AND anio = $anio AND estado = 'pagado'";
    $result = $conexion->query($sql_ganancia);
    $ganancia = $result ? $result->fetch_assoc()['total'] : 0;
    $ganancias_mensuales[] = $ganancia ?: 0;
}

echo json_encode([
    'success' => true,
    'pagaron' => $pagaron,
    'no_pagaron' => $no_pagaron,
    'cortados' => $cortados,
    'ganancias_mensuales' => $ganancias_mensuales,
    'meses' => $meses,
    'total_ganancias' => array_sum($ganancias_mensuales)
]);

$conexion->close();
?>
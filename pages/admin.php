<?php
session_start();

// Verificar si está logueado y es admin
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/../config/conexion.php';
include __DIR__ . '/../controllers/funcion_log.php';

$log = new SistemaLog($conexion);

// ✅ CORREGIDO: Usar el método correcto verDashboard()
//$log->verDashboard($_SESSION['id_usuario'], $_SESSION['usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Chango Vision</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            overflow-x: hidden;
        }

        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0f2b3d 0%, #1b4f6e 100%);
            color: white;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .logo-area {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .logo-area h2 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .logo-area span {
            font-size: 0.7rem;
            opacity: 0.7;
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.6;
            margin-bottom: 0.8rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            color: white;
            text-decoration: none;
            margin-bottom: 0.3rem;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.1);
        }

        .nav-item.active {
            background: rgba(255,255,255,0.2);
        }

        .nav-item i {
            width: 20px;
            font-size: 1.1rem;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem 2rem;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e2a41;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #0f2b3d, #1b4f6e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
        }

        /* ========== STATS CARDS ========== */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .icon {
            width: 50px;
            height: 50px;
            background: #e0f2fe;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stat-card .icon i {
            font-size: 1.5rem;
            color: #1b4f6e;
        }

        .stat-card .number {
            font-size: 2rem;
            font-weight: 700;
            color: #1e2a41;
        }

        .stat-card .label {
            color: #666;
            font-size: 0.8rem;
        }

        /* ========== LOGS SECTION ========== */
        .logs-section {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .logs-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eef2f6;
        }

        .logs-header h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1e2a41;
            font-size: 1.1rem;
        }

        .btn-refresh {
            background: linear-gradient(135deg, #0f2b3d, #1b4f6e);
            color: white;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            transition: 0.2s;
        }

        .btn-refresh:hover {
            opacity: 0.9;
            transform: scale(0.97);
        }

        /* TABLA DE LOGS */
        .log-table {
            width: 100%;
            border-collapse: collapse;
        }

        .log-table th {
            text-align: left;
            padding: 1rem 0.8rem;
            background: #f8fafc;
            font-weight: 600;
            color: #1e2a41;
            font-size: 0.85rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .log-table td {
            padding: 0.8rem;
            border-bottom: 1px solid #edf2f7;
            color: #1f2b48;
            font-size: 0.85rem;
        }

        .log-table tr:hover td {
            background: #f8fafc;
        }

        .loading {
            text-align: center;
            padding: 3rem;
            color: #94a3b8;
        }

        .empty {
            text-align: center;
            padding: 3rem;
            color: #94a3b8;
        }

        .footer {
            margin-top: 2rem;
            padding-top: 1rem;
            text-align: center;
            font-size: 0.75rem;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
        }

        .status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            margin-right: 6px;
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .stats-row { grid-template-columns: 1fr; }
            .log-table th, .log-table td { padding: 0.5rem; font-size: 0.75rem; }
        }
    </style>
</head>
<body>
<div class="app-container">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo-area">
            <h2>Panel Administrador</h2>
            <span>Panel de Control</span>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">MAIN</div>
            <div class="nav-item active" data-page="dashboard">
                <i class="fas fa-tachometer-alt"></i>
                <span>Actividad</span>
            </div>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">CONFIGURATION</div>
            <div class="nav-item" data-page="config">
                <i class="fas fa-cog"></i><span>System Config</span>
            </div>
            <a href="../pages/admin_cliente.php" class="nav-item">
                <i class="fas fa-users"></i><span>Administrar Clientes</span>
            </a>
            <a href="../pages/estadisticas.php" class="nav-item">
                <i class="fas fa-chart-bar"></i><span>Estadísticas</span>
            </a>
            <div class="nav-item" data-page="services">
                <i class="fas fa-server"></i><span>Services</span>
            </div>
            <div class="nav-item" data-page="statistics">
                <i class="fas fa-chart-bar"></i><span>Statistics</span>
            </div>
        </div>

        <!-- SECCIÓN DE SESIÓN con Cambiar Contraseña y Cerrar Sesión -->
        <div class="nav-section" style="margin-top: auto;">
            <!-- Botón CAMBIAR CONTRASEÑA -->
            <a href="/Chango Vision/pages/cambiar_pass.php" class="nav-item" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                <i class="fas fa-key"></i>
                <span>Cambiar Contraseña</span>
            </a>
            
            <!-- Botón CERRAR SESIÓN -->
            <a href="../logout.php" id="logoutSidebarBtn" class="nav-item" onclick="return confirmarCierre(event)">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="top-bar">
            <div class="page-title">Registro de Actividad</div>
            <div class="user-info">
                <span><?= htmlspecialchars($_SESSION['usuario'] ?? 'Administrador') ?></span>
                <div class="user-avatar">A</div>
            </div>
        </div>

        <!-- TARJETAS ESTADÍSTICAS -->
        <div class="stats-row" id="statsContainer">
            <div class="stat-card">
                <div class="icon"><i class="fas fa-chart-line"></i></div>
                <div class="number" id="totalLogs">-</div>
                <div class="label">Total Registros</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-calendar-day"></i></div>
                <div class="number" id="logsHoy">-</div>
                <div class="label">Actividad Hoy</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-users"></i></div>
                <div class="number" id="totalClientes">-</div>
                <div class="label">Total Clientes</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-user-cog"></i></div>
                <div class="number" id="totalTecnicos">-</div>
                <div class="label">Total Técnicos</div>
            </div>
        </div>

        <!-- LISTADO DE LOGS -->
        <div class="logs-section">
            <div class="logs-header">
                <h3><i class="fas fa-history"></i> Últimos Registros de Actividad</h3>
                <button class="btn-refresh" id="refreshLogs">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
            </div>
            <div id="logsContainer">
                <div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando actividad...</div>
            </div>
        </div>

        <div class="footer">
            <div>© 2026 Panel Admin - Todos los derechos reservados</div>
            <div class="footer-status">
                <span><span class="status-dot"></span> System Online</span>
                <span><i class="fas fa-user-shield"></i> ADMINISTRATOR</span>
            </div>
        </div>
    </div>
</div>

<script>
    async function cargarDashboard() {
        const logsContainer = document.getElementById('logsContainer');
        logsContainer.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando actividad...</div>';
        
        try {
            const response = await fetch('../controllers/get_log.php');
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('totalLogs').textContent = data.estadisticas.total || 0;
                document.getElementById('logsHoy').textContent = data.estadisticas.hoy || 0;
                document.getElementById('totalClientes').textContent = data.totalClientes || 0;
                document.getElementById('totalTecnicos').textContent = data.totalTecnicos || 0;
                
                if (data.logs && data.logs.length > 0) {
                    let html = `<table class="log-table"><thead><tr><th>Usuario</th><th>Acción</th><th>Afectado</th><th>Fecha</th><th>Hora</th></tr></thead><tbody>`;
                    
                    data.logs.forEach(log => {
                        let fecha = log.fecha || '-';
                        if (fecha !== '-' && fecha.includes('-')) {
                            const partes = fecha.split('-');
                            fecha = `${partes[2]}/${partes[1]}/${partes[0]}`;
                        }
                        
                        html += `<tr>
                            <td><strong>${escapeHtml(log.administrador || 'Anónimo')}</strong></td>
                            <td>${escapeHtml(log.accion || '-')}</td>
                            <td>${escapeHtml(log.afectado || '-')}</td>
                            <td>${fecha}</td>
                            <td>${log.hora || '-'}</td>
                        </tr>`;
                    });
                    
                    html += `</tbody></table>`;
                    logsContainer.innerHTML = html;
                } else {
                    logsContainer.innerHTML = '<div class="empty">No hay registros de actividad</div>';
                }
            } else {
                logsContainer.innerHTML = '<div class="empty">Error al cargar los datos</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            logsContainer.innerHTML = '<div class="empty">Error de conexión con el servidor</div>';
        }
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function confirmarCierre(event) {
        if (!confirm('¿Estás seguro de cerrar sesión?')) {
            event.preventDefault();
            return false;
        }
        return true;
    }
    
    cargarDashboard();
    document.getElementById('refreshLogs').addEventListener('click', cargarDashboard);
    
    // Evento para cerrar sesión con confirmación
    document.getElementById('logoutSidebarBtn').addEventListener('click', (e) => {
        if (!confirm('¿Estás seguro de cerrar sesión?')) {
            e.preventDefault();
        }
    });
    
    setInterval(cargarDashboard, 30000);
</script>
</body>
</html>
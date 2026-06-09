<?php
session_start();

// Verificar si está logueado y es administrador de red (id_rol = 4)
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 4) {
    header('Location: ../login.php');
    exit;
}

include __DIR__ . '/../config/conexion.php';
include __DIR__ . '/../controllers/funcion_log.php';

$log = new SistemaLog($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador de Red - Chango Vision</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f4f8; overflow-x: hidden; }
        .app-container { display: flex; min-height: 100vh; }
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
        .logo-area { margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.2); }
        .logo-area h2 { font-size: 1.5rem; font-weight: 700; }
        .logo-area span { font-size: 0.7rem; opacity: 0.7; }
        .nav-section { margin-bottom: 1.5rem; }
        .nav-section-title { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.6; margin-bottom: 0.8rem; }
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
        .nav-item:hover { background: rgba(255,255,255,0.1); }
        .nav-item.active { background: rgba(255,255,255,0.2); }
        .nav-item i { width: 20px; font-size: 1.1rem; }
        .main-content { flex: 1; margin-left: 280px; padding: 2rem; }
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
        .page-title { font-size: 1.5rem; font-weight: 700; color: #1e2a41; }
        .user-info { display: flex; align-items: center; gap: 12px; }
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
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card .icon { width: 50px; height: 50px; background: #e0f2fe; border-radius: 15px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; }
        .stat-card .icon i { font-size: 1.5rem; color: #1b4f6e; }
        .stat-card .number { font-size: 2rem; font-weight: 700; color: #1e2a41; }
        .stat-card .label { color: #666; font-size: 0.8rem; }
        .card { background: white; border-radius: 20px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid #eee; }
        .card-header h3 { font-size: 1rem; font-weight: 600; }
        .two-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
        .service-item { padding: 0.75rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .service-item:last-child { border-bottom: none; }
        .service-name { font-weight: 600; font-size: 0.9rem; }
        .service-details { font-size: 0.7rem; color: #666; margin-top: 0.25rem; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 30px; font-size: 0.7rem; font-weight: 600; }
        .badge-active { background: #dcfce7; color: #16a34a; }
        .badge-warning { background: #fef3c7; color: #d97706; }
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
        }
        .footer { margin-top: 2rem; padding-top: 1rem; text-align: center; font-size: 0.75rem; color: #94a3b8; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; }
        .status-dot { display: inline-block; width: 8px; height: 8px; background: #10b981; border-radius: 50%; margin-right: 6px; }
        @keyframes slideIn { from { transform: translateX(100px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @media (max-width: 768px) { .sidebar { display: none; } .main-content { margin-left: 0; } .two-columns { grid-template-columns: 1fr; } .stats-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="app-container">
    <div class="sidebar">
        <div class="logo-area">
            <h2>Red Admin</h2>
            <span>Control Panel</span>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">MAIN</div>
            <div class="nav-item active" data-page="dashboard">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
            </div>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">RED</div>
            <a href="servicio.php" class="nav-item">
                <i class="fas fa-wifi"></i><span>Servicios de Internet</span>
            </a>
            <div class="nav-item" data-page="tickets">
                <i class="fas fa-ticket-alt"></i><span>Tickets Técnicos</span>
            </div>
            <div class="nav-item" data-page="technicians">
                <i class="fas fa-tools"></i><span>Técnicos</span>
            </div>
            <div class="nav-item" data-page="monitoring">
                <i class="fas fa-chart-line"></i><span>Monitoreo de Red</span>
            </div>
        </div>
        <div class="nav-section" style="margin-top: auto;">
            <a href="/Chango Vision/pages/cambiar_pass.php" class="nav-item" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                <i class="fas fa-key"></i><span>Cambiar Contraseña</span>
            </a>
            <a href="../logout.php" id="logoutSidebarBtn" class="nav-item">
                <i class="fas fa-sign-out-alt"></i><span>Cerrar Sesión</span>
            </a>
        </div>
    </div>
    <div class="main-content">
        <div class="top-bar">
            <div class="page-title" id="pageTitle">Dashboard</div>
            <div class="user-info">
                <span><?= htmlspecialchars($_SESSION['usuario'] ?? 'Administrador Red') ?></span>
                <div class="user-avatar">R</div>
            </div>
        </div>
        <div id="dynamicContent">
            <div id="dashboardContent">
                <div class="stats-row">
                    <div class="stat-card"><div class="icon"><i class="fas fa-wifi"></i></div><div class="number" id="totalServices">0</div><div class="label">Total Servicios</div></div>
                    <div class="stat-card"><div class="icon"><i class="fas fa-dollar-sign"></i></div><div class="number" id="totalIngresos">$0</div><div class="label">Ingresos Mensuales</div></div>
                    <div class="stat-card"><div class="icon"><i class="fas fa-headset"></i></div><div class="number" id="pendingTickets">0</div><div class="label">Tickets Pendientes</div></div>
                    <div class="stat-card"><div class="icon"><i class="fas fa-chart-line"></i></div><div class="number" id="networkLoad">0%</div><div class="label">Carga de Red</div></div>
                </div>
                <div class="two-columns">
                    <div class="card">
                        <div class="card-header"><h3><i class="fas fa-server"></i> Servicios Activos</h3></div>
                        <div id="topServicesList"><div class="service-item"><div><div class="service-name">Cargando servicios...</div></div></div></div>
                    </div>
                    <div class="card">
                        <div class="card-header"><h3><i class="fas fa-tasks"></i> Tickets Recientes</h3></div>
                        <div id="recentTicketsList"><div class="service-item"><div><div class="service-name">Cargando tickets...</div></div></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer">
            <div>© 2026 Panel Administrador de Red</div>
            <div class="footer-status"><span class="status-dot"></span> System Online <i class="fas fa-user-shield"></i> ADMINISTRADOR DE RED</div>
        </div>
    </div>
</div>
<script>
    let servicios = [];
    let tickets = [
        { id: 1, cliente: "Juan Pérez", problema: "Falla de conexión", prioridad: "alta", estado: "pendiente" },
        { id: 2, cliente: "María López", problema: "Velocidad lenta", prioridad: "media", estado: "en_proceso" }
    ];
    let tecnicos = [
        { id: 1, nombre: "Pedro Sánchez", especialidad: "Fibra óptica", disponible: true },
        { id: 2, nombre: "Luis Martínez", especialidad: "Radio enlace", disponible: true }
    ];

    async function cargarServiciosDB() {
        try {
            const response = await fetch('../controllers/servicios_controller.php?accion=listar');
            const result = await response.json();
            if (result.success) {
                servicios = result.data;
                actualizarDashboard();
                cargarServiciosDestacados();
            }
        } catch (error) { console.error('Error:', error); }
    }

    function cargarServiciosDestacados() {
        const container = document.getElementById('topServicesList');
        if (!container) return;
        if (servicios.length === 0) { container.innerHTML = '<div class="service-item"><div><div class="service-name">No hay servicios</div></div></div>'; return; }
        container.innerHTML = servicios.slice(0, 3).map(s => `
            <div class="service-item">
                <div><div class="service-name">${escapeHtml(s.nombre)}</div><div class="service-details">${s.velocidad} · $${parseInt(s.precio).toLocaleString()}</div></div>
                <div class="service-status"><span class="badge badge-active">Activo</span></div>
            </div>
        `).join('');
    }

    function cargarTickets() {
        const container = document.getElementById('dynamicContent');
        let html = `<div class="card"><div class="card-header"><h3><i class="fas fa-ticket-alt"></i> Tickets Técnicos</h3></div>
            <div style="overflow-x: auto;"><table class="log-table" style="width:100%; border-collapse: collapse;"><thead><tr><th>ID</th><th>Cliente</th><th>Problema</th><th>Prioridad</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
            ${tickets.map(t => `<tr><td>${t.id}</td><td><strong>${escapeHtml(t.cliente)}</strong></td><td>${escapeHtml(t.problema)}</td>
            <td><span class="badge ${t.prioridad === 'alta' ? 'badge-warning' : 'badge-active'}">${t.prioridad}</span></td>
            <td><span class="badge ${t.estado === 'pendiente' ? 'badge-warning' : 'badge-active'}">${t.estado}</span></td>
            <td><select onchange="cambiarEstadoTicket(${t.id}, this.value)"><option value="pendiente" ${t.estado === 'pendiente' ? 'selected' : ''}>Pendiente</option>
            <option value="en_proceso" ${t.estado === 'en_proceso' ? 'selected' : ''}>En proceso</option>
            <option value="resuelto" ${t.estado === 'resuelto' ? 'selected' : ''}>Resuelto</option></select></td></tr>`).join('')}
            </tbody></table></div></div><div style="margin-top:1rem;"><button class="btn-refresh" onclick="cargarDashboardView()"><i class="fas fa-arrow-left"></i> Volver</button></div>`;
        container.innerHTML = html;
        document.getElementById('pageTitle').innerText = 'Tickets Técnicos';
    }

    function cambiarEstadoTicket(id, nuevoEstado) {
        const ticket = tickets.find(t => t.id === id);
        if (ticket) { ticket.estado = nuevoEstado; cargarTickets(); }
    }

    function cargarTecnicos() {
        const container = document.getElementById('dynamicContent');
        let html = `<div class="card"><div class="card-header"><h3><i class="fas fa-tools"></i> Técnicos</h3></div>
            <div style="overflow-x: auto;"><table class="log-table"><thead><tr><th>ID</th><th>Nombre</th><th>Especialidad</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
            ${tecnicos.map(t => `<tr><td>${t.id}</td><td><strong>${escapeHtml(t.nombre)}</strong></td><td>${escapeHtml(t.especialidad)}</td>
            <td><span class="badge ${t.disponible ? 'badge-active' : 'badge-warning'}">${t.disponible ? 'Disponible' : 'Ocupado'}</span></td>
            <td><button class="btn-refresh" style="background:#f59e0b;" onclick="cambiarEstadoTecnico(${t.id})">Cambiar Estado</button></td></tr>`).join('')}
            </tbody></table></div></div><div style="margin-top:1rem;"><button class="btn-refresh" onclick="cargarDashboardView()"><i class="fas fa-arrow-left"></i> Volver</button></div>`;
        container.innerHTML = html;
        document.getElementById('pageTitle').innerText = 'Técnicos';
    }

    function cambiarEstadoTecnico(id) {
        const tecnico = tecnicos.find(t => t.id === id);
        if (tecnico) { tecnico.disponible = !tecnico.disponible; cargarTecnicos(); }
    }

    function cargarMonitoreo() {
        const container = document.getElementById('dynamicContent');
        let html = `<div class="card"><div class="card-header"><h3><i class="fas fa-chart-line"></i> Monitoreo de Red</h3></div>
            <div class="stats-row"><div class="stat-card"><div class="icon"><i class="fas fa-tachometer-alt"></i></div><div class="number">98%</div><div class="label">Uptime</div></div>
            <div class="stat-card"><div class="icon"><i class="fas fa-clock"></i></div><div class="number">24/7</div><div class="label">Monitorización</div></div></div>
            <div class="service-item"><div>Latencia Promedio</div><div>25 ms</div></div>
            <div class="service-item"><div>Ancho de Banda Total</div><div>10 Gbps</div></div>
            <div class="service-item"><div>Uso Actual</div><div>3.2 Gbps (32%)</div></div>
            <div class="service-item"><div>Pérdida de Paquetes</div><div>0.2%</div></div>
            </div><div style="margin-top:1rem;"><button class="btn-refresh" onclick="cargarDashboardView()"><i class="fas fa-arrow-left"></i> Volver</button></div>`;
        container.innerHTML = html;
        document.getElementById('pageTitle').innerText = 'Monitoreo de Red';
    }

    function actualizarDashboard() {
        document.getElementById('totalServices').innerText = servicios.length;
        const ingresos = servicios.reduce((sum, s) => sum + parseFloat(s.precio), 0);
        document.getElementById('totalIngresos').innerText = '$' + ingresos.toLocaleString();
        document.getElementById('pendingTickets').innerText = tickets.filter(t => t.estado === 'pendiente').length;
        document.getElementById('networkLoad').innerText = Math.floor(Math.random() * 30 + 65) + '%';
    }

    function cargarDashboardView() {
        document.getElementById('dynamicContent').innerHTML = document.getElementById('dashboardContent').innerHTML;
        document.getElementById('pageTitle').innerText = 'Dashboard';
        cargarServiciosDestacados();
        actualizarDashboard();
    }

    function escapeHtml(text) { if (!text) return ''; const div = document.createElement('div'); div.textContent = text; return div.innerHTML; }

    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            const page = this.dataset.page;
            if (page === 'tickets') cargarTickets();
            else if (page === 'technicians') cargarTecnicos();
            else if (page === 'monitoring') cargarMonitoreo();
            else if (page === 'dashboard') cargarDashboardView();
        });
    });
    cargarServiciosDB();
</script>
</body>
</html>
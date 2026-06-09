<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/../config/conexion.php';
include __DIR__ . '/../controllers/funcion_log.php';

 $log = new SistemaLog($conexion);

// ===============================
// OBTENER CLIENTES
// ===============================
$sql = "SELECT * FROM clientes ORDER BY id DESC";
 $result = $conexion->query($sql);
$clientes = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$totalClientes = count($clientes);

// MENSAJES
$mensaje = $_SESSION['mensaje'] ?? '';
$mensaje_tipo = $_SESSION['mensaje_tipo'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']);

// ===============================
// ACCIONES POST
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // REGISTRAR CLIENTE
if ($accion === 'registrar') {
    $nombre = trim($_POST['nombre']);
    $dni = trim($_POST['dni']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);

    $sql = "INSERT INTO clientes (nombre, dni, email, telefono, direccion, estado) VALUES (?, ?, ?, ?, ?, 'Activo')";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssss", $nombre, $dni, $email, $telefono, $direccion);

    if ($stmt->execute()) {
        // ✅ Ahora solo pasamos el nombre, sin email
        $log->registrarCliente($_SESSION['id_usuario'], $_SESSION['usuario'], $nombre);
        $_SESSION['mensaje'] = "Cliente registrado correctamente";
        $_SESSION['mensaje_tipo'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al registrar cliente";
        $_SESSION['mensaje_tipo'] = "error";
    }
    header("Location: admin_cliente.php");
    exit;
}

    // ELIMINAR CLIENTE
    if ($accion === 'eliminar') {
        $id = intval($_POST['id']);
        $nombre = $_POST['nombre'];

        $stmt = $conexion->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $log->eliminarCliente($_SESSION['id_usuario'], $_SESSION['usuario'], $id, $nombre);
            $_SESSION['mensaje'] = "Cliente eliminado correctamente";
            $_SESSION['mensaje_tipo'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al eliminar cliente";
            $_SESSION['mensaje_tipo'] = "error";
        }
        header("Location: admin_cliente.php");
        exit;
    }

    // EDITAR CLIENTE
    if ($accion === 'editar') {
        $id = intval($_POST['id']);
        $nombre = trim($_POST['nombre']);
        $dni = trim($_POST['dni']);
        $email = trim($_POST['email']);
        $telefono = trim($_POST['telefono']);
        $direccion = trim($_POST['direccion']);

        $sql = "UPDATE clientes SET nombre=?, dni=?, email=?, telefono=?, direccion=? WHERE id=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssi", $nombre, $dni, $email, $telefono, $direccion, $id);

        if ($stmt->execute()) {
            $log->editarCliente($_SESSION['id_usuario'], $_SESSION['usuario'], $id, $nombre);
            $_SESSION['mensaje'] = "Cliente actualizado correctamente";
            $_SESSION['mensaje_tipo'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al actualizar cliente";
            $_SESSION['mensaje_tipo'] = "error";
        }
        header("Location: admin_cliente.php");
        exit;
    }

    // CAMBIAR ESTADO
    if ($accion === 'cambiar_estado') {
        $id = intval($_POST['id']);
        $estado = $_POST['estado'];
        $nombre = $_POST['nombre'];

        $stmt = $conexion->prepare("UPDATE clientes SET estado=? WHERE id=?");
        $stmt->bind_param("si", $estado, $id);

        if ($stmt->execute()) {
            $log->cambiarEstadoCliente($_SESSION['id_usuario'], $_SESSION['usuario'], $id, $nombre, $estado);
            $_SESSION['mensaje'] = "Estado actualizado correctamente";
            $_SESSION['mensaje_tipo'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al cambiar estado";
            $_SESSION['mensaje_tipo'] = "error";
        }
        header("Location: admin_cliente.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Clientes - Chango Vision</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .dashboard {
            max-width: 1400px;
            width: 100%;
            background: white;
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #0f2b3d 0%, #1b4f6e 100%);
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .logo h1 {
            color: white;
            font-weight: 700;
            font-size: 1.6rem;
        }

        .logo p {
            color: #b9e2f5;
            font-size: 0.8rem;
            margin-top: 4px;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn-primary {
            background: white;
            border: none;
            padding: 0.6rem 1.3rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.85rem;
            color: #1b4f6e;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            background: #e6f7ff;
            transform: scale(0.97);
        }

        .btn-outline-light {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.5);
            padding: 0.6rem 1.3rem;
            border-radius: 40px;
            font-weight: 500;
            color: white;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-outline-light:hover {
            background: rgba(255,255,255,0.15);
        }

        .stats-row {
            display: flex;
            gap: 1.5rem;
            padding: 2rem 2rem 0.5rem 2rem;
        }

        .stat-card {
            background: #f9fafc;
            flex: 1;
            padding: 1.2rem;
            border-radius: 24px;
            border: 1px solid #e9eef3;
        }

        .stat-title {
            color: #5b6e8c;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: #1b4f6e;
            margin-top: 8px;
        }

        .search-container {
            display: flex;
            gap: 15px;
            margin: 0 2rem 1.5rem 2rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-box input {
            padding: 10px 15px;
            border-radius: 40px;
            border: 1px solid #ddd;
            width: 300px;
            font-size: 0.9rem;
            outline: none;
        }

        .search-box input:focus {
            border-color: #1b4f6e;
        }

        .filter-btn {
            padding: 8px 20px;
            border-radius: 40px;
            border: none;
            background: #e2e8f0;
            cursor: pointer;
            transition: 0.3s;
            font-weight: 500;
        }

        .filter-btn.active {
            background: #1b4f6e;
            color: white;
        }

        .table-wrapper {
            padding: 1rem 2rem 2rem 2rem;
        }

        .section-title {
            font-weight: 700;
            font-size: 1.2rem;
            color: #1e2a41;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .client-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 20px;
            overflow: hidden;
        }

        .client-table th {
            text-align: left;
            padding: 1rem;
            background: #f8fafd;
            font-weight: 600;
            color: #2c3e66;
            font-size: 0.8rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .client-table td {
            padding: 1rem;
            border-bottom: 1px solid #edf2f7;
            color: #1f2b48;
            font-size: 0.85rem;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge.activo {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge.inactivo {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-edit, .btn-delete, .btn-state {
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 5px;
            transition: 0.3s;
            font-size: 0.75rem;
        }

        .btn-edit {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .btn-edit:hover {
            background-color: #1976d2;
            color: white;
        }

        .btn-delete {
            background-color: #ffebee;
            color: #d32f2f;
        }

        .btn-delete:hover {
            background-color: #d32f2f;
            color: white;
        }

        .btn-state {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .btn-state:hover {
            background-color: #f57c00;
            color: white;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            visibility: hidden;
            opacity: 0;
            transition: 0.2s;
        }

        .modal-overlay.active {
            visibility: visible;
            opacity: 1;
        }

        .modal-container {
            background: white;
            width: 90%;
            max-width: 500px;
            border-radius: 20px;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #0f2b3d, #1b4f6e);
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: white;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #1b4f6e;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #0f2b3d, #1b4f6e);
            color: white;
            width: 100%;
            padding: 0.8rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }

        .toast-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            z-index: 2000;
            animation: slideIn 0.3s ease;
        }
        .toast-success { background: #4CAF50; }
        .toast-error { background: #f44336; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 700px) {
            .stats-row { flex-direction: column; }
            .header { flex-direction: column; text-align: center; }
            .search-container { flex-direction: column; margin: 0 1rem 1rem 1rem; }
            .search-box input { width: 100%; }
            .table-wrapper { overflow-x: auto; }
        }
    </style>
</head>
<body>

<div class="dashboard">
    <div class="header">
        <div class="logo">
            <h1><i class="fas fa-store"></i> Chango Vision</h1>
            <p>Gestión de Clientes</p>
        </div>
        <div class="header-actions">
            <button class="btn-primary" onclick="abrirModal()">
                <i class="fas fa-user-plus"></i> Nuevo Cliente
            </button>
            <a href="admin.php" class="btn-outline-light">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-title">Total Clientes</div>
            <div class="stat-number"><?= $totalClientes ?></div>
        </div>
    </div>

    <div class="search-container">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="🔍 Buscar por nombre, DNI, email...">
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="todos">Todos</button>
            <button class="filter-btn" data-filter="Activo">Activos</button>
            <button class="filter-btn" data-filter="Inactivo">Inactivos</button>
        </div>
    </div>

    <div class="table-wrapper">
        <div class="section-title">
            <i class="fas fa-user-friends"></i> Listado de Clientes
        </div>
        <table class="client-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>DNI</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="clientesTableBody">
                <?php if (empty($clientes)): ?>
                    <tr><td colspan="8" style="text-align: center;">No hay clientes registrados</td></tr>
                <?php else: ?>
                    <?php foreach ($clientes as $cliente): ?>
                    <tr data-estado="<?= $cliente['estado'] ?? 'Activo' ?>">
                        <td><?= $cliente['id'] ?></td>
                        <td><strong><?= htmlspecialchars($cliente['nombre']) ?></strong></td>
                        <td><?= htmlspecialchars($cliente['dni'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($cliente['email']) ?></td>
                        <td><?= htmlspecialchars($cliente['telefono'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($cliente['direccion'] ?? '-') ?></td>
                        <td>
                            <span class="badge <?= ($cliente['estado'] ?? 'Activo') == 'Activo' ? 'activo' : 'inactivo' ?>">
                                <?= ($cliente['estado'] ?? 'Activo') == 'Activo' ? '✅ Activo' : '❌ Inactivo' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn-edit" onclick='editarCliente(<?= json_encode($cliente) ?>)'><i class="fas fa-edit"></i></button>
                            <button class="btn-delete" onclick='eliminarCliente(<?= $cliente['id'] ?>, "<?= htmlspecialchars($cliente['nombre']) ?>")'><i class="fas fa-trash"></i></button>
                            <button class="btn-state" onclick='cambiarEstado(<?= $cliente['id'] ?>, "<?= htmlspecialchars($cliente['nombre']) ?>", "<?= $cliente['estado'] ?? 'Activo' ?>")'><i class="fas fa-exchange-alt"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL -->
<div id="clienteModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fas fa-user-plus"></i> Nuevo Cliente</h3>
            <button class="close-modal" onclick="cerrarModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formCliente" method="POST">
                <input type="hidden" name="accion" id="formAccion" value="registrar">
                <input type="hidden" name="id" id="clienteId">
                
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" id="nombreCliente" name="nombre" required>
                </div>
                <div class="form-group">
                    <label>DNI</label>
                    <input type="text" id="dniCliente" name="dni" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="emailCliente" name="email" required>
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" id="telefonoCliente" name="telefono">
                </div>
                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" id="direccionCliente" name="direccion">
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Guardar</button>
            </form>
        </div>
    </div>
</div>

<!-- Formularios ocultos -->
<form id="formEliminar" method="POST" style="display:none;">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id" id="eliminarId">
    <input type="hidden" name="nombre" id="eliminarNombre">
</form>

<form id="formEstado" method="POST" style="display:none;">
    <input type="hidden" name="accion" value="cambiar_estado">
    <input type="hidden" name="id" id="estadoId">
    <input type="hidden" name="estado" id="estadoValor">
    <input type="hidden" name="nombre" id="estadoNombre">
</form>

<script>
    <?php if ($mensaje): ?>
        mostrarToast('<?= $mensaje ?>', '<?= $mensaje_tipo ?>');
    <?php endif; ?>

    function mostrarToast(mensaje, tipo) {
        const toast = document.createElement('div');
        toast.className = `toast-message toast-${tipo === 'success' ? 'success' : 'error'}`;
        toast.innerHTML = `<i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${mensaje}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    const modal = document.getElementById('clienteModal');

    function abrirModal() {
        modal.classList.add('active');
        document.getElementById('formAccion').value = 'registrar';
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Nuevo Cliente';
        document.getElementById('clienteId').value = '';
        document.getElementById('nombreCliente').value = '';
        document.getElementById('dniCliente').value = '';
        document.getElementById('emailCliente').value = '';
        document.getElementById('telefonoCliente').value = '';
        document.getElementById('direccionCliente').value = '';
    }

    function cerrarModal() {
        modal.classList.remove('active');
    }

    function editarCliente(cliente) {
        abrirModal();
        document.getElementById('formAccion').value = 'editar';
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editar Cliente';
        document.getElementById('clienteId').value = cliente.id;
        document.getElementById('nombreCliente').value = cliente.nombre;
        document.getElementById('dniCliente').value = cliente.dni || '';
        document.getElementById('emailCliente').value = cliente.email;
        document.getElementById('telefonoCliente').value = cliente.telefono || '';
        document.getElementById('direccionCliente').value = cliente.direccion || '';
    }

    function eliminarCliente(id, nombre) {
        if (confirm(`¿Eliminar cliente "${nombre}"?`)) {
            document.getElementById('eliminarId').value = id;
            document.getElementById('eliminarNombre').value = nombre;
            document.getElementById('formEliminar').submit();
        }
    }

    function cambiarEstado(id, nombre, estadoActual) {
        const nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';
        const mensaje = nuevoEstado === 'Activo' ? `¿Activar cliente "${nombre}"?` : `¿Desactivar cliente "${nombre}"?`;
        if (confirm(mensaje)) {
            document.getElementById('estadoId').value = id;
            document.getElementById('estadoValor').value = nuevoEstado;
            document.getElementById('estadoNombre').value = nombre;
            document.getElementById('formEstado').submit();
        }
    }

    // Filtros
    const searchInput = document.getElementById('searchInput');
    const filterBtns = document.querySelectorAll('.filter-btn');
    let currentFilter = 'todos';

    function filtrarTabla() {
        const searchTerm = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('#clientesTableBody tr');
        rows.forEach(row => {
            if (row.cells.length < 8) return;
            const nombre = row.cells[1]?.textContent.toLowerCase() || '';
            const dni = row.cells[2]?.textContent.toLowerCase() || '';
            const email = row.cells[3]?.textContent.toLowerCase() || '';
            const estado = row.dataset.estado || 'Activo';
            
            const matchesSearch = nombre.includes(searchTerm) || dni.includes(searchTerm) || email.includes(searchTerm);
            const matchesFilter = currentFilter === 'todos' || estado === currentFilter;
            
            row.style.display = matchesSearch && matchesFilter ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filtrarTabla);
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentFilter = btn.dataset.filter;
            filtrarTabla();
        });
    });
</script>

</body>
</html>
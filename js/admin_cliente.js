// --- LÓGICA CON BASE DE DATOS ---
let clientes = [];
let editandoId = null; // Variable para saber si estamos editando o creando

// Elementos del DOM
const adminBtn = document.getElementById('adminClientesBtn');
const modal = document.getElementById('clienteModal');
const closeModalBtn = document.getElementById('closeModalBtn');
const formCliente = document.getElementById('formCliente');
const clientesTableBody = document.getElementById('clientesTableBody');
const totalClientesSpan = document.getElementById('totalClientesCount');

// 1. FUNCIÓN PARA OBTENER CLIENTES (LISTAR)
async function cargarClientes() {
    try {
        const respuesta = await fetch('../funciones/listar_cliente.php');
        clientes = await respuesta.json();
        
        totalClientesSpan.innerText = clientes.length;

        if (clientes.length === 0) {
            clientesTableBody.innerHTML = `<tr><td colspan="8" style="text-align:center;">No hay clientes registrados.</td></tr>`;
            return;
        }

        let html = '';
        clientes.forEach(cliente => {
            html += `
                <tr>
                    <td>${cliente.id}</td>
                    <td><strong>${escapeHtml(cliente.nombre)}</strong></td>
                    <td>${escapeHtml(cliente.dni)}</td>
                    <td>${escapeHtml(cliente.email)}</td>
                    <td>${escapeHtml(cliente.telefono)}</td>
                    <td>${escapeHtml(cliente.direccion)}</td>
                    <td>
                        <span class="badge ${cliente.estado === 'Activo' ? 'status-active' : 'status-inactive'}">
                            ${cliente.estado}
                        </span>
                    </td>
                    <td>
                        <button class="btn-edit" onclick="prepararEdicion(${cliente.id})"><i class="fas fa-edit"></i></button>
                        <button class="btn-delete" onclick="eliminarCliente(${cliente.id})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
        });
        clientesTableBody.innerHTML = html;

    } catch (error) {
        console.error("Error al cargar:", error);
    }
}

// 2. FUNCIÓN PARA ELIMINAR (BORRADO LÓGICO)
async function eliminarCliente(id) {
    if (confirm("¿Estás seguro de que deseas desactivar este cliente?")) {
        try {
            const formData = new FormData();
            formData.append('id', id);

            const res = await fetch('../funciones/eliminar_cliente.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.status === "success") {
                cargarClientes(); 
            }
        } catch (error) {
            alert("Error al eliminar");
        }
    }
}

// 3. FUNCIÓN PARA PREPARAR LA EDICIÓN
function prepararEdicion(id) {
    const cliente = clientes.find(c => c.id == id);
    if (cliente) {
        editandoId = id; // Guardamos el ID
        
        // Llenamos el formulario con los datos actuales
        document.getElementById('nombreCliente').value = cliente.nombre;
        document.getElementById('dniCliente').value = cliente.dni;
        document.getElementById('emailCliente').value = cliente.email;
        document.getElementById('telefonoCliente').value = cliente.telefono;
        document.getElementById('direccionCliente').value = cliente.direccion;

        // Cambiamos visualmente el modal
        document.querySelector('.modal-header h3').innerHTML = '<i class="fas fa-edit"></i> Editar Cliente';
        document.querySelector('.btn-submit').innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
        
        modal.classList.add('active');
    }
}

// 4. EVENTO SUBMIT (CREAR O EDITAR)
formCliente.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('nombre', document.getElementById('nombreCliente').value);
    formData.append('dni', document.getElementById('dniCliente').value);
    formData.append('email', document.getElementById('emailCliente').value);
    formData.append('telefono', document.getElementById('telefonoCliente').value);
    formData.append('direccion', document.getElementById('direccionCliente').value);

    // Si editandoId tiene valor, usamos la ruta de editar, sino la de guardar
    let url = editandoId ? '../funciones/editar_clientes.php' : '../funciones/guardar_cliente.php';
    if (editandoId) formData.append('id', editandoId);

    try {
        const respuesta = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const resultado = await respuesta.json();

        if (resultado.status === "success") {
            cerrarModal();
            alert(editandoId ? '✅ Cliente actualizado' : '✅ Cliente registrado');
            editandoId = null; // Resetear variable
            cargarClientes(); 
        } else {
            alert('Error: ' + resultado.message);
        }
    } catch (error) {
        alert('Error de conexión con el servidor');
    }
});

// Variables de estado global
let filtroBusqueda = '';
let filtroEstado = 'todos';

// 1. La Función Maestra de Filtrado
function obtenerClientesFiltrados(listaOriginal) {
    return listaOriginal.filter(cliente => {
        // Coincidencia por texto (Nombre, DNI o Email)
        const coincideBusqueda = 
            cliente.nombre.toLowerCase().includes(filtroBusqueda.toLowerCase()) ||
            cliente.dni.toLowerCase().includes(filtroBusqueda.toLowerCase());

        // Coincidencia por estado (Activo/Inactivo)
        const coincideEstado = 
            filtroEstado === 'todos' || cliente.estado === filtroEstado;

        return coincideBusqueda && coincideEstado;
    });
}

// 2. Evento para Búsqueda (Input)
document.getElementById('searchInput').addEventListener('input', (e) => {
    filtroBusqueda = e.target.value;
    renderizarTabla(); // Llamas a tu función que dibuja la tabla
});

// 3. Evento para Botones de Estado
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        // Cambiar clase visual
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Aplicar filtro
        filtroEstado = btn.dataset.filter;
        renderizarTabla();
    });
});

// 4. Función para dibujar la tabla (Ejemplo rápido)
function renderizarTabla() {
    const dataFiltrada = obtenerClientesFiltrados(clientes);
    const tbody = document.getElementById('clientesTableBody');
    
    tbody.innerHTML = dataFiltrada.map(c => `
        <tr>
            <td>${c.nombre}</td>
            <td>${c.dni}</td>
            <td>${c.estado}</td>
        </tr>
    `).join('');
}

// --- FUNCIONES DE APOYO ---
function escapeHtml(str) {
    if(!str) return '';
    return str.toString().replace(/[&<>]/g, function(m) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;' };
        return map[m];
    });
}

function abrirModalClientes() {
    editandoId = null; // Aseguramos que no estamos en modo edición
    formCliente.reset();
    document.querySelector('.modal-header h3').innerHTML = '<i class="fas fa-user-plus"></i> Cargar Nuevo Cliente';
    document.querySelector('.btn-submit').innerHTML = '<i class="fas fa-save"></i> Registrar Cliente';
    modal.classList.add('active');
}

function cerrarModal() {
    modal.classList.remove('active');
}

adminBtn.addEventListener('click', (e) => {
    e.preventDefault();
    abrirModalClientes();
});

closeModalBtn.addEventListener('click', cerrarModal);

// Ejecutar al cargar la página
window.onload = cargarClientes;
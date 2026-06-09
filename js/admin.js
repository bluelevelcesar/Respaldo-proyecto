// ============================================
// MEDIA CONTROL PANEL - SCRIPT COMPLETO
// ============================================

// ========== DATOS DE EJEMPLO ==========
let appData = {
    stats: {
        mediaServices: 24,
        offlineServices: 3,
        activeCustomers: 1284,
        totalRevenue: 42500
    },
    
    services: [
        { port: 8008, name: "Dan O'Connor (DanOsongs.com)", description: "En la Brisa", connections: 1, bitrate: "128Kbps", slug: "flussonictv", status: "active" },
        { port: 8012, name: "Radio Continental", description: "Noticias 24/7", connections: 45, bitrate: "256Kbps", slug: "radiocontinental", status: "active" },
        { port: 8025, name: "Music Top 40", description: "Los 40 principales", connections: 128, bitrate: "320Kbps", slug: "musictop40", status: "active" },
        { port: 8030, name: "Jazz & Blues", description: "Música instrumental", connections: 23, bitrate: "192Kbps", slug: "jazzblues", status: "active" },
        { port: 8045, name: "Rock Nacional", description: "Lo mejor del rock", connections: 67, bitrate: "256Kbps", slug: "rocknacional", status: "inactive" }
    ],
    
    emailTemplates: [
        { name: "Welcome Email", description: "New customer registration", lastEdited: "2024-01-15" },
        { name: "Invoice Notification", description: "Monthly billing reminder", lastEdited: "2024-01-20" },
        { name: "Service Alert", description: "Outage notification", lastEdited: "2024-01-18" },
        { name: "Payment Confirmation", description: "Payment received", lastEdited: "2024-01-22" }
    ],
    
    recentLogins: [
        { user: "Administrator", username: "admin", ip: "192.168.33.1", time: "5 seconds ago", timestamp: new Date() },
        { user: "John Smith", username: "jsmith", ip: "192.168.33.45", time: "2 minutes ago", timestamp: new Date(Date.now() - 120000) },
        { user: "Maria Garcia", username: "mgarcia", ip: "192.168.33.78", time: "15 minutes ago", timestamp: new Date(Date.now() - 900000) },
        { user: "Technical Support", username: "tech", ip: "192.168.33.102", time: "1 hour ago", timestamp: new Date(Date.now() - 3600000) },
        { user: "Carlos Lopez", username: "carlosl", ip: "192.168.33.156", time: "3 hours ago", timestamp: new Date(Date.now() - 10800000) }
    ],
    
    gettingStarted: [
        { id: "company", name: "COMPANY NAME", completed: false, icon: "fa-building" },
        { id: "email", name: "EMAIL SETTINGS", completed: false, icon: "fa-envelope" },
        { id: "recaptcha", name: "RECAPTCHA", completed: false, icon: "fa-shield-alt" },
        { id: "service", name: "CREATE SERVICE", completed: false, icon: "fa-plus-circle" }
    ],
    
    currentPage: "dashboard",
    lastUpdate: new Date()
};

// ========== FUNCIONES DE RENDERIZADO ==========
function renderStats() {
    const statsContainer = document.querySelector('.stats-grid');
    if (!statsContainer) return;
    
    statsContainer.innerHTML = `
        <div class="stat-card" data-stat="mediaServices">
            <div class="stat-title">MEDIA SERVICES</div>
            <div class="stat-value" id="statMediaServices">${appData.stats.mediaServices}</div>
            <a href="#" class="stat-link" onclick="showServices()">View all services →</a>
        </div>
        <div class="stat-card" data-stat="offlineServices">
            <div class="stat-title">OFFLINE SERVICES</div>
            <div class="stat-value" id="statOfflineServices">${appData.stats.offlineServices}</div>
            <a href="#" class="stat-link" onclick="showOfflineServices()">View offline →</a>
        </div>
        <div class="stat-card" data-stat="activeCustomers">
            <div class="stat-title">ACTIVE CUSTOMERS</div>
            <div class="stat-value" id="statActiveCustomers">${appData.stats.activeCustomers.toLocaleString()}</div>
            <a href="#" class="stat-link" onclick="showCustomers()">View all customers →</a>
        </div>
        <div class="stat-card" data-stat="totalRevenue">
            <div class="stat-title">TOTAL REVENUE</div>
            <div class="stat-value" id="statTotalRevenue">$${appData.stats.totalRevenue.toLocaleString()}</div>
            <a href="#" class="stat-link" onclick="showReports()">View reports →</a>
        </div>
    `;
}

function renderGettingStarted() {
    const container = document.querySelector('.getting-started-items');
    if (!container) return;
    
    container.innerHTML = appData.gettingStarted.map(item => `
        <div class="gs-item" onclick="toggleGettingStarted('${item.id}')" style="cursor: pointer;">
            <i class="fas ${item.icon}"></i>
            <span style="flex: 1;">${item.name}</span>
            ${item.completed ? '<i class="fas fa-check-circle" style="color: #4caf50;"></i>' : '<i class="far fa-circle" style="color: rgba(255,255,255,0.5);"></i>'}
        </div>
    `).join('');
}

function renderTopServices() {
    const container = document.querySelector('.services-list');
    if (!container) return;
    
    const activeServices = appData.services.filter(s => s.status === 'active').slice(0, 5);
    
    container.innerHTML = activeServices.map(service => `
        <div class="service-item" data-service-port="${service.port}">
            <div style="flex: 1;">
                <div class="service-port">${service.port}</div>
                <div class="service-name">${service.name}</div>
                <div class="service-details">${service.description} · ${service.connections} connections @ ${service.bitrate}</div>
            </div>
            <div class="service-status">
                <div class="service-port">${service.slug}</div>
                <div class="service-connections">
                    <span class="status-badge ${service.status === 'active' ? 'status-active' : 'status-inactive'}">
                        ${service.status === 'active' ? 'Active' : 'Inactive'}
                    </span>
                </div>
                <div class="service-actions">
                    <i class="fas fa-play" onclick="toggleService(${service.port})" style="cursor: pointer; color: #4caf50; margin-right: 8px;"></i>
                    <i class="fas fa-chart-line" onclick="viewServiceStats(${service.port})" style="cursor: pointer; color: #667eea;"></i>
                </div>
            </div>
        </div>
    `).join('');
}

function renderEmailTemplates() {
    const container = document.querySelector('.templates-list');
    if (!container) return;
    
    container.innerHTML = appData.emailTemplates.map(template => `
        <div class="service-item">
            <div>
                <div class="service-name">${template.name}</div>
                <div class="service-details">${template.description}</div>
            </div>
            <div class="service-status">
                <span class="login-time">Last edit: ${template.lastEdited}</span>
                <i class="fas fa-edit" onclick="editEmailTemplate('${template.name}')" style="color: #667eea; cursor: pointer; margin-left: 15px;"></i>
                <i class="fas fa-eye" onclick="previewEmailTemplate('${template.name}')" style="color: #4caf50; cursor: pointer; margin-left: 8px;"></i>
            </div>
        </div>
    `).join('');
}

function renderRecentLogins() {
    const container = document.querySelector('.logins-list');
    if (!container) return;
    
    container.innerHTML = appData.recentLogins.map(login => `
        <div class="login-item">
            <div>
                <div class="login-user">${login.user} (${login.username})</div>
                <div class="login-ip">${login.ip}</div>
            </div>
            <div class="login-time">${login.time}</div>
        </div>
    `).join('');
}

function updateFooter() {
    const footerStatus = document.querySelector('.footer-status');
    if (!footerStatus) return;
    
    const now = new Date();
    const lastAdminLogin = appData.recentLogins.find(l => l.username === 'admin');
    
    footerStatus.innerHTML = `
        <span><span class="status-dot"></span> System Online</span>
        <span><i class="fas fa-user-shield"></i> ADMINISTRATOR</span>
        <span><i class="far fa-clock"></i> Last login: ${lastAdminLogin ? lastAdminLogin.time : 'N/A'}</span>
        <span><i class="fas fa-sync-alt" onclick="refreshData()" style="cursor: pointer;"></i></span>
    `;
    
    const footerTime = document.querySelector('.footer-time');
    if (footerTime) {
        footerTime.innerHTML = `Last update: ${appData.lastUpdate.toLocaleTimeString()}`;
    }
}

// ========== FUNCIONES DE ACTUALIZACIÓN ==========
function updateStats() {
    const offlineCount = appData.services.filter(s => s.status === 'inactive').length;
    appData.stats.offlineServices = offlineCount;
    
    document.getElementById('statMediaServices').innerText = appData.stats.mediaServices;
    document.getElementById('statOfflineServices').innerText = appData.stats.offlineServices;
    document.getElementById('statActiveCustomers').innerText = appData.stats.activeCustomers.toLocaleString();
    document.getElementById('statTotalRevenue').innerText = `$${appData.stats.totalRevenue.toLocaleString()}`;
}

function refreshData() {
    appData.lastUpdate = new Date();
    updateFooter();
    showNotification('Data refreshed successfully', 'success');
}

// ========== FUNCIONES DE ACCIÓN ==========
function showServices() {
    appData.currentPage = 'services';
    showNotification('Loading all services...', 'info');
    // Aquí puedes cargar una página de servicios completa
}

function showOfflineServices() {
    const offlineServices = appData.services.filter(s => s.status === 'inactive');
    if (offlineServices.length === 0) {
        showNotification('No offline services found', 'warning');
    } else {
        showNotification(`${offlineServices.length} offline services found`, 'info');
    }
}

function showCustomers() {
    appData.currentPage = 'customers';
    showNotification('Loading customer list...', 'info');
}

function showReports() {
    appData.currentPage = 'reports';
    showNotification('Generating financial reports...', 'info');
}

function toggleGettingStarted(itemId) {
    const item = appData.gettingStarted.find(i => i.id === itemId);
    if (item) {
        item.completed = !item.completed;
        renderGettingStarted();
        
        const allCompleted = appData.gettingStarted.every(i => i.completed);
        if (allCompleted) {
            showNotification('🎉 Congratulations! All setup steps completed!', 'success');
        } else {
            showNotification(`${item.name} ${item.completed ? 'completed' : 'marked as pending'}`, 'info');
        }
    }
}

function toggleService(port) {
    const service = appData.services.find(s => s.port === port);
    if (service) {
        service.status = service.status === 'active' ? 'inactive' : 'active';
        renderTopServices();
        updateStats();
        
        const action = service.status === 'active' ? 'started' : 'stopped';
        showNotification(`Service ${service.name} ${action} successfully`, 'success');
    }
}

function viewServiceStats(port) {
    const service = appData.services.find(s => s.port === port);
    if (service) {
        showNotification(`Viewing statistics for: ${service.name}`, 'info');
    }
}

function editEmailTemplate(templateName) {
    const newContent = prompt(`Edit email template: ${templateName}\n\nEnter new content:`);
    if (newContent) {
        showNotification(`Email template "${templateName}" updated successfully`, 'success');
    }
}

function previewEmailTemplate(templateName) {
    showNotification(`Previewing email template: ${templateName}`, 'info');
}

// ========== SISTEMA DE NOTIFICACIONES ==========
function showNotification(message, type = 'info') {
    // Eliminar notificación anterior si existe
    const existingNotification = document.querySelector('.custom-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Crear nueva notificación
    const notification = document.createElement('div');
    notification.className = `custom-notification notification-${type}`;
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas ${icons[type] || icons.info}"></i>
            <span>${message}</span>
            <i class="fas fa-times" onclick="this.parentElement.parentElement.remove()" style="margin-left: auto; cursor: pointer; opacity: 0.7;"></i>
        </div>
    `;
    
    // Estilos de la notificación
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease;
        border-left: 4px solid ${type === 'success' ? '#4caf50' : type === 'error' ? '#f44336' : type === 'warning' ? '#ff9800' : '#2196f3'};
    `;
    
    document.body.appendChild(notification);
    
    // Auto cerrar después de 3 segundos
    setTimeout(() => {
        if (notification && notification.remove) {
            notification.remove();
        }
    }, 3000);
}

// ========== NAVEGACIÓN ==========
function setupNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            
            const pageName = this.querySelector('span')?.innerText || '';
            showNotification(`Navigating to: ${pageName}`, 'info');
            
            // Aquí puedes cargar diferentes páginas
            updatePageTitle(pageName);
        });
    });
}

function updatePageTitle(title) {
    const pageTitleElement = document.querySelector('.page-title');
    if (pageTitleElement) {
        pageTitleElement.innerText = title;
    }
}

// ========== INICIALIZACIÓN ==========
function init() {
    renderStats();
    renderGettingStarted();
    renderTopServices();
    renderEmailTemplates();
    renderRecentLogins();
    updateFooter();
    setupNavigation();
    
    // Agregar animación de entrada
    document.body.style.opacity = '0';
    setTimeout(() => {
        document.body.style.transition = 'opacity 0.5s';
        document.body.style.opacity = '1';
    }, 100);
    
    showNotification('Welcome to Media Control Panel', 'success');
    
    // Auto-refresh cada 30 segundos
    setInterval(() => {
        refreshData();
    }, 30000);
}

// Iniciar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', init);

// ========== ESTILOS ADICIONALES (inyectados por JS) ==========
const additionalStyles = document.createElement('style');
additionalStyles.textContent = `
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .custom-notification {
        font-family: 'Inter', sans-serif;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    
    .status-active {
        background: #dcfce7;
        color: #166534;
    }
    
    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .service-actions {
        margin-top: 5px;
    }
    
    .stat-card {
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .gs-item {
        transition: all 0.3s;
    }
    
    .gs-item:hover {
        background: rgba(255,255,255,0.2);
        transform: translateX(5px);
    }
    
    .service-item {
        transition: all 0.3s;
    }
    
    .service-item:hover {
        background: #f8f9fa;
        transform: translateX(5px);
    }
`;

document.head.appendChild(additionalStyles);

// ========== EXPORTAR FUNCIONES GLOBALES ==========
window.showServices = showServices;
window.showOfflineServices = showOfflineServices;
window.showCustomers = showCustomers;
window.showReports = showReports;
window.toggleGettingStarted = toggleGettingStarted;
window.toggleService = toggleService;
window.viewServiceStats = viewServiceStats;
window.editEmailTemplate = editEmailTemplate;
window.previewEmailTemplate = previewEmailTemplate;
window.refreshData = refreshData;
window.showNotification = showNotification;

// Función para cerrar sesión
function cerrarSesion() {
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        sessionStorage.clear();
        localStorage.clear();
        window.location.href = '/../../login.php';
    }
}

// Botón del top-bar
const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', cerrarSesion);
}

// Botón del sidebar
const logoutSidebarBtn = document.getElementById('logoutSidebarBtn');
if (logoutSidebarBtn) {
    logoutSidebarBtn.addEventListener('click', function(e) {
        e.preventDefault();
        cerrarSesion();
    });
}
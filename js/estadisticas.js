 // Datos para los gráficos
    const servicios = ['Internet Básico', 'Internet Premium', 'Internet Ultra', 'Internet Gamer', 'Internet Empresarial'];
    const clientesPorServicio = [450, 380, 250, 120, 50];
    const planesData = [32, 27, 18, 9, 4];

    // Gráfico de barras
    const ctxBar = document.getElementById('serviciosChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: servicios,
            datasets: [{
                label: 'Número de Clientes',
                data: clientesPorServicio,
                backgroundColor: [
                    '#0f2b3d',
                    '#1b4f6e',
                    '#2d6a8f',
                    '#3d8bb0',
                    '#4da6d1'
                ],
                borderRadius: 8,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Clientes: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Cantidad de Clientes'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Tipo de Servicio'
                    }
                }
            }
        }
    });

    // Gráfico de pie/dona
    const ctxPie = document.getElementById('planChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: servicios,
            datasets: [{
                data: planesData,
                backgroundColor: [
                    '#0f2b3d',
                    '#1b4f6e',
                    '#2d6a8f',
                    '#3d8bb0',
                    '#4da6d1'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw}%`;
                        }
                    }
                }
            }
        }
    });

    // Función para actualizar estadísticas con animación
    function actualizarEstadisticas() {
        const totalClientesElement = document.getElementById('totalClientes');
        const dineroGeneradoElement = document.getElementById('dineroGenerado');
        
        let total = 0;
        let ingresos = 0;
        
        clientesPorServicio.forEach(num => total += num);
        servicios.forEach((serv, index) => {
            if(serv === 'Internet Básico') ingresos += 22500;
            if(serv === 'Internet Premium') ingresos += 28500;
            if(serv === 'Internet Ultra') ingresos += 25000;
            if(serv === 'Internet Gamer') ingresos += 18000;
            if(serv === 'Internet Empresarial') ingresos += 12500;
        });
        
        // Animación de conteo
        let startTotal = 0;
        let startIngresos = 0;
        const duration = 1000;
        const step = 20;
        const incrementTotal = total / (duration / step);
        const incrementIngresos = ingresos / (duration / step);
        
        const timer = setInterval(() => {
            startTotal += incrementTotal;
            startIngresos += incrementIngresos;
            
            if(startTotal >= total) {
                totalClientesElement.textContent = total.toLocaleString();
                dineroGeneradoElement.textContent = '$' + ingresos.toLocaleString();
                clearInterval(timer);
            } else {
                totalClientesElement.textContent = Math.floor(startTotal).toLocaleString();
                dineroGeneradoElement.textContent = '$' + Math.floor(startIngresos).toLocaleString();
            }
        }, step);
    }
    
    // Ejecutar animación al cargar
    window.addEventListener('load', actualizarEstadisticas);
    
    // Botón para administrar clientes
    document.getElementById('adminClientesBtn').addEventListener('click', function() {
        alert('Redirigiendo al módulo de administración de clientes...');
        // window.location.href = 'admin_cliente.php';
    });
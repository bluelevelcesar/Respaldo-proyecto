(function() {
    const form = document.getElementById('loginForm');
    const emailInput = document.getElementById('emailUsuario');
    const passwordInput = document.getElementById('password');
    const messageContainer = document.getElementById('messageContainer');
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const toggleIcon = document.getElementById('toggleIcon');

    toggleBtn?.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        if (toggleIcon) toggleIcon.className = isPassword ? 'fas fa-eye' : 'fas fa-eye-slash';
    });

    const showMsg = (text, isError = true) => {
        messageContainer.innerHTML = `<div class="message-area ${isError ? 'error-message' : 'success-message'}">${text}</div>`;
    };

    const isValid = () => {
        if (!emailInput.value.trim()) {
            showMsg('Ingresa usuario o correo.');
            emailInput.focus();
            return false;
        }
        if (passwordInput.value.length < 4) {
            showMsg('Contraseña muy corta (mínimo 4 caracteres).');
            passwordInput.focus();
            return false;
        }
        return true;
    };

    const redirects = {
        'admin': '/Chango Vision/pages/admin.php',
        'cobrador': '/Chango Vision/pages/cobrador.php',
        'tecnico': '/Chango Vision/pages/tecnico.php',
        'admin_red': '/Chango Vision/pages/admin_red.php'
    };

    const roleNames = {
        'admin': 'Administrador',
        'cobrador': 'Cobrador',
        'tecnico': 'Técnico',
        'admin_red': 'Administrador de Red'
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!isValid()) return;

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn?.textContent || 'Ingresar';
        
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Verificando...';
        }
        
        showMsg('Verificando credenciales...', false);

        try {
            // ✅ Usa la misma ruta que funciona en el navegador
            const response = await fetch('/Chango%20Vision/controllers/sesion.php', {
                method: 'POST',
                body: new FormData(form)
            });
            
            const result = await response.json();
            console.log(result);

            if (!result.success) {
                showMsg(result.msg);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
                return;
            }

            const destino = redirects[result.rol];
            if (destino) {
                showMsg(`Bienvenido ${roleNames[result.rol]}`, false);
                setTimeout(() => window.location.href = destino, 1000);
            } else {
                showMsg('Rol no reconocido');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showMsg('Error de conexión con el servidor');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    });
})();
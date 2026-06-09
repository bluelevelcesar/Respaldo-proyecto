<?php
session_start();

// Verificar si está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../login.php');
    exit;
}

// Obtener datos del usuario logueado
include __DIR__ . '/../config/conexion.php';
$id_usuario = $_SESSION['id_usuario'];
$sql = "SELECT usuario, email FROM usuario WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - Chango Vision</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f2b3d 0%, #1b4f6e 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            width: 100%;
        }

        .card {
            background: white;
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #0f2b3d 0%, #1b4f6e 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }

        .card-header h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .card-header p {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .card-body {
            padding: 2rem;
        }

        .info-email {
            background: #f0f4f8;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.85rem;
            color: #1b4f6e;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #1b4f6e;
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem 2.5rem 0.8rem 1rem;
            border: 1px solid #cbdde9;
            border-radius: 16px;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s;
        }

        .form-group input:focus {
            border-color: #1b4f6e;
            box-shadow: 0 0 0 3px rgba(27, 79, 110, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .toggle-password:hover {
            color: #1b4f6e;
        }

        .btn {
            width: 100%;
            padding: 0.8rem;
            border: none;
            border-radius: 40px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0f2b3d, #1b4f6e);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-volver {
            background: #6c757d;
            color: white;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin-top: 1rem;
            width: 100%;
            padding: 0.8rem;
            border-radius: 40px;
        }

        .btn-volver:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .message {
            margin-top: 1rem;
            padding: 0.8rem;
            border-radius: 12px;
            text-align: center;
            font-size: 0.85rem;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.success {
            background: #dcfce7;
            color: #16a34a;
            border-left: 4px solid #16a34a;
        }

        .message.error {
            background: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        .message.info {
            background: #e0f2fe;
            color: #0284c7;
            border-left: 4px solid #0284c7;
        }

        .hidden {
            display: none;
        }

        .fa-spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Requisitos de contraseña */
        .password-requirements {
            font-size: 0.7rem;
            color: #666;
            margin-top: 5px;
            padding-left: 5px;
        }

        .password-requirements ul {
            list-style: none;
            padding-left: 0;
        }

        .password-requirements li {
            margin-bottom: 3px;
        }

        .password-requirements li.valid {
            color: #16a34a;
        }

        .password-requirements li.invalid {
            color: #dc2626;
        }

        .password-requirements i {
            width: 16px;
            margin-right: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-edit"></i> Cambiar Contraseña</h2>
            <p>Ingresa tu nueva contraseña</p>
        </div>
        <div class="card-body">
            <div class="info-email">
                <i class="fas fa-envelope"></i> Enviaremos un enlace de confirmación a: <strong><?php echo htmlspecialchars($usuario['email']); ?></strong>
            </div>

            <form id="formCambiar">
                <div class="form-group">
                    <label>Nueva Contraseña</label>
                    <div class="input-wrapper">
                        <input type="password" id="pass_nueva" name="password_nueva" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('pass_nueva', this)">
                            <i class="far fa-eye-slash"></i>
                        </button>
                    </div>
                    <div class="password-requirements" id="passRequirements">
                        <ul>
                            <li id="req-length"><i class="far fa-circle"></i> Mínimo 4 caracteres</li>
                            <li id="req-upper"><i class="far fa-circle"></i> Al menos una mayúscula</li>
                            <li id="req-number"><i class="far fa-circle"></i> Al menos un número</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label>Repetir Nueva Contraseña</label>
                    <div class="input-wrapper">
                        <input type="password" id="pass_confirmar" name="password_confirmar" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('pass_confirmar', this)">
                            <i class="far fa-eye-slash"></i>
                        </button>
                    </div>
                    <div class="password-requirements" id="confirmMatch" style="display: none;">
                        <ul>
                            <li id="req-match"><i class="far fa-circle"></i> Las contraseñas coinciden</li>
                        </ul>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="btnCambiar">
                    <i class="fas fa-paper-plane"></i> Enviar enlace de confirmación
                </button>
            </form>
            <div id="mensajeCambiar" class="message hidden"></div>
            <a href="admin.php" class="btn-volver">← Volver al Panel</a>
        </div>
    </div>
</div>

<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }

    // Validación de contraseña en tiempo real
    const passInput = document.getElementById('pass_nueva');
    const confirmInput = document.getElementById('pass_confirmar');
    const reqLength = document.getElementById('req-length');
    const reqUpper = document.getElementById('req-upper');
    const reqNumber = document.getElementById('req-number');
    const reqMatch = document.getElementById('req-match');
    const confirmMatchDiv = document.getElementById('confirmMatch');

    function validatePassword() {
        const password = passInput.value;
        
        // Validar longitud
        if (password.length >= 4) {
            reqLength.innerHTML = '<i class="fas fa-check-circle"></i> Mínimo 4 caracteres';
            reqLength.classList.add('valid');
            reqLength.classList.remove('invalid');
        } else {
            reqLength.innerHTML = '<i class="far fa-circle"></i> Mínimo 4 caracteres';
            reqLength.classList.remove('valid');
        }
        
        // Validar mayúscula
        if (/[A-Z]/.test(password)) {
            reqUpper.innerHTML = '<i class="fas fa-check-circle"></i> Al menos una mayúscula';
            reqUpper.classList.add('valid');
            reqUpper.classList.remove('invalid');
        } else {
            reqUpper.innerHTML = '<i class="far fa-circle"></i> Al menos una mayúscula';
            reqUpper.classList.remove('valid');
        }
        
        // Validar número
        if (/[0-9]/.test(password)) {
            reqNumber.innerHTML = '<i class="fas fa-check-circle"></i> Al menos un número';
            reqNumber.classList.add('valid');
            reqNumber.classList.remove('invalid');
        } else {
            reqNumber.innerHTML = '<i class="far fa-circle"></i> Al menos un número';
            reqNumber.classList.remove('valid');
        }
        
        // Mostrar/ocultar validación de coincidencia
        if (password.length > 0) {
            confirmMatchDiv.style.display = 'block';
            validateMatch();
        } else {
            confirmMatchDiv.style.display = 'none';
        }
    }
    
    function validateMatch() {
        const password = passInput.value;
        const confirm = confirmInput.value;
        
        if (confirm.length === 0) {
            reqMatch.innerHTML = '<i class="far fa-circle"></i> Las contraseñas coinciden';
            reqMatch.classList.remove('valid');
            return;
        }
        
        if (password === confirm) {
            reqMatch.innerHTML = '<i class="fas fa-check-circle"></i> Las contraseñas coinciden';
            reqMatch.classList.add('valid');
            reqMatch.classList.remove('invalid');
        } else {
            reqMatch.innerHTML = '<i class="fas fa-times-circle"></i> Las contraseñas no coinciden';
            reqMatch.classList.remove('valid');
        }
    }
    
    passInput.addEventListener('input', validatePassword);
    confirmInput.addEventListener('input', validateMatch);

    const formCambiar = document.getElementById('formCambiar');
    const mensajeCambiar = document.getElementById('mensajeCambiar');
    const btnCambiar = document.getElementById('btnCambiar');

    formCambiar.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const passNueva = document.getElementById('pass_nueva').value;
        const passConfirmar = document.getElementById('pass_confirmar').value;
        
        // Validaciones mejoradas
        if (passNueva.length < 4) {
            mensajeCambiar.innerHTML = '❌ La contraseña debe tener al menos 4 caracteres';
            mensajeCambiar.classList.remove('hidden', 'success', 'info');
            mensajeCambiar.classList.add('error');
            return;
        }
        
        if (!/[A-Z]/.test(passNueva)) {
            mensajeCambiar.innerHTML = '❌ La contraseña debe contener al menos una letra mayúscula';
            mensajeCambiar.classList.remove('hidden', 'success', 'info');
            mensajeCambiar.classList.add('error');
            return;
        }
        
        if (!/[0-9]/.test(passNueva)) {
            mensajeCambiar.innerHTML = '❌ La contraseña debe contener al menos un número';
            mensajeCambiar.classList.remove('hidden', 'success', 'info');
            mensajeCambiar.classList.add('error');
            return;
        }
        
        if (passNueva !== passConfirmar) {
            mensajeCambiar.innerHTML = '❌ Las contraseñas no coinciden';
            mensajeCambiar.classList.remove('hidden', 'success', 'info');
            mensajeCambiar.classList.add('error');
            return;
        }
        
        btnCambiar.disabled = true;
        btnCambiar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        mensajeCambiar.classList.remove('hidden', 'success', 'error');
        mensajeCambiar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando enlace a tu correo...';
        mensajeCambiar.classList.add('info');
        
        try {
            const response = await fetch('../controllers/enviar_recuperacion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'email=' + encodeURIComponent('<?php echo $usuario['email']; ?>') + '&cambio_directo=1&nueva_password=' + encodeURIComponent(passNueva)
            });
            
            const result = await response.text();
            console.log('Respuesta:', result);
            
            if (result === 'enviado') {
                mensajeCambiar.innerHTML = '✅ ¡Enlace enviado! Revisa tu correo electrónico. Haz clic en el enlace para completar el cambio de contraseña.';
                mensajeCambiar.classList.remove('error', 'info');
                mensajeCambiar.classList.add('success');
                document.getElementById('pass_nueva').value = '';
                document.getElementById('pass_confirmar').value = '';
                confirmMatchDiv.style.display = 'none';
            } else if (result === 'no_encontrado') {
                mensajeCambiar.innerHTML = '❌ No se encontró tu correo en el sistema. Contacta al administrador.';
                mensajeCambiar.classList.remove('success', 'info');
                mensajeCambiar.classList.add('error');
            } else if (result.startsWith('error:')) {
                mensajeCambiar.innerHTML = '❌ Error del servidor: ' + result.substring(6);
                mensajeCambiar.classList.remove('success', 'info');
                mensajeCambiar.classList.add('error');
            } else {
                mensajeCambiar.innerHTML = '❌ Error: ' + result;
                mensajeCambiar.classList.remove('success', 'info');
                mensajeCambiar.classList.add('error');
            }
        } catch (error) {
            console.error('Error:', error);
            mensajeCambiar.innerHTML = '❌ Error de conexión con el servidor. Intenta nuevamente.';
            mensajeCambiar.classList.remove('success', 'info');
            mensajeCambiar.classList.add('error');
        } finally {
            btnCambiar.disabled = false;
            btnCambiar.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar enlace de confirmación';
            
            // Ocultar mensaje después de 5 segundos si es éxito
            if (mensajeCambiar.classList.contains('success')) {
                setTimeout(() => {
                    mensajeCambiar.classList.add('hidden');
                }, 5000);
            }
        }
    });
</script>

</body>
</html>
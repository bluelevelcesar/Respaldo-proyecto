<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Inicio de Sesión | Chango Vision</title>
  <link rel="stylesheet" href="/Chango Vision/css/login.css"> <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="login-container">
  <div class="login-header">
    <div class="icon-circle"><i class="fas fa-globe"></i></div>
    <h1>Conecta con tu red</h1>
    <p class="subtitulo">Portal de Servicios Chango Vision</p>
  </div>

  <div class="login-container">

</div>

  <div class="login-form">
    <form id="loginForm" novalidate>
      
      <div class="input-group">
        <label for="emailUsuario">Usuario o Correo</label>
        <div style="position: relative;">
          <input name="email" type="text" id="emailUsuario" class="input-field" placeholder="ejemplo@correo.com" required>
          <i class="fas fa-user-circle input-icon"></i>
        </div>
      </div>

      <div class="input-group">
        <label for="password">Contraseña</label>
        <div class="password-wrapper" style="position: relative;">
          <input name="password" type="password" id="password" class="input-field" placeholder="••••••••" required>
          <i class="fas fa-lock input-icon"></i>
          <button type="button" class="toggle-password" id="togglePasswordBtn" 
                  style="position: absolute; right: 1rem; bottom: 0.9rem; background: none; border: none; cursor: pointer; color: #6b8da8;">
            <i class="far fa-eye-slash" id="toggleIcon"></i>
          </button>
        </div>
      </div>

      <div style="text-align: center; margin-bottom: 1.5rem;">
        <a href="/Chango Vision/pages/envio_link.php" id="forgotPasswordLink" style="color: #2c7cb6; font-size: 0.9rem; text-decoration: none; font-weight: 600;">
          ¿Olvidaste tu contraseña?
        </a>
      </div>

      <button type="submit" class="login-btn" id="loginButton">
        <span>Ingresar ahora</span>
        
      </button>

      <div id="messageContainer"></div>
    </form>
  </div>
</div>

<script src="/Chango Vision/js/login.js"></script>
  


</body>
</html>
        <?php
        // funciones/login_check.php
        session_start();
        header('Content-Type: application/json');

        include __DIR__ . '/../config/conexion.php';
        include __DIR__ . '/funcion_log.php';

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            echo json_encode(["success" => false, "msg" => "Complete todos los campos"]);
            exit;
        }

        $log = new SistemaLog($conexion);

        $sql = "SELECT * FROM usuario WHERE email = ? OR usuario = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            // 📝 LOG: Usuario no encontrado
            $log->loginFallido($email, "Usuario no encontrado en la base de datos");
            echo json_encode(["success" => false, "msg" => "Usuario no encontrado"]);
            exit;
        }

        $usuario = $resultado->fetch_assoc();

        if (!password_verify($password, $usuario['contrasena'])) {
            // 📝 LOG: Contraseña incorrecta
            $log->loginFallido($email, "Contraseña incorrecta para usuario: " . $usuario['usuario']);
            echo json_encode(["success" => false, "msg" => "Contraseña incorrecta"]);
            exit;
        }

        // Guardar sesión
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['id_rol'] = $usuario['id_rol'];
        $_SESSION['usuario'] = $usuario['usuario'];

        // Determinar rol
        $rol = "otro";
        if ($usuario['id_rol'] == 1) $rol = "admin";
        elseif ($usuario['id_rol'] == 2) $rol = "cliente";
        elseif ($usuario['id_rol'] == 3) $rol = "tecnico";

        // 📝 LOG: Login exitoso
        $log->loginExitoso($usuario['id_usuario'], $usuario['usuario'], $rol);

        echo json_encode(["success" => true, "rol" => $rol, "usuario" => $usuario['usuario']]);
        ?>
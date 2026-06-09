<?php

header('Content-Type: text/plain');

require_once __DIR__ . '/../config/conexion.php';

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Método no permitido');
}

$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    exit('no_encontrado');
}

// =====================================================
// 📌 CÓDIGO AGREGADO PARA CAMBIO DIRECTO DE CONTRASEÑA
// =====================================================
$cambio_directo = isset($_POST['cambio_directo']) && $_POST['cambio_directo'] == '1';
$nueva_password = $_POST['nueva_password'] ?? '';

$mail = new PHPMailer(true);

try {

    // BUSCAR USUARIO
    $sql = "SELECT id_usuario, usuario
            FROM usuario
            WHERE email = ?";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        exit('error: ' . $conexion->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    if (!$usuario) {
        exit('no_encontrado');
    }

    // GENERAR TOKEN
    $token = bin2hex(random_bytes(32));
    $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // GUARDAR TOKEN
    $sql = "UPDATE usuario
            SET reset_token = ?, reset_expira = ?
            WHERE id_usuario = ?";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        exit('error: ' . $conexion->error);
    }

    $stmt->bind_param(
        "ssi",
        $token,
        $expiracion,
        $usuario['id_usuario']
    );

    $stmt->execute();

    // =====================================================
    // 📌 GUARDAR CONTRASEÑA TEMPORAL SI ES CAMBIO DIRECTO
    // =====================================================
    if ($cambio_directo && !empty($nueva_password) && strlen($nueva_password) >= 4) {
        // Verificar si existe la columna nueva_password_temp
        $check_col = $conexion->query("SHOW COLUMNS FROM usuario LIKE 'nueva_password_temp'");
        if ($check_col->num_rows == 0) {
            $conexion->query("ALTER TABLE usuario ADD COLUMN nueva_password_temp VARCHAR(255) NULL");
        }
        
        $sql_temp = "UPDATE usuario SET nueva_password_temp = ? WHERE id_usuario = ?";
        $stmt_temp = $conexion->prepare($sql_temp);
        $stmt_temp->bind_param("si", $nueva_password, $usuario['id_usuario']);
        $stmt_temp->execute();
        $stmt_temp->close();
    }

    // CREAR ENLACE
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? "https://"
        : "http://";

    $host = $_SERVER['HTTP_HOST'];

    // Si es cambio directo, usar confirmar_cambio.php en lugar de reset_contra.php
    if ($cambio_directo) {
        $enlace = $protocol . $host . '/Chango Vision/pages/confirmar_cambio.php?token=' . $token;
    } else {
        $enlace = $protocol . $host . '/Chango Vision/pages/reset_contra.php?token=' . $token;
    }

    // CONFIGURAR SMTP
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
    $mail->isSMTP();

    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    // CORREO DEL SISTEMA
    $mail->Username = 'chagovision.system@gmail.com';

    // CONTRASEÑA DE APLICACIÓN DE GOOGLE
    $mail->Password = 'jonqhfkqidoblbyx';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    // REMITENTE
    $mail->setFrom(
        'chagovision.system@gmail.com',
        'Chago Vision'
    );

    // DESTINATARIO
    $mail->addAddress(
        $email,
        $usuario['usuario']
    );

    // CONTENIDO
    $mail->isHTML(true);

    // Cambiar título y mensaje según el tipo
    if ($cambio_directo) {
        $mail->Subject = 'Confirmación de cambio de contraseña - Chago Vision';
        $boton_texto = 'Confirmar cambio de contraseña';
        $titulo = 'Confirmación de cambio de contraseña';
        $mensaje_intro = "Hemos recibido una solicitud para <strong>cambiar tu contraseña</strong>.";
        $instruccion = "Haz clic en el siguiente botón para confirmar el cambio:";
    } else {
        $mail->Subject = 'Recuperación de contraseña - Chago Vision';
        $boton_texto = 'Restablecer contraseña';
        $titulo = 'Recuperación de contraseña';
        $mensaje_intro = "Recibimos una solicitud para restablecer tu contraseña en <strong>Chago Vision</strong>.";
        $instruccion = "Haz clic en el siguiente botón para crear una nueva contraseña:";
    }

    $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;'>

            <div style='max-width:600px; margin:auto; background:#ffffff; padding:25px; border-radius:12px;'>

                <h2 style='color:#1b4f6e;'>{$titulo}</h2>

                <p>Hola <strong>{$usuario['usuario']}</strong>,</p>

                <p>{$mensaje_intro}</p>

                <p>{$instruccion}</p>

                <p style='text-align:center;'>
                    <a href='$enlace'
                       style='
                            background:#1b4f6e;
                            color:white;
                            padding:12px 20px;
                            text-decoration:none;
                            border-radius:6px;
                            display:inline-block;
                       '>
                        {$boton_texto}
                    </a>
                </p>

                <p>O copia este enlace en tu navegador:</p>

                <p>
                    <a href='$enlace'>$enlace</a>
                </p>

                <p><strong>Este enlace expirará en 1 hora.</strong></p>

                <p>Si no solicitaste este cambio, ignorá este mensaje.</p>

            </div>

        </body>
        </html>
    ";

    $mail->AltBody = "
{$titulo} - Chago Vision

Hola {$usuario['usuario']}

{$mensaje_intro}

Link:
$enlace

Este enlace expirará en 1 hora.

Si no solicitaste este cambio, ignorá este mensaje.
    ";

    $mail->send();

    echo 'enviado';

} catch (Exception $e) {

    echo 'error: ' . $mail->ErrorInfo;

} catch (Throwable $e) {

    echo 'error: ' . $e->getMessage();
}
?>
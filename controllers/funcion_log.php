<?php
// funciones/funcion_log.php

class SistemaLog {

    private $conexion;
    private $tabla = "log";

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // ==================================================
    // REGISTRO GENERAL (PRIVADO)
    // ==================================================
    private function registrar($id_admin, $nombre_admin, $accion, $afectado, $tipo = 'info') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $paginaActual = $_SERVER['REQUEST_URI'] ?? '';

        $sql = "INSERT INTO log (id_usuario, usuario, accion, descripcion, tipo, ip_address, user_agent, pagina) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $id_admin_null = empty($id_admin) ? null : $id_admin;

        $stmt->bind_param(
            "isssssss",
            $id_admin_null,
            $nombre_admin,
            $accion,
            $afectado,
            $tipo,
            $ip,
            $userAgent,
            $paginaActual
        );

        return $stmt->execute();
    }

    
    // ==================================================
    // METODO PARA CERRAR SESION
    // ==================================================
    public function cerrarSesion($id_admin, $nombre_admin) {
        $afectado = "El usuario '" . $nombre_admin . "' cerró su sesión";
        return $this->registrar(
            $id_admin,
            $nombre_admin,
            "Cierre de sesión",
            $afectado,
            "info"
        );
    }


    // ==================================================
    // METODOS PARA CLIENTES
    // ==================================================
    
    public function registrarCliente($id_admin, $nombre_admin, $cliente_nombre) {
        $afectado = "Se registró un nuevo cliente: " . $cliente_nombre;
        return $this->registrar(
            $id_admin,
            $nombre_admin,
            "Registro de cliente",
            $afectado,
            "success"
        );
    }
    
    public function editarCliente($id_admin, $nombre_admin, $cliente_id, $cliente_nombre, $datos_viejos = [], $datos_nuevos = []) {
        $cambios = [];
        
        if (!empty($datos_viejos) && !empty($datos_nuevos)) {
            $campos_a_mostrar = ['nombre', 'dni', 'telefono', 'direccion'];
            
            foreach ($campos_a_mostrar as $campo) {
                $valor_viejo = $datos_viejos[$campo] ?? '';
                $valor_nuevo = $datos_nuevos[$campo] ?? '';
                if ($valor_viejo != $valor_nuevo && !empty($valor_nuevo)) {
                    $nombre_campo = ucfirst($campo);
                    $cambios[] = $nombre_campo . " de '" . $valor_viejo . "' a '" . $valor_nuevo . "'";
                }
            }
        }
        
        if (count($cambios) > 0) {
            $afectado = "Se modificó el cliente '" . $cliente_nombre . "': " . implode(", ", $cambios);
        } else {
            $afectado = "Se editó la información del cliente: " . $cliente_nombre;
        }
        
        return $this->registrar(
            $id_admin,
            $nombre_admin,
            "Edición de cliente",
            $afectado,
            "info"
        );
    }
    
    public function eliminarCliente($id_admin, $nombre_admin, $cliente_id, $cliente_nombre) {
        $afectado = "Se eliminó al cliente '" . $cliente_nombre . "' del sistema";
        return $this->registrar(
            $id_admin,
            $nombre_admin,
            "Eliminación de cliente",
            $afectado,
            "warning"
        );
    }
    
    public function cambiarEstadoCliente($id_admin, $nombre_admin, $cliente_id, $cliente_nombre, $estado_nuevo) {
        $estadoTexto = $estado_nuevo === 'Activo' ? "activado" : "desactivado";
        $afectado = "Se " . $estadoTexto . " la cuenta del cliente '" . $cliente_nombre . "'";
        return $this->registrar(
            $id_admin,
            $nombre_admin,
            "Cambio de estado de cliente",
            $afectado,
            "info"
        );
    }

    // ==================================================
    // METODOS PARA USUARIOS (trabajadores)
    // ==================================================
    
    public function crearUsuario($id_admin, $nombre_admin, $usuario_nombre, $rol) {
        $afectado = "Se registró un nuevo usuario: " . $usuario_nombre . " (" . $rol . ")";
        return $this->registrar(
            $id_admin,
            $nombre_admin,
            "Registro de usuario",
            $afectado,
            "success"
        );
    }
    
    public function editarUsuario($id_admin, $nombre_admin, $usuario_id, $usuario_nombre, $datos_viejos = [], $datos_nuevos = []) {
        $cambios = [];
        
        if (!empty($datos_viejos) && !empty($datos_nuevos)) {
            foreach ($datos_nuevos as $campo => $valor_nuevo) {
                $valor_viejo = $datos_viejos[$campo] ?? '';
                if ($valor_viejo != $valor_nuevo && !empty($valor_nuevo)) {
                    $nombre_campo = ucfirst($campo);
                    $cambios[] = $nombre_campo . " de '" . $valor_viejo . "' a '" . $valor_nuevo . "'";
                }
            }
        }
        
        if (count($cambios) > 0) {
            $afectado = "Se modificó el usuario '" . $usuario_nombre . "': " . implode(", ", $cambios);
        } else {
            $afectado = "Se editó la información del usuario: " . $usuario_nombre;
        }
        
        return $this->registrar(
            $id_admin,
            $nombre_admin,
            "Edición de usuario",
            $afectado,
            "info"
        );
    }
    
    public function eliminarUsuario($id_admin, $nombre_admin, $usuario_id, $usuario_nombre) {
        $afectado = "Se eliminó al usuario '" . $usuario_nombre . "' del sistema";
        return $this->registrar(
            $id_admin,
            $nombre_admin,
            "Eliminación de usuario",
            $afectado,
            "warning"
        );
    }

    // ==================================================
    // OBTENER LOGS
    // ==================================================
    public function obtenerLogsRecientes($limite = 50) {
        $sql = "SELECT 
                    usuario as administrador, 
                    accion, 
                    descripcion as afectado, 
                    DATE(fecha_registro) as fecha, 
                    TIME(fecha_registro) as hora
                FROM log
                WHERE accion IN ('Registro de cliente', 'Edición de cliente', 'Eliminación de cliente', 'Cambio de estado de cliente',
                                 'Registro de usuario', 'Edición de usuario', 'Eliminación de usuario')
                ORDER BY fecha_registro DESC
                LIMIT ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function getEstadisticas() {
        $stats = [];
        $result = $this->conexion->query("SELECT COUNT(*) as total FROM log");
        $stats['total'] = $result->fetch_assoc()['total'];
        
        $result = $this->conexion->query("SELECT COUNT(*) as total FROM log WHERE DATE(fecha_registro) = CURDATE()");
        $stats['hoy'] = $result->fetch_assoc()['total'];
        
        return $stats;
    }
}
?>
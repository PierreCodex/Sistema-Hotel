<?php
class Usuario extends Conectar
{

    /**
     * Busca un usuario por correo y contraseña
     * @param string $correo
     * @param string $password
     * @return array|false Datos del usuario o false si no se encuentra
     */
    public function findUserByCredentials($correo, $password)
    {
        $conectar = parent::conexion();
        parent::set_names();

        // Primero obtenemos el usuario por correo
        $sql = "SELECT * FROM usuario WHERE Correo=? AND Estado=1";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $correo);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user) {
            // Verificar si la contraseña está hasheada (comienza con $2y$)
            if (strpos($user['Pass'], '$2y$') === 0) {
                // Contraseña hasheada - usar password_verify
                if (password_verify($password, $user['Pass'])) {
                    return $user;
                }
            } else {
                // Contraseña en texto plano - comparación directa
                if ($password === $user['Pass']) {
                    // Actualizar la contraseña a formato hasheado
                    $this->updatePasswordToHashed($user['IdUsuario'], $password);
                    return $user;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Actualiza una contraseña de texto plano a formato hasheado
     * @param int $userId
     * @param string $plainPassword
     */
    private function updatePasswordToHashed($userId, $plainPassword): void
    {
        $conectar = parent::conexion();
        parent::set_names();
        
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
        
        $sql = "UPDATE usuario SET Pass = ? WHERE IdUsuario = ?";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $hashedPassword);
        $stmt->bindValue(2, $userId);
        $stmt->execute();
    }

    /* Listar todos los usuarios activos excluyendo al usuario logueado */

    public function get_usuario($current_user_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_USUARIO_03(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $current_user_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Obtener usuario por ID */
    public function get_usuario_x_usu_id($usu_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_USUARIO_02(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Eliminar usuario (cambio de estado) */
    public function delete_usuario($usu_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_D_USUARIO_01(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Insertar nuevo usuario */
    public function insert_usuario($usu_nom, $usu_ape, $usu_dni, $usu_correo, $usu_pass, $rol_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        
        // Hashear la contraseña antes de guardarla
        $hashed_password = password_hash($usu_pass, PASSWORD_DEFAULT);
        
        $sql = "CALL SP_I_USUARIO_01(?,?,?,?,?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_nom);
        $sql->bindValue(2, $usu_ape);
        $sql->bindValue(3, $usu_dni);
        $sql->bindValue(4, $usu_correo);
        $sql->bindValue(5, $hashed_password);
        $sql->bindValue(6, $rol_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Actualizar usuario */
    public function update_usuario($usu_id, $usu_nom, $usu_ape, $usu_dni, $usu_correo, $usu_pass, $rol_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        
        // Hashear la contraseña antes de guardarla
        $hashed_password = password_hash($usu_pass, PASSWORD_DEFAULT);
        
        $sql = "CALL SP_U_USUARIO_01(?,?,?,?,?,?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_id);
        $sql->bindValue(2, $usu_nom);
        $sql->bindValue(3, $usu_ape);
        $sql->bindValue(4, $usu_dni);
        $sql->bindValue(5, $usu_correo);
        $sql->bindValue(6, $hashed_password);
        $sql->bindValue(7, $rol_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Actualizar contraseña */
    public function update_usuario_pass($usu_id, $usu_pass)
    {
        $conectar = parent::conexion();
        parent::set_names();
        
        // Hashear la contraseña antes de guardarla
        $hashed_password = password_hash($usu_pass, PASSWORD_DEFAULT);
        
        $sql = "CALL SP_U_USUARIO_PASS_01(?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_id);
        $sql->bindValue(2, $hashed_password);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    // Validaciones de duplicados
    public function existe_usuario_correo($usu_correo){
        $conectar=parent::Conexion();
        $sql="SELECT IdUsuario FROM usuario WHERE Correo = ? AND Estado=1";
        $query=$conectar->prepare($sql);
        $query->bindValue(1,$usu_correo);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Validar si el usuario existe pero está inactivo */
    public function existe_usuario_correo_inactivo($usu_correo){
        $conectar=parent::Conexion();
        parent::set_names();
        $sql="CALL SP_L_USUARIO_CORREO_INACTIVO_01(?)";
        $query=$conectar->prepare($sql);
        $query->bindValue(1,$usu_correo);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Validar si el DNI ya existe en la base de datos */
    public function existe_usuario_dni($usu_dni){
        $conectar=parent::Conexion();
        $sql="CALL SP_L_USUARIO_BY_DNI_01(?)";
        $query=$conectar->prepare($sql);
        $query->bindValue(1,$usu_dni);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    /* Cambiar estado del usuario (activar/desactivar) */
    public function cambiar_estado_usuario($usu_id, $nuevo_estado)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_CAMBIAR_ESTADO_USUARIO_01(?, ?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_id);    
        $sql->bindValue(2, $nuevo_estado);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Genera un token de sesión único
     * @return string Token aleatorio de 64 caracteres
     */
    public function generar_session_token() {
        return bin2hex(random_bytes(32)); // Genera un token de 64 caracteres hexadecimales
    }
    
    /**
     * Actualiza el token de sesión del usuario en la base de datos
     * @param int $usu_id ID del usuario
     * @param string|null $token Token de sesión a guardar, o NULL para limpiar
     * @return bool True si se actualizó correctamente
     */
    public function actualizar_session_token($usu_id, $token) {
        $conectar = parent::conexion();
        parent::set_names();
        
        // Si el token es NULL, limpiar ambos campos
        if ($token === NULL) {
            $sql = "UPDATE usuario SET session_token = NULL, session_created_at = NULL WHERE IdUsuario = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $usu_id);
        } else {
            // Si hay token, actualizar con timestamp
            $sql = "UPDATE usuario SET session_token = ?, session_created_at = NOW() WHERE IdUsuario = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $token);
            $stmt->bindValue(2, $usu_id);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Verifica si el token de sesión del usuario coincide con el almacenado en BD
     * @param int $usu_id ID del usuario
     * @param string $token Token de sesión a verificar
     * @return bool True si el token coincide, False si no
     */
    public function verificar_session_token($usu_id, $token) {
        $conectar = parent::conexion();
        parent::set_names();
        
        $sql = "SELECT session_token FROM usuario WHERE IdUsuario = ? AND Estado = 1";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $usu_id);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado && isset($resultado['session_token'])) {
            // Comparar el token de la sesión con el de la BD
            return $resultado['session_token'] === $token;
        }
        
        return false;
    }
    
    /**
     * Verifica si el usuario tiene un token de sesión anterior (sesión activa en otro dispositivo)
     * @param int $usu_id ID del usuario
     * @return bool True si existe un token anterior, False si no
     */
    public function tiene_session_token_anterior($usu_id) {
        $conectar = parent::conexion();
        parent::set_names();
        
        $sql = "SELECT session_token FROM usuario WHERE IdUsuario = ? AND Estado = 1";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $usu_id);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Retorna true si existe un token (no vacío y no null)
        return ($resultado && isset($resultado['session_token']) && !empty($resultado['session_token']));
    }
    
}

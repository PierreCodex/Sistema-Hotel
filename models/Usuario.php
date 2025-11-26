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
    private function updatePasswordToHashed($userId, $plainPassword)
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
    
}

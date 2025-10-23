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

        $sql = "SELECT * FROM usuario WHERE Correo=? AND Pass=? AND Estado=1";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $correo);
        $stmt->bindValue(2, $password);
        $stmt->execute();

        return $stmt->fetch();
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
        $sql = "CALL SP_I_USUARIO_01(?,?,?,?,?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_nom);
        $sql->bindValue(2, $usu_ape);
        $sql->bindValue(3, $usu_dni);
        $sql->bindValue(4, $usu_correo);
        $sql->bindValue(5, $usu_pass);
        $sql->bindValue(6, $rol_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Actualizar usuario */
    public function update_usuario($usu_id, $usu_nom, $usu_ape, $usu_dni, $usu_correo, $usu_pass, $rol_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_U_USUARIO_01(?,?,?,?,?,?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_id);
        $sql->bindValue(2, $usu_nom);
        $sql->bindValue(3, $usu_ape);
        $sql->bindValue(4, $usu_dni);
        $sql->bindValue(5, $usu_correo);
        $sql->bindValue(6, $usu_pass);
        $sql->bindValue(7, $rol_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Actualizar contraseña */
    public function update_usuario_pass($usu_id, $usu_pass)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_U_USUARIO_PASS_01(?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_id);
        $sql->bindValue(2, $usu_pass);
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
}

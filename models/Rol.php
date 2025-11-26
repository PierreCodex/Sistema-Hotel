<?php
class Rol extends Conectar
{

    /* Listar todos los roles (activos e inactivos) */
    public function get_rol()
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_ROL_01()";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }
 
    /* Listar todos los roles (SOLO ACTIVOS) */
    public function get_rol_activo()
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_ROL_03()";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }



    /* Obtener rol por ID */
    public function get_rol_x_rol_id($rol_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_ROL_02(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $rol_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Eliminar rol (cambio de estado) */
    public function delete_rol($rol_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_D_ROL_01(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $rol_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Insertar nuevo rol */
    public function insert_rol($rol_nom)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_I_ROL_01(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $rol_nom);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Actualizar rol */
    public function update_rol($rol_id, $rol_nom)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_U_ROL_01(?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $rol_id);
        $sql->bindValue(2, $rol_nom);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Verificar si existe un rol con el mismo nombre */
    public function verificar_rol_existente($rol_nom, $rol_id = null)
    {
        $conectar = parent::conexion();
        parent::set_names();

        if ($rol_id == null) {
            // Para inserción - verificar si existe el nombre
            $sql = "SELECT COUNT(*) as total FROM rol WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(?)) AND Estado = 1";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $rol_nom);
        } else {
            // Para actualización - verificar si existe el nombre en otro registro
            $sql = "SELECT COUNT(*) as total FROM rol WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(?)) AND IdRol != ? AND Estado = 1";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $rol_nom);
            $sql->bindValue(2, $rol_id);
        }

        $sql->execute();
        $resultado = $sql->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }


// agregue metodo para validar la longitud del nombre del rol
    public function validarLongitud($nombre)
    {
        $longitud = strlen(trim($nombre));
        return $longitud > 0 && $longitud <=50;
    }

    /* Validar acceso de rol para un usuario */
    public function validar_acceso_rol($usu_id, $modulo)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_MENU_03(?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_id);
        $sql->bindValue(2, $modulo);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }


    /* Cambiar estado del rol (activar/desactivar) */
    public function cambiar_estado_rol($rol_id, $nuevo_estado)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_CAMBIAR_ESTADO_ROL_01(?, ?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $rol_id);
        $sql->bindValue(2, $nuevo_estado);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

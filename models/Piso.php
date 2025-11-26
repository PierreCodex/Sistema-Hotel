<?php
class Piso extends Conectar{

    /* Listar todos los pisos activos e inactivos */
    public function get_piso(){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_PISO_01()";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Listar todos los pisos activos */

    public function get_piso_activo(){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_PISO_03()";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Obtener piso por ID */
    public function get_piso_x_piso_id($piso_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_PISO_02(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $piso_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Eliminar piso () */
    public function delete_piso($piso_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_D_PISO_01(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $piso_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Insertar nuevo piso */
    public function insert_piso($piso_nom){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_I_PISO_01(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $piso_nom);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Actualizar piso */
    public function update_piso($piso_id, $piso_nom){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_U_PISO_01(?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $piso_id);
        $sql->bindValue(2, $piso_nom);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Verificar si existe un piso con el mismo nombre */
    public function verificar_piso_existente($piso_nom, $piso_id = null){
        $conectar = parent::conexion();
        parent::set_names();
        
        if($piso_id == null){
            // Para inserción - verificar si existe el nombre (solo activos)
            $sql = "SELECT COUNT(*) as total FROM piso WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(?)) AND Estado = 1";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $piso_nom);
        } else {
            // Para actualización - verificar si existe el nombre en otro registro (solo activos)
            $sql = "SELECT COUNT(*) as total FROM piso WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(?)) AND IdPiso != ? AND Estado = 1";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $piso_nom);
            $sql->bindValue(2, $piso_id);
        }
        
        $sql->execute();
        $resultado = $sql->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

     /* Cambiar estado del Piso (activar/desactivar) */
     public function cambiar_estado_piso($piso_id, $nuevo_estado){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_CAMBIAR_ESTADO_PISO_01(?, ?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $piso_id);
        $sql->bindValue(2, $nuevo_estado);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>
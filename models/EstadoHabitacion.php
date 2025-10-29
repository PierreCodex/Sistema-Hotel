<?php
class EstadoHabitacion extends Conectar
{
    // Método para listar todos los estados de habitación
    public function get_estado_habitacion()
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_ESTADO_HABITACION_01()";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para listar estados de habitación activos
    public function get_estado_habitacion_activos()
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_ESTADO_HABITACION_03()";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para obtener estado de habitación por ID
    public function get_estado_habitacion_x_id($est_hab_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_ESTADO_HABITACION_02(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $est_hab_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para eliminar estado de habitación (cambiar estado a 0)
    public function delete_estado_habitacion($est_hab_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_D_ESTADO_HABITACION_01(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $est_hab_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para insertar nuevo estado de habitación
    public function insert_estado_habitacion($est_hab_nom)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_I_ESTADO_HABITACION_01(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $est_hab_nom);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para actualizar estado de habitación
    public function update_estado_habitacion($est_hab_id, $est_hab_nom)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_U_ESTADO_HABITACION_01(?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $est_hab_id);
        $sql->bindValue(2, $est_hab_nom);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para verificar si existe estado de habitación por nombre

    // Método para verificar si existe estado de habitación por nombre excluyendo un ID específico
    public function verificar_estado_habitacion_existe($est_hab_nom, $est_hab_id = null){
        $conectar = parent::conexion();
        parent::set_names();
        
        if($est_hab_id == null){
            // Para inserción - verificar si existe el nombre (solo activos)
            $sql = "SELECT COUNT(*) as total FROM estado_habitacion WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(?)) AND Estado = 1";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $est_hab_nom);   
        } else {
            // Para actualización - verificar si existe el nombre en otro registro (solo activos)
            $sql = "SELECT COUNT(*) as total FROM estado_habitacion WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(?)) AND IdEstadoHabitacion != ? AND Estado = 1";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $est_hab_nom);       
            $sql->bindValue(2, $est_hab_id);
        }
        
        $sql->execute();
        $resultado = $sql->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

    // Método para cambiar estado del estado de habitación
    public function cambiar_estado_estado_habitacion($est_hab_id, $nuevo_estado)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_CAMBIAR_ESTADO_ESTADO_HABITACION_01(?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $est_hab_id);
        $sql->bindValue(2, $nuevo_estado);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
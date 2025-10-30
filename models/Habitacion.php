<?php
class Habitacion extends Conectar{

    /* Listar todas las habitaciones activas e inactivas */
    public function get_habitacion(){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_HABITACION_01()";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Listar todas las habitaciones activas */
    public function get_habitacion_activa(){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_HABITACION_03()";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Obtener habitación por ID */
    public function get_habitacion_x_hab_id($hab_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_HABITACION_02(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $hab_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Eliminar habitación (cambio de estado) */
    public function delete_habitacion($hab_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_D_HABITACION_01(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $hab_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Insertar nueva habitación */
    public function insert_habitacion($hab_num, $hab_det, $hab_pre, $hab_est_id, $hab_piso_id, $hab_cat_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_I_HABITACION_01(?,?,?,?,?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $hab_num);
        $sql->bindValue(2, $hab_det);
        $sql->bindValue(3, $hab_pre);
        $sql->bindValue(4, $hab_est_id);
        $sql->bindValue(5, $hab_piso_id);
        $sql->bindValue(6, $hab_cat_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Actualizar habitación */
    public function update_habitacion($hab_id, $hab_num, $hab_det, $hab_pre, $hab_est_id, $hab_piso_id, $hab_cat_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_U_HABITACION_01(?,?,?,?,?,?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $hab_id);
        $sql->bindValue(2, $hab_num);
        $sql->bindValue(3, $hab_det);
        $sql->bindValue(4, $hab_pre);
        $sql->bindValue(5, $hab_est_id);
        $sql->bindValue(6, $hab_piso_id);
        $sql->bindValue(7, $hab_cat_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Verificar si existe una habitación con el mismo número */
    public function verificar_habitacion_existente($hab_num, $hab_id = null){
        $conectar = parent::conexion();
        parent::set_names();
        
        if($hab_id == null){
            // Para inserción - verificar si existe el número (solo activos)
            $sql = "SELECT COUNT(*) as total FROM habitacion WHERE UPPER(TRIM(Numero)) = UPPER(TRIM(?)) AND Estado = 1";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $hab_num);
        } else {
            // Para actualización - verificar si existe el número en otro registro (solo activos)
            $sql = "SELECT COUNT(*) as total FROM habitacion WHERE UPPER(TRIM(Numero)) = UPPER(TRIM(?)) AND IdHabitacion != ? AND Estado = 1";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $hab_num);
            $sql->bindValue(2, $hab_id);
        }
        
        $sql->execute();
        $resultado = $sql->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

    /* Cambiar estado de la habitación (activar/desactivar) */
    public function cambiar_estado_habitacion($hab_id, $nuevo_estado){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_CAMBIAR_ESTADO_HABITACION_01(?, ?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $hab_id);
        $sql->bindValue(2, $nuevo_estado);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Obtener habitaciones por piso */
    public function get_habitacion_x_piso($piso_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT h.*, p.Descripcion as PisoNombre, c.Descripcion as CategoriaNombre, eh.Descripcion as EstadoNombre 
                FROM habitacion h 
                INNER JOIN piso p ON h.IdPiso = p.IdPiso 
                INNER JOIN categoria c ON h.IdCategoria = c.IdCategoria 
                INNER JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion 
                WHERE h.IdPiso = ? AND h.Estado = 1 
                ORDER BY h.Numero";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $piso_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Obtener habitaciones por categoría */
    public function get_habitacion_x_categoria($cat_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT h.*, p.Descripcion as PisoNombre, c.Descripcion as CategoriaNombre, eh.Descripcion as EstadoNombre 
                FROM habitacion h 
                INNER JOIN piso p ON h.IdPiso = p.IdPiso 
                INNER JOIN categoria c ON h.IdCategoria = c.IdCategoria 
                INNER JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion 
                WHERE h.IdCategoria = ? AND h.Estado = 1 
                ORDER BY h.Numero";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $cat_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Obtener habitaciones por estado */
    public function get_habitacion_x_estado($est_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT h.*, p.Descripcion as PisoNombre, c.Descripcion as CategoriaNombre, eh.Descripcion as EstadoNombre 
                FROM habitacion h 
                INNER JOIN piso p ON h.IdPiso = p.IdPiso 
                INNER JOIN categoria c ON h.IdCategoria = c.IdCategoria 
                INNER JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion 
                WHERE h.IdEstadoHabitacion = ? AND h.Estado = 1 
                ORDER BY h.Numero";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $est_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Verificar disponibilidad de habitación */
    public function verificar_disponibilidad_habitacion($hab_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT h.*, eh.Descripcion as EstadoNombre 
                FROM habitacion h 
                INNER JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion 
                WHERE h.IdHabitacion = ? AND h.Estado = 1";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $hab_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>
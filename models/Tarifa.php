<?php
class Tarifa extends Conectar{
    /* Catálogo completo de tarifas */
    public function get_tarifa(){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_TARIFA_CATALOGO_01()";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Listar todas las tarifas activas */
    public function get_tarifas_activas(){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_TARIFA_01()";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Obtener tarifa por ID */
    public function get_tarifa_x_tar_id($tar_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_TARIFA_X_ID_01(?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $tar_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Listar tarifas asignadas a una habitación */
    public function get_tarifas_asignadas_por_habitacion($hab_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_TARIFA_X_HABITACION_01(?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $hab_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Asignar tarifa a habitación */
    public function asignar_tarifa_habitacion($hab_id, $tarifa_id, $fecha_inicio, $fecha_fin = null){
        $conectar = parent::conexion();
        parent::set_names();

        $sql = "CALL SP_I_HABITACION_TARIFA_01(?,?,?,?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $hab_id);
        $stmt->bindValue(2, $tarifa_id);
        $stmt->bindValue(3, $fecha_inicio);
        $stmt->bindValue(4, $fecha_fin);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res && isset($res['id_insertado']) ? $res['id_insertado'] : null;
    }

    /* Actualizar vigencia de asignación */
    public function actualizar_vigencia_tarifa_habitacion($habitacion_tarifa_id, $fecha_inicio, $fecha_fin = null){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_U_HABITACION_TARIFA_01(?,?,?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $habitacion_tarifa_id);
        $stmt->bindValue(2, $fecha_inicio);
        $stmt->bindValue(3, $fecha_fin);
        $stmt->execute();
        return true;
    }

    /* Eliminar asignación de tarifa */
    public function eliminar_tarifa_habitacion($habitacion_tarifa_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_D_HABITACION_TARIFA_01(?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $habitacion_tarifa_id);
        $stmt->execute();
        return true;
    }

    /* METODO CRUD DE tarifa */

    /* Insertar tarifa */
    public function insert_tarifa($tar_desc, $tar_precio){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_I_TARIFA_01(?,?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $tar_desc);
        $stmt->bindValue(2, $tar_precio);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Actualizar tarifa */
    public function update_tarifa($tar_id, $tar_desc, $tar_precio){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_U_TARIFA_01(?,?,?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $tar_id);
        $stmt->bindValue(2, $tar_desc);
        $stmt->bindValue(3, $tar_precio);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Verificar tarifa existente por descripción (excluyendo ID opcional) */
    public function verificar_tarifa_existente($tar_desc, $exclude_id = null){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_S_TARIFA_VERIFICAR_EXISTENTE_01(?,?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $tar_desc);
        $stmt->bindValue(2, $exclude_id);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($res['cnt']) ? ((int)$res['cnt'] > 0) : false;
    }

    /* Cambiar estado de tarifa */
    public function cambiar_estado_tarifa($tar_id, $nuevo_estado){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_U_TARIFA_CAMBIAR_ESTADO_01(?,?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $tar_id);
        $stmt->bindValue(2, $nuevo_estado);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Eliminar tarifa (baja lógica) */
    public function delete_tarifa($tar_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_D_TARIFA_01(?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $tar_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
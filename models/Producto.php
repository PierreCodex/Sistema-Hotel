<?php
class Producto extends Conectar{

    /* Listar todas las productos activas e inactivas */
    public function get_producto(){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_PRODUCTO_01()";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Listar todas las productos activas */

    public function get_producto_activo(){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_PRODUCTO_03()";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Obtener producto por ID */
    public function get_producto_x_id($pro_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_PRODUCTO_02(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $pro_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Eliminar producto */
    public function delete_producto($pro_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_D_PRODUCTO_01(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $pro_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Insertar nueva producto */
    public function insert_producto($pro_nom, $pro_det, $pro_pre, $pro_can){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_I_PRODUCTO_01(?,?,?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $pro_nom);
        $sql->bindValue(2, $pro_det);
        $sql->bindValue(3, $pro_pre);
        $sql->bindValue(4, $pro_can);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Actualizar producto */
    public function update_producto($pro_id, $pro_nom, $pro_det, $pro_pre, $pro_can){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_U_PRODUCTO_01(?,?,?,?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $pro_id);
        $sql->bindValue(2, $pro_nom);
        $sql->bindValue(3, $pro_det);
        $sql->bindValue(4, $pro_pre);
        $sql->bindValue(5, $pro_can);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Verificar si existe una producto con el mismo nombre */
    public function verificar_producto_existente($pro_nom, $pro_id = null){
        $conectar = parent::conexion();
        parent::set_names();
        
        if($pro_id == null){
            // Para inserción - verificar si existe el nombre (solo activos)
            $sql = "SELECT COUNT(*) as total FROM producto WHERE UPPER(TRIM(Nombre)) = UPPER(TRIM(?)) AND Estado = 1";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $pro_nom);  
        } else {
            // Para actualización - verificar si existe el nombre en otro registro (solo activos)
            $sql = "SELECT COUNT(*) as total FROM producto WHERE UPPER(TRIM(Nombre)) = UPPER(TRIM(?)) AND IdProducto != ? AND Estado = 1";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $pro_nom);               
            $sql->bindValue(2, $pro_id);
        }
        
        $sql->execute();
        $resultado = $sql->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

     /* Cambiar estado del Producto (activar/desactivar) */
     public function cambiar_estado_producto($pro_id, $nuevo_estado){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_CAMBIAR_ESTADO_PRODUCTO_01(?, ?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $pro_id);   
        $sql->bindValue(2, $nuevo_estado);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>
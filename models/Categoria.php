<?php
class Categoria extends Conectar{

    /* Listar todas las categorías activas */
    public function get_categoria(){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_CATEGORIA_01()";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Obtener categoría por ID */
    public function get_categoria_x_cat_id($cat_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_CATEGORIA_02(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $cat_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Eliminar categoría (cambio de estado) */
    public function delete_categoria($cat_id){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_D_CATEGORIA_01(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $cat_id);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Insertar nueva categoría */
    public function insert_categoria($cat_nom){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_I_CATEGORIA_01(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $cat_nom);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Actualizar categoría */
    public function update_categoria($cat_id, $cat_nom){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_U_CATEGORIA_01(?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $cat_id);
        $sql->bindValue(2, $cat_nom);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>
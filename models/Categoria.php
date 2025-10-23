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

    /* Verificar si existe una categoría con el mismo nombre */
    public function verificar_categoria_existente($cat_nom, $cat_id = null){
        $conectar = parent::conexion();
        parent::set_names();
        
        if($cat_id == null){
            // Para inserción - verificar si existe el nombre (solo activos)
            $sql = "SELECT COUNT(*) as total FROM categoria WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(?)) AND Estado = 1";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $cat_nom);
        } else {
            // Para actualización - verificar si existe el nombre en otro registro (solo activos)
            $sql = "SELECT COUNT(*) as total FROM categoria WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(?)) AND IdCategoria != ? AND Estado = 1";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $cat_nom);
            $sql->bindValue(2, $cat_id);
        }
        
        $sql->execute();
        $resultado = $sql->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

}
?>
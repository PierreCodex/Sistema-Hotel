<?php
    class Cliente extends Conectar{
        /* TODO: Listar Registros activos e inactivos */

/* TODO: Listar Registros activos  */
    public function get_cliente_activo(){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_CLIENTE_01()";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

        /* TODO: Listar Registro por ID en especifico */
        public function get_cliente_x_cli_id($cli_id){
            $conectar=parent::conexion();
            parent::set_names();
            $sql="CALL SP_L_CLIENTE_02(?)";
            $query=$conectar->prepare($sql);
            $query->bindValue(1,$cli_id);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }

        /* TODO: Eliminar o cambiar estado a eliminado */
        public function delete_cliente($cli_id){
            $conectar=parent::Conexion();
            $sql="SP_D_CLIENTE_01 ?";
            $query=$conectar->prepare($sql);
            $query->bindValue(1,$cli_id);
            $query->execute();
        }

        /* TODO: Registro de datos */
        public function insert_cliente($cli_tipo_doc,$cli_doc,$cli_nom,$cli_ape,$cli_direcc){
            $conectar=parent::Conexion();
            $sql="CALL SP_I_CLIENTE_01(?,?,?,?,?, @p_id_cliente)";
            $query=$conectar->prepare($sql);
            $query->bindValue(1,$cli_tipo_doc);
            $query->bindValue(2,$cli_doc);
            $query->bindValue(3,$cli_nom);
            $query->bindValue(4,$cli_ape);
            $query->bindValue(5,$cli_direcc);
            $query->execute();
            
            // Obtener el ID del cliente insertado
            $query=$conectar->prepare("SELECT @p_id_cliente as id");
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['id'];
        }

        /* TODO:Actualizar Datos */
        public function update_cliente($cli_id,$cli_tipo_doc,$cli_doc,$cli_nom,$cli_ape,$cli_direcc){
            $conectar=parent::Conexion();
            $sql="CALL SP_U_CLIENTE_01( ?,?,?,?,?,?)";
            $query=$conectar->prepare($sql);
            $query->bindValue(1,$cli_id);
            $query->bindValue(2,$cli_tipo_doc);
            $query->bindValue(3,$cli_doc);
            $query->bindValue(4,$cli_nom);
            $query->bindValue(5,$cli_ape);
            $query->bindValue(6,$cli_direcc);
            $query->execute();
        }
    }
?>
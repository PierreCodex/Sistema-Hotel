<?php
    class Cliente extends Conectar{
        /* TODO: Listar Registros activos e inactivos */

/* Listar todos los clientes (activos e inactivos) - Para mantenimiento admin */
    public function get_cliente(){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_CLIENTE_01()";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Listar solo clientes activos - Para combos y vista empleado */
    public function get_cliente_activo(){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_CLIENTE_03()";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Obtener clientes activos con conteo de visitas (Paginado) */
    public function get_cliente_con_conteo_paginado($search_value, $start, $limit){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_CLIENTE_CON_CONTEO_PAGINADO(?, ?, ?)";
        $query = $conectar->prepare($sql);
        $query->bindValue(1, $search_value);
        $query->bindValue(2, $start, PDO::PARAM_INT);
        $query->bindValue(3, $limit, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Contar total de clientes (filtrado) */
    public function count_clientes_con_conteo($search_value){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_COUNT_CLIENTE_CON_CONTEO(?)";
        $query = $conectar->prepare($sql);
        $query->bindValue(1, $search_value);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
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
        
        /* Obtener detalles completos del cliente con auditoría */
        public function get_cliente_detalles($cli_id){
            $conectar = parent::conexion();
            parent::set_names();
            
            $sql = "SELECT 
                        c.*,
                        uc.Nombre as UsuarioCreacionNombre,
                        uc.Apellido as UsuarioCreacionApellido,
                        um.Nombre as UsuarioModificacionNombre,
                        um.Apellido as UsuarioModificacionApellido,
                        (SELECT COUNT(*) FROM recepcion r WHERE r.IdCliente = c.IdCliente) as TotalVisitas,
                        (SELECT MAX(r.FechaEntrada) FROM recepcion r WHERE r.IdCliente = c.IdCliente) as UltimaVisita
                    FROM cliente c
                    LEFT JOIN usuario uc ON c.IdUsuarioCreacion = uc.IdUsuario
                    LEFT JOIN usuario um ON c.IdUsuarioModificacion = um.IdUsuario
                    WHERE c.IdCliente = ?";
            
            $query = $conectar->prepare($sql);
            $query->bindValue(1, $cli_id, PDO::PARAM_INT);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        }

 
        /* Insertar nuevo cliente con auditoría */
        public function insert_cliente($cli_tipo_doc,$cli_doc,$cli_nom,$cli_ape,$cli_direcc){
            $conectar=parent::Conexion();
            parent::set_names();
            
            // Obtener ID del usuario actual
            $usuario_id = isset($_SESSION["IdUsuario"]) ? $_SESSION["IdUsuario"] : null;
            
            $sql="CALL SP_I_CLIENTE_01(?,?,?,?,?,?)";
            $query=$conectar->prepare($sql);
            $query->bindValue(1,$cli_tipo_doc);
            $query->bindValue(2,$cli_doc);
            $query->bindValue(3,$cli_nom);
            $query->bindValue(4,$cli_ape);
            $query->bindValue(5,$cli_direcc);
            $query->bindValue(6,$usuario_id);
            $query->execute();
            
            // Obtener el resultado del SP
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['IdCliente'];
        }

        /* Actualizar cliente existente con auditoría (US050) */
        public function update_cliente($cli_id,$cli_tipo_doc,$cli_doc,$cli_nom,$cli_ape,$cli_direcc){
            $conectar=parent::Conexion();
            parent::set_names();
            
            // Obtener ID del usuario actual
            $usuario_id = isset($_SESSION["IdUsuario"]) ? $_SESSION["IdUsuario"] : null;
            
            $sql="CALL SP_U_CLIENTE_01(?,?,?,?,?,?,?)";
            $query=$conectar->prepare($sql);
            $query->bindValue(1,$cli_id);
            $query->bindValue(2,$cli_tipo_doc);
            $query->bindValue(3,$cli_doc);
            $query->bindValue(4,$cli_nom);
            $query->bindValue(5,$cli_ape);
            $query->bindValue(6,$cli_direcc);
            $query->bindValue(7,$usuario_id);
            $query->execute();
            return true;
        }

        /* Eliminar cliente */
        public function delete_cliente($cli_id){
            $conectar=parent::Conexion();
            parent::set_names();
            $sql="CALL SP_D_CLIENTE_01(?)";
            $query=$conectar->prepare($sql);
            $query->bindValue(1,$cli_id);
            $query->execute();
            return $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        /* Cambiar estado del cliente (activar/desactivar) con auditoría (US051) */
        public function cambiar_estado_cliente($cli_id, $nuevo_estado){
            $conectar=parent::Conexion();
            parent::set_names();
            
            // Obtener ID del usuario actual
            $usuario_id = isset($_SESSION["IdUsuario"]) ? $_SESSION["IdUsuario"] : null;
            
            $sql="CALL SP_CAMBIAR_ESTADO_CLIENTE_01(?,?,?)";
            $query=$conectar->prepare($sql);
            $query->bindValue(1,$cli_id);
            $query->bindValue(2,$nuevo_estado);
            $query->bindValue(3,$usuario_id);
            $query->execute();
            return $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        }


        /* Verificar si el cliente tiene recepciones activas (hospedado actualmente) */
        public function tiene_recepciones_activas($cli_id){
            $conectar = parent::Conexion();
            parent::set_names();
            
            $sql = "SELECT COUNT(*) as cantidad 
                    FROM recepcion 
                    WHERE IdCliente = ? 
                    AND Estado = 'Ocupado'
                    LIMIT 1";
            
            $query = $conectar->prepare($sql);
            $query->bindValue(1, $cli_id);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            
            return [
                'tiene_activas' => ($result['cantidad'] > 0),
                'cantidad' => $result['cantidad']
            ];
        }

        /* Verificar si existe un cliente con el mismo documento (DNI/RUC) */
        public function verificar_documento_existe($cli_doc, $cli_id = null){
            $conectar = parent::Conexion();
            parent::set_names();
            
            // Buscar cliente activo con el mismo documento
            if ($cli_id) {
                // Excluir el cliente actual (para edición)
                $sql = "SELECT IdCliente, TipoDocumento, Documento, Nombre, Apellido 
                        FROM cliente 
                        WHERE Documento = ? AND Estado = 1 AND IdCliente != ?
                        LIMIT 1";
                $query = $conectar->prepare($sql);
                $query->bindValue(1, $cli_doc);
                $query->bindValue(2, $cli_id, PDO::PARAM_INT);
            } else {
                // Buscar cualquier cliente con ese documento
                $sql = "SELECT IdCliente, TipoDocumento, Documento, Nombre, Apellido 
                        FROM cliente 
                        WHERE Documento = ? AND Estado = 1
                        LIMIT 1";
                $query = $conectar->prepare($sql);
                $query->bindValue(1, $cli_doc);
            }
            
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return [
                    'existe' => true,
                    'cliente' => $result
                ];
            }
            
            return ['existe' => false];
        }
    }
?>
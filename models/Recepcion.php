<?php
    class Recepcion extends Conectar{

        // Inserta una recepción y retorna el Id generado
        public function insert_recepcion($cli_id, $hab_id, $precio_inicial, $adelanto, $observacion, $fecha_salida = null){
            $conectar = parent::Conexion();
            parent::set_names();

            // Llamada al stored procedure con variable de salida
            $sql = "CALL SP_I_RECEPCION_01(?,?,?,?,?,?,@p_id_recepcion)";
            $query = $conectar->prepare($sql);
            $query->bindValue(1, $cli_id);
            $query->bindValue(2, $hab_id);
            $query->bindValue(3, $precio_inicial);
            $query->bindValue(4, $adelanto);
            $query->bindValue(5, $observacion);
            $query->bindValue(6, $fecha_salida);
            $query->execute();

            // Obtener el ID de la recepción insertada
            $query = $conectar->prepare("SELECT @p_id_recepcion AS id");
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['id'];
        }

        // Lista ocupaciones activas (cliente actual por habitación)
        public function listar_ocupaciones_activas(){
            $conectar = parent::conexion();
            parent::set_names();
            // Trae recepciones activas con su cliente y el IdRecepcion
            $sql = "SELECT 
                        r.IdRecepcion AS REC_ID,
                        r.IdHabitacion AS HAB_ID,
                        r.IdCliente AS CLI_ID,
                        CONCAT(c.Nombre, ' ', c.Apellido) AS CLI_NOMBRE,
                        c.Nombre AS CLI_NOM,
                        c.Apellido AS CLI_APE
                    FROM recepcion r
                    INNER JOIN cliente c ON c.IdCliente = r.IdCliente
                    WHERE r.Estado = 1
                    ORDER BY r.FechaEntrada DESC";
            $query = $conectar->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }

        // Obtiene IdRecepcion activo por IdHabitacion (último registro activo)
        public function get_recepcion_activa_por_habitacion($hab_id){
            $conectar = parent::Conexion();
            parent::set_names();
            $sql = "SELECT r.IdRecepcion
                    FROM recepcion r
                    WHERE r.IdHabitacion = ? AND r.Estado = 1
                    ORDER BY r.FechaEntrada DESC
                    LIMIT 1";
            $query = $conectar->prepare($sql);
            $query->bindValue(1, $hab_id, PDO::PARAM_INT);
            $query->execute();
            $row = $query->fetch(PDO::FETCH_ASSOC);
            return $row ? intval($row['IdRecepcion']) : 0;
        }

        // Obtiene detalle de una recepción por Id (incluye campos de la tabla recepcion)
        public function get_recepcion_x_id($rec_id){
            $conectar = parent::Conexion();
            parent::set_names();
            $sql = "SELECT 
                        r.IdRecepcion,
                        r.IdCliente,
                        r.IdHabitacion,
                        r.FechaEntrada,
                        r.FechaSalida,
                        r.FechaSalidaConfirmacion,
                        r.PrecioInicial,
                        r.Adelanto,
                        r.PrecioRestante,
                        r.TotalPagado,
                        r.CostoPenalidad,
                        r.Observacion,
                        r.Estado
                    FROM recepcion r
                    WHERE r.IdRecepcion = ?
                    LIMIT 1";
            $query = $conectar->prepare($sql);
            $query->bindValue(1, $rec_id, PDO::PARAM_INT);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        }
    }
?>
<?php
class Recepcion extends Conectar
{

    // Inserta una recepción y retorna el Id generado
    public function insert_recepcion($cli_id, $hab_id, $precio_inicial, $adelanto, $observacion, $fecha_salida = null, $tar_id = null, $tipo_comprobante = '03', $usuario_id = null)
    {
        $conectar = parent::Conexion();
        parent::set_names();

        // Si no se proporciona usuario_id, intentar obtenerlo de la sesión
        if ($usuario_id === null && isset($_SESSION['IdUsuario'])) {
            $usuario_id = intval($_SESSION['IdUsuario']);
        }

        // Llamada al stored procedure con variable de salida (incluyendo tarifa, tipo de comprobante y usuario)
        $sql = "CALL SP_I_RECEPCION_03(?,?,?,?,?,?,?,?,?,@p_id_recepcion)";
        $query = $conectar->prepare($sql);
        $query->bindValue(1, $cli_id);
        $query->bindValue(2, $hab_id);
        $query->bindValue(3, $tar_id, PDO::PARAM_INT);
        $query->bindValue(4, $precio_inicial);
        $query->bindValue(5, $adelanto);
        $query->bindValue(6, $observacion);
        $query->bindValue(7, $fecha_salida);
        $query->bindValue(8, $tipo_comprobante);
        $query->bindValue(9, $usuario_id, PDO::PARAM_INT);
        $query->execute();

        // Obtener el ID de la recepción insertada
        $query = $conectar->prepare("SELECT @p_id_recepcion AS id");
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['id'];
    }

    // Lista ocupaciones activas (cliente actual por habitación)
    public function listar_ocupaciones_activas()
    {
        $conectar = parent::conexion();
        parent::set_names();
        // Trae recepciones activas con su cliente y el IdRecepcion
        $sql = "SELECT 
                        r.IdRecepcion AS REC_ID,
                        r.IdHabitacion AS HAB_ID,
                        r.IdCliente AS CLI_ID,
                        r.FechaSalida AS FECHA_SALIDA,
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

    // Lista recepciones finalizadas del usuario logueado (para historial de ventas)
    public function listar_recepciones_finalizadas_por_usuario($usuario_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        
        $sql = "SELECT 
                    r.IdRecepcion,
                    r.IdHabitacion,
                    r.IdCliente,
                    r.FechaEntrada,
                    r.FechaSalida,
                    r.FechaSalidaConfirmacion,
                    h.Numero AS NumeroHabitacion,
                    c.Nombre AS ClienteNombre,
                    c.Apellido AS ClienteApellido
                FROM recepcion r
                INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
                INNER JOIN cliente c ON r.IdCliente = c.IdCliente
                WHERE r.Estado = 0 
                AND r.IdUsuario = ?
                ORDER BY r.FechaSalidaConfirmacion DESC
                LIMIT 100";
        
        $query = $conectar->prepare($sql);
        $query->bindValue(1, $usuario_id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtiene IdRecepcion activo por IdHabitacion (último registro activo)
    public function get_recepcion_activa_por_habitacion($hab_id)
    {
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
    public function get_recepcion_x_id($rec_id)
    {
        $conectar = parent::Conexion();
        parent::set_names();
        $sql = "SELECT 
                        r.IdRecepcion,
                        r.IdCliente,
                        r.IdHabitacion,
                        r.IdTarifa,
                        h.Numero AS HAB_NUM,
                        t.Descripcion AS TARIFA_DESC,
                        r.FechaEntrada,
                        r.FechaSalida,
                        r.FechaSalidaConfirmacion,
                        r.PrecioInicial,
                        r.Adelanto,
                        r.PrecioRestante,
                        r.TotalPagado,
                        r.CostoPenalidad,
                        r.Observacion,
                        r.TipoComprobante,
                        r.Estado
                    FROM recepcion r
                    INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
                    LEFT JOIN tarifa t ON r.IdTarifa = t.IdTarifa
                    WHERE r.IdRecepcion = ?
                    LIMIT 1";
        $query = $conectar->prepare($sql);
        $query->bindValue(1, $rec_id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function confirmar_salida($rec_id, $costo_penalidad, $total_pagado, $fecha_confirmacion)
    {
        $conectar = parent::Conexion();
        parent::set_names();

        // Finalizar la recepción (cambiar Estado a 0)
        $sql = "UPDATE recepcion 
                    SET FechaSalidaConfirmacion = ?, TotalPagado = ?, CostoPenalidad = ?, Estado = 0 
                    WHERE IdRecepcion = ?";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $fecha_confirmacion);
        $stmt->bindValue(2, $total_pagado);
        $stmt->bindValue(3, $costo_penalidad);
        $stmt->bindValue(4, $rec_id, PDO::PARAM_INT);
        $stmt->execute();

        // Cambiar todas las ventas PENDIENTES a PAGADO
        $sqlVentas = "UPDATE venta SET Estado = 'PAGADO' 
                          WHERE IdRecepcion = ? AND Estado = 'PENDIENTE'";
        $qVentas = $conectar->prepare($sqlVentas);
        $qVentas->bindValue(1, $rec_id, PDO::PARAM_INT);
        $qVentas->execute();

        // Cambiar habitación a LIMPIEZA
        $sqlHab = "UPDATE habitacion h 
                       INNER JOIN recepcion r ON h.IdHabitacion = r.IdHabitacion 
                       SET h.IdEstadoHabitacion = 13 
                       WHERE r.IdRecepcion = ?";
        $qHab = $conectar->prepare($sqlHab);
        $qHab->bindValue(1, $rec_id, PDO::PARAM_INT);
        $qHab->execute();
        return true;
    }
    // Verifica si un cliente tiene una recepción activa
    public function cliente_tiene_recepcion_activa($cli_id)
    {
        $conectar = parent::Conexion();
        parent::set_names();
        $sql = "CALL SP_CHECK_RECEPCION_ACTIVA_CLIENTE(?)";
        $query = $conectar->prepare($sql);
        $query->bindValue(1, $cli_id);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }

    // Obtener historial de recepciones finalizadas por cliente
    public function get_historial_por_cliente($cli_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "CALL SP_L_RECEPCION_HISTORIAL_CLIENTE(?)";
        $query = $conectar->prepare($sql);
        $query->bindValue(1, $cli_id);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}

<?php
class Venta extends Conectar{

    // Inserta cabecera de venta con SQL visible y retorna el Id generado
    public function insert_venta($id_recepcion){
        $conectar = parent::Conexion();
        parent::set_names();
        // Crear cabecera con estado inicial 'BORRADOR' para no marcar 'PENDIENTE' hasta guardar
        $sql = "INSERT INTO venta (IdRecepcion, Total, Estado) VALUES (?, 0, 'BORRADOR')";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $id_recepcion, PDO::PARAM_INT);
        $stmt->execute();
        return intval($conectar->lastInsertId());
    }

    // Busca venta borrador existente para una recepción
    public function get_venta_borrador_por_recepcion($id_recepcion){
        $conectar = parent::Conexion();
        parent::set_names();
        $sql = "SELECT IdVenta FROM venta WHERE IdRecepcion = ? AND Estado = 'BORRADOR' LIMIT 1";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $id_recepcion, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? intval($row['IdVenta']) : 0;
    }

    // Reutiliza venta borrador existente o crea nueva
    public function get_or_create_venta_borrador($id_recepcion){
        // Buscar venta borrador existente
        $id_venta_existente = $this->get_venta_borrador_por_recepcion($id_recepcion);
        
        if ($id_venta_existente > 0) {
            return $id_venta_existente; // Reutilizar existente
        }
        
        // Si no existe, crear nueva
        return $this->insert_venta($id_recepcion);
    }

    // Inserta detalle de venta con SQL visible (subtotal = precio * cantidad)
    // ACTUALIZADO: Ahora descuenta stock inmediatamente al agregar detalle
    public function insert_detalle_venta($id_venta, $id_producto, $prod_pventa, $cantidad){
        $conectar = parent::Conexion();
        parent::set_names();
        
        try {
            // Iniciar transacción para garantizar consistencia
            $conectar->beginTransaction();
            
            $subtotal = round(floatval($prod_pventa) * intval($cantidad), 2);
            
            // 1. Insertar detalle de venta
            $sql = "INSERT INTO detalle_venta (IdVenta, IdProducto, Cantidad, SubTotal) VALUES (?, ?, ?, ?)";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $id_venta, PDO::PARAM_INT);
            $stmt->bindValue(2, $id_producto, PDO::PARAM_INT);
            $stmt->bindValue(3, $cantidad, PDO::PARAM_INT);
            $stmt->bindValue(4, $subtotal);
            $stmt->execute();
            $detalle_id = intval($conectar->lastInsertId());
            
            // 2. Descontar stock inmediatamente
            $sql_stock = "UPDATE producto SET Cantidad = Cantidad - ? WHERE IdProducto = ? AND Cantidad >= ?";
            $stmt_stock = $conectar->prepare($sql_stock);
            $stmt_stock->bindValue(1, $cantidad, PDO::PARAM_INT);
            $stmt_stock->bindValue(2, $id_producto, PDO::PARAM_INT);
            $stmt_stock->bindValue(3, $cantidad, PDO::PARAM_INT);
            $stmt_stock->execute();
            
            // Verificar que el stock se actualizó (protección contra condiciones de carrera)
            if ($stmt_stock->rowCount() === 0) {
                $conectar->rollBack();
                throw new Exception("No se pudo descontar el stock. Posible stock insuficiente.");
            }
            
            // Confirmar transacción
            $conectar->commit();
            return $detalle_id;
            
        } catch (Exception $e) {
            $conectar->rollBack();
            throw $e;
        }
    }
        public function get_venta_detalle($vent_id){
            $conectar = parent::Conexion();
            // Llamada correcta a SP en MySQL
            $sql = "CALL SP_L_VENTA_01(?)";
            $query = $conectar->prepare($sql);
            $query->bindValue(1, $vent_id, PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            // Limpiar cursores adicionales de CALL si aplica
            while ($query->nextRowset()) { /* avanzar */ }
            return $result;
        }

    public function get_venta_x_id($id_venta){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT v.IdVenta, v.IdRecepcion, v.Total, v.Estado,
                       r.IdCliente, h.Numero AS HabitacionNumero
                FROM venta v
                INNER JOIN recepcion r ON v.IdRecepcion = r.IdRecepcion
                INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
                WHERE v.IdVenta = ?";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $id_venta);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_detalle_venta_x_id_venta($id_venta){
        $conectar = parent::Conexion();
        parent::set_names();
        $sql = "SELECT
                    dv.IdDetalleVenta AS DETV_ID,
                    p.Nombre AS PRO_NOM,
                    p.Precio AS PROD_PVENTA,
                    dv.Cantidad AS DETV_CANT,
                    dv.SubTotal AS DETV_TOTAL,
                    dv.IdVenta AS VENT_ID,
                    dv.IdProducto AS PROD_ID
                FROM detalle_venta dv
                INNER JOIN producto p ON dv.IdProducto = p.IdProducto
                WHERE dv.IdVenta = ?
                ORDER BY dv.IdDetalleVenta DESC";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $id_venta, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_ventas(){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT v.IdVenta, v.IdRecepcion, v.Total, v.Estado,
                       r.IdCliente, h.Numero AS HabitacionNumero
                FROM venta v
                INNER JOIN recepcion r ON v.IdRecepcion = r.IdRecepcion
                INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
                ORDER BY v.IdVenta DESC";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_ventas_x_recepcion($id_recepcion){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT v.IdVenta, v.IdRecepcion, v.Total, v.Estado
                FROM venta v
                WHERE v.IdRecepcion = ?
                ORDER BY v.IdVenta DESC";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $id_recepcion);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update_venta_total($id_venta, $total){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "UPDATE venta SET Total = ? WHERE IdVenta = ?";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $total);
        $sql->bindValue(2, $id_venta);
        $sql->execute();
        return $sql->rowCount();
    }

    public function update_venta_estado($id_venta, $estado){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "UPDATE venta SET Estado = ? WHERE IdVenta = ?";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $estado);
        $sql->bindValue(2, $id_venta);
        $sql->execute();
        return $sql->rowCount();
    }

    public function delete_venta($id_venta){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "UPDATE venta SET Estado = 'ANULADO' WHERE IdVenta = ?";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $id_venta);
        $sql->execute();
        return $sql->rowCount();
    }

    // Elimina detalle de venta y restaura el stock del producto
    public function delete_detalle_venta($id_detalle_venta){
        $conectar = parent::Conexion();
        parent::set_names();
        
        try {
            // Iniciar transacción
            $conectar->beginTransaction();
            
            // 1. Obtener información del detalle antes de eliminarlo
            $sql_info = "SELECT IdProducto, Cantidad FROM detalle_venta WHERE IdDetalleVenta = ?";
            $stmt_info = $conectar->prepare($sql_info);
            $stmt_info->bindValue(1, $id_detalle_venta, PDO::PARAM_INT);
            $stmt_info->execute();
            $detalle_info = $stmt_info->fetch(PDO::FETCH_ASSOC);
            
            if (!$detalle_info) {
                $conectar->rollBack();
                throw new Exception("Detalle de venta no encontrado");
            }
            
            // 2. Eliminar el detalle
            $sql = "DELETE FROM detalle_venta WHERE IdDetalleVenta = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $id_detalle_venta, PDO::PARAM_INT);
            $stmt->execute();
            
            // 3. Restaurar stock al producto
            $sql_stock = "UPDATE producto SET Cantidad = Cantidad + ? WHERE IdProducto = ?";
            $stmt_stock = $conectar->prepare($sql_stock);
            $stmt_stock->bindValue(1, $detalle_info['Cantidad'], PDO::PARAM_INT);
            $stmt_stock->bindValue(2, $detalle_info['IdProducto'], PDO::PARAM_INT);
            $stmt_stock->execute();
            
            // Confirmar transacción
            $conectar->commit();
            return true;
            
        } catch (Exception $e) {
            $conectar->rollBack();
            throw $e;
        }
    }

    public function update_detalle_venta($id_detalle_venta, $cantidad, $subtotal){
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "UPDATE detalle_venta SET Cantidad = ?, SubTotal = ? WHERE IdDetalleVenta = ?";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $cantidad);
        $sql->bindValue(2, $subtotal);
        $sql->bindValue(3, $id_detalle_venta);
        $sql->execute();
        return $sql->rowCount();
    }

    public function get_total_detalles_por_venta($id_venta){
        $conectar = parent::Conexion();
        parent::set_names();
        $sql = "SELECT COALESCE(SUM(SubTotal),0) AS TotalDetalles FROM detalle_venta WHERE IdVenta = ?";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $id_venta, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Calcula subtotal, IGV y total y actualiza venta.Total con SQL visible
    public function calcular_totales_y_actualizar($id_venta){
        $conectar = parent::Conexion();
        parent::set_names();
        $sqlSum = "SELECT COALESCE(SUM(SubTotal), 0) AS subtotal FROM detalle_venta WHERE IdVenta = ?";
        $stmt = $conectar->prepare($sqlSum);
        $stmt->bindValue(1, $id_venta, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $subtotal = isset($row['subtotal']) ? floatval($row['subtotal']) : 0.0;
        $igv = round($subtotal * 0.18, 2);
        $total = round($subtotal + $igv, 2);

        $sqlUpd = "UPDATE venta SET Total = ? WHERE IdVenta = ?";
        $stmtUpd = $conectar->prepare($sqlUpd);
        $stmtUpd->bindValue(1, $total);
        $stmtUpd->bindValue(2, $id_venta, PDO::PARAM_INT);
        $stmtUpd->execute();

        return [
            'VENT_SUBTOTAL' => number_format($subtotal, 2, '.', ''),
            'VENT_IGV' => number_format($igv, 2, '.', ''),
            'VENT_TOTAL' => number_format($total, 2, '.', '')
        ];
    }

    // Finaliza la venta (sin descontar stock porque ya se descontó al agregar cada detalle)
    public function finalizar_venta($id_venta){
        $conectar = parent::Conexion();
        parent::set_names();
        // Solo marcar venta activa - el stock ya fue descontado en insert_detalle_venta()
        $sqlVenta = "UPDATE venta SET Estado = 'ACTIVO' WHERE IdVenta = ?";
        $stmtVenta = $conectar->prepare($sqlVenta);
        $stmtVenta->bindValue(1, $id_venta, PDO::PARAM_INT);
        $stmtVenta->execute();

        // NOTA: No descontamos stock aquí porque ya se descontó al agregar cada detalle
        // Si el usuario elimina un detalle, el stock se restaura automáticamente

        return true;
    }

    /**
     * Cancela una venta borrador: restaura stock de todos sus detalles y elimina la venta
     * Útil cuando el usuario cancela/cierra sin guardar
     */
    public function cancelar_venta_borrador($id_venta){
        $conectar = parent::Conexion();
        parent::set_names();
        
        try {
            $conectar->beginTransaction();
            
            // 1. Verificar que la venta esté en estado BORRADOR
            $sql_check = "SELECT IdVenta, Estado FROM venta WHERE IdVenta = ?";
            $stmt_check = $conectar->prepare($sql_check);
            $stmt_check->bindValue(1, $id_venta, PDO::PARAM_INT);
            $stmt_check->execute();
            $venta_info = $stmt_check->fetch(PDO::FETCH_ASSOC);
            
            error_log("DEBUG cancelar_venta_borrador - Venta ID: $id_venta, Info: " . json_encode($venta_info));
            
            if (!$venta_info) {
                $conectar->rollBack();
                error_log("ERROR: Venta $id_venta no encontrada");
                return false;
            }
            
            if ($venta_info['Estado'] !== 'BORRADOR') {
                $conectar->rollBack();
                error_log("ERROR: Venta $id_venta no es BORRADOR, estado actual: " . $venta_info['Estado']);
                return false;
            }
            
            // 2. Restaurar stock de todos los detalles
            $sql_restore = "UPDATE producto p
                           INNER JOIN detalle_venta dv ON p.IdProducto = dv.IdProducto
                           SET p.Cantidad = p.Cantidad + dv.Cantidad
                           WHERE dv.IdVenta = ?";
            $stmt_restore = $conectar->prepare($sql_restore);
            $stmt_restore->bindValue(1, $id_venta, PDO::PARAM_INT);
            $stmt_restore->execute();
            $stock_restaurado = $stmt_restore->rowCount();
            error_log("DEBUG: Stock restaurado para $stock_restaurado productos");
            
            // 3. Eliminar todos los detalles PRIMERO (por restricción de FK)
            $sql_del_det = "DELETE FROM detalle_venta WHERE IdVenta = ?";
            $stmt_del_det = $conectar->prepare($sql_del_det);
            $stmt_del_det->bindValue(1, $id_venta, PDO::PARAM_INT);
            $stmt_del_det->execute();
            $detalles_eliminados = $stmt_del_det->rowCount();
            error_log("DEBUG: Detalles eliminados: $detalles_eliminados");
            
            // 4. Verificar que NO queden detalles
            $sql_verify = "SELECT COUNT(*) as total FROM detalle_venta WHERE IdVenta = ?";
            $stmt_verify = $conectar->prepare($sql_verify);
            $stmt_verify->bindValue(1, $id_venta, PDO::PARAM_INT);
            $stmt_verify->execute();
            $detalles_restantes = $stmt_verify->fetch(PDO::FETCH_ASSOC)['total'];
            error_log("DEBUG: Detalles restantes después de DELETE: $detalles_restantes");
            
            if ($detalles_restantes > 0) {
                $conectar->rollBack();
                error_log("ERROR: Aún quedan $detalles_restantes detalles, no se puede eliminar venta");
                return false;
            }
            
            // 5. Eliminar la venta borrador
            $sql_del_venta = "DELETE FROM venta WHERE IdVenta = ?";
            $stmt_del_venta = $conectar->prepare($sql_del_venta);
            $stmt_del_venta->bindValue(1, $id_venta, PDO::PARAM_INT);
            $stmt_del_venta->execute();
            $venta_eliminada = $stmt_del_venta->rowCount();
            error_log("DEBUG: Venta eliminada (rowCount): $venta_eliminada");
            
            // 6. Verificar que la venta se eliminó
            $sql_verify_venta = "SELECT COUNT(*) as total FROM venta WHERE IdVenta = ?";
            $stmt_verify_venta = $conectar->prepare($sql_verify_venta);
            $stmt_verify_venta->bindValue(1, $id_venta, PDO::PARAM_INT);
            $stmt_verify_venta->execute();
            $venta_existe = $stmt_verify_venta->fetch(PDO::FETCH_ASSOC)['total'];
            error_log("DEBUG: Venta existe después de DELETE: $venta_existe");
            
            if ($venta_existe > 0) {
                $conectar->rollBack();
                error_log("ERROR: La venta $id_venta NO se eliminó, aún existe en BD");
                return false;
            }
            
            $conectar->commit();
            error_log("SUCCESS: Venta $id_venta eliminada correctamente");
            return true;
            
        } catch (Exception $e) {
            $conectar->rollBack();
            error_log("EXCEPTION al cancelar venta borrador: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Limpia ventas borrador antiguas (más de X horas) y restaura su stock
     * Puede llamarse periódicamente para limpiar ventas abandonadas
     * NOTA: Requiere que la tabla venta tenga la columna FechaCreacion
     *       ALTER TABLE venta ADD COLUMN FechaCreacion DATETIME DEFAULT CURRENT_TIMESTAMP;
     */
    public function limpiar_borradores_antiguos($horas = 24){
        $conectar = parent::Conexion();
        parent::set_names();
        
        try {
            // Verificar si existe la columna FechaCreacion
            $sql_check = "SHOW COLUMNS FROM venta LIKE 'FechaCreacion'";
            $stmt_check = $conectar->query($sql_check);
            $has_fecha = $stmt_check->rowCount() > 0;
            
            if (!$has_fecha) {
                // Si no existe la columna, no podemos limpiar por fecha
                // Retornar 0 sin hacer nada (el admin debe agregar la columna)
                return 0;
            }
            
            $conectar->beginTransaction();
            
            // 1. Restaurar stock de detalles de ventas borrador antiguas
            $sql_restore = "UPDATE producto p
                           INNER JOIN detalle_venta dv ON p.IdProducto = dv.IdProducto
                           INNER JOIN venta v ON dv.IdVenta = v.IdVenta
                           SET p.Cantidad = p.Cantidad + dv.Cantidad
                           WHERE v.Estado = 'BORRADOR'
                           AND v.FechaCreacion < DATE_SUB(NOW(), INTERVAL ? HOUR)";
            $stmt_restore = $conectar->prepare($sql_restore);
            $stmt_restore->bindValue(1, $horas, PDO::PARAM_INT);
            $stmt_restore->execute();
            
            // 2. Eliminar detalles de ventas borrador antiguas
            $sql_del_det = "DELETE dv FROM detalle_venta dv
                           INNER JOIN venta v ON dv.IdVenta = v.IdVenta
                           WHERE v.Estado = 'BORRADOR'
                           AND v.FechaCreacion < DATE_SUB(NOW(), INTERVAL ? HOUR)";
            $stmt_del_det = $conectar->prepare($sql_del_det);
            $stmt_del_det->bindValue(1, $horas, PDO::PARAM_INT);
            $stmt_del_det->execute();
            
            // 3. Eliminar ventas borrador antiguas
            $sql_del_venta = "DELETE FROM venta 
                             WHERE Estado = 'BORRADOR'
                             AND FechaCreacion < DATE_SUB(NOW(), INTERVAL ? HOUR)";
            $stmt_del_venta = $conectar->prepare($sql_del_venta);
            $stmt_del_venta->bindValue(1, $horas, PDO::PARAM_INT);
            $rows_deleted = $stmt_del_venta->execute();
            
            $conectar->commit();
            return $stmt_del_venta->rowCount();
            
        } catch (Exception $e) {
            $conectar->rollBack();
            throw $e;
        }
    }
}
?>
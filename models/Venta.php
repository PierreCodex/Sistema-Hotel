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

    // Inserta detalle de venta con SQL visible (subtotal = precio * cantidad)
    public function insert_detalle_venta($id_venta, $id_producto, $prod_pventa, $cantidad){
        $conectar = parent::Conexion();
        parent::set_names();
        $subtotal = round(floatval($prod_pventa) * intval($cantidad), 2);
        $sql = "INSERT INTO detalle_venta (IdVenta, IdProducto, Cantidad, SubTotal) VALUES (?, ?, ?, ?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $id_venta, PDO::PARAM_INT);
        $stmt->bindValue(2, $id_producto, PDO::PARAM_INT);
        $stmt->bindValue(3, $cantidad, PDO::PARAM_INT);
        $stmt->bindValue(4, $subtotal);
        $stmt->execute();
        return intval($conectar->lastInsertId());
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

    public function delete_detalle_venta($id_detalle_venta){
        $conectar = parent::Conexion();
        parent::set_names();
        $sql = "DELETE FROM detalle_venta WHERE IdDetalleVenta = ?";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $id_detalle_venta, PDO::PARAM_INT);
        $stmt->execute();
        return true;
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

    // Finaliza la venta y descuenta stock con SQL visible
    public function finalizar_venta($id_venta){
        $conectar = parent::Conexion();
        parent::set_names();
        // Marcar venta activa
        $sqlVenta = "UPDATE venta SET Estado = 'ACTIVO' WHERE IdVenta = ?";
        $stmtVenta = $conectar->prepare($sqlVenta);
        $stmtVenta->bindValue(1, $id_venta, PDO::PARAM_INT);
        $stmtVenta->execute();

        // Descontar stock por cada detalle
        $sqlStock = "UPDATE producto p
                     INNER JOIN detalle_venta dv ON p.IdProducto = dv.IdProducto
                     SET p.Cantidad = GREATEST(p.Cantidad - dv.Cantidad, 0)
                     WHERE dv.IdVenta = ?";
        $stmtStock = $conectar->prepare($sqlStock);
        $stmtStock->bindValue(1, $id_venta, PDO::PARAM_INT);
        $stmtStock->execute();

        return true;
    }
}
?>
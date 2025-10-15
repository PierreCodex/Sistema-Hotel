<?php
class Menu extends Conectar {
    
    public function get_menu_x_rol_id($idUsuario) {
        $conectar = parent::conexion();
        parent::set_names();
        
        
        // Menús básicos según el tipo de persona
        $menus = array();
        
        if ($idUsuario == 1) { // Administrador
            $menus = array(
                array(
                    "MEN_NOM" => "Dashboard",
                    "MEN_RUTA" => "../home/",
                    "MEN_GRUPO" => "Dashboard",
                    "MEND_PERMI" => "Si",
                    "MEN_IDENTI" => "dashboard"
                ),
                array(
                    "MEN_NOM" => "Habitaciones",
                    "MEN_RUTA" => "../MntHabitacion/",
                    "MEN_GRUPO" => "Mantenimiento",
                    "MEND_PERMI" => "Si",
                    "MEN_IDENTI" => "habitaciones"
                ),
                array(
                    "MEN_NOM" => "Categoría",
                    "MEN_RUTA" => "../MntCategoria/",
                    "MEN_GRUPO" => "Mantenimiento",
                    "MEND_PERMI" => "Si",
                    "MEN_IDENTI" => "categoria"
                ),
                array(
                    "MEN_NOM" => "Pisos",
                    "MEN_RUTA" => "../MntPisos/",
                    "MEN_GRUPO" => "Mantenimiento",
                    "MEND_PERMI" => "Si",
                    "MEN_IDENTI" => "pisos"
                )
            );
        } else if ($idUsuario == 2) { // Empleado
            $menus = array(
                array(
                    "MEN_NOM" => "Dashboard",
                    "MEN_RUTA" => "../home/",
                    "MEN_GRUPO" => "Dashboard",
                    "MEND_PERMI" => "Si",
                    "MEN_IDENTI" => "dashboard"
                ),
                array(
                    "MEN_NOM" => "Habitaciones",
                    "MEN_RUTA" => "../habitaciones/",
                    "MEN_GRUPO" => "Hotel",
                    "MEND_PERMI" => "Si",
                    "MEN_IDENTI" => "habitaciones"
                )
            );
        }
        
        return $menus;
    }
    
    public function get_tipo_usuario_descripcion($idUsuario) {
        try {
            $conectar = parent::conexion();
            parent::set_names();
            
            $sql = "SELECT Descripcion FROM rol WHERE IdRol = ? AND Estado = 1";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $idUsuario);
            $sql->execute();
            
            $result = $sql->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result;
            } else {
                // Si no se encuentra en la base de datos, devolver según el ID
                if ($idUsuario == 1) {  
                    return array('Descripcion' => 'Administrador');
                } else if ($idUsuario == 2) {
                    return array('Descripcion' => 'Empleado');
                } else {
                    return array('Descripcion' => 'Usuario');
                }
            }
        } catch (Exception $e) {
            // En caso de error, devolver según el ID
            if ($idUsuario == 1) {              
                return array('Descripcion' => 'Administrador');
            } else if ($idUsuario == 2) {
                return array('Descripcion' => 'Empleado');
            } else {
                return array('Descripcion' => 'Usuario');
            }
        }
    }
}
?>
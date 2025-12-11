<?php
require_once("../../models/Menu.php");
$menu = new Menu();
/* TODO: Obtener listado de acceso por ROL ID del Usuario */
$datos = $menu->get_menu_x_rol_id($_SESSION["IdRol"]);

// Función para verificar si el usuario tiene acceso a al menos un elemento del grupo
function tieneAccesoGrupo($datos, $grupo) {
    foreach ($datos as $row) {
        if ($row["MEN_GRUPO"] == $grupo && $row["MEND_PERMI"] == "Si") {
            return true;
        }
    }
    return false;
}
?>

<div class="app-menu navbar-menu">

    <div class="navbar-brand-box">

        <a href="index.html" class="logo Logo-dark">
            <span class="logo-sm">
                <img src="../../assets/images/logo-sm.png" alt="" height="42">
            </span>
            <span class="logo-lg">
                <img src="../../assets/images/Logo-dark.png" alt="" height="80">
            </span>
        </a>

    <a href="index.html" class="logo Logo-light">
            <span class="logo-sm">
                <img src="../../assets/images/ogo-sm.png" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="../../assets/images/ogo-light.png" alt="" height="77">
            </span>
        </a>

        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>

    </div>

    <div id="scrollbar">

        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">


                <li class="menu-title"><span data-key="t-menu">Menu</span></li>

                <?php
                foreach ($datos as $row) {
                    if ($row["MEN_GRUPO"] == "Principal" && $row["MEND_PERMI"] == "Si") {
                ?>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="<?php echo $row["MEN_RUTA"]; ?>">
                                <i class="ri-honour-line"></i> <span data-key="t-widgets"><?php echo $row["MEN_NOM"]; ?></span>
                            </a>
                        </li>
                <?php
                    }
                }
                ?>

                <li class="menu-title"><span data-key="t-menu">Modulo de Gestion</span></li>

                <?php if (tieneAccesoGrupo($datos, 'Gestion')): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarGestion" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarGestion">
                        <i class=" ri-hotel-line"></i> <span data-key="t-mantenimiento">Gestion</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarGestion">
                        <ul class="nav nav-sm flex-column">
                            <?php
                            foreach ($datos as $row) {
                                if ($row["MEN_GRUPO"] == "Gestion" && $row["MEND_PERMI"] == "Si") {
                            ?>
                                    <li class="nav-item">
                                        <a href="<?php echo $row["MEN_RUTA"]; ?>" class="nav-link" data-key="t-<?php echo strtolower($row["MEN_NOM"]); ?>">
                                            <?php echo $row["MEN_NOM"]; ?>
                                        </a>
                                    </li>
                            <?php
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </li> <!-- end Gestion Menu -->
                <?php endif; ?>

                <!--  Gestion Tienda -->
          
                <?php if (tieneAccesoGrupo($datos, 'Tienda')): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarTienda" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarTienda">
                        <i class="ri-store-2-line"></i> <span data-key="t-mantenimiento">Tienda</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarTienda">
                        <ul class="nav nav-sm flex-column">
                            <?php
                            foreach ($datos as $row) {
                                if ($row["MEN_GRUPO"] == "Tienda" && $row["MEND_PERMI"] == "Si") {
                            ?>
                                    <li class="nav-item">
                                        <a href="<?php echo $row["MEN_RUTA"]; ?>" class="nav-link" data-key="t-<?php echo strtolower($row["MEN_NOM"]); ?>">
                                            <?php echo $row["MEN_NOM"]; ?>
                                        </a>
                                    </li>
                            <?php
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </li> <!-- end Gestion Tienda -->
                <?php endif; ?>

                <!-- Getiona  -->
                <?php if (tieneAccesoGrupo($datos, 'Mantenimiento')): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarMantenimiento" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMantenimiento">
                        <i class="ri-settings-3-line"></i> <span data-key="t-mantenimiento">Mantenimiento</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarMantenimiento">
                        <ul class="nav nav-sm flex-column">
                            <?php
                            foreach ($datos as $row) {
                                if ($row["MEN_GRUPO"] == "Mantenimiento" && $row["MEND_PERMI"] == "Si") {
                            ?>
                                    <li class="nav-item">
                                        <a href="<?php echo $row["MEN_RUTA"]; ?>" class="nav-link" data-key="t-<?php echo strtolower($row["MEN_NOM"]); ?>">
                                            <?php echo $row["MEN_NOM"]; ?>
                                        </a>
                                    </li>
                            <?php
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </li> <!-- end Mantenimiento Menu -->
                <?php endif; ?>



                <!-- Habitaciones  -->
                <?php if (tieneAccesoGrupo($datos, 'Habitaciones')): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarHabitaciones" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarHabitaciones">
                        <i class="ri-settings-3-line"></i> <span data-key="t-habitaciones">Habitaciones</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarHabitaciones">
                        <ul class="nav nav-sm flex-column">
                            <?php
                            foreach ($datos as $row) {
                                if ($row["MEN_GRUPO"] == "Habitaciones" && $row["MEND_PERMI"] == "Si") {
                            ?>
                                    <li class="nav-item">
                                        <a href="<?php echo $row["MEN_RUTA"]; ?>" class="nav-link" data-key="t-<?php echo strtolower($row["MEN_NOM"]); ?>">
                                            <?php echo $row["MEN_NOM"]; ?>
                                        </a>
                                    </li>
                            <?php
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </li> <!-- end Habitaciones -->
                <?php endif; ?>


                <!-- Mantenimiento Usuarios -->
                <?php if (tieneAccesoGrupo($datos, 'Usuarios')): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarUsuarios" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarUsuarios">
                        <i class="bx bx-user-circle"></i> <span data-key="t-mantenimiento">Usuarios </span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarUsuarios">
                        <ul class="nav nav-sm flex-column">
                            <?php
                            foreach ($datos as $row) {
                                if ($row["MEN_GRUPO"] == "Usuarios" && $row["MEND_PERMI"] == "Si") {
                            ?>
                                    <li class="nav-item">
                                        <a href="<?php echo $row["MEN_RUTA"]; ?>" class="nav-link" data-key="t-<?php echo strtolower($row["MEN_NOM"]); ?>">
                                            <?php echo $row["MEN_NOM"]; ?>
                                        </a>
                                    </li>
                            <?php
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </li> <!-- end Mantenimiento Clientes -->
                <?php endif; ?>

                <!-- Mantenimiento CLientes -->
                 
                <?php
                foreach ($datos as $row) {
                    if ($row["MEN_GRUPO"] == "Clientes" && $row["MEND_PERMI"] == "Si") {
                ?>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="<?php echo $row["MEN_RUTA"]; ?>">
                                <i class="ri-honour-line"></i> <span data-key="t-widgets"><?php echo $row["MEN_NOM"]; ?></span>
                            </a>
                        </li>
                <?php
                    }
                }
                ?>


     <!-- Modulo de Reportes -->

               
                <?php if (tieneAccesoGrupo($datos, 'Reportes')): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarReportes" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarReportes">
                        <i class="bx bxs-report"></i> <span data-key="t-mantenimiento">Reportes </span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarReportes">
                        <ul class="nav nav-sm flex-column">
                            <?php
                            foreach ($datos as $row) {
                                if ($row["MEN_GRUPO"] == "Reportes" && $row["MEND_PERMI"] == "Si") {
                            ?>
                                    <li class="nav-item">
                                        <a href="<?php echo $row["MEN_RUTA"]; ?>" class="nav-link" data-key="t-<?php echo strtolower($row["MEN_NOM"]); ?>">
                                            <?php echo $row["MEN_NOM"]; ?>
                                        </a>
                                    </li>
                            <?php
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </li> <!-- end Mantenimiento Clientes -->
                <?php endif; ?>

               <li class="menu-title"><span data-key="t-menu">Configuraciones</span></li>
               <?php if (tieneAccesoGrupo($datos, 'Configuraciones')): ?>
                <li class="nav-item">

                                   <?php
                foreach ($datos as $row) {
                    if ($row["MEN_GRUPO"] == "Configuraciones" && $row["MEND_PERMI"] == "Si") {
                ?>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="<?php echo $row["MEN_RUTA"]; ?>">
                                <i class=" ri-building-2-fill"></i> <span data-key="t-widgets"><?php echo $row["MEN_NOM"]; ?></span>
                            </a>
                        </li>
                <?php
                    }
                }
                ?>
                </li> <!-- end Configuraciones -->
                <?php endif; ?>                 
        </div>

    </div>

    <div class="sidebar-background"></div>
</div>

<div class="vertical-overlay"></div>
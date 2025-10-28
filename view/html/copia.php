<?php
require_once("../../models/Menu.php");
$menu = new Menu();
$datos = $menu->get_menu_x_rol_id($_SESSION["IdRol"]);
?>

<div class="app-menu navbar-menu">

    <div class="navbar-brand-box">

        <a href="index.html" class="logo logo-dark">
            <span class="logo-sm">
                <img src="../../assets/images/logo-sm.png" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="../../assets/images/logo-dark.png" alt="" height="17">
            </span>
        </a>

        <a href="index.html" class="logo logo-light">
            <span class="logo-sm">
                <img src="../../assets/images/logo-sm.png" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="../../assets/images/logo-light.png" alt="" height="17">
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
                    if ($row["MEN_GRUPO"] == "Dashboard" && $row["MEND_PERMI"] == "Si") {
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

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarTienda" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarTienda">
                        <i class="ri-settings-3-line"></i> <span data-key="t-mantenimiento">Tienda</span>
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
                </li> <!-- end Gestion Menu -->

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

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarReportes" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarReportes">
                        <i class="ri-settings-3-line"></i> <span data-key="t-mantenimiento">Reportes </span>
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
                </li> <!-- end Mantenimiento Reportes -->





                <li class="menu-title"><span data-key="t-menu">Mantenimiento Usuarios</span></li>

                <?php
                foreach ($datos as $row) {
                    if ($row["MEN_GRUPO"] == "Usuarios" && $row["MEND_PERMI"] == "Si") {
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



                <!-- Mantenimiento Clientes -->


            </ul>
            <ul class="navbar-nav" id="navbar-nav">

                <li class="menu-title"><span data-key="t-menu">Menu</span></li>     

            </ul>
        </div>

    </div>

    <div class="sidebar-background"></div>
</div>

<div class="vertical-overlay"></div>
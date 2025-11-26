<?php
require_once("../../config/conexion.php");
require_once("../../config/session.php");
// Verificar autenticación
if (isset($_SESSION["IdUsuario"])) {
?>

    <!doctype html>
    <html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">

    <head>
        <title>Hotel Las Palmeras | Home</title>
        <?php require_once("../html/head.php"); ?>


    </head>

    <body>

        <div id="layout-wrapper">

            <?php require_once("../html/header.php"); ?>

            <?php require_once("../html/menu.php"); ?>

            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0">Recepcion Vender - Vista General de Habitaciones Ocupadas</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Hotel</a></li>
                                            <li class="breadcrumb-item active">Tienda</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>


                        <!-- end page title -->
                        <div class="col-xxl-12">

                            <div class="card">
                                <div class="card-body">

                                    <!-- Nav tabs (dinámico vía JS) -->
                                    <ul id="recepcion-tabs" class="nav nav-pills nav-customs nav-danger mb-3" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#tab-todos" role="tab">TODOS</a>
                                        </li>
                                        <!-- Los tabs de pisos se inyectan por JS -->
                                    </ul>
                                    <!-- Tab panes -->
                                    <div id="recepcion-tabcontent" class="tab-content text-muted">
                                        <!-- Tab: TODOS -->
                                        <div class="tab-pane active" id="tab-todos" role="tabpanel">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="row row-cols-xxl-5 row-cols-xl-4 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1" id="todos-cards-row"></div>
                                                </div><!-- end col -->
                                            </div><!-- end row -->
                                        </div>
                                        <!-- Las pestañas de cada piso se inyectan por JS -->
                                    </div>
                                </div><!-- end card-body -->
                            </div>
                        </div>

                        <!-- container-fluid -->
                    </div>
                    <!-- End Page-content -->

                    <footer class="footer">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-sm-6">
                                    <script>
                                        document.write(new Date().getFullYear())
                                    </script> © Velzon.
                                </div>
                                <div class="col-sm-6">
                                    <div class="text-sm-end d-none d-sm-block">
                                        Design & Develop by Themesbrand
                                    </div>
                                </div>
                            </div>
                        </div>
                    </footer>
                </div>

            </div>

            <?php require_once("../html/js.php"); ?>
            <script src="viewocupadas.js"></script>
    </body>

    </html>
<?php
} else {
    header("Location:" . Conectar::ruta() . "view/404/");
}
?>
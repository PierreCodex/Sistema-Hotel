<?php
require_once("../../config/conexion.php");
if (isset($_SESSION["IdUsuario"])) {
?>

    <!doctype html>
    <html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">

    <head>
        <title>Hotel Las Palmeras | Producto</title>
        <?php require_once("../html/head.php"); ?>
    </head>

    <body>

        <div id="layout-wrapper">

            <?php require_once("../html/header.php"); ?>

            <?php require_once("../html/menu.php"); ?>

            <div class="main-content">
                <div class="page-content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0">Listado de Productos</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Mantenimiento</a></li>
                                            <li class="breadcrumb-item active">Producto</li>    
                                        </ol>
                                    </div>

                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h5 class="card-title mb-0"><i class="ri-store-2-line me-2"></i>Productos Disponibles</h5>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-soft-primary btn-sm filtro-stock active" data-filtro="todos">
                                                        <i class="ri-list-check me-1"></i>Todos
                                                    </button>
                                                    <button type="button" class="btn btn-soft-success btn-sm filtro-stock" data-filtro="disponible">
                                                        <i class="ri-checkbox-circle-line me-1"></i>Con Stock
                                                    </button>
                                                    <button type="button" class="btn btn-soft-danger btn-sm filtro-stock" data-filtro="agotado">
                                                        <i class="ri-close-circle-line me-1"></i>Agotados
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <table id="table_data" class="table table-bordered dt-responsive table-striped align-middle" style="width:100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Descripción</th>
                                                    <th class="text-end">Precio</th>
                                                    <th class="text-center">Stock</th>
                                                    <th class="text-center">Disponibilidad</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <?php require_once("../html/footer.php"); ?>
            </div>

        </div>

        <!-- Modal de mantenimiento no necesario para empleado -->

        <?php require_once("../html/js.php"); ?>
        <script type="text/javascript" src="producto.js"></script>
    </body>

    </html>
<?php
} else {
    header("Location:" . Conectar::ruta() . "view/404/");
}
?>
<?php
require_once("../../config/conexion.php");
if (isset($_SESSION["IdUsuario"])) {
?>

    <!doctype html>
    <html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">

    <head>
        <title>Hotel Las Palmeras | Detalle Recepcion</title>
        <?php require_once("../html/head.php"); ?>
        <link rel="stylesheet" href="password-strength.css">
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
                                    <h4 class="mb-sm-0">Detalle Recepcion</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Mantenimiento</a></li>
                                            <li class="breadcrumb-item active">Recepcion</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-xxl-9">
                                <div class="card" id="demo">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="card-body">
                                                <div class="text-muted">
                                                    <h6 class="mb-2 fw-semibold text-uppercase">Resumen de la Habitacion</h6>
                                                    <div class="pt-3 border-top border-top-dashed mt-4">
                                                    </div>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-lg-3 col-6">
                                                        <p class="text-muted mb-2  fw-semibold">Nombre</p>
                                                        <h5 class="fs-14 mb-0" id="txtnombre"></h5>
                                                    </div>

                                                    <div class="col-lg-3 col-6">
                                                        <p class="text-muted mb-2  fw-semibold">Detalles</p>
                                                        <h5 class="fs-14 mb-0"><span id="txtdetalle"></span></h5>
                                                    </div>

                                                    <div class="col-lg-3 col-6">
                                                        <p class="text-muted mb-2  fw-semibold">Categoria</p>
                                                        <span class="badge badge-soft-success fs-11" id="txtcategoria"></span>
                                                    </div>


                                                    <div class="col-lg-3 col-6">
                                                        <p class="text-muted mb-2  fw-semibold">Estado</p>
                                                        <div class="badge bg-success fs-12" id="txtestado"></div>

                                                    </div>
                                                    <div class="pt-3 border-top border-top-dashed mt-4">
                                                    </div>
                                                    <div class="col-lg-6 col-6">
                                                  
                                                            <label for="cli_id" class="text-muted mb-0">Cliente</label>
                                                            <h5 class="fs-14 mb-0"><span id="txtcliente"></span></h5>
                                                    
                                                    </div>
                                                    <div class="col-lg-3 col-6">
                                                        <label for="cli_id" class="text-muted mb-2">N. Documento</label>
                                                        <h5 class="fs-14 mb-0"><span id="txtdocumento"></span></h5>
                                                    </div>
                                                    <div class="col-lg-3 col-6">
                                                        <label for="cli_id" class="text-muted mb-2">Dirrecion</label>
                                                        <h5 class="fs-14 mb-0"><span id="txtdireccion"></span></h5>

                                                    </div>

                                                </div>

                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <form method="post" id="recepcion_form">
                                                <div class="card-body">
                                                    <div class="text-muted">
                                                        <h6 class="mb-2 fw-semibold text-uppercase">Detalle de Hospedaje</h6>
                                                        <div class="pt-3 border-top border-top-dashed mt-4">
                                                        </div>
                                                    </div>

                                                    <div class="row g-3">
                                                        <div class="col-lg-3 col-6">
                                                            <p class="text-muted mb-2  fw-semibold">Costo de Habitacion</p>
                                                            <div class="input-group">
                                                                <span class="input-group-text">S/.</span>
                                                                <input type="text" id="txtcosto" name="txtcosto" class="form-control" aria-label="Total a pagar" readonly>
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-3 col-6">
                                                            <p class="text-muted mb-2  fw-semibold">Cantidad Adelantada</p>
                                                            <div class="input-group">
                                                                <span class="input-group-text">S/.</span>
                                                                <input type="text" id="Adelanto" name="Adelanto" class="form-control" aria-label="Total a pagar" readonly>
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-3 col-6">
                                                            <p class="text-muted mb-2  fw-semibold">Cantidad Restante</p>
                                                            <div class="input-group">
                                                                <span class="input-group-text">S/.</span>
                                                                <input type="text" id="txtcantidadrestante" name="txtcantidadrestante" class="form-control" aria-label="Total a pagar" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-3 col-6">
                                                            <p class="text-muted mb-2  fw-semibold">Fecha de Salida</p>
                                                            <input type="text" id="fecha_salida" name="fecha_salida" class="form-control"
                                                                data-provider="flatpickr"
                                                                data-enable-time="true"
                                                                data-time_24hr="true"
                                                                data-allow-input="false"
                                                                data-click-opens="false"
                                                                data-date-format="Y-m-d H:i" readonly>
                                                        </div>
                                                    </div>
                                               
                                             
                                                </div>
                                                <div class="card-body">
                                                    <div class="text-muted">
                                                        <h6 class="mb-2 fw-semibold text-uppercase">Servicio a la Habitacion</h6>
                                                        <div class="pt-3 border-top border-top-dashed mt-4">
                                                        </div>
                                                    </div>
                                                    <table id="table_data" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Producto</th>
                                                                <th>Cantidad</th>
                                                                <th>Precio Unitario</th>
                                                                <th>Estado Venta</th>
                                                                <th>Subtotal</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>

                                                            </tbody>
                                                        </table>
                                                    </div>

                                            </form>
                                        </div>


                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                <?php require_once("../html/footer.php"); ?>
            </div>

        </div>

        <?php require_once("../Cliente/mantenimiento.php"); ?>
        <?php require_once("../html/js.php"); ?>

        <script type="text/javascript" src="detallerecepcion.js"></script>
    </body>

    </html>
<?php
} else {
    header("Location:" . Conectar::ruta() . "view/404/");
}
?>
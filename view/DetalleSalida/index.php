<?php
require_once("../../config/conexion.php");
require_once("../../middleware/AuthorizationMiddleware.php");
// Validar que el usuario tenga permiso para acceder a este módulo
AuthorizationMiddleware::requirePermission('gst-recepcion');
?>

    <!doctype html>
    <html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">

    <head>
        <title>Hotel Las Palmeras | Detalle Salida</title>
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
                                    <h4 class="mb-sm-0">Detalle Salida</h4>

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
                                    <div class="row g-3 p-3">
                                        <div class="col-xl-4">
                                            <div class="card">
                                                <div class="card-body p-0">
                                                    <div class="alert alert-secondary rounded-top alert-solid alert-label-icon border-0 rounded-0 m-0 d-flex align-items-center" role="alert">
                                                        <i class="bx bx-bed label-icon"></i>
                                                        <div class="flex-shrink-0">
                                                            <a href="pages-pricing.html" class="text-reset text-decoration-"><b>Habitacion</b></a>
                                                        </div>
                                                    </div>
                                                    <div data-simplebar style="max-height: 364px;" class="p-3">
                                                        <div class="acitivity-timeline acitivity-main">
                                                            <div class="acitivity-item d-flex">
                                                                <div class="flex-shrink-0 avatar-xs acitivity-avatar">
                                                                    <div class="avatar-title bg-soft-success text-success rounded-circle">
                                                                        <i class=" ri-building-2-fill"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <h6 class="mb-1">N Habitacion </h6>
                                                                    <p class="text-muted mb-1" id="txtnombre"></p>

                                                                </div>
                                                            </div>
                                                            <div class="acitivity-item py-3 d-flex">
                                                                <div class="flex-shrink-0 avatar-xs acitivity-avatar">
                                                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                                        <i class="ri-stack-fill"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <h6 class="mb-1">Detalles</h6>
                                                                    <p class="text-muted mb-1" id="txtdetalle"></p>
                                                                    </p>
                                                                </div>

                                                            </div>
                                                            <div class="acitivity-item d-flex">
                                                                <div class="flex-shrink-0 avatar-xs acitivity-avatar">
                                                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                                        <i class="ri-stack-fill"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <h6 class="mb-1">Categoria</h6>
                                                                    <p class="text-muted mb-1" id="txtcategoria"></p>

                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>

                                                </div> <!-- end card-body-->
                                            </div>
                                        </div> <!-- end col-->
                                        <div class="col-xl-4">
                                            <div class="card">
                                                <div class="card-body p-0">
                                                    <div class="alert alert-secondary rounded-top alert-solid alert-label-icon border-0 rounded-0 m-0 d-flex align-items-center" role="alert">
                                                        <i class="bx bxs-user label-icon"></i>
                                                        <div class="flex-shrink-0">
                                                            <a href="pages-pricing.html" class="text-reset text-decoration-"><b>Cliente</b></a>
                                                        </div>
                                                    </div>
                                                    <div data-simplebar style="max-height: 364px;" class="p-3">
                                                        <div class="acitivity-timeline acitivity-main">
                                                            <div class="acitivity-item d-flex">
                                                                <div class="flex-shrink-0 avatar-xs acitivity-avatar">
                                                                    <div class="avatar-title bg-soft-success text-success rounded-circle">
                                                                        <i class="bx bxs-user-circle"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <h6 class="mb-1">Nombres </h6>
                                                                    <p class="text-muted mb-1" id="txtcliente"></p>

                                                                </div>
                                                            </div>
                                                            <div class="acitivity-item py-3 d-flex">
                                                                <div class="flex-shrink-0 avatar-xs acitivity-avatar">
                                                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                                        <i class="bx bx-id-card"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <h6 class="mb-1">N. Documento</h6>
                                                                    <p class="text-muted mb-1" id="txtdocumento"></p>
                                                                    </p>
                                                                </div>

                                                            </div>
                                                            <div class="acitivity-item d-flex">
                                                                <div class="flex-shrink-0 avatar-xs acitivity-avatar">
                                                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                                        <i class="ri-stack-fill"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <h6 class="mb-1">Direccion</h6>
                                                                    <p class="text-muted mb-1" id="txtdireccion"></p>

                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>


                                                </div> <!-- end card-body-->

                                            </div>
                                        </div> <!-- end col-->
                                        <div class="col-xl-4">
                                            <div class="card">
                                                <div class="card-body p-0">
                                                    <div class="alert alert-secondary rounded-top alert-solid alert-label-icon border-0 rounded-0 m-0 d-flex align-items-center" role="alert">
                                                        <i class="bx  bx bx-calendar label-icon"></i>
                                                        <div class="flex-shrink-0">
                                                            <a href="pages-pricing.html" class="text-reset text-decoration-"><b>Entrada/Salida</b></a>
                                                        </div>
                                                    </div>
                                                    <div data-simplebar style="max-height: 364px;" class="p-3">
                                                        <div class="acitivity-timeline acitivity-main">
                                                            <div class="acitivity-item d-flex">
                                                                <div class="flex-shrink-0 avatar-xs acitivity-avatar">
                                                                    <div class="avatar-title bg-soft-success text-success rounded-circle">
                                                                        <i class="bx bx-calendar-event"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <h6 class="mb-1">Entrada </h6>
                                                                    <p class="text-muted mb-1" id="fecha_entrada" name="fecha_entrada"></p>

                                                                </div>
                                                            </div>
                                                            <div class="acitivity-item py-3 d-flex">
                                                                <div class="flex-shrink-0 avatar-xs acitivity-avatar">
                                                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                                        <i class="bx bx-calendar-event"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <h6 class="mb-1">Salida</h6>
                                                                    <p class="text-muted mb-1" id="fecha_salida" name="fecha_salida"></p>
                                                                </div>

                                                            </div>


                                                        </div>
                                                    </div>
                                                </div> <!-- end card-body-->
                                            </div>
                                        </div> <!-- end col-->

                                        <div class="col-lg-12">
                                            <form method="post" id="salida_form">
                                                <div class="card-body">
                                                    <div class="text-muted">
                                                        <h6 class="mb-2 fw-semibold text-uppercase">Detalle de Hospedaje</h6>
                                                        <div class="pt-3 border-top border-top-dashed mt-4"></div>
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
                                                            <p class="text-muted mb-2  fw-semibold">Costo por penalidad</p>
                                                            <div class="input-group">
                                                                <span class="input-group-text">S/.</span>
                                                                <input type="text" id="costo_penalidad" name="costo_penalidad" class="form-control" aria-label="Costo por penalidad">
                                                            </div>
                                                        </div>

                                                        <div class="pt-3 border-top border-top-dashed mt-4"></div>
                                                    </div>
    <div class="text-muted">
                                                        <h6 class="mb-2 fw-semibold text-uppercase">Servicio de Habitacion</h6>
                                                        <div class="pt-3 border-top border-top-dashed mt-4"></div>
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="table_data" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>Producto</th>
                                                                    <th>Cantidad</th>
                                                                    <th>Precio Unitario</th>
                                                                    <th>Estado Venta</th>
                                                                    <th>Subtotal</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody></tbody>
                                                        </table>
                                                    </div>

                                                    <div class="card-body">
                                                        <div class="row g-3">
                                                            <div class="col-lg-3 col-6">
                                                                <p class="text-muted mb-2  fw-semibold">Total a pagar</p>
                                                                <div class="input-group">
                                                                    <span class="input-group-text">S/.</span>
                                                                    <input type="text" id="total_pagar" name="total_pagar" class="form-control" aria-label="Total a pagar" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-3 col-6">
                                                                <p class="text-muted mb-2  fw-semibold">Método de Pago</p>
                                                                <select id="metodo_pago" name="metodo_pago" class="form-select">
                                                                    <option value="EFECTIVO" selected>Efectivo</option>
                                                                    <option value="YAPE">Yape</option>
                                                                    <option value="PLIN">Plin</option>
                                                                    <option value="TARJETA">Tarjeta</option>
                                                                    <option value="TRANSFERENCIA">Transferencia</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-lg-3 col-6 d-flex align-items-end">
                                                                <button type="button" id="btn_confirmar_salida" class="btn btn-primary w-100">Confirmar salida</button>
                                                            </div>
                                                        </div>
                                                    </div>

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

        <?php require_once("modal-comprobante.php"); ?>
        <?php require_once("../html/js.php"); ?>
        <script type="text/javascript" src="detallesalida.js"></script>
    </body>

    </html>

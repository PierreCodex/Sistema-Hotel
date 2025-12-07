<?php
require_once("../../config/conexion.php");
if (isset($_SESSION["IdUsuario"])) {
?>

    <!doctype html>
    <html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">

    <head>
        <title>Hotel Las Palmeras | Habitacion</title>
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
                                    <h4 class="mb-sm-0">Rentar Habitacion</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Mantenimiento</a></li>
                                            <li class="breadcrumb-item active">Habitacion</li>
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
                                                    <h6 class="mb-2 fw-semibold text-uppercase">Datos de la Habitacion</h6>
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
                                                        <p class="text-muted mb-2  fw-semibold">Precio</p>
                                                        <h5 class="fs-14 mb-0"><span id="txtprecio"></span></h5>
                                                    </div>

                                                    <div class="col-lg-3 col-6">
                                                        <p class="text-muted mb-2  fw-semibold">Estado</p>
                                                        <div class="badge bg-success fs-12" id="txtestado"></div>

                                                    </div>

                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="card-body">
                                                <div class="text-muted">
                                                    <h6 class="mb-2 fw-semibold text-uppercase">Datos del cliente</h6>
                                                    <div class="pt-3 border-top border-top-dashed mt-4">
                                                    </div>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-lg-12 col-6">
                                                        <div class="d-flex align-items-center mb-2 gap-2">
                                                            <label for="cli_id" class="text-muted mb-0">Cliente
                                                                <button type="button" id="btnnuevo" class="btn btn-sm btn-outline-primary ms-2">[+ Nuevo]</button>
                                                            </label>
                                                        </div>
                                                        <select id="cli_id" name="cli_id" class="form-control form-select" aria-label="Seleccione">
                                                            <option value='0' selected>Seleccione</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-lg-3 col-6">
                                                        <label for="cli_id" class="text-muted mb-2">Documento</label>
                                                        <input type="text" class="form-control" id="cli_tipo_doc" name="cli_tipo_doc" placeholder="Tipo de Documento" readonly />

                                                    </div>

                                                    <div class="col-lg-3 col-6">
                                                        <label for="cli_id" class="text-muted mb-2">N. Documento</label>
                                                        <input type="text" class="form-control" id="cli_doc" name="cli_doc" placeholder="Número de Documento" readonly />

                                                    </div>
                                                    <div class="col-lg-3 col-6">
                                                        <label for="cli_id" class="text-muted mb-2">Dirrecion</label>
                                                        <input type="text" class="form-control" id="cli_direcc" name="cli_direcc" placeholder="Dirección" readonly />

                                                    </div>

                                                    <div class="col-lg-3 col-6">
                                                        <label for="tipo_comprobante" class="text-muted mb-2">Tipo de Comprobante</label>
                                                        <select id="tipo_comprobante" name="tipo_comprobante" class="form-control form-select">
                                                            <option value="03" selected>Boleta</option>
                                                            <option value="01">Factura</option>
                                                        </select>
                                                        <small class="text-muted" id="info_comprobante">DNI = Boleta | RUC = Factura</small>
                                                    </div>

                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <form method="post" id="recepcion_form">
                                                <div class="card-body">
                                                    <div class="text-muted">
                                                        <h6 class="mb-2 fw-semibold text-uppercase">Datos de la reservación</h6>
                                                        <div class="pt-3 border-top border-top-dashed mt-4">
                                                        </div>
                                                    </div>
                                                    <div class="row g-3">
                                                        <input type="hidden" id="hab_id" name="hab_id" />
                                                        <div class="col-lg-3 col-6">
                                                            <div class="d-flex align-items-center mb-2 gap-2">
                                                                <label for="tar_id" class="text-muted mb-0">Tarifa
                                                                </label>
                                                            </div>
                                                            <select id="tar_id" name="tar_id" class="form-control form-select" aria-label="Seleccione">
                                                                <option value='0' selected>Seleccione</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-lg-2 col-6">
                                                            <label for="precio" class="text-muted mb-2">Precio</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">S/.</span>
                                                                <input type="text" id="precio_inicial" name="precio_inicial" class="form-control" aria-label="Precio en soles" readonly>
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-3 col-6" id="noches_block">
                                                            <label for="precio" class="text-muted mb-2">Cant noches</label>
                                                            <div class="input-step step-secondary full-width">
                                                                <button type="button" class="minus">–</button>
                                                                <input type="number" class="product-quantity" value="1" min="0" max="100" readonly>
                                                                <button type="button" class="plus">+</button>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-3 col-6">
                                                            <h4><span class="badge badge-soft-secondary badge-border">Total a Pagar</span></h4>
                                                            <div class="input-group">
                                                                <span class="input-group-text">S/.</span>
                                                                <input type="text" id="total_pagar" name="total_pagar" class="form-control" aria-label="Total a pagar" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-3 col-6">
                                                            <label for="fecha_entrada" class="text-muted mb-2">Fecha Entrada</label>
                                                            <input type="text" id="fecha_entrada" name="fecha_entrada" class="form-control"
                                                                data-provider="flatpickr"
                                                                data-enable-time="true"
                                                                data-time_24hr="true"
                                                                data-date-format="Y-m-d H:i" readonly>
                                                        </div>

                                                        <div class="col-lg-3 col-6">
                                                            <label for="fecha_salida" class="text-muted mb-2">Fecha y Hora Salida</label>
                                                            <input type="text" id="fecha_salida" name="fecha_salida" class="form-control"
                                                                data-provider="flatpickr"
                                                                data-enable-time="true"
                                                                data-time_24hr="true"
                                                                data-date-format="Y-m-d H:i" readonly>
                                                        </div>
                                                        <div class="col-lg-2 col-6">
                                                            <label for="cli_id" class="text-muted mb-2">Adelanto</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">S/.</span>
                                                                <input type="text" id="adelanto" name="adelanto" class="form-control" aria-label="Precio en soles">
                                                            </div>

                                                        </div>
                                                        <div class="col-lg-6 col-6">
                                                            <label for="observacion" class="form-label">Observaciones</label>
                                                            <textarea class="form-control" id="observacion" name="observacion" placeholder="Ingrese observaciones"
                                                                required></textarea>

                                                        </div>
                                                        <div class="col-lg-3 col-6">



                                                        </div>

                                                        <!-- Checkbox: Recepción por 3 horas -->
                                                        <div class="col-lg-6 col-6 align-self-end">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" id="recepcion_3h" name="recepcion_3h">
                                                                <label class="form-check-label" for="recepcion_3h">
                                                                    Recepción por 3 horas
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <button type="submit" name="action" value="add" class="btn btn-primary">
                                                                <i class="mdi mdi-content-save me-1"></i>Guardar
                                                            </button>
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

        <?php require_once("../Cliente/mantenimiento.php"); ?>
        <?php require_once("../html/js.php"); ?>

        <!-- Validaciones de cliente (centralizado) -->
        <script type="text/javascript" src="../Cliente/mntcliente.js"></script>
        <!-- Lógica específica de recepción -->
        <script type="text/javascript" src="mntrecepcion.js"></script>
    </body>

    </html>
<?php
} else {
    header("Location:" . Conectar::ruta() . "view/404/");
}
?>
<?php
require_once("../../config/conexion.php");
if (isset($_SESSION["IdUsuario"])) {
    // Capturar número de habitación desde la URL
    $habitacionNum = isset($_GET['habitacion']) ? preg_replace('/[^A-Za-z0-9-]/', '', $_GET['habitacion']) : null;
    if ($habitacionNum) {
        $_SESSION['Numero'] = $habitacionNum;
    }
    // Capturar IdHabitacion si se envía en la URL
    if (isset($_GET['hab_id'])) {
        $_SESSION['IdHabitacion'] = (int) $_GET['hab_id'];
    }
?>

    <!doctype html>
    <html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">

    <head>
        <title>Hotel Las Palmeras | Habitacion</title>
        <?php require_once("../html/head.php"); ?>
        <link rel="stylesheet" href="password-strength.css">
    </head>

    ejecutal

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
                                    <h4 class="mb-sm-0">Venta de Productos</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Tienda</a></li>
                                            <li class="breadcrumb-item active">Venta de Productos</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center flex-wrap gap-2">
                                                <div class="flex-grow-1">
                                                    <?php $num = $_GET['habitacion'] ?? ($_SESSION['Numero'] ?? null); ?>
                                                    <h5 class="mb-0">HABITACIÓN N<?php echo $num ? htmlspecialchars($num, ENT_QUOTES, 'UTF-8') : 'Sin habitación'; ?> - AGREGAR PRODUCTOS</h5>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TODO:Datos del Producto -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header align-items-center d-flex">
                                            <h4 class="card-title mb-0 flex-grow-1">Agregar Producto</h4>
                                        </div>

                                        <div class="card-body">
                                            <div class="live-preview">
                                                <div class="row align-items-center g-3">

                                                    <div class="col-lg-3">
                                                        <label for="pro_id" class="form-label">Producto</label>
                                                        <select id="pro_id" name="pro_id" class="form-control form-select" aria-label="Seleccionar">
                                                            <option selected>Seleccione</option>

                                                        </select>
                                                    </div>

                                                    <div class="col-lg-2 col-6">
                                                        <label for="precio" class="text-muted mb-2">Precio</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">S/.</span>
                                                            <input type="text" id="prod_pventa" name="prod_pventa" class="form-control" aria-label="Precio en soles" readonly>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-2 col-6">
                                                        <label for="prod_stock" class="form-label">Stock</label>
                                                        <input type="text" class="form-control" id="prod_stock" name="prod_stock" placeholder="Stock" readonly />
                                                    </div>


                                                    <div class="col-lg-1">
                                                        <label for="detv_cant" class="form-label">Cant.</label>
                                                        <input type="number" class="form-control" id="detv_cant" name="detv_cant" placeholder="0" />
                                                    </div>

                                                    <div class="col-lg-1 d-grid gap-1">
                                                        <label for="comp_cant" class="form-label">&nbsp;</label>
                                                        <button type="button" id="btnagregar" class="btn btn-primary btn-icon waves-effect waves-light"><i class="ri-add-box-line"></i></button>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TODO:Detalle de Venta -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header align-items-center d-flex">
                                            <h4 class="card-title mb-0 flex-grow-1">Detalle de Venta</h4>
                                        </div>

                                        <div class="card-body">
                                            <table id="table_data" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>Producto</th>
                                                        <th>Cant</th>
                                                        <th>Precio</th>
                                                        <th>Importe</th>

                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>

                                            <!-- TODO:Calculo Detalle -->
                                            <table class="table table-borderless table-nowrap align-middle mb-0 ms-auto" style="width:250px">
                                                <tbody>
                                                    <tr>
                                                        <td>Sub Total</td>
                                                        <td class="text-end" id="txtsubtotal">0</td>
                                                    </tr>
                                                    <tr>
                                                        <td>IGV (18%)</td>
                                                        <td class="text-end" id="txtigv">0</td>
                                                    </tr>
                                                    <tr class="border-top border-top-dashed fs-15">
                                                        <th scope="row">Total</th>
                                                        <th class="text-end" id="txttotal">0</th>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <div class="mt-4">
                                                <label for="compr_coment" class="form-label text-muted text-uppercase fw-semibold">Estado de la Venta</label>
                                                <select class="form-select" id="vent_estado" name="vent_estado" required="">
                                                    <option value="">Seleccione</option>
                                                    <option value="PENDIENTE">PENDIENTE</option>
                                                    <option value="PAGADO">PAGADO</option>
                                                </select>
                                            </div>

                                            <div class="hstack gap-2 left-content-end d-print-none mt-4">
                                                <button type="button" id="btnguardar" class="btn btn-success"><i class="ri-printer-line align-bottom me-1"></i> Guardar</button>
                                                <a id="btnlimpiar" class="btn btn-danger"><i class="ri-close-circle-line align-bottom me-1"></i> Cancelar Venta</a>
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

                <script type="text/javascript" src="mntvender.js"></script>
    </body>

    </html>
<?php
} else {
    header("Location:" . Conectar::ruta() . "view/404/");
}
?>
<?php
require_once("../../config/conexion.php");
require_once("../../config/session.php");
// Verificar autenticación
if (isset($_SESSION["IdUsuario"])) {
?>

<!doctype html>
<html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">

<head>
    <title>Hotel Las Palmeras | Historial de Comprobantes</title>
    <?php require_once("../html/head.php"); ?>
    <!-- Flatpickr para selección de fechas -->
 
</head>

<body>

    <div id="layout-wrapper">

        <?php require_once("../html/header.php"); ?>
        <?php require_once("../html/menu.php"); ?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            
            <!-- Título de página -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Historial de Ventas - Mis Recepciones</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="../Home/">Inicio</a></li>
                                <li class="breadcrumb-item active">Historial de Ventas</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Recepciones Finalizadas -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="ri-file-list-3-line me-2"></i>Mis Recepciones Finalizadas
                            </h5>
                            <div>
                                <span class="badge bg-info">Últimas 100 recepciones</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_recepciones" class="table table-hover nowrap align-middle" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Habitación</th>
                                            <th>Cliente</th>
                                            <th>Fecha Entrada</th>
                                            <th>Fecha Salida</th>
                                            <th>Total Ventas</th>
                                            <th>Productos</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Ver Productos Vendidos -->
<div id="modalProductosVendidos" class="modal fade" tabindex="-1" aria-labelledby="modalProductosVendidosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalProductosVendidosLabel">
                    <i class="ri-shopping-bag-line me-2"></i>Productos Vendidos
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Información de la Recepción -->
                <div class="card border border-info mb-3">
                    <div class="card-header bg-info bg-opacity-10">
                        <h6 class="mb-0 text-info">
                            <i class="ri-information-line me-1"></i>Información de la Recepción
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted mb-1">Habitación</label>
                                <p class="fw-semibold mb-0" id="modal_habitacion">-</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted mb-1">Cliente</label>
                                <p class="fw-semibold mb-0" id="modal_cliente">-</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted mb-1">Fecha Salida</label>
                                <p class="mb-0" id="modal_fecha_salida">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos Vendidos -->
                <div class="card border border-success mb-0">
                    <div class="card-header bg-success bg-opacity-10">
                        <h6 class="mb-0 text-success">
                            <i class="ri-shopping-cart-line me-1"></i>Productos Vendidos
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio Unit.</th>
                                        <th class="text-end">Subtotal</th>
                                        <th class="text-center">Estado Venta</th>
                                    </tr>
                                </thead>
                                <tbody id="modal_productos_tbody">
                                    <tr>
                                        <td colspan="5" class="text-center">No hay productos</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th class="text-end" id="modal_total">S/ 0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once("../html/js.php"); ?>
<script src="historial.js"></script>

</body>
</html>
<?php
} else {
    header("Location:" . Conectar::ruta() . "index.php");
    exit();
}
?>

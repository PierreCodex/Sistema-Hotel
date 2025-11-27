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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
                                <h4 class="mb-sm-0">Historial de Comprobantes</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Facturación</a></li>
                                        <li class="breadcrumb-item active">Comprobantes</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <!-- Tarjetas de Resumen -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <p class="text-uppercase fw-medium text-muted mb-0">Total Emitidos</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span id="total_emitidos">0</span></h4>
                                            <span class="text-muted">Comprobantes del período</span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-primary rounded fs-3">
                                                <i class="ri-file-list-3-line text-primary"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <p class="text-uppercase fw-medium text-muted mb-0">Boletas</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span class="badge bg-soft-info text-info">B001</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span id="total_boletas">0</span></h4>
                                            <span class="text-muted">S/ <span id="monto_boletas">0.00</span></span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-info rounded fs-3">
                                                <i class="ri-bill-line text-info"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <p class="text-uppercase fw-medium text-muted mb-0">Facturas</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span class="badge bg-soft-success text-success">F001</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span id="total_facturas">0</span></h4>
                                            <span class="text-muted">S/ <span id="monto_facturas">0.00</span></span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-success rounded fs-3">
                                                <i class="ri-file-text-line text-success"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <p class="text-uppercase fw-medium text-muted mb-0">Total Facturado</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">S/ <span id="total_facturado">0.00</span></h4>
                                            <span class="text-muted">Ingresos del período</span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-warning rounded fs-3">
                                                <i class="ri-money-dollar-circle-line text-warning"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end Tarjetas -->

                    <!-- Filtros -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="ri-filter-3-line align-middle me-1"></i> Filtros de Búsqueda</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Fecha Inicio</label>
                                            <input type="text" class="form-control flatpickr-input" id="fecha_inicio" placeholder="Seleccionar">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Fecha Fin</label>
                                            <input type="text" class="form-control flatpickr-input" id="fecha_fin" placeholder="Seleccionar">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Tipo</label>
                                            <select class="form-select" id="filtro_tipo">
                                                <option value="">Todos</option>
                                                <option value="03">Boleta</option>
                                                <option value="01">Factura</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Estado</label>
                                            <select class="form-select" id="filtro_estado">
                                                <option value="">Todos</option>
                                                <option value="ACEPTADA">Aceptada</option>
                                                <option value="EMITIDA">Emitida</option>
                                                <option value="RECHAZADA">Rechazada</option>
                                                <option value="ANULADA">Anulada</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Período Rápido</label>
                                            <select class="form-select" id="periodo_rapido">
                                                <option value="">Personalizado</option>
                                                <option value="hoy">Hoy</option>
                                                <option value="semana">Esta Semana</option>
                                                <option value="mes" selected>Este Mes</option>
                                                <option value="anio">Este Año</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-primary w-100" onclick="cargarComprobantes()">
                                                <i class="ri-search-line align-middle me-1"></i> Buscar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end Filtros -->

                    <!-- Tabla de Comprobantes -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Lista de Comprobantes</h5>
                                    <div class="flex-shrink-0">
                                        <button type="button" class="btn btn-success btn-sm" onclick="exportarExcel()">
                                            <i class="ri-file-excel-2-line align-middle me-1"></i> Excel
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="exportarPDF()">
                                            <i class="ri-file-pdf-line align-middle me-1"></i> PDF
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="tabla_comprobantes" class="table table-bordered table-striped table-hover align-middle" style="width:100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tipo</th>
                                                    <th>Serie - Número</th>
                                                    <th>Fecha Emisión</th>
                                                    <th>Cliente</th>
                                                    <th>RUC/DNI</th>
                                                    <th>SubTotal</th>
                                                    <th>IGV</th>
                                                    <th>Total</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody_comprobantes">
                                                <!-- Se llena dinámicamente -->
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-info">
                                                    <th colspan="5" class="text-end">TOTALES:</th>
                                                    <th id="footer_subtotal">S/ 0.00</th>
                                                    <th id="footer_igv">S/ 0.00</th>
                                                    <th id="footer_total">S/ 0.00</th>
                                                    <th colspan="2"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end Tabla -->

                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <script>document.write(new Date().getFullYear())</script> © Hotel Las Palmeras.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Sistema de Gestión Hotelera
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

    </div>

    <!-- Modal Ver Detalle -->
    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalleLabel">
                        <i class="ri-file-list-3-line me-2"></i>Detalle del Comprobante
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenido_detalle">
                    <!-- Se carga dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <?php require_once("../html/js.php"); ?>
    
    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    
    <!-- Script del módulo -->
    <script src="historial_comprobantes.js"></script>

</body>
</html>

<?php
} else {
    header("Location:" . Conectar::ruta() . "index.php");
    exit();
}
?>

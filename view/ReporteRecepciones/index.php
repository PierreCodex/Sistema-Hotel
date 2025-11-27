<?php
require_once("../../config/conexion.php");
require_once("../../config/session.php");
// Verificar autenticación
if (isset($_SESSION["IdUsuario"])) {
?>

<!doctype html>
<html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">

<head>
    <title>Hotel Las Palmeras | Reporte de Recepciones</title>
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
                                <h4 class="mb-sm-0">Reporte de Recepciones</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Reportes</a></li>
                                        <li class="breadcrumb-item active">Recepciones</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <!-- Filtros -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="ri-filter-3-line align-middle me-1"></i> Filtros de Búsqueda</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Fecha Inicio</label>
                                            <input type="text" class="form-control flatpickr-input" id="fecha_inicio" placeholder="Seleccionar fecha">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Fecha Fin</label>
                                            <input type="text" class="form-control flatpickr-input" id="fecha_fin" placeholder="Seleccionar fecha">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Estado</label>
                                            <select class="form-select" id="filtro_estado">
                                                <option value="">Todos</option>
                                                <option value="1">Activa (En Curso)</option>
                                                <option value="0">Finalizada</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Período Rápido</label>
                                            <select class="form-select" id="periodo_rapido">
                                                <option value="">Personalizado</option>
                                                <option value="hoy">Hoy</option>
                                                <option value="semana">Esta Semana</option>
                                                <option value="mes">Este Mes</option>
                                                <option value="anio">Este Año</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-primary w-100" onclick="cargarReporte()">
                                                <i class="ri-search-line align-middle me-1"></i> Buscar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end Filtros -->

                    <!-- Tarjetas de Resumen -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <p class="text-uppercase fw-medium text-muted mb-0">Total Recepciones</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-success fs-14 mb-0" id="variacion_recepciones">
                                                <i class="ri-arrow-right-up-line fs-13 align-middle"></i> 0%
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span id="total_recepciones">0</span></h4>
                                            <span class="text-muted">Check-ins realizados</span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-primary rounded fs-3">
                                                <i class="bx bx-door-open text-primary"></i>
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
                                            <p class="text-uppercase fw-medium text-muted mb-0">Recepciones Activas</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span id="recepciones_activas">0</span></h4>
                                            <span class="text-muted">Huéspedes actuales</span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-warning rounded fs-3">
                                                <i class="bx bx-user-check text-warning"></i>
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
                                            <p class="text-uppercase fw-medium text-muted mb-0">Ingresos Hospedaje</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">S/ <span id="ingresos_hospedaje">0.00</span></h4>
                                            <span class="text-muted">Total recaudado</span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-success rounded fs-3">
                                                <i class="bx bx-dollar-circle text-success"></i>
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
                                            <p class="text-uppercase fw-medium text-muted mb-0">Estancia Promedio</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span id="estancia_promedio">0</span> <small class="text-muted">hrs</small></h4>
                                            <span class="text-muted">Duración hospedaje</span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-info rounded fs-3">
                                                <i class="bx bx-time text-info"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end Tarjetas -->

                    <!-- Gráficos -->
                    <div class="row">
                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-header border-0 align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Ocupación por Período</h4>
                                    <div>
                                        <button type="button" class="btn btn-soft-secondary btn-sm" onclick="cambiarVistaGrafico('diario')">Diario</button>
                                        <button type="button" class="btn btn-soft-secondary btn-sm" onclick="cambiarVistaGrafico('semanal')">Semanal</button>
                                        <button type="button" class="btn btn-soft-primary btn-sm" onclick="cambiarVistaGrafico('mensual')">Mensual</button>
                                    </div>
                                </div>
                                <div class="card-body p-0 pb-2">
                                    <div id="grafico_ocupacion" style="height: 350px;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="card card-height-100">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Ocupación por Piso</h4>
                                </div>
                                <div class="card-body">
                                    <div id="grafico_pisos" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end Gráficos -->

                    <!-- Tabla de Recepciones Detalladas -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Detalle de Recepciones</h5>
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
                                        <table id="tabla_recepciones" class="table table-bordered table-striped table-hover align-middle" style="width:100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Cliente</th>
                                                    <th>Habitación</th>
                                                    <th>Piso</th>
                                                    <th>Check-In</th>
                                                    <th>Check-Out</th>
                                                    <th>Tarifa</th>
                                                    <th>Total Pagado</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody_recepciones">
                                                <!-- Se llena dinámicamente -->
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-info">
                                                    <th colspan="7" class="text-end">TOTAL RECAUDADO:</th>
                                                    <th id="footer_total">S/ 0.00</th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end Tabla -->

                    <!-- Tablas secundarias -->
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="ri-hotel-bed-line text-primary me-1"></i> Habitaciones Más Solicitadas</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Habitación</th>
                                                    <th>Categoría</th>
                                                    <th>Recepciones</th>
                                                    <th>Ingresos</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody_habitaciones_top">
                                                <!-- Se llena dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="ri-price-tag-3-line text-warning me-1"></i> Ingresos por Tarifa</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Tarifa</th>
                                                    <th>Recepciones</th>
                                                    <th>Total</th>
                                                    <th>% Participación</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody_tarifas">
                                                <!-- Se llena dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end Tablas secundarias -->

                    <!-- Clientes frecuentes -->
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="ri-user-star-line text-success me-1"></i> Clientes Frecuentes</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Cliente</th>
                                                    <th>Documento</th>
                                                    <th>Visitas</th>
                                                    <th>Total Gastado</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody_clientes_frecuentes">
                                                <!-- Se llena dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="ri-building-line text-info me-1"></i> Ocupación por Piso</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Piso</th>
                                                    <th>Habitaciones</th>
                                                    <th>Recepciones</th>
                                                    <th>Ingresos</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody_pisos">
                                                <!-- Se llena dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end Clientes frecuentes -->

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

    <?php require_once("../html/js.php"); ?>
    
    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    
    <!-- Script del módulo -->
    <script src="reporte_recepciones.js"></script>

</body>
</html>

<?php
} else {
    header("Location:" . Conectar::ruta() . "index.php");
    exit();
}
?>

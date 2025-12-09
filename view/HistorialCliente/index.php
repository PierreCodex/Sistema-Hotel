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
                                <h4 class="mb-sm-0">Historial de Clientes</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Facturación</a></li>
                                        <li class="breadcrumb-item active">Clientes</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="row justify-content-center mb-4">
                                        <div class="col-lg-6">
                                            <div class="row g-2">
                                                <div class="col">
                                                    <div class="position-relative mb-3">
                                                        <input type="text" class="form-control form-control-lg bg-light border-light" id="searchInput" placeholder="Buscar cliente por nombre o documento...">
                                                        <a class="btn btn-link link-success btn-lg position-absolute end-0 top-0" href="javascript:void(0);"></a>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <button type="button" class="btn btn-primary btn-lg waves-effect waves-light" id="btnSearch"><i class="mdi mdi-magnify me-1"></i> Buscar</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <h5 class="fs-16 fw-semibold text-center mb-0">Mostrando resultados para "<span class="text-primary fw-medium fst-italic" id="searchQueryLabel">...</span>"</h5>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#all" role="tab" aria-selected="true">
                                                <i class="ri-search-2-line text-muted align-bottom me-1"></i> Listado
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#timeline" role="tab" aria-selected="false" id="tab-timeline">
                                                <i class="ri-time-line text-muted align-bottom me-1"></i> Línea de Tiempo
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                
                                <div class="card-body p-4">
                                    <div class="tab-content text-muted">
                                        <!-- LIST VIEW TAB -->
                                        <div class="tab-pane active" id="all" role="tabpanel">
                                            <div id="results-list-container">
                                                <!-- Results will be loaded here via JS -->
                                            </div>
                                             <!-- Pagination could go here -->
                                        </div>

                                        <!-- TIMELINE VIEW TAB -->
                                        <div class="tab-pane" id="timeline" role="tabpanel">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div>
                                                        <h5 class="mb-4" id="timeline-client-name">Seleccione un cliente para ver su historial</h5>
                                                        <div class="timeline-2" id="timeline-container">
                                                            <!-- Timeline items will be loaded here via JS -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Templates -->
                    <!-- 1. Search Result Item Template (Client Card in List View) -->
                    <template id="search-result-template">
                        <div class="pb-3 search-result-item border-bottom ">
                            <h5 class="mb-1"><a href="javascript:void(0);" class="client-name-link">Client Name</a></h5>
                            <p class="text-success mb-2 client-doc">Documento: <span>12345678</span></p>
                            <p class="text-muted mb-2 client-address">Dirección: ...</p>
                            <ul class="list-inline d-flex align-items-center g-3 text-muted fs-14 mb-0">
                                <li class="list-inline-item me-3"><i class="ri-hotel-bed-line align-middle me-1"></i><span class="client-visits">0</span> Visitas</li>
                                <li class="list-inline-item me-3">
                                    <button class="btn btn-sm btn-soft-info view-timeline-btn"><i class="ri-time-line align-middle me-1"></i> Ver Historial Completo</button>
                                </li>
                            </ul>
                        </div>
                    </template>

                    <!-- 2. Timeline Item Template (Vertical History) -->
                     <template id="timeline-item-template">
                        <div class="timeline-row-item">
                             <!-- Year/Date Marker (Optional, handled in logic) -->
                            <div class="timeline-year d-none">
                                <p class="visit-full-date">12 Dec 2021</p>
                            </div>
                            
                            <div class="timeline-continue">
                                <div class="row timeline-right">
                                    <div class="col-12">
                                        <p class="timeline-date visit-time-range">
                                            02:35AM to 04:30PM
                                        </p>
                                    </div>
                                    <div class="col-12">
                                        <div class="timeline-box">
                                            <div class="timeline-text">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0">
                                                        <i class="ri-hotel-bed-fill text-primary fs-24"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h5 class="mb-1 visit-room-title">Habitación 101 - Presidencial</h5>
                                                        <p class="text-muted mb-1 visit-summary">Estadía finalizada. Total: <span class="fw-bold visit-total">S/. 150.00</span></p>
                                                        
                                                        <!-- Sección de Productos (Consumo) -->
                                                        <div class="mt-2 p-2 bg-light rounded d-none products-container">
                                                            <h6 class="fs-12 text-uppercase text-muted mb-1">Consumo Adicional</h6>
                                                            <ul class="list-unstyled mb-0 fs-12 products-list">
                                                                <!-- <li>Coca Cola x2 - S/. 10.00</li> -->
                                                            </ul>
                                                        </div>

                                                        <div class="mt-2 d-flex gap-2">
                                                            <span class="badge visit-status-badge">Finalizado</span>
                                                            <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary btn-invoice-link"><i class="ri-file-list-3-line me-1"></i> Ver Comprobante</a>
                                                            <button class="btn btn-sm btn-soft-warning btn-toggle-products"><i class="ri-shopping-cart-2-line me-1"></i> Ver Consumos</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <!-- end Templates -->

    </div>

    <!-- Modal Ver Detalle -->
    <!-- El Modal Detalle ha sido eliminado en favor del Timeline View -->

    <!-- Modal Comprobante -->
    <?php require_once("../DetalleSalida/modal-comprobante.php"); ?>

    <?php require_once("../html/js.php"); ?>
    
    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    
    <!-- Script del módulo -->
    <script src="historial_clientes.js"></script>

</body>
</html>

<?php
} else {
    header("Location:" . Conectar::ruta() . "index.php");
    exit();
}
?>

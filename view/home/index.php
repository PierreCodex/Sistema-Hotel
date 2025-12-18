<?php
require_once("../../config/conexion.php");
require_once("../../middleware/AuthorizationMiddleware.php");
require_once("../../config/session.php");
require_once("../../models/Dashboard.php");

// Validar que el usuario tenga permiso para acceder al dashboard
AuthorizationMiddleware::requirePermission('dashboard');

$dashboard = new Dashboard();
$esAdmin = ($_SESSION["IdRol"] == 1); // Verificar si es Administrador por rol
$usuarioId = $_SESSION["IdUsuario"]; // ID del usuario actual

if($esAdmin) {
    $stats = $dashboard->obtenerEstadisticasAdmin();
    $recepcionesActivas = $dashboard->obtenerRecepcionesActivas(5); // Admin ve todas
    $ultimasVentas = $dashboard->obtenerUltimasVentas(5);
    $ingresos7dias = $dashboard->obtenerIngresosUltimos7Dias();
} else {
    $stats = $dashboard->obtenerEstadisticasEmpleado();
    $recepcionesActivas = $dashboard->obtenerRecepcionesActivas(5, $usuarioId); // Empleado solo ve las suyas
}
?>

<!doctype html>
<html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">
<head>
    <title>Hotel Las Palmeras | Dashboard</title>
    <?php require_once("../html/head.php"); ?>
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
                                <h4 class="mb-sm-0">Dashboard - <?php echo $esAdmin ? 'Administrador' : 'Empleado'; ?></h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Menu</a></li>
                                        <li class="breadcrumb-item active">Dashboard</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alerta de sesión anterior cerrada (US062) -->
                    <?php if (isset($_SESSION["previous_session_closed"]) && $_SESSION["previous_session_closed"] === true): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="ri-alert-line me-2"></i>
                                <strong>Se ha cerrado sesión en otro dispositivo.</strong> Solo se permite tener una sesión activa, por lo que se ha cerrado sesión en otro dispositivo.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                    <?php 
                        // Limpiar el flag después de mostrarlo
                        unset($_SESSION["previous_session_closed"]);
                    endif; 
                    ?>

                    <?php if($esAdmin): // ================== VISTA ADMINISTRADOR ================== ?>
                    
                    <!-- Tarjetas principales -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Habitaciones</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-success fs-14 mb-0">
                                                <i class="ri-arrow-right-up-line fs-13 align-middle"></i> Activas
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                <span class="counter-value" data-target="<?php echo $stats['total_habitaciones']; ?>"><?php echo $stats['total_habitaciones']; ?></span>
                                            </h4>
                                            <a href="../MntHabitacion/" class="text-decoration-underline">Ver habitaciones</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                                <i class="bx bx-building-house text-primary"></i>
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
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Disponibles</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-success fs-14 mb-0">
                                                <i class="ri-checkbox-circle-line fs-13 align-middle"></i>
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                <span class="counter-value text-success" data-target="<?php echo $stats['habitaciones_disponibles']; ?>"><?php echo $stats['habitaciones_disponibles']; ?></span>
                                            </h4>
                                            <a href="../MntRecepcion/" class="text-decoration-underline">Nueva recepción</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-success-subtle rounded fs-3">
                                                <i class="bx bx-door-open text-success"></i>
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
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Ocupadas</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-warning fs-14 mb-0">
                                                <i class="ri-user-fill fs-13 align-middle"></i>
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                <span class="counter-value text-warning" data-target="<?php echo $stats['habitaciones_ocupadas']; ?>"><?php echo $stats['habitaciones_ocupadas']; ?></span>
                                            </h4>
                                            <a href="../ListRecepcion/" class="text-decoration-underline">Ver recepciones</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-warning-subtle rounded fs-3">
                                                <i class="bx bx-bed text-warning"></i>
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
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">En Limpieza</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-info fs-14 mb-0">
                                                <i class="ri-brush-line fs-13 align-middle"></i>
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                <span class="counter-value text-info" data-target="<?php echo $stats['habitaciones_limpieza']; ?>"><?php echo $stats['habitaciones_limpieza']; ?></span>
                                            </h4>
                                            <a href="../MntEstadoHabitacion/" class="text-decoration-underline">Ver estados</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-info-subtle rounded fs-3">
                                                <i class="bx bx-brush text-info"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjetas de ingresos -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate bg-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="fw-medium text-white-50 mb-0">Ingresos Hospedaje Hoy</p>
                                            <h2 class="mt-4 ff-secondary fw-semibold text-white">
                                                S/ <?php echo number_format($stats['ingresos_hospedaje_hoy'], 2); ?>
                                            </h2>
                                        </div>
                                        <div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-white-subtle rounded-circle fs-2">
                                                    <i class="bx bx-hotel text-white"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate bg-success">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="fw-medium text-white-50 mb-0">Ventas Hoy</p>
                                            <h2 class="mt-4 ff-secondary fw-semibold text-white">
                                                S/ <?php echo number_format($stats['ventas_hoy'], 2); ?>
                                            </h2>
                                        </div>
                                        <div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-white-subtle rounded-circle fs-2">
                                                    <i class="bx bx-cart text-white"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate bg-warning">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="fw-medium text-white-50 mb-0">Hospedaje este Mes</p>
                                            <h2 class="mt-4 ff-secondary fw-semibold text-white">
                                                S/ <?php echo number_format($stats['ingresos_hospedaje_mes'], 2); ?>
                                            </h2>
                                        </div>
                                        <div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-white-subtle rounded-circle fs-2">
                                                    <i class="bx bx-calendar text-white"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate bg-danger">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="fw-medium text-white-50 mb-0">Ventas este Mes</p>
                                            <h2 class="mt-4 ff-secondary fw-semibold text-white">
                                                S/ <?php echo number_format($stats['ventas_mes'], 2); ?>
                                            </h2>
                                        </div>
                                        <div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-white-subtle rounded-circle fs-2">
                                                    <i class="bx bx-trending-up text-white"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico y Estadísticas adicionales -->
                    <div class="row">
                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-header border-0 align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Ingresos Últimos 7 Días</h4>
                                    <div class="flex-shrink-0">
                                        <a href="../ReporteVentas/" class="btn btn-soft-primary btn-sm">
                                            <i class="ri-bar-chart-line align-middle me-1"></i> Ver Reporte
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div id="grafico_ingresos" style="height: 320px;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="card card-height-100">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Resumen General</h4>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-light rounded-circle fs-3">
                                                <i class="ri-user-line text-primary"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <p class="text-muted mb-0">Total Clientes</p>
                                            <h5 class="mb-0"><?php echo $stats['total_clientes']; ?></h5>
                                        </div>
                                        <a href="../Cliente/" class="btn btn-sm btn-soft-primary">Ver</a>
                                    </div>

                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-light rounded-circle fs-3">
                                                <i class="ri-team-line text-success"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <p class="text-muted mb-0">Usuarios Activos</p>
                                            <h5 class="mb-0"><?php echo $stats['total_usuarios']; ?></h5>
                                        </div>
                                        <a href="../MntUsuario/" class="btn btn-sm btn-soft-success">Ver</a>
                                    </div>

                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-light rounded-circle fs-3">
                                                <i class="ri-hotel-line text-warning"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <p class="text-muted mb-0">Recepciones del Mes</p>
                                            <h5 class="mb-0"><?php echo $stats['recepciones_mes']; ?></h5>
                                        </div>
                                        <a href="../ReporteRecepciones/" class="btn btn-sm btn-soft-warning">Ver</a>
                                    </div>

                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-light rounded-circle fs-3">
                                                <i class="ri-logout-box-line text-danger"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <p class="text-muted mb-0">Check-outs Pendientes</p>
                                            <h5 class="mb-0 <?php echo $stats['checkouts_pendientes'] > 0 ? 'text-danger' : ''; ?>">
                                                <?php echo $stats['checkouts_pendientes']; ?>
                                            </h5>
                                        </div>
                                        <a href="../ListRecepcion/" class="btn btn-sm btn-soft-danger">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tablas de últimas recepciones y ventas -->
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">
                                        <i class="ri-hotel-bed-line text-primary me-1"></i> Recepciones Activas
                                    </h4>
                                    <a href="../ListRecepcion/" class="btn btn-soft-primary btn-sm">Ver todas</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive table-card">
                                        <table class="table table-hover table-centered align-middle mb-0">
                                            <thead class="text-muted table-light">
                                                <tr>
                                                    <th>Cliente</th>
                                                    <th>Hab.</th>
                                                    <th>Check-out</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(count($recepcionesActivas) > 0): ?>
                                                    <?php foreach($recepcionesActivas as $rec): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-xs flex-shrink-0 me-2">
                                                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                                        <?php echo strtoupper(substr($rec['Cliente'], 0, 1)); ?>
                                                                    </div>
                                                                </div>
                                                                <span class="text-truncate" style="max-width: 120px;"><?php echo $rec['Cliente']; ?></span>
                                                            </div>
                                                        </td>
                                                        <td><span class="badge bg-primary"><?php echo $rec['Habitacion']; ?></span></td>
                                                        <td>
                                                            <?php 
                                                                $fechaSalida = new DateTime($rec['FechaSalida']);
                                                                $hoy = new DateTime();
                                                                $esHoy = $fechaSalida->format('Y-m-d') == $hoy->format('Y-m-d');
                                                                $esPasado = $fechaSalida < $hoy;
                                                            ?>
                                                            <span class="badge <?php echo $esPasado ? 'bg-danger' : ($esHoy ? 'bg-warning' : 'bg-info'); ?>">
                                                                <?php echo $fechaSalida->format('d/m H:i'); ?>
                                                            </span>
                                                        </td>
                                                        <td class="fw-medium">S/ <?php echo number_format($rec['TotalPagado'], 2); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">
                                                            <i class="ri-hotel-line fs-1 d-block mb-2"></i>
                                                            No hay recepciones activas
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">
                                        <i class="ri-shopping-cart-line text-success me-1"></i> Últimas Ventas
                                    </h4>
                                    <a href="../MntVender/" class="btn btn-soft-success btn-sm">Ver todas</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive table-card">
                                        <table class="table table-hover table-centered align-middle mb-0">
                                            <thead class="text-muted table-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Hab.</th>
                                                    <th>Total</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(count($ultimasVentas) > 0): ?>
                                                    <?php foreach($ultimasVentas as $venta): ?>
                                                    <tr>
                                                        <td><code>#<?php echo $venta['IdVenta']; ?></code></td>
                                                        <td><span class="badge bg-info"><?php echo $venta['Habitacion']; ?></span></td>
                                                        <td class="fw-medium">S/ <?php echo number_format($venta['Total'], 2); ?></td>
                                                        <td>
                                                            <?php 
                                                                $estadoClass = [
                                                                    'PAGADO' => 'success',
                                                                    'PENDIENTE' => 'warning',
                                                                    'ANULADO' => 'danger'
                                                                ];
                                                                $clase = $estadoClass[$venta['Estado']] ?? 'secondary';
                                                            ?>
                                                            <span class="badge bg-<?php echo $clase; ?>-subtle text-<?php echo $clase; ?>">
                                                                <?php echo $venta['Estado']; ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">
                                                            <i class="ri-shopping-cart-line fs-1 d-block mb-2"></i>
                                                            No hay ventas registradas
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php else: // ================== VISTA EMPLEADO ================== ?>
                    
                    <!-- Tarjetas para empleado -->
                    <div class="row">
                        <div class="col-xl-4 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Habitaciones Disponibles</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-success fs-14 mb-0">
                                                <i class="ri-checkbox-circle-line fs-13 align-middle"></i>
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                <span class="counter-value text-success" data-target="<?php echo $stats['habitaciones_disponibles']; ?>"><?php echo $stats['habitaciones_disponibles']; ?></span>
                                            </h4>
                                            <a href="../MntRecepcion/" class="text-decoration-underline">Nueva recepción</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-success-subtle rounded fs-3">
                                                <i class="bx bx-door-open text-success"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Habitaciones Ocupadas</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-warning fs-14 mb-0">
                                                <i class="ri-user-fill fs-13 align-middle"></i>
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                <span class="counter-value text-warning" data-target="<?php echo $stats['habitaciones_ocupadas']; ?>"><?php echo $stats['habitaciones_ocupadas']; ?></span>
                                            </h4>
                                            <a href="../ListRecepcion/" class="text-decoration-underline">Ver recepciones</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-warning-subtle rounded fs-3">
                                                <i class="bx bx-bed text-warning"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">En Limpieza</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-info fs-14 mb-0">
                                                <i class="ri-brush-line fs-13 align-middle"></i>
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                <span class="counter-value text-info" data-target="<?php echo $stats['habitaciones_limpieza']; ?>"><?php echo $stats['habitaciones_limpieza']; ?></span>
                                            </h4>
                                            <span class="text-muted">Pendientes</span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-info-subtle rounded fs-3">
                                                <i class="bx bx-brush text-info"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjetas de actividad del día -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate bg-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="fw-medium text-white-50 mb-0">Check-ins Hoy</p>
                                            <h2 class="mt-4 ff-secondary fw-semibold text-white">
                                                <?php echo $stats['checkins_hoy']; ?>
                                            </h2>
                                        </div>
                                        <div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-white-subtle rounded-circle fs-2">
                                                    <i class="bx bx-log-in text-white"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate bg-warning">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="fw-medium text-white-50 mb-0">Check-outs Pendientes</p>
                                            <h2 class="mt-4 ff-secondary fw-semibold text-white">
                                                <?php echo $stats['checkouts_hoy']; ?>
                                            </h2>
                                        </div>
                                        <div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-white-subtle rounded-circle fs-2">
                                                    <i class="bx bx-log-out text-white"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate bg-danger">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="fw-medium text-white-50 mb-0">Ventas Pendientes</p>
                                            <h2 class="mt-4 ff-secondary fw-semibold text-white">
                                                <?php echo $stats['ventas_pendientes']; ?>
                                            </h2>
                                        </div>
                                        <div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-white-subtle rounded-circle fs-2">
                                                    <i class="bx bx-cart text-white"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate bg-success">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="fw-medium text-white-50 mb-0">Estado General</p>
                                            <h2 class="mt-4 ff-secondary fw-semibold text-white">
                                                <i class="ri-checkbox-circle-line"></i> OK
                                            </h2>
                                        </div>
                                        <div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-white-subtle rounded-circle fs-2">
                                                    <i class="bx bx-check text-white"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones Rápidas -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0"><i class="ri-flashlight-line text-warning me-1"></i> Acciones Rápidas</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3 col-6">
                                            <a href="../MntRecepcion/" class="btn btn-primary btn-lg w-100">
                                                <i class="bx bx-plus-circle me-2 d-block d-md-inline fs-3 mb-1 mb-md-0"></i>
                                                <span>Nueva Recepción</span>
                                            </a>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <a href="../ListRecepcion/" class="btn btn-warning btn-lg w-100">
                                                <i class="bx bx-log-out me-2 d-block d-md-inline fs-3 mb-1 mb-md-0"></i>
                                                <span>Check-out</span>
                                            </a>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <a href="../ListVender/" class="btn btn-success btn-lg w-100">
                                                <i class="bx bx-cart me-2 d-block d-md-inline fs-3 mb-1 mb-md-0"></i>
                                                <span>Nueva Venta</span>
                                            </a>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <a href="../MntCliente/" class="btn btn-info btn-lg w-100">
                                                <i class="bx bx-user-plus me-2 d-block d-md-inline fs-3 mb-1 mb-md-0"></i>
                                                <span>Nuevo Cliente</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de recepciones activas para empleado -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">
                                        <i class="ri-hotel-bed-line text-primary me-1"></i> Recepciones Activas
                                    </h4>
                                    <a href="../ListRecepcion/" class="btn btn-soft-primary btn-sm">Ver todas</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-centered align-middle mb-0">
                                            <thead class="text-muted table-light">
                                                <tr>
                                                    <th>Cliente</th>
                                                    <th>Habitación</th>
                                                    <th>Check-in</th>
                                                    <th>Check-out</th>
                                                    <th>Total</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(count($recepcionesActivas) > 0): ?>
                                                    <?php foreach($recepcionesActivas as $rec): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-xs flex-shrink-0 me-2">
                                                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                                        <?php echo strtoupper(substr($rec['Cliente'], 0, 1)); ?>
                                                                    </div>
                                                                </div>
                                                                <?php echo $rec['Cliente']; ?>
                                                            </div>
                                                        </td>
                                                        <td><span class="badge bg-primary fs-6"><?php echo $rec['Habitacion']; ?></span></td>
                                                        <td><?php echo date('d/m/Y H:i', strtotime($rec['FechaEntrada'])); ?></td>
                                                        <td>
                                                            <?php 
                                                                $fechaSalida = new DateTime($rec['FechaSalida']);
                                                                $hoy = new DateTime();
                                                                $esHoy = $fechaSalida->format('Y-m-d') == $hoy->format('Y-m-d');
                                                                $esPasado = $fechaSalida < $hoy;
                                                            ?>
                                                            <span class="badge <?php echo $esPasado ? 'bg-danger' : ($esHoy ? 'bg-warning' : 'bg-success'); ?>">
                                                                <?php echo $fechaSalida->format('d/m/Y H:i'); ?>
                                                            </span>
                                                        </td>
                                                        <td class="fw-semibold">S/ <?php echo number_format($rec['TotalPagado'], 2); ?></td>
                                                        <td>
                                                            <a href="../DetalleRecepcion/index.php?recepcion=<?php echo $rec['IdRecepcion']; ?>" class="btn btn-soft-info btn-sm" title="Ver detalle">
                                                                <i class="ri-eye-line"></i>
                                                            </a>
                                                            <a href="../ListVender/?rec=<?php echo $rec['IdRecepcion']; ?>" class="btn btn-soft-success btn-sm" title="Vender">
                                                                <i class="ri-shopping-cart-line"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">
                                                            <i class="ri-hotel-line fs-1 d-block mb-2"></i>
                                                            No hay recepciones activas
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php endif; ?>

                </div>
            </div>

            <?php require_once("../html/footer.php"); ?>
        </div>

    </div>

    <?php require_once("../html/js.php"); ?>
    
    <?php if($esAdmin): ?>
    <!-- ApexCharts para el gráfico (solo admin) -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Datos del gráfico
        var datosGrafico = <?php echo json_encode($ingresos7dias); ?>;
        
        var opciones = {
            series: [{
                name: 'Hospedaje',
                type: 'column',
                data: datosGrafico.map(d => parseFloat(d.hospedaje))
            }, {
                name: 'Ventas',
                type: 'column',
                data: datosGrafico.map(d => parseFloat(d.ventas))
            }],
            chart: {
                height: 320,
                type: 'bar',
                toolbar: { show: false },
                stacked: false
            },
            colors: ['#405189', '#0ab39c'],
            plotOptions: {
                bar: {
                    columnWidth: '50%',
                    borderRadius: 4
                }
            },
            stroke: {
                width: [0, 0]
            },
            xaxis: {
                categories: datosGrafico.map(d => d.dia)
            },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return 'S/ ' + val.toFixed(0);
                    }
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function(val) {
                        return 'S/ ' + val.toFixed(2);
                    }
                }
            },
            legend: {
                position: 'top'
            },
            fill: {
                opacity: 1
            }
        };

        var chart = new ApexCharts(document.querySelector("#grafico_ingresos"), opciones);
        chart.render();
    });
    </script>
    <?php endif; ?>

</body>
</html>

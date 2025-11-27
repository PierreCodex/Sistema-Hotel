<?php
    require_once("../../config/conexion.php");
    if(isset($_SESSION["IdUsuario"])){
?>

<!doctype html>
<html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">
<head>
    <title>Hotel Las Palmeras | Habitaciones</title>
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
                                <h4 class="mb-sm-0">Listado de Habitaciones</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Listado</a></li>
                                        <li class="breadcrumb-item active">Habitacion</li>
                                    </ol>
                                </div>

                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h5 class="card-title mb-0"><i class="ri-hotel-bed-line me-2"></i>Estado de Habitaciones</h5>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-soft-primary btn-sm filtro-estado active" data-estado="todos">
                                                    <i class="ri-list-check me-1"></i>Todos
                                                </button>
                                                <button type="button" class="btn btn-soft-success btn-sm filtro-estado" data-estado="disponible">
                                                    <i class="ri-checkbox-circle-line me-1"></i>Disponibles
                                                </button>
                                                <button type="button" class="btn btn-soft-danger btn-sm filtro-estado" data-estado="ocupado">
                                                    <i class="ri-forbid-line me-1"></i>Ocupadas
                                                </button>
                                                <button type="button" class="btn btn-soft-warning btn-sm filtro-estado" data-estado="limpieza">
                                                    <i class="ri-brush-line me-1"></i>Limpieza
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="table_data" class="table table-bordered dt-responsive table-striped align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th>N° Hab.</th>
                                                <th>Categoría</th>
                                                <th>Piso</th>
                                                <th>Características</th>
                                                <th>Tarifas</th>
                                                <th>Estado</th>
                                                <th class="text-center">Acción</th>
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

    <?php require_once("../html/js.php"); ?>
   
    <script type="text/javascript" src="habitacion.js"></script>
</body>

</html>
<?php
    }else{
        header("Location:".Conectar::ruta()."view/404/");
    }
?>
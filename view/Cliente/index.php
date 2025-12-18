<?php
    require_once("../../config/conexion.php");
    if(isset($_SESSION["IdUsuario"])){
?>

<!doctype html>
<html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">
<head>
    <title>Hotel Las Palmeras | Cliente</title>
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
                                <h4 class="mb-sm-0">Mantenimiento Cliente</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Mantenimiento</a></li>
                                        <li class="breadcrumb-item active">Cliente</li>
                                    </ol>
                                </div>

                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <button type="button" id="btnnuevo" class="btn btn-primary">
                                        <i class="bx bx-plus me-1"></i> Nuevo Cliente
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="table_data" class="table nowrap align-middle" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>T. Documento</th>
                                                    <th>Documento</th>
                                                    <th>Nombre</th>
                                                    <th>Apellido</th>
                                                    <th>Direccion</th>  
                                                    <th>Estado</th>
                                                    <th></th> <!-- Editar -->
                                                    <th>Activar/Desactivar</th>
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
            </div>

            <?php require_once("../html/footer.php"); ?>
        </div>

    </div>

    <?php require_once("mantenimiento.php"); ?>

    <?php require_once("../html/js.php"); ?>
    <script type="text/javascript">
        // Información del usuario actual para restricciones de rol
        const currentUserRole = <?php echo $_SESSION["IdRol"]; ?>;
        const currentUserId = <?php echo $_SESSION["IdUsuario"]; ?>;
    </script>
    <script type="text/javascript" src="mntcliente.js"></script>
</body>

</html>
<?php
    }else{
        header("Location:".Conectar::ruta()."view/404/");
    }
?>
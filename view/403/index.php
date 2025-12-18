<!doctype html>
<html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">
<head>
    <title>Hotel Las Palmeras | Acceso Denegado</title>
    <?php require_once("../../config/conexion.php"); ?>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../assets/images/favicon.ico">
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../../assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../../assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="../../assets/css/custom.min.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="auth-page-wrapper py-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="bg-overlay"></div>
        <div class="auth-page-content overflow-hidden pt-lg-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-5">
                        <div class="card overflow-hidden">
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <lord-icon
                                        src="https://cdn.lordicon.com/tdrtiskw.json"
                                        trigger="loop"
                                        colors="primary:#405189,secondary:#0ab39c"
                                        style="width:120px;height:120px">
                                    </lord-icon>
                                    
                                    <h1 class="text-primary mb-4">403</h1>
                                    <h4 class="text-uppercase">Acceso Denegado</h4>
                                    <p class="text-muted mb-4">
                                        No tienes permisos para acceder a este módulo.
                                    </p>
                                    
                                    <div class="alert alert-warning" role="alert">
                                        <i class="ri-error-warning-line me-2"></i>
                                        <strong>Atención:</strong> Si crees que deberías tener acceso a este módulo, 
                                        contacta al administrador del sistema.
                                    </div>
                                    
                                    <div class="mt-4">
                                        <a href="<?php echo Conectar::ruta(); ?>view/home/" class="btn btn-success">
                                            <i class="ri-home-4-line me-1"></i> Volver al Inicio
                                        </a>
                                        
                                        <?php if(isset($_SESSION["IdUsuario"])): ?>
                                        <a href="<?php echo Conectar::ruta(); ?>controller/logout.php" class="btn btn-outline-danger">
                                            <i class="ri-logout-box-line me-1"></i> Cerrar Sesión
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
</body>
</html>

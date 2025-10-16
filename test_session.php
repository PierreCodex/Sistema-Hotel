<?php
require_once("config/conexion.php");
require_once("middleware/SessionMiddleware.php");

// Verificar si hay una sesión activa
$authCheck = SessionMiddleware::checkAuthentication();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Sistema de Sesiones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet">
    <style>
        .session-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .status-active { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-expired { color: #dc3545; }
        .countdown { 
            font-size: 1.5em; 
            font-weight: bold; 
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🔐 Prueba del Sistema de Gestión de Sesiones</h3>
                    </div>
                    <div class="card-body">
                        
                        <?php if ($authCheck['authenticated']): ?>
                            <div class="alert alert-success">
                                <h5>✅ Usuario Autenticado</h5>
                                <p><strong>Usuario:</strong> <?php echo $authCheck['user_data']['Nombre'] . ' ' . $authCheck['user_data']['Apellido']; ?></p>
                                <p><strong>Email:</strong> <?php echo $authCheck['user_data']['Correo']; ?></p>
                                <p><strong>Rol ID:</strong> <?php echo $authCheck['user_data']['IdRol']; ?></p>
                            </div>
                            
                            <div class="session-info">
                                <h5>📊 Información de Sesión</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Estado:</strong> 
                                            <span class="status-<?php echo $authCheck['session_status']['status']; ?>">
                                                <?php echo ucfirst($authCheck['session_status']['status']); ?>
                                            </span>
                                        </p>
                                        <p><strong>Tiempo restante:</strong> 
                                            <span class="countdown" data-session-info="time-left">
                                                <?php echo floor($authCheck['session_status']['time_left'] / 60); ?> min
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Mensaje:</strong> <?php echo $authCheck['session_status']['message']; ?></p>
                                        <p><strong>Configuración:</strong> 30 minutos de duración</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h5>🧪 Acciones de Prueba</h5>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-primary" onclick="checkSession()">
                                        Verificar Sesión
                                    </button>
                                    <button type="button" class="btn btn-success" onclick="renewSession()">
                                        Renovar Sesión
                                    </button>
                                    <button type="button" class="btn btn-warning" onclick="simulateExpiration()">
                                        Simular Expiración
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="logout()">
                                        Cerrar Sesión
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h5>📝 Log de Actividad</h5>
                                <div id="activity-log" class="border p-3" style="height: 200px; overflow-y: auto; background: #f8f9fa;">
                                    <small class="text-muted">Los eventos de sesión aparecerán aquí...</small>
                                </div>
                            </div>
                            
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <h5>⚠️ No hay sesión activa</h5>
                                <p>Para probar el sistema de sesiones, primero debe iniciar sesión.</p>
                                <a href="index.php" class="btn btn-primary">Ir al Login</a>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>📋 Características del Sistema de Sesiones</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>🔧 Configuración</h6>
                                <ul class="list-unstyled">
                                    <li>✅ Duración: 30 minutos</li>
                                    <li>✅ Advertencia: 5 minutos antes</li>
                                    <li>✅ Verificación automática cada minuto</li>
                                    <li>✅ Renovación en actividad del usuario</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>🚀 Funcionalidades</h6>
                                <ul class="list-unstyled">
                                    <li>✅ Expiración automática</li>
                                    <li>✅ Notificaciones de advertencia</li>
                                    <li>✅ Renovación manual y automática</li>
                                    <li>✅ Redirección segura al login</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/sweetalert2/sweetalert2.min.js"></script>
    <script src="assets/js/session-manager.js"></script>
    
    <script>
        // Configuración del Session Manager para pruebas
        const sessionConfig = {
            baseUrl: '<?php echo Conectar::ruta(); ?>',
            checkInterval: 10000, // Verificar cada 10 segundos para pruebas
            warningTime: 300, // Advertir 5 minutos antes
            renewUrl: 'middleware/SessionMiddleware.php',
            loginUrl: 'index.php'
        };
        
        let sessionManager;
        
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($authCheck['authenticated']): ?>
            // Inicializar el gestor de sesiones
            sessionManager = new SessionManager(sessionConfig);
            
            // Agregar logging personalizado
            const originalCheckSession = sessionManager.checkSession;
            sessionManager.checkSession = async function() {
                logActivity('Verificando estado de sesión...');
                await originalCheckSession.call(this);
            };
            
            logActivity('Sistema de sesiones inicializado');
            <?php endif; ?>
        });
        
        // Funciones de prueba
        function checkSession() {
            if (sessionManager) {
                logActivity('Verificación manual de sesión solicitada');
                sessionManager.checkSession();
            }
        }
        
        function renewSession() {
            if (sessionManager) {
                logActivity('Renovación manual de sesión solicitada');
                sessionManager.renewSession();
            }
        }
        
        function simulateExpiration() {
            logActivity('Simulando expiración de sesión...');
            if (sessionManager) {
                sessionManager.handleSessionExpired();
            }
        }
        
        function logout() {
            logActivity('Cerrando sesión...');
            window.location.href = 'view/html/logout.php';
        }
        
        // Función para logging
        function logActivity(message) {
            const log = document.getElementById('activity-log');
            if (log) {
                const timestamp = new Date().toLocaleTimeString();
                const entry = document.createElement('div');
                entry.innerHTML = `<small><strong>[${timestamp}]</strong> ${message}</small>`;
                log.appendChild(entry);
                log.scrollTop = log.scrollHeight;
            }
        }
        
        // Actualizar información de sesión cada 5 segundos
        <?php if ($authCheck['authenticated']): ?>
        setInterval(function() {
            fetch('middleware/SessionMiddleware.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=check_session'
            })
            .then(response => response.json())
            .then(data => {
                const timeLeftElement = document.querySelector('[data-session-info="time-left"]');
                if (timeLeftElement && data.time_left) {
                    const minutes = Math.floor(data.time_left / 60);
                    const seconds = data.time_left % 60;
                    timeLeftElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                }
            })
            .catch(error => {
                console.error('Error actualizando información de sesión:', error);
            });
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>
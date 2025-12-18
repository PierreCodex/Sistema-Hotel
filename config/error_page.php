<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Hotel Paris</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 60px 40px;
            text-align: center;
        }
        
        .error-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: white;
        }
        
        h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 20px;
        }
        
        p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .btn-home {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .error-code {
            font-size: 14px;
            color: #999;
            margin-top: 30px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            ⚠️
        </div>
        <h1>¡Oops! Algo salió mal</h1>
        <p>
            Lo sentimos, ha ocurrido un error inesperado en el sistema. 
            Nuestro equipo técnico ha sido notificado y está trabajando para resolverlo.
        </p>
        <p>
            Por favor, intenta nuevamente en unos momentos o contacta con el administrador del sistema si el problema persiste.
        </p>
        <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/SistemaHotel-PHP/'; ?>" class="btn-home">
            Volver al Inicio
        </a>
        <div class="error-code">
            Error ID: <?php echo uniqid('ERR-'); ?> | <?php echo date('Y-m-d H:i:s'); ?>
        </div>
    </div>
</body>
</html>

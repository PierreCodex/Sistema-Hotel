<?php
// Script de depuración temporal para verificar tokens
require_once("../config/conexion.php");
require_once("../models/Usuario.php");

$usuario = new Usuario();

// Obtener todos los usuarios y sus tokens
$conectar = new Conectar();
$conn = $conectar->Conexion();
$sql = "SELECT IdUsuario, Nombre, Correo, session_token, session_created_at FROM usuario WHERE Estado = 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Estado de Tokens de Sesión</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Token</th><th>Creado</th><th>Tiene Token?</th></tr>";

foreach ($usuarios as $user) {
    $tiene_token = $usuario->tiene_session_token_anterior($user['IdUsuario']);
    echo "<tr>";
    echo "<td>{$user['IdUsuario']}</td>";
    echo "<td>{$user['Nombre']}</td>";
    echo "<td>{$user['Correo']}</td>";
    echo "<td>" . (empty($user['session_token']) ? '<em>NULL/Vacío</em>' : substr($user['session_token'], 0, 20) . '...') . "</td>";
    echo "<td>" . ($user['session_created_at'] ?? '<em>NULL</em>') . "</td>";
    echo "<td>" . ($tiene_token ? '<strong style="color:green">SÍ</strong>' : '<strong style="color:red">NO</strong>') . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<br><h3>Limpiar todos los tokens (para testing)</h3>";
echo "<form method='post'>";
echo "<button type='submit' name='limpiar' value='1'>Limpiar Todos los Tokens</button>";
echo "</form>";

if (isset($_POST['limpiar'])) {
    $sql = "UPDATE usuario SET session_token = NULL, session_created_at = NULL";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    echo "<p style='color:green'>✅ Tokens limpiados. <a href=''>Recargar</a></p>";
}
?>

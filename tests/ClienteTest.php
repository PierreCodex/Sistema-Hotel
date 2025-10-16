<?php
use PHPUnit\Framework\TestCase;

class ClienteTest extends TestCase
{
    public function testInsertarYRecuperarCliente()
    {
        $pdo = new PDO('mysql:host=localhost;dbname=db-hotel', 'root', '');
        $documento = '68653241';
        $correo = 'gael@gmail.com';

        // Insertar cliente
        $stmt = $pdo->prepare("INSERT INTO cliente (TipoDocumento, Documento, Nombre, Apellido, Correo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['DNI', $documento, 'Juann', 'Pérezz', $correo]);

        // Recuperar cliente por documento
        $stmt = $pdo->prepare("SELECT * FROM cliente WHERE Documento = ?");
        $stmt->execute([$documento]);
        $cliente = $stmt->fetch();

        $this->assertEquals('Juan', $cliente['Nombre']);
        $this->assertEquals('Pérez', $cliente['Apellido']);
        $this->assertEquals($correo, $cliente['Correo']);
    }
}
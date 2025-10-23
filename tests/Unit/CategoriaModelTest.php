<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../models/Categoria.php';

class CategoriaModelTest extends TestCase
{
    public function testVerificarCategoriaExistenteConResultadoFalse()
    {
        $categoria = $this->getMockBuilder(Categoria::class)
            ->onlyMethods(['conexion'])
            ->getMock();

        // Simulamos la conexión PDO
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->method('prepare')->willReturn($stmt);
        $stmt->method('fetch')->willReturn(['total' => 0]);

        $categoria->method('conexion')->willReturn($pdo);

        $resultado = $categoria->verificar_categoria_existente('Limpieza');
        $this->assertFalse($resultado, "Debería retornar false si la categoría no existe");
    }
}

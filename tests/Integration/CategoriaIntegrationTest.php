<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../models/Categoria.php';

class CategoriaIntegrationTest extends TestCase
{
    private $categoria;

    protected function setUp(): void
    {
        $this->categoria = new Categoria();
    }

    public function testInsertCategoria()
    {
        $resultado = $this->categoria->insert_categoria('Servicios de Spa');
        $this->assertIsArray($resultado, "El resultado debe ser un array");
    }

    public function testListarCategorias()
    {
        $resultado = $this->categoria->get_categoria();
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado, "Debe devolver al menos una categoría");
    }

    public function testActualizarCategoria()
    {
        $resultado = $this->categoria->update_categoria(1, 'Restaurante Gourmet');
        $this->assertIsArray($resultado);
    }
}

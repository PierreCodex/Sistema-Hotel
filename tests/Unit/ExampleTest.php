<?php

use PHPUnit\Framework\TestCase;

/**
 * Prueba de ejemplo para verificar que PHPUnit funciona correctamente
 */
class ExampleTest extends TestCase
{
    /**
     * Prueba básica que siempre debe pasar
     */
    public function testBasicAssertion(): void
    {
        $this->assertTrue(true);
        $this->assertEquals(2, 1 + 1);
        $this->assertIsString("Hello World");
    }

    /**
     * Prueba de operaciones matemáticas
     */
    public function testMathOperations(): void
    {
        $this->assertEquals(4, 2 + 2);
        $this->assertEquals(6, 2 * 3);
        $this->assertEquals(2, 4 / 2);
        $this->assertEquals(1, 5 % 2);
    }

    /**
     * Prueba de arrays
     */
    public function testArrayOperations(): void
    {
        $array = [1, 2, 3, 4, 5];
        
        $this->assertCount(5, $array);
        $this->assertContains(3, $array);
        $this->assertNotContains(6, $array);
        $this->assertEquals([1, 2, 3, 4, 5], $array);
    }

    /**
     * Prueba de strings
     */
    public function testStringOperations(): void
    {
        $string = "Sistema Hotel PHP";
        
        $this->assertStringContainsString("Hotel", $string);
        $this->assertStringStartsWith("Sistema", $string);
        $this->assertStringEndsWith("PHP", $string);
        $this->assertEquals(17, strlen($string));
    }
}
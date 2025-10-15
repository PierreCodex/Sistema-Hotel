<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Limpiar sesiones antes de cada prueba
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
        } else if (session_status() === PHP_SESSION_NONE) {
            // Solo iniciar sesión si no hay una activa
            @session_start();
        }
        
        // Asegurar que $_SESSION esté disponible
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
    }

    protected function tearDown(): void
    {
        // Limpiar después de cada prueba
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            // No destruir la sesión completamente para evitar conflictos
        }
        
        // Limpiar variables POST
        $_POST = [];
        
        parent::tearDown();
    }

    /**
     * Helper para simular datos POST
     */
    protected function simulatePost($data)
    {
        $_POST = $data;
    }

    /**
     * Helper para limpiar datos POST
     */
    protected function clearPost()
    {
        $_POST = [];
    }

    /**
     * Helper para verificar variables de sesión
     */
    protected function assertSessionHas($key, $value = null)
    {
        $this->assertArrayHasKey($key, $_SESSION, "La sesión no contiene la clave: {$key}");
        
        if ($value !== null) {
            $this->assertEquals($value, $_SESSION[$key], "El valor de la sesión para {$key} no coincide");
        }
    }

    /**
     * Helper para verificar que una variable de sesión no existe
     */
    protected function assertSessionMissing($key)
    {
        $this->assertArrayNotHasKey($key, $_SESSION, "La sesión contiene la clave inesperada: {$key}");
    }
}
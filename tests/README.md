# Pruebas de Autenticación - Sistema Hotel PHP

Este directorio contiene las pruebas unitarias para el sistema de autenticación del proyecto Sistema Hotel PHP, implementadas con PHPUnit.

## Estructura de Pruebas

### Pruebas Implementadas

#### 1. **testLoginWithValidCredentials()**
- **Propósito**: Verificar que un usuario con credenciales válidas pueda iniciar sesión exitosamente
- **Validaciones**:
  - Variables de sesión se establecen correctamente
  - Datos del usuario se almacenan en la sesión
  - Email se guarda correctamente

#### 2. **testLoginWithInvalidCredentials()**
- **Propósito**: Verificar que credenciales incorrectas no permitan el acceso
- **Validaciones**:
  - No se establecen variables de sesión
  - Sistema rechaza credenciales incorrectas
  - Seguridad del login

#### 3. **testLoginWithEmptyFields()**
- **Propósito**: Validar el manejo de campos vacíos
- **Escenarios probados**:
  - Ambos campos vacíos
  - Solo email vacío
  - Solo contraseña vacía
- **Validaciones**:
  - Sistema rechaza campos vacíos
  - No se procesan logins incompletos

#### 4. **testSessionVariablesAfterLogin()**
- **Propósito**: Verificar que las variables de sesión se establecen correctamente
- **Validaciones**:
  - Todas las variables requeridas existen
  - Tipos de datos correctos
  - Valores específicos
  - Formato de email válido

## Archivos de Prueba

### `SimpleAuthenticationTest.php`
Contiene las pruebas principales de autenticación sin dependencias complejas de sesión.

### `AuthenticationTest.php`
Versión más avanzada con simulación del modelo Usuario.

### `AuthenticationWithMockTest.php`
Implementación con mocks de base de datos para pruebas más realistas.

## Cómo Ejecutar las Pruebas

### Instalar Dependencias
```bash
composer install
```

### Ejecutar Todas las Pruebas
```bash
vendor/bin/phpunit --testdox
```

### Ejecutar Solo Pruebas de Autenticación
```bash
vendor/bin/phpunit tests/Unit/SimpleAuthenticationTest.php --testdox
```

### Ejecutar con Cobertura de Código
```bash
vendor/bin/phpunit --coverage-html coverage
```

## Configuración

### `phpunit.xml`
Archivo de configuración principal que define:
- Directorios de pruebas
- Bootstrap de inicialización
- Configuración de cobertura
- Variables de entorno

### `bootstrap.php`
Archivo de inicialización que:
- Carga autoloader de Composer
- Configura entorno de pruebas
- Incluye archivos necesarios
- Define funciones helper

### `TestCase.php`
Clase base que proporciona:
- Configuración común para todas las pruebas
- Métodos helper para sesiones
- Limpieza entre pruebas
- Assertions personalizadas

## Resultados de Pruebas

Las pruebas actuales incluyen:
- ✔ Login with valid credentials
- ✔ Login with invalid credentials  
- ✔ Login with empty fields
- ✔ Session variables after login
- ✔ Login without submission
- ✔ Email format validation
- ✔ Different user roles

**Total**: 7 pruebas, 47 assertions - ✅ TODAS PASANDO

## Extensión de Pruebas

Para agregar nuevas pruebas:

1. Crear nueva clase en `tests/Unit/`
2. Extender de `Tests\TestCase`
3. Implementar métodos de prueba con prefijo `test`
4. Usar assertions de PHPUnit
5. Documentar el propósito de cada prueba

### Ejemplo de Nueva Prueba
```php
public function testNewFeature()
{
    // Arrange
    $data = ['key' => 'value'];
    
    // Act
    $result = $this->processData($data);
    
    // Assert
    $this->assertEquals('expected', $result);
}
```

## Mejores Prácticas

1. **Nomenclatura**: Usar nombres descriptivos para métodos de prueba
2. **Estructura AAA**: Arrange, Act, Assert
3. **Aislamiento**: Cada prueba debe ser independiente
4. **Limpieza**: Limpiar estado entre pruebas
5. **Documentación**: Comentar el propósito de cada prueba

## Troubleshooting

### Problemas Comunes

1. **Error de sesión**: Verificar que no hay conflictos de session_start()
2. **Autoload**: Asegurar que composer install se ejecutó
3. **Permisos**: Verificar permisos de escritura en directorio de pruebas

### Logs y Debug

Para debug detallado:
```bash
vendor/bin/phpunit --verbose --debug
```
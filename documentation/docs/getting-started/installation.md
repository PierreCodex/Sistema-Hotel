---
sidebar_position: 1
---

# Instalación

Guía completa para instalar y configurar Sistema Hotel en tu entorno local.

## Requisitos del Sistema

| Requisito | Versión Mínima |
|-----------|----------------|
| PHP | 8.0+ |
| MySQL | 5.7+ |
| Apache | 2.4+ |
| Composer | 2.0+ |
| Node.js | 18+ (para tests JS) |

:::tip Recomendado
Usa **XAMPP** para una instalación rápida que incluye PHP, MySQL y Apache preconfigurados.
:::

## Pasos de Instalación

### 1. Clonar el Repositorio

```bash
git clone https://github.com/PierreCodex/Sistema-Hotel.git
cd Sistema-Hotel
```

### 2. Instalar Dependencias PHP

```bash
composer install
```

### 3. Configurar la Base de Datos

1. Crea una base de datos MySQL llamada `db_hotel`
2. Importa el archivo SQL:

```bash
mysql -u root -p db_hotel < docs/sql/db-hotel.sql
```

### 4. Configurar Conexión

Edita el archivo de configuración en `config/database.php`:

```php
<?php
return [
    'host' => 'localhost',
    'database' => 'db_hotel',
    'username' => 'root',
    'password' => ''
];
```

### 5. Iniciar el Servidor

Si usas XAMPP:
1. Inicia Apache y MySQL desde el panel de control
2. Accede a `http://localhost/SistemaHotel-PHP`

## Verificar Instalación

Deberías ver la página de login del sistema. Las credenciales por defecto son:

| Usuario | Contraseña |
|---------|------------|
| admin | admin123 |

## Solución de Problemas

### Error de conexión a base de datos
- Verifica que MySQL esté corriendo
- Confirma las credenciales en `config/database.php`

### Página en blanco
- Revisa los logs de Apache en `xampp/apache/logs/error.log`
- Habilita errores PHP: `display_errors = On`

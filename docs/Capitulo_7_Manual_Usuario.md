# CAPÍTULO 7: MANUALES Y CÓDIGO FUENTE

## 7.1 Manual de Usuario

### 7.1.1 Guía de Instalación

#### Requisitos del Sistema

Para instalar y ejecutar el Sistema Hotel se requieren los siguientes componentes:

**Requisitos de Software:**

| Componente | Versión Mínima | Descripción |
|------------|----------------|-------------|
| PHP | 8.0 o superior | Lenguaje de programación del backend |
| MySQL | 5.7 o superior | Sistema de gestión de base de datos |
| Apache | 2.4 o superior | Servidor web |
| Composer | 2.0 o superior | Gestor de dependencias PHP |
| Node.js | 18 o superior | Para pruebas JavaScript (opcional) |

**Requisitos de Hardware:**

- Procesador: Intel Core i3 o equivalente
- Memoria RAM: 4 GB mínimo (8 GB recomendado)
- Espacio en disco: 500 MB para la aplicación + espacio para base de datos
- Conexión a internet: Para instalación de dependencias

#### Pasos de Instalación

**Paso 1: Instalación de XAMPP**

1. Descargar XAMPP desde https://www.apachefriends.org/
2. Ejecutar el instalador y seguir las instrucciones
3. Seleccionar los componentes: Apache, MySQL, PHP
4. Completar la instalación en la ruta por defecto (C:\xampp)

**Paso 2: Clonar el Repositorio**

Abrir una terminal en la carpeta htdocs de XAMPP:

```
cd C:\xampp\htdocs
git clone https://github.com/PierreCodex/Sistema-Hotel.git
cd Sistema-Hotel
```

**Paso 3: Instalar Dependencias**

Ejecutar Composer para instalar las dependencias PHP:

```
composer install
```

Si se requieren pruebas JavaScript:

```
npm install
```

**Paso 4: Configurar la Base de Datos**

1. Iniciar Apache y MySQL desde el panel de control de XAMPP
2. Acceder a phpMyAdmin: http://localhost/phpmyadmin
3. Crear una nueva base de datos llamada `db_hotel`
4. Importar el archivo SQL ubicado en `docs/sql/db-hotel.sql`

**Paso 5: Configurar la Conexión a Base de Datos**

Editar el archivo `config/conexion.php` con las credenciales de la base de datos:

```php
<?php
class Conectar {
    protected $dbh;
    
    protected function Conexion() {
        try {
            $conectar = $this->dbh = new PDO(
                "mysql:host=localhost;dbname=db_hotel",
                "root",
                ""
            );
            return $conectar;
        } catch (Exception $e) {
            print "¡Error BD!: " . $e->getMessage();
            die();
        }
    }
}
?>
```

**Paso 6: Verificar la Instalación**

1. Abrir el navegador web
2. Acceder a: http://localhost/Sistema-Hotel
3. Debe aparecer la pantalla de inicio de sesión

**Credenciales por Defecto:**

- Usuario: admin@hotel.com
- Contraseña: admin123

#### Solución de Problemas de Instalación

**Problema: Error de conexión a base de datos**

Solución:
- Verificar que MySQL esté corriendo en XAMPP
- Confirmar que el nombre de la base de datos sea correcto
- Revisar usuario y contraseña en config/conexion.php

**Problema: Página en blanco al acceder**

Solución:
- Habilitar visualización de errores en php.ini: `display_errors = On`
- Revisar logs de Apache en `xampp/apache/logs/error.log`
- Verificar que la extensión PDO esté habilitada en PHP

**Problema: Composer no se reconoce**

Solución:
- Instalar Composer desde https://getcomposer.org/
- Agregar Composer a las variables de entorno del sistema
- Reiniciar la terminal después de la instalación

---

### 7.1.2 Guía de Uso

#### 7.1.2.1 Inicio de Sesión

**Acceso al Sistema:**

1. Abrir navegador web (Chrome, Firefox, Edge)
2. Ingresar la URL: http://localhost/Sistema-Hotel
3. Aparecerá la pantalla de inicio de sesión

**Proceso de Autenticación:**

1. Ingresar correo electrónico del usuario
2. Ingresar contraseña
3. Hacer clic en el botón "Iniciar Sesión"
4. El sistema validará las credenciales
5. Si son correctas, redirigirá al dashboard principal

**Códigos de Error de Login:**

| Código | Mensaje | Solución |
|--------|---------|----------|
| 1 | Credenciales incorrectas | Verificar usuario y contraseña |
| 2 | Campos vacíos | Completar ambos campos |
| 3 | Email vacío | Ingresar correo electrónico |
| 4 | Contraseña vacía | Ingresar contraseña |
| 5 | Email con formato inválido | Usar formato correcto de email |

**Cerrar Sesión:**

1. Hacer clic en el menú de usuario (esquina superior derecha)
2. Seleccionar "Cerrar Sesión"
3. El sistema cerrará la sesión y redirigirá al login

#### 7.1.2.2 Gestión de Habitaciones

**Crear Nueva Habitación:**

1. Acceder al menú "Habitaciones" → "Mantenimiento"
2. Hacer clic en el botón "Nueva Habitación"
3. Completar el formulario:
   - Número de habitación (obligatorio, único)
   - Descripción (obligatorio)
   - Seleccionar piso (obligatorio)
   - Seleccionar categoría (obligatorio)
   - Estado inicial (por defecto: Disponible)
4. Hacer clic en "Guardar"

**Gestionar Categorías:**

Las categorías definen el tipo de habitación:

- Simple: 1 cama individual
- Doble: 2 camas individuales
- Matrimonial: 1 cama matrimonial
- Suite: Habitación premium con sala de estar

**Procedimiento:**

1. Ir a "Habitaciones" → "Categorías"
2. Clic en "Nueva Categoría"
3. Ingresar nombre de la categoría
4. Guardar

**Gestionar Pisos:**

1. Ir a "Habitaciones" → "Pisos"
2. Clic en "Nuevo Piso"
3. Ingresar nombre del piso (ej: "Piso 1", "Piso 2")
4. Guardar

**Asignar Tarifas a Habitaciones:**

El sistema permite asignar múltiples tarifas con vigencia temporal:

1. En la lista de habitaciones, clic en el botón "Tarifas" (ícono de dinero)
2. Seleccionar la tarifa a asignar
3. Definir fecha de inicio de vigencia
4. Opcionalmente, definir fecha de fin
5. Guardar

**Ejemplo de Tarifas:**

- Tarifa "3 Horas": S/. 50.00
- Tarifa "Por Noche": S/. 120.00
- Tarifa "Temporada Alta": S/. 180.00 (vigente del 15/12 al 15/01)

**Estados de Habitación:**

| Estado | Descripción | Uso |
|--------|-------------|-----|
| Disponible | Lista para reservar | Habitación limpia y lista |
| Ocupada | Cliente hospedado | Durante la estadía |
| Limpieza | En proceso de limpieza | Después del check-out |
| Mantenimiento | Requiere reparación | Fuera de servicio |

#### 7.1.2.3 Proceso de Recepción (Check-in / Check-out)

**Realizar Check-in:**

El sistema soporta dos tipos de estadía:

**A) Check-in por 3 Horas:**

1. Ir a "Recepción" → "Nueva Recepción"
2. Seleccionar habitación disponible
3. Registrar datos del cliente:
   - DNI/Pasaporte (obligatorio)
   - Nombres (obligatorio)
   - Apellidos (obligatorio)
   - Teléfono (opcional)
   - Email (opcional)
4. Seleccionar tarifa "3 Horas"
5. El sistema calculará automáticamente la hora de salida (hora actual + 3 horas)
6. Ingresar adelanto (monto pagado por adelantado)
7. Seleccionar tipo de comprobante:
   - Boleta (para personas naturales)
   - Factura (para empresas, requiere RUC)
8. Confirmar check-in

**B) Check-in por Noche:**

1. Seguir pasos 1-3 del check-in por 3 horas
2. Seleccionar tarifa "Por Noche"
3. Definir fecha y hora de salida personalizada
4. Continuar con pasos 6-8

**Realizar Check-out:**

1. Ir a "Recepción" → "Recepciones Activas"
2. Buscar la habitación del cliente
3. Hacer clic en "Check-out"
4. El sistema mostrará:
   - Precio inicial de la habitación
   - Consumos adicionales (productos/servicios)
   - Adelanto pagado
   - Saldo pendiente
5. Si hay salida tardía, agregar penalidad
6. Confirmar el pago total
7. El sistema:
   - Marca la recepción como finalizada
   - Cambia el estado de la habitación a "Limpieza"
   - Genera el comprobante de pago

**Cálculo de Penalidades:**

Si el cliente excede el tiempo de estadía:

- Por 3 horas: Se cobra por hora adicional
- Por noche: Se cobra día adicional completo

#### 7.1.2.4 Facturación

**Generar Boleta:**

1. Durante el check-out, seleccionar "Boleta" como tipo de comprobante
2. Verificar datos del cliente (DNI)
3. El sistema genera automáticamente:
   - Subtotal (precio habitación + consumos)
   - IGV (18%)
   - Total
4. Imprimir o descargar PDF

**Generar Factura:**

1. Durante el check-out, seleccionar "Factura"
2. Ingresar datos fiscales:
   - RUC de la empresa
   - Razón social
   - Dirección fiscal
3. El sistema calcula los montos
4. Imprimir o descargar PDF

**Historial de Comprobantes:**

1. Ir a "Facturación" → "Historial"
2. Filtrar por:
   - Rango de fechas
   - Tipo de comprobante
   - Cliente
3. Ver, reimprimir o anular comprobantes

#### 7.1.2.5 Productos y Ventas (Room Service)

**Registrar Productos:**

1. Ir a "Productos" → "Mantenimiento"
2. Clic en "Nuevo Producto"
3. Completar:
   - Nombre del producto
   - Descripción
   - Precio de venta
   - Stock inicial
4. Guardar

**Registrar Venta a Habitación:**

1. Ir a "Ventas" → "Nueva Venta"
2. Seleccionar la habitación ocupada
3. El sistema carga la recepción activa
4. Agregar productos:
   - Seleccionar producto del catálogo
   - Ingresar cantidad
   - El sistema valida stock disponible
   - Agregar a la venta
5. Revisar el total (incluye IGV)
6. Seleccionar forma de pago:
   - "Pagado": Se cobra inmediatamente
   - "Pendiente": Se cobra al check-out
7. Confirmar venta

**Estados de Venta:**

- Borrador: Venta en proceso, no confirmada
- Pendiente: Confirmada, se cobrará al check-out
- Pagado: Cobrada completamente

#### 7.1.2.6 Administración de Usuarios y Roles

**Crear Usuario:**

1. Ir a "Administración" → "Usuarios"
2. Clic en "Nuevo Usuario"
3. Completar formulario:
   - Nombre
   - Apellido
   - DNI (único)
   - Correo electrónico (único)
   - Contraseña
   - Rol (Administrador, Recepcionista, Empleado)
4. Guardar

**Gestionar Roles:**

1. Ir a "Administración" → "Roles"
2. Ver roles existentes o crear nuevo
3. Para configurar permisos:
   - Clic en botón "Permisos" del rol
   - Habilitar/deshabilitar acceso a cada módulo
   - Guardar cambios

**Roles Típicos:**

| Rol | Permisos |
|-----|----------|
| Administrador | Acceso completo a todos los módulos |
| Recepcionista | Habitaciones, recepción, facturación, productos |
| Empleado | Solo vista de habitaciones y registro de ventas |

---

### 7.1.3 Resolución de Problemas Comunes

#### Problemas de Autenticación

**Problema: No puedo iniciar sesión con mis credenciales**

Causas posibles:
- Credenciales incorrectas
- Usuario desactivado
- Sesión bloqueada

Soluciones:
1. Verificar que el correo y contraseña sean correctos
2. Contactar al administrador para verificar estado de la cuenta
3. Solicitar restablecimiento de contraseña
4. Verificar que no haya espacios antes o después del correo

**Problema: La sesión se cierra automáticamente**

Causas:
- Tiempo de inactividad excedido
- Configuración de sesión en el servidor

Soluciones:
1. Volver a iniciar sesión
2. Mantener actividad en el sistema
3. Contactar al administrador para ajustar tiempo de sesión

#### Problemas en Gestión de Habitaciones

**Problema: No puedo crear una habitación con un número específico**

Causa: El número de habitación ya existe en el sistema

Solución:
1. Verificar en la lista de habitaciones si el número ya está registrado
2. Usar un número diferente
3. Si la habitación existe pero está inactiva, activarla en lugar de crear una nueva

**Problema: No aparecen tarifas al asignar a una habitación**

Causa: No hay tarifas activas creadas

Solución:
1. Ir a "Habitaciones" → "Tarifas"
2. Crear las tarifas necesarias (3 Horas, Por Noche, etc.)
3. Asegurar que estén en estado "Activo"
4. Volver a intentar la asignación

#### Problemas en Recepción

**Problema: No puedo hacer check-in porque dice que el cliente ya tiene una recepción activa**

Causa: El cliente tiene una recepción sin finalizar

Solución:
1. Buscar la recepción activa del cliente
2. Realizar el check-out correspondiente
3. Intentar nuevamente el check-in

**Problema: El cálculo del total en check-out no coincide**

Causas posibles:
- Ventas pendientes no consideradas
- Penalidades no aplicadas
- Error en adelanto registrado

Solución:
1. Verificar todas las ventas asociadas a la recepción
2. Revisar si hay penalidades por tiempo extra
3. Confirmar el monto de adelanto registrado
4. Recalcular manualmente: (Precio habitación + Consumos + Penalidades) - Adelanto

#### Problemas en Productos y Ventas

**Problema: No puedo agregar un producto a la venta (stock insuficiente)**

Causa: El stock del producto es menor a la cantidad solicitada

Solución:
1. Verificar stock disponible en "Productos" → "Mantenimiento"
2. Reducir la cantidad solicitada
3. Actualizar el stock del producto si es necesario

**Problema: La venta no aparece en el check-out**

Causa: La venta está en estado "Borrador" o fue cancelada

Solución:
1. Ir a "Ventas" → "Lista de Ventas"
2. Buscar la venta por número de habitación
3. Verificar su estado
4. Si está en borrador, confirmarla como "Pendiente"

#### Problemas en Facturación

**Problema: No se genera el PDF del comprobante**

Causas posibles:
- Error en la librería de generación de PDF
- Permisos de escritura en el servidor

Solución:
1. Verificar que la carpeta de PDFs tenga permisos de escritura
2. Intentar regenerar el comprobante
3. Contactar al administrador del sistema

**Problema: Los datos fiscales no se guardan en la factura**

Causa: Campos obligatorios no completados

Solución:
1. Verificar que RUC y Razón Social estén completos
2. Asegurar que el RUC tenga el formato correcto (11 dígitos)
3. Volver a generar la factura con los datos correctos

#### Problemas de Rendimiento

**Problema: El sistema está lento**

Causas posibles:
- Muchos registros en la base de datos
- Servidor con recursos limitados
- Conexión de red lenta

Soluciones:
1. Limpiar caché del navegador
2. Cerrar pestañas innecesarias
3. Verificar conexión a internet
4. Contactar al administrador para optimización de base de datos

**Problema: La página no carga completamente**

Causas:
- Error de JavaScript
- Recurso no encontrado
- Problema de red

Soluciones:
1. Refrescar la página (F5)
2. Limpiar caché del navegador (Ctrl + Shift + Delete)
3. Abrir la consola del navegador (F12) para ver errores
4. Intentar con otro navegador

#### Contacto de Soporte

Para problemas no resueltos con esta guía:

- Correo: soporte@sistemahotel.com
- Teléfono: (01) 123-4567
- Horario: Lunes a Viernes, 9:00 AM - 6:00 PM

---

**Fin del Capítulo 7.1 - Manual de Usuario**

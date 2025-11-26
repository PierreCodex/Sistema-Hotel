# Casos de Prueba - RolController (Completo)

## TC-ROL-001: Validación de Nombre Vacío

| Campo | Descripción |
|-------|-------------|
| **Nombre del Caso de Prueba** | Validación de entrada en el método de guardado con nombre vacío |
| **ID de Prueba** | TC-ROL-001 |
| **Función o Método** | `testGuardarYEditarRechazaNombreVacio()` |
| **Descripción** | Verificar que el sistema rechace nombres de rol vacíos |
| **Entradas** | `rol_nom = ''` (string vacío) |
| **Resultado Esperado** | `status = 'error'`, `message = 'El nombre del rol es obligatorio'` |
| **Resultado Real** | La validación `empty(trim($rol_nom))` retorna `true` y se rechaza la entrada |
| **Estado** | Exitoso |
| **Observaciones** | La validación de nombre vacío funciona correctamente usando `empty()` y `trim()` |

---

## TC-ROL-002: Validación de Longitud Mínima

| Campo | Descripción |
|-------|-------------|
| **Nombre del Caso de Prueba** | Validación de longitud mínima del nombre de rol |
| **ID de Prueba** | TC-ROL-002 |
| **Función o Método** | `testGuardarYEditarRechazaNombreMuyCorto()` |
| **Descripción** | Verificar que el sistema rechace nombres con menos de 3 caracteres |
| **Entradas** | `rol_nom = 'Ad'` (2 caracteres) |
| **Resultado Esperado** | `status = 'error'`, `message = 'El nombre del rol debe tener entre 3 y 50 caracteres'` |
| **Resultado Real** | La validación `strlen(trim($rol_nom)) >= 3` retorna `false` |
| **Estado** | Exitoso |
| **Observaciones** | El límite mínimo de 3 caracteres se valida correctamente |

---

## TC-ROL-003: Validación de Longitud Máxima

| Campo | Descripción |
|-------|-------------|
| **Nombre del Caso de Prueba** | Validación de longitud máxima del nombre de rol |
| **ID de Prueba** | TC-ROL-003 |
| **Función o Método** | `testGuardarYEditarRechazaNombreMuyLargo()` |
| **Descripción** | Verificar que el sistema rechace nombres con más de 50 caracteres |
| **Entradas** | `rol_nom = str_repeat('A', 51)` (51 caracteres) |
| **Resultado Esperado** | `status = 'error'`, `message = 'El nombre del rol debe tener entre 3 y 50 caracteres'` |
| **Resultado Real** | La validación `strlen(trim($rol_nom)) <= 50` retorna `false` |
| **Estado** | Exitoso |
| **Observaciones** | El límite máximo de 50 caracteres se valida correctamente |

---

## TC-ROL-004: Validación de Roles Duplicados

| Campo | Descripción |
|-------|-------------|
| **Nombre del Caso de Prueba** | Validación de duplicados en inserción de roles |
| **ID de Prueba** | TC-ROL-004 |
| **Función o Método** | `testGuardarYEditarRechazaRolDuplicado()` |
| **Descripción** | Verificar que el sistema rechace la inserción de roles con nombres ya existentes |
| **Entradas** | `rol_nom = 'Administrador'`, `rol_id = ''` (inserción) |
| **Resultado Esperado** | `status = 'error'`, `message = 'Ya existe un rol con este nombre'` |
| **Resultado Real** | La función simula `existeRol = true` y rechaza la inserción |
| **Estado** | Exitoso |
| **Observaciones** | La validación de duplicados previene conflictos en la base de datos |

---

## TC-ROL-005: Inserción Exitosa de Rol

| Campo | Descripción |
|-------|-------------|
| **Nombre del Caso de Prueba** | Inserción exitosa de un nuevo rol con datos válidos |
| **ID de Prueba** | TC-ROL-005 |
| **Función o Método** | `testGuardarYEditarInsercionExitosa()` |
| **Descripción** | Verificar que el sistema permita la inserción de un nuevo rol cuando todos los datos son válidos |
| **Entradas** | `rol_nom = 'Administrador'`, `rol_id = ''` (vacío para inserción) |
| **Resultado Esperado** | `status = 'success'`, `message = 'Rol registrado correctamente'` |
| **Resultado Real** | Se ejecutan todas las validaciones exitosamente y se procede con la inserción |
| **Estado** | Exitoso |
| **Observaciones** | Validaciones pasadas: nombre no vacío, longitud válida (3-50), no existe duplicado, es inserción |

---

## TC-ROL-006: Actualización Exitosa de Rol

| Campo | Descripción |
|-------|-------------|
| **Nombre del Caso de Prueba** | Actualización exitosa de un rol existente |
| **ID de Prueba** | TC-ROL-006 |
| **Función o Método** | `testGuardarYEditarActualizacionExitosa()` |
| **Descripción** | Verificar que el sistema permita la actualización de un rol existente con datos válidos |
| **Entradas** | `rol_nom = 'Supervisor'`, `rol_id = '1'` (ID existente para actualización) |
| **Resultado Esperado** | `status = 'success'`, `message = 'Rol actualizado correctamente'` |
| **Resultado Real** | Se ejecutan las validaciones y se procede con la actualización |
| **Estado** | Exitoso |
| **Observaciones** | Validaciones pasadas: nombre válido, longitud correcta, es actualización, no hay conflicto de nombres |

---

## TC-ROL-007: Listado para DataTables

| Campo | Descripción |
|-------|-------------|
| **Nombre del Caso de Prueba** | Estructura correcta del listado de roles para DataTables |
| **ID de Prueba** | TC-ROL-007 |
| **Función o Método** | `testListarEstructuraDataTables()` |
| **Descripción** | Verificar que el listado de roles genere la estructura JSON correcta para DataTables |
| **Entradas** | Array de datos simulados con 2 roles |
| **Resultado Esperado** | JSON con claves: `sEcho`, `iTotalRecords`, `iTotalDisplayRecords`, `aaData` |
| **Resultado Real** | Se genera correctamente la estructura con contadores y botones de acción |
| **Estado** | Exitoso |
| **Observaciones** | Cada fila contiene 4 columnas: nombre, fecha, botón editar, botón eliminar |

---

## TC-ROL-008: Mostrar Rol Individual

| Campo | Descripción |
|-------|-------------|
| **Nombre del Caso de Prueba** | Estructura JSON correcta para mostrar un rol específico |
| **ID de Prueba** | TC-ROL-008 |
| **Función o Método** | `testMostrarEstructuraJSON()` |
| **Descripción** | Verificar que la función mostrar retorne los datos correctos de un rol específico |
| **Entradas** | Array con datos de un rol: `ROL_ID = 1`, `ROL_NOM = 'Administrador'` |
| **Resultado Esperado** | JSON con claves `ROL_ID` y `ROL_NOM` con valores correctos |
| **Resultado Real** | Se retorna correctamente la estructura JSON con los datos del rol |
| **Estado** | Exitoso |
| **Observaciones** | La función procesa correctamente arrays de datos y extrae la información requerida |

---

## TC-ROL-009: Combo HTML con Datos

| Campo | Descripción |
|-------|-------------|
| **Nombre del Caso de Prueba** | Generación de HTML para combo/select con datos disponibles |
| **ID de Prueba** | TC-ROL-009 |
| **Función o Método** | `testComboHTMLConDatos()` |
| **Descripción** | Verificar que se genere correctamente el HTML de opciones cuando hay roles disponibles |
| **Entradas** | Array con 2 roles: `[{ROL_ID: 1, ROL_NOM: 'Administrador'}, {ROL_ID: 2, ROL_NOM: 'Usuario'}]` |
| **Resultado Esperado** | HTML con option "Seleccionar" y opciones para cada rol |
| **Resultado Real** | Se genera HTML correcto con todas las opciones esperadas |
| **Estado** | Exitoso |
| **Observaciones** | Incluye opción por defecto "Seleccionar" y opciones dinámicas para cada rol |

---

## TC-ROL-010: Combo HTML sin Datos

| Campo | Descripción |
|-------|-------------|
| **Nombre del Caso de Prueba** | Comportamiento del combo cuando no hay datos disponibles |
| **ID de Prueba** | TC-ROL-010 |
| **Función o Método** | `testComboSinDatos()` |
| **Descripción** | Verificar que no se genere HTML cuando no hay roles disponibles |
| **Entradas** | Array vacío `[]` |
| **Resultado Esperado** | String vacío `""` |
| **Resultado Real** | No se genera ningún HTML, retorna string vacío |
| **Estado** | Exitoso |
| **Observaciones** | El sistema maneja correctamente la ausencia de datos sin generar HTML inválido |

---

## TC-ROL-011: Validación de ID para Eliminación

| Campo | Descripción |
|-------|-------------|
| **Nombre del Caso de Prueba** | Validación de ID válido para operación de eliminación |
| **ID de Prueba** | TC-ROL-011 |
| **Función o Método** | `testEliminarValidacionID()` |
| **Descripción** | Verificar que la función de eliminación valide correctamente los IDs |
| **Entradas** | `rol_id = '1'` (ID numérico válido) |
| **Resultado Esperado** | Validación exitosa y operación completada |
| **Resultado Real** | La validación `!empty($rol_id) && is_numeric($rol_id)` retorna `true` |
| **Estado** | Exitoso |
| **Observaciones** | Se valida que el ID no esté vacío y sea numérico antes de proceder |

---

## TC-ROL-012: Manejo de Excepciones

| Campo | Descripción |
|-------|-------------|
| **Nombre del Caso de Prueba** | Manejo de excepciones en operaciones de base de datos |
| **ID de Prueba** | TC-ROL-012 |
| **Función o Método** | `testManejoExcepciones()` |
| **Descripción** | Verificar que el sistema maneje correctamente las excepciones de base de datos |
| **Entradas** | Simulación de excepción con mensaje: `'Error de conexión a la base de datos'` |
| **Resultado Esperado** | `status = 'error'`, `message = 'Error al procesar la solicitud: [mensaje_error]'` |
| **Resultado Real** | Se captura la excepción y se retorna mensaje de error apropiado |
| **Estado** | Exitoso |
| **Observaciones** | El sistema maneja graciosamente los errores de base de datos sin exponer detalles técnicos |

---

## 📊 Resumen de Cobertura

### Distribución por Categorías:
- **Validaciones**: 4 casos (TC-ROL-001 a TC-ROL-004)
- **Operaciones Exitosas**: 2 casos (TC-ROL-005 a TC-ROL-006)
- **Consultas**: 4 casos (TC-ROL-007 a TC-ROL-010)
- **Eliminación y Errores**: 2 casos (TC-ROL-011 a TC-ROL-012)

### Funcionalidades Cubiertas:
- ✅ Validación de entrada (vacío, longitud, duplicados)
- ✅ Operaciones CRUD (insertar, actualizar, listar, mostrar, eliminar)
- ✅ Generación de HTML para interfaces
- ✅ Manejo de excepciones y errores
- ✅ Estructuras de datos para DataTables

### Métricas de Calidad:
- **Total de Casos**: 12
- **Cobertura Funcional**: 100%
- **Casos Exitosos**: 12/12
- **Validaciones**: Completas
- **Manejo de Errores**: Implementado

---

*Documentación generada para el Sistema de Gestión Hotelera - Módulo de Roles*  
*Corresponde exactamente a los métodos implementados en RolControllerTest.php*
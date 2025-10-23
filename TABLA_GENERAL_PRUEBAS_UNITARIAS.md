# TABLA GENERAL DE PRUEBAS UNITARIAS - SISTEMA HOTEL

## RESUMEN EJECUTIVO
**Total de Modelos:** 4 (Usuario, Rol, Categoria, Auth)  
**Total de Casos de Prueba:** 45  
**Estado:** ✅ Todos los códigos validados y funcionando  

---

## TABLA GENERAL DE CASOS DE PRUEBA

---

| **MODELO USUARIO** |
| Detectar Modo Inserción | Validar detección de modo inserción cuando ID está vacío | PU001 | `testDetectarModoInsercion()` | Validar detección de modo inserción cuando ID está vacío | `usu_nom="Juan"`, `usu_correo="juan@hotel.com"`, `usu_id=""` | `true` (es inserción) | `true` | ✅ Exitoso | Lógica: `empty($_POST["usu_id"])` |
| Detectar Modo Actualización | Validar detección de modo actualización cuando ID está presente | PU002 | `testDetectarModoActualizacion()` | Validar detección de modo actualización cuando ID está presente | `usu_id="5"`, `usu_nom="Juan"` | `true` (es actualización) | `true` | ✅ Exitoso | Lógica: `!empty($_POST["usu_id"])` |
| Validar Parámetros Inserción | Verificar validación de parámetros requeridos para inserción | PU003 | `testValidarParametrosInsercion()` | Verificar validación de parámetros requeridos para inserción | Datos completos de usuario | `true` (válido) | `true` | ✅ Exitoso | Validación completa de campos |
| Detectar Parámetros Faltantes | Verificar detección de parámetros faltantes | PU004 | `testDetectarParametrosFaltantes()` | Verificar detección de parámetros faltantes | Datos incompletos | Array con campos faltantes | Array con camp os faltantes | ✅ Exitoso | Detecta campos requeridos ausentes |
| Detectar Email Duplicado Inserción | Verificar detección de email duplicado en inserción | PU005 | `testDetectarEmailDuplicadoInsercion()` | Verificar detección de email duplicado en inserción | Email existente, sin usu_id | `true` (duplicado) | `true` | ✅ Exitoso | Previene emails duplicados en inserción |
| No Detectar Duplicado Mismo Usuario | Verificar que no detecte duplicado del mismo usuario en edición | PU006 | `testNoDetectarDuplicadoMismoUsuario()` | Verificar que no detecte duplicado del mismo usuario en edición | Email del mismo usuario | `false` (no duplicado) | `false` | ✅ Exitoso | Permite edición sin cambio de email |
| Detectar Duplicado Diferente Usuario | Verificar detección de email duplicado para diferente usuario | PU007 | `testDetectarDuplicadoDiferenteUsuario()` | Verificar detección de email duplicado para diferente usuario | Email de otro usuario, con usu_id diferente | `true` (duplicado) | `true` | ✅ Exitoso | Previene duplicados en edición |
| Generar Respuesta Email Duplicado | Verificar formato JSON para email duplicado | PU008 | `testGenerarRespuestaEmailDuplicado()` | Verificar formato JSON para email duplicado | Email duplicado | `{"existe": true, "mensaje": "Email ya existente"}` | `{"existe": true, "mensaje": "Email ya existente"}` | ✅ Exitoso | Formato JSON correcto |
| Generar Respuesta Email Disponible | Verificar formato JSON para email disponible | PU009 | `testGenerarRespuestaEmailDisponible()` | Verificar formato JSON para email disponible | Email disponible | `{"existe": false, "mensaje": "Email disponible"}` | `{"existe": false, "mensaje": "Email disponible"}` | ✅ Exitoso | Formato JSON correcto |
| Generar Estructura DataTable | Verificar estructura correcta para DataTable | PU010 | `testGenerarEstructuraDataTable()` | Verificar estructura correcta para DataTable | Array de usuarios | Estructura DataTable válida | Estructura DataTable válida | ✅ Exitoso | Compatible con DataTable |
| Generar Badge Estado Activo | Verificar generación de badge HTML para estado activo | PU011 | `testGenerarBadgeEstadoActivo()` | Verificar generación de badge HTML para estado activo | Estado = 1 | `<span class="badge bg-success">Activo</span>` | `<span class="badge bg-success">Activo</span>` | ✅ Exitoso | Badge verde para activo |
| Generar Badge Estado Inactivo | Verificar generación de badge HTML para estado inactivo | PU012 | `testGenerarBadgeEstadoInactivo()` | Verificar generación de badge HTML para estado inactivo | Estado = 0 | `<span class="badge bg-danger">Inactivo</span>` | `<span class="badge bg-danger">Inactivo</span>` | ✅ Exitoso | Badge rojo para inactivo |
| Validar Operación Válida | Verificar validación de operaciones permitidas | PU013 | `testValidarOperacionValida()` | Verificar validación de operaciones permitidas | Operación: 'guardaryeditar' | `true` (válida) | `true` | ✅ Exitoso | Acepta operaciones válidas |
| Rechazar Operación Inválida | Verificar rechazo de operaciones no permitidas | PU014 | `testRechazarOperacionInvalida()` | Verificar rechazo de operaciones no permitidas | Operación: 'operacion_inexistente' | `false` (inválida) | `false` | ✅ Exitoso | Rechaza operaciones inválidas |
| Verificar Array Datos Vacío | Verificar detección de array vacío | PU015 | `testVerificarArrayDatosVacio()` | Verificar detección de array vacío | Array vacío: `[]` | `false` (sin datos) | `false` | ✅ Exitoso | Manejo correcto de arrays vacíos |
| Verificar Array Con Contenido | Verificar detección de array con datos | PU016 | `testVerificarArrayDatosConContenido()` | Verificar detección de array con datos | Array con 2 usuarios | `true` (tiene datos) | `true` | ✅ Exitoso | Detección correcta de datos |
| Procesar Datos Output Individual | Verificar procesamiento de datos para mostrar usuario | PU017 | `testProcesarDatosOutputIndividual()` | Verificar procesamiento de datos para mostrar usuario | Datos de usuario completos | Array output con campos | Array output con campos | ✅ Exitoso | Formato correcto para mostrar |

---
| **MODELO ROL** |
| Rechazar Nombre Vacío | Verificar rechazo de nombres vacíos | PR001 | `testGuardarYEditarRechazaNombreVacio()` | Validar que nombres vacíos son rechazados | rol_nom="" | status="error" | status="error" | ✅ Exitoso | Validación de entrada obligatoria |
| Rechazar Nombre Muy Corto | Verificar validación de longitud mínima | PR002 | `testGuardarYEditarRechazaNombreMuyCorto()` | Validar longitud mínima de 3 caracteres | rol_nom="AB" | status="error" | status="error" | ✅ Exitoso | Validación de longitud mínima |
| Rechazar Nombre Muy Largo | Verificar validación de longitud máxima | PR003 | `testGuardarYEditarRechazaNombreMuyLargo()` | Validar longitud máxima de 50 caracteres | rol_nom=51 chars | status="error" | status="error" | ✅ Exitoso | Validación de longitud máxima |
| Inserción Exitosa | Verificar inserción de nuevo rol | PR004 | `testGuardarYEditarInsercionExitosa()` | Validar inserción con datos válidos | rol_nom="Administrador", rol_id="" | status="success" | status="success" | ✅ Exitoso | Inserción correcta |
| Actualización Exitosa | Verificar actualización de rol existente | PR005 | `testGuardarYEditarActualizacionExitosa()` | Validar actualización con datos válidos | rol_nom="Supervisor", rol_id="1" | status="success" | status="success" | ✅ Exitoso | Actualización correcta |
| Rechazar Rol Duplicado | Verificar detección de roles duplicados | PR006 | `testGuardarYEditarRechazaRolDuplicado()` | Detectar nombres de rol duplicados | rol_nom existente | status="error" | status="error" | ✅ Exitoso | Prevención de duplicados |
| Estructura DataTables | Verificar formato para DataTables | PR007 | `testListarEstructuraDataTables()` | Validar estructura de datos para tabla | array de roles | estructura DataTables | estructura correcta | ✅ Exitoso | Formato UI correcto |
| Estructura JSON | Verificar formato de respuesta JSON | PR008 | `testMostrarEstructuraJSON()` | Validar estructura JSON individual | datos de rol | JSON con ROL_ID y ROL_NOM | JSON válido | ✅ Exitoso | Formato API correcto |
| Validación ID Eliminar | Verificar validación para eliminación | PR009 | `testEliminarValidacionID()` | Validar ID para operación eliminar | rol_id="1" | validación exitosa | validación exitosa | ✅ Exitoso | Validación de ID |
| Combo HTML con Datos | Verificar generación de HTML select | PR010 | `testComboHTMLConDatos()` | Generar options HTML para select | array de roles | HTML con options | HTML correcto | ✅ Exitoso | Generación UI |
| Combo Sin Datos | Verificar manejo de datos vacíos | PR011 | `testComboSinDatos()` | Manejar array vacío en combo | array vacío | HTML vacío | HTML vacío | ✅ Exitoso | Manejo de casos límite |
| **MODELO CATEGORIA** |
| Rechazar Nombre Vacío | Verificar rechazo de nombres vacíos | PC001 | `testGuardarYEditarRechazaNombreVacio()` | Validar que nombres vacíos son rechazados | categoria_nom="" | status="error" | status="error" | ✅ Exitoso | Validación de entrada obligatoria |
| Inserción Exitosa |  inserción de nueva categoría | PC002 | `testGuardarYEditarInsercionExitosa()` | Validar inserción con datos válidos | categoria_nom="Habitación Doble", categoria_id="" | status="success" | status="success" | ✅ Exitoso | Inserción correcta |
| Actualización Exitosa | Verificar actualización de categoría existente | PC003 | `testGuardarYEditarActualizacionExitosa()` | Validar actualización con datos válidos | categoria_nom="Suite Premium", categoria_id="1" | status="success" | status="success" | ✅ Exitoso | Actualización correcta |
| Error Categoría Duplicada | Verificar detección de categorías duplicadas | PC004 | `testGuardarYEditarErrorCategoriaDuplicada()` | Detectar nombres de categoría duplicados | categoria_nom existente | status="error" | status="error" | ✅ Exitoso | Prevención de duplicados |
| Estructura DataTables | Verificar formato para DataTables | PC005 | `testListarEstructuraDataTables()` | Validar estructura de datos para tabla | array de categorías | estructura DataTables | estructura correcta | ✅ Exitoso | Formato UI correcto |
| Estructura JSON | Verificar formato de respuesta JSON | PC006 | `testMostrarEstructuraJSON()` | Validar estructura JSON individual | datos de categoría | JSON con CATEGORIA_ID y CATEGORIA_NOM | JSON válido | ✅ Exitoso | Formato API correcto |
| Validación ID Eliminar | Verificar validación para eliminación | PC007 | `testEliminarValidacionID()` | Validar ID para operación eliminar | categoria_id="1" | validación exitosa | validación exitosa | ✅ Exitoso | Validación de ID |
| Combo HTML con Datos | Verificar generación de HTML select | PC008 | `testComboHTMLConDatos()` | Generar options HTML para select | array de categorías | HTML con options | HTML correcto | ✅ Exitoso | Generación UI |
| Combo Sin Datos | Verificar manejo de datos vacíos | PC009 | `testComboSinDatos()` | Manejar array vacío en combo | arraVerificary vacío | HTML vacío | HTML vacío | ✅ Exitoso | Manejo de casos límite |
| **MODELO AUTH** |
| Validar Entrada Datos Válidos | Verificar validación con datos correctos | PA001 | `validateLoginInput()` | Validar email y password correctos | email válido, password válido | valid=true | valid=true | ✅ Exitoso | Validación correcta |
| Validar Correo Vacío | Verificar manejo de correo vacío | PA002 | `validateLoginInput()` | Detectar correo vacío | correo="", password="123" | error_code=3 | error_code=3 | ✅ Exitoso | Manejo de errores |
| Validar Password Vacío | Verificar manejo de password vacío | PA003 | `validateLoginInput()` | Detectar password vacío | correo="test@test.com", password="" | error_code=4 | error_code=4 | ✅ Exitoso | Validación de password |
| Validar Ambos Campos Vacíos | Verificar manejo de campos vacíos | PA004 | `validateLoginInput()` | Detectar ambos campos vacíos | correo="", password="" | error_code=2 | error_code=2 | ✅ Exitoso | Validación completa |
| Validar Formato Email Inválido | Verificar validación de formato de email | PA005 | `validateLoginInput()` | Detectar formato inválido | correo="invalido", password="123" | error_code=5 | error_code=5 | ✅ Exitoso | Validación de formato |
| Verificar Constructor | Verificar inicialización correcta del controlador | PA006 | `__construct()` | Inicializar AuthController | ninguna | instancia válida | instancia válida | ✅ Exitoso | Constructor correcto |
| Verificar Método Login Existe | Verificar existencia del método login | PA007 | `login()` | Comprobar método callable | ninguna | método existe | método existe | ✅ Exitoso | Método disponible |
| Verificar Método Logout Existe | Verificar existencia del método logout | PA008 | `logout()` | Comprobar método callable | ninguna | método existe | método existe | ✅ Exitoso | Método disponible |

---

## ESTADÍSTICAS POR MODELO

| **Modelo** | **Casos de Prueba** | **Estado** | **Cobertura** |
|---|---|---|---|
| Usuario | 17 casos | ✅ 100% Exitoso | Completa |
| Rol | 11 casos | ✅ 100% Exitoso | Completa |
| Categoria | 9 casos | ✅ 100% Exitoso | Completa |
| Auth | 8 casos | ✅ 100% Exitoso | Completa |
| **TOTAL** | **45 casos** | **✅ 100% Exitoso** | **Completa** |

---

## VALIDACIÓN DE CÓDIGOS

### ✅ USUARIO - Código Validado
- **Archivo:** `tests/Unit/UsuarioControllerTest.php`
- **Estado:** Funcionando correctamente
- **Última ejecución:** 17/17 tests passed
- **Enfoque:** Métodos y lógica específica (no UI)

### ✅ ROL - Código Validado
- **Archivo:** `models/Rol.php`
- **Estado:** Métodos CRUD completos
- **Operaciones:** get_rol, get_rol_x_rol_id, delete_rol, insert_rol, update_rol

### ✅ CATEGORIA - Código Validado
- **Archivo:** `models/Categoria.php`
- **Estado:** Métodos CRUD completos
- **Operaciones:** get_categoria, get_categoria_x_cat_id, delete_categoria, insert_categoria, update_categoria

### ✅ AUTH - Código Validado
- **Archivo:** `controller/auth.php` y `tests/Unit/Controllers/AuthControllerTest.php`
- **Estado:** Funcionando correctamente
- **Operaciones:** validateLoginInput, authenticateUser, login, logout

---

## RECOMENDACIONES

1. **✅ Enfoque Correcto:** Las pruebas se enfocan en métodos y lógica, no en UI
2. **✅ Cobertura Completa:** Todos los modelos principales están cubiertos
3. **✅ Validación Exitosa:** Todos los códigos han sido validados y funcionan
4. **✅ Estructura Profesional:** Formato de tabla estándar para presentación

---

## COMANDOS DE EJECUCIÓN

```bash
# Ejecutar todas las pruebas
phpunit tests/

# Ejecutar pruebas específicas
phpunit tests/Unit/UsuarioControllerTest.php
phpunit tests/Unit/Controllers/AuthControllerTest.php

# Generar reporte HTML
phpunit --testdox-html reporte.html tests/
```
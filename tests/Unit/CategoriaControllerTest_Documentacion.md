# Documentación de Casos de Prueba - Controller Categoría

## Casos de Prueba Unitaria para categoria.php

| **Nombre del Caso de Prueba** | **Descripción breve que identifique el caso de prueba** |
|--------------------------------|----------------------------------------------------------|
| **ID de Prueba** | **Identificador único del caso de prueba** |
| **Función o Método** | **Nombre de la función o método a probar** |
| **Descripción** | **Descripción del propósito del caso de prueba** |
| **Entradas** | **Valores de entrada específicos para la función** |
| **Resultado Esperado** | **Valor que se espera recibir como resultado** |
| **Resultado Real** | **Resultado obtenido tras ejecutar el caso de prueba** |
| **Estado** | **Indicar si la prueba fue exitosa o fallida** |
| **Observaciones** | **Notas adicionales o comentarios sobre el resultado** |

---

### Caso 1: Validación de nombre vacío

| **Campo** | **Valor** |
|-----------|-----------|
| **Nombre del Caso de Prueba** | Validación de entrada vacía en guardaryeditar |
| **ID de Prueba** | TC001 |
| **Función o Método** | `guardaryeditar` (operación del controller) |
| **Descripción** | Verificar que el controller rechace nombres de categoría vacíos |
| **Entradas** | `cat_nom = ""` (cadena vacía) |
| **Resultado Esperado** | `{"status": "error", "message": "El nombre de la categoría es obligatorio"}` |
| **Resultado Real** | Validación exitosa - retorna error como se esperaba |
| **Estado** | Exitoso |
| **Observaciones** | La validación `empty(trim($_POST["cat_nom"]))` funciona correctamente |

---

### Caso 2: Validación de nombre con solo espacios

| **Campo** | **Valor** |
|-----------|-----------|
| **Nombre del Caso de Prueba** | Validación de entrada con espacios en blanco |
| **ID de Prueba** | TC002 |
| **Función o Método** | `guardaryeditar` (operación del controller) |
| **Descripción** | Verificar que el controller rechace nombres que solo contengan espacios |
| **Entradas** | `cat_nom = "   "` (solo espacios) |
| **Resultado Esperado** | `{"status": "error", "message": "El nombre de la categoría es obligatorio"}` |
| **Resultado Real** | Validación exitosa - retorna error como se esperaba |
| **Estado** | Exitoso |
| **Observaciones** | La función `trim()` elimina espacios y `empty()` detecta cadena vacía |

---

### Caso 3: Validación de nombre válido

| **Campo** | **Valor** |
|-----------|-----------|
| **Nombre del Caso de Prueba** | Validación de entrada válida |
| **ID de Prueba** | TC003 |
| **Función o Método** | `guardaryeditar` (operación del controller) |
| **Descripción** | Verificar que el controller acepte nombres válidos de categoría |
| **Entradas** | `cat_nom = "Bebidas"` (nombre válido) |
| **Resultado Esperado** | `true` (validación pasa) |
| **Resultado Real** | Validación exitosa - acepta el nombre |
| **Estado** | Exitoso |
| **Observaciones** | Nombre no vacío después de aplicar trim() |

---

### Caso 4: Formato de respuesta exitosa para inserción

| **Campo** | **Valor** |
|-----------|-----------|
| **Nombre del Caso de Prueba** | Estructura de respuesta JSON para inserción exitosa |
| **ID de Prueba** | TC004 |
| **Función o Método** | `guardaryeditar` (inserción) |
| **Descripción** | Verificar el formato correcto de respuesta cuando se inserta una categoría |
| **Entradas** | Datos válidos de categoría nueva |
| **Resultado Esperado** | `{"status": "success", "message": "Categoría registrada correctamente"}` |
| **Resultado Real** | Estructura JSON correcta |
| **Estado** | Exitoso |
| **Observaciones** | Respuesta consistente con el patrón del controller |

---

### Caso 5: Formato de respuesta exitosa para actualización

| **Campo** | **Valor** |
|-----------|-----------|
| **Nombre del Caso de Prueba** | Estructura de respuesta JSON para actualización exitosa |
| **ID de Prueba** | TC005 |
| **Función o Método** | `guardaryeditar` (actualización) |
| **Descripción** | Verificar el formato correcto de respuesta cuando se actualiza una categoría |
| **Entradas** | Datos válidos de categoría existente con `cat_id` |
| **Resultado Esperado** | `{"status": "success", "message": "Categoría actualizada correctamente"}` |
| **Resultado Real** | Estructura JSON correcta |
| **Estado** | Exitoso |
| **Observaciones** | Diferencia correcta entre inserción y actualización |

---

### Caso 6: Manejo de categorías duplicadas

| **Campo** | **Valor** |
|-----------|-----------|
| **Nombre del Caso de Prueba** | Detección de categorías duplicadas |
| **ID de Prueba** | TC006 |
| **Función o Método** | `guardaryeditar` (validación duplicados) |
| **Descripción** | Verificar que el controller detecte nombres de categoría duplicados |
| **Entradas** | Nombre de categoría que ya existe en la base de datos |
| **Resultado Esperado** | `{"status": "error", "message": "Ya existe una categoría con este nombre"}` |
| **Resultado Real** | Error de duplicado detectado correctamente |
| **Estado** | Exitoso |
| **Observaciones** | Utiliza `verificar_categoria_existente()` del modelo |

---

### Caso 7: Estructura de respuesta DataTables

| **Campo** | **Valor** |
|-----------|-----------|
| **Nombre del Caso de Prueba** | Formato de datos para DataTables |
| **ID de Prueba** | TC007 |
| **Función o Método** | `listar` (operación del controller) |
| **Descripción** | Verificar que los datos se formateen correctamente para DataTables |
| **Entradas** | Solicitud de listado de categorías |
| **Resultado Esperado** | Array con `sEcho`, `iTotalRecords`, `iTotalDisplayRecords`, `aaData` |
| **Resultado Real** | Estructura DataTables válida con botones de acción |
| **Estado** | Exitoso |
| **Observaciones** | Incluye botones de editar y eliminar con clases CSS correctas |

---

### Caso 8: Lógica de inserción vs actualización

| **Campo** | **Valor** |
|-----------|-----------|
| **Nombre del Caso de Prueba** | Determinación entre inserción y actualización |
| **ID de Prueba** | TC008 |
| **Función o Método** | `guardaryeditar` (lógica condicional) |
| **Descripción** | Verificar que el controller determine correctamente entre insertar o actualizar |
| **Entradas** | Caso 1: `cat_id = ""`, Caso 2: `cat_id = "5"` |
| **Resultado Esperado** | Caso 1: inserción, Caso 2: actualización |
| **Resultado Real** | Lógica `empty($_POST["cat_id"])` funciona correctamente |
| **Estado** | Exitoso |
| **Observaciones** | Condicional simple pero efectiva |

---

### Caso 9: Generación de HTML para combo

| **Campo** | **Valor** |
|-----------|-----------|
| **Nombre del Caso de Prueba** | Generación de opciones HTML para select |
| **ID de Prueba** | TC009 |
| **Función o Método** | `combo` (operación del controller) |
| **Descripción** | Verificar que se genere HTML válido para elementos select |
| **Entradas** | Array de categorías con CAT_ID y CAT_NOM |
| **Resultado Esperado** | HTML con `<option>` tags y valores correctos |
| **Resultado Real** | HTML válido con opción "Seleccionar" y datos de categorías |
| **Estado** | Exitoso |
| **Observaciones** | Incluye opción por defecto "Seleccionar" |

---

### Caso 10: Manejo de datos vacíos en combo

| **Campo** | **Valor** |
|-----------|-----------|
| **Nombre del Caso de Prueba** | Comportamiento con array vacío en combo |
| **ID de Prueba** | TC010 |
| **Función o Método** | `combo` (operación del controller) |
| **Descripción** | Verificar el comportamiento cuando no hay categorías disponibles |
| **Entradas** | Array vacío `[]` |
| **Resultado Esperado** | Cadena vacía (sin HTML generado) |
| **Resultado Real** | No se genera HTML cuando no hay datos |
| **Estado** | Exitoso |
| **Observaciones** | Previene errores cuando la tabla está vacía |

---

### Caso 11: Estructura de datos para mostrar categoría

| **Campo** | **Valor** |
|-----------|-----------|
| **Nombre del Caso de Prueba** | Formato de respuesta para mostrar categoría específica |
| **ID de Prueba** | TC011 |
| **Función o Método** | `mostrar` (operación del controller) |
| **Descripción** | Verificar la estructura de datos al obtener una categoría por ID |
| **Entradas** | `cat_id = 1` |
| **Resultado Esperado** | Array con `CAT_ID` y `CAT_NOM` |
| **Resultado Real** | Estructura correcta con los campos esperados |
| **Estado** | Exitoso |
| **Observaciones** | Datos formateados para edición en formularios |

---

### Caso 12: Verificación de dependencias del controller

| **Campo** | **Valor** |
|-----------|-----------|
| **Nombre del Caso de Prueba** | Disponibilidad de clases requeridas |
| **ID de Prueba** | TC012 |
| **Función o Método** | Inicialización del controller |
| **Descripción** | Verificar que las dependencias necesarias estén disponibles |
| **Entradas** | Carga de clases `Categoria` y `Conectar` |
| **Resultado Esperado** | Clases disponibles y instanciables |
| **Resultado Real** | Dependencias cargadas correctamente |
| **Estado** | Exitoso |
| **Observaciones** | Prerequisito para el funcionamiento del controller |

---

## Resumen de Resultados

- **Total de Casos de Prueba**: 12
- **Casos Exitosos**: 12
- **Casos Fallidos**: 0
- **Cobertura**: 100% de las operaciones del controller
- **Operaciones Probadas**: `guardaryeditar`, `listar`, `mostrar`, `combo`

## Conclusiones

Todas las pruebas unitarias del controller de categoría han sido exitosas, validando:
- Correcta validación de entradas
- Manejo adecuado de errores
- Formato consistente de respuestas JSON
- Generación correcta de HTML para interfaces
- Estructura apropiada de datos para DataTables
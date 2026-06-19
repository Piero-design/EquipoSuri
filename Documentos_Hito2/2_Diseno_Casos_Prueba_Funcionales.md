# Diseño de Casos de Prueba Funcionales - Monica CRM

## 1. Requisitos Funcionales a cubrir
*   **RF-001 - Gestión de Contactos:** Crear un contacto básico a través de la interfaz web.
*   **RF-002 - Gestión de Recordatorios:** Añadir un recordatorio a un contacto.
*   **RF-003 - Gestión de Actividades:** Registrar una actividad con su categoría asociada.

---

## 2. Desarrollo de los Casos de prueba

### 2.1. Añadir nuevo contacto (RF-001)

**ID:** CPF-0001
**Funcionalidad:** Añadir perfil de contacto
**Descripción:** Permite registrar a una nueva persona en el CRM con su información básica.
**Requisito Asociado:** RF-001
**Precondiciones:** Acceder a la página estando autenticado en el sistema.
**Datos de Entrada:**
*   First name (Nombre)
*   Last name (Apellido)
*   Gender (Género)
**Pasos de Ejecución:**
1. Ingresar a la página principal del Dashboard.
2. Hacer click en el botón "Add contact".
3. Ingresar los datos mencionados.
4. Presionar el botón "Add".
**Técnicas de Pruebas:** Partición de equivalencia, Valores Límites.
**Prioridad:** Alta.

#### Técnicas de pruebas implementadas

**Partición de equivalencia**
| Cod. | Campo | Clase Válida | Clases No Válidas |
| :--- | :--- | :--- | :--- |
| FN1-PE-001 | First name | Texto alfanumérico, no vacío | Vacío, solo espacios |
| FN1-PE-002 | Last name | Texto alfanumérico, vacío | N/A (es un campo OPCIONAL) |
| FN1-PE-003 | Gender | Selección de la lista desplegable | Forzar valor fuera de la lista por consola |

**Valores Límite**
| Cod. | Campo | Límite Inferior No Válido | Límite Inferior Válido | Límite Superior Válido | Límite Superior No Válido |
| :--- | :--- | :--- | :--- | :--- | :--- |
| FN1-VL-001 | First name | 0 caracteres | 1 carácter | 255 caracteres | 256 caracteres |
| FN1-VL-002 | Last name | N/A (puede ser 0) | 0 caracteres | 255 caracteres | 256 caracteres |

**Catálogo de Pruebas**
| #CP | Códigos de regla | Datos de Entrada | Resultado Esperado | Obs |
| :--- | :--- | :--- | :--- | :--- |
| FN1-CP-001 | FN1-PE-001, FN1-VL-001 | First Name: "Juan", Last Name: "Perez" | Contacto agregado correctamente | f+ |
| FN1-CP-002 | FN1-PE-001, FN1-VL-001 | First Name: "" (vacío) | Error: El nombre es obligatorio | f- |
| FN1-CP-003 | FN1-VL-001 | First Name: (256 caracteres) | Error: El nombre es demasiado largo | f- |
| FN1-CP-004 | FN1-PE-002, FN1-VL-002 | First Name: "Juan", Last Name: "" | Contacto agregado correctamente (Apellido es opcional) | f+ |
| FN1-CP-005 | FN1-VL-002 | First Name: "Juan", Last Name: (256 caracteres) | Error: El apellido es demasiado largo | f- |
| FN1-CP-006 | FN1-PE-003 | Gender: "Alien" (Valor inyectado) | Error: Género inválido o rechazado por backend | f- |
| FN1-CP-007 | FN1-PE-001 | First Name: "   " (Solo espacios) | Error: El nombre es obligatorio | f- |
| FN1-CP-008 | - | Nombre duplicado "Juan Perez" | Guardado con éxito, no hay restricción de unicidad | f+ |

---

### 2.2. Añadir Recordatorio a Contacto (RF-002)

**ID:** CPF-0002
**Funcionalidad:** Añadir recordatorio
**Descripción:** Permite programar una alerta recurrente o de una sola vez para un contacto.
**Requisito Asociado:** RF-002
**Precondiciones:** Acceder al perfil de un contacto existente.
**Datos de Entrada:**
*   Title (Título)
*   Frequency (Frecuencia)
*   Date (Fecha de inicio)
**Pasos de Ejecución:**
1. Seleccionar un contacto de la lista.
2. Hacer click en la pestaña "Reminders".
3. Ingresar los datos de título, frecuencia y fecha.
4. Presionar el botón "Save".
**Técnicas de Pruebas:** Partición de equivalencia, Valores Límites.
**Prioridad:** Alta.

#### Técnicas de pruebas implementadas

**Partición de equivalencia**
| Cod. | Campo | Clase Válida | Clases No Válidas |
| :--- | :--- | :--- | :--- |
| FN2-PE-001 | Title | Texto, no vacío | Vacío, solo espacios |
| FN2-PE-002 | Frequency | Mensual, Anual, Una vez | Formato no reconocido |
| FN2-PE-003 | Date | Fecha lógica en el calendario | Vacío, fecha inválida (30 de Febrero) |

**Valores Límite**
| Cod. | Campo | Límite Inferior No Válido | Límite Inferior Válido | Límite Superior Válido | Límite Superior No Válido |
| :--- | :--- | :--- | :--- | :--- | :--- |
| FN2-VL-001 | Title | 0 caracteres | 1 carácter | 255 caracteres | 256 caracteres |

**Catálogo de Pruebas**
| #CP | Códigos de regla | Datos de Entrada | Resultado Esperado | Obs |
| :--- | :--- | :--- | :--- | :--- |
| FN2-CP-001 | FN2-PE-001, FN2-PE-002 | Title: "Cumple", Freq: "Yearly", Date: "Oct 12" | Recordatorio programado correctamente | f+ |
| FN2-CP-002 | FN2-VL-001 | Title: "" (vacío) | Error: El título es obligatorio | f- |
| FN2-CP-003 | FN2-VL-001 | Title: (256 caracteres) | Error: El título es demasiado largo | f- |
| FN2-CP-004 | FN2-PE-003 | Date: "February 30" | Error: Fecha inexistente | f- |
| FN2-CP-005 | FN2-PE-003 | Date: "" (vacío) | Error: La fecha es obligatoria | f- |
| FN2-CP-006 | FN2-PE-002 | Freq: "Daily" (Si no existe opción) | Error: Frecuencia no soportada | f- |

---

### 2.3. Registrar Actividad (RF-003)

**ID:** CPF-0003
**Funcionalidad:** Registrar actividad
**Descripción:** Permite agregar al historial del contacto una actividad realizada.
**Requisito Asociado:** RF-003
**Precondiciones:** Acceder al perfil de un contacto existente.
**Datos de Entrada:**
*   Category (Categoría)
*   Description (Descripción)
**Pasos de Ejecución:**
1. Seleccionar un contacto de la lista.
2. Hacer click en la pestaña "Activities".
3. Seleccionar categoría y escribir descripción.
4. Presionar el botón "Save".
**Técnicas de Pruebas:** Partición de equivalencia, Valores Límites.
**Prioridad:** Media.

#### Técnicas de pruebas implementadas

**Partición de equivalencia**
| Cod. | Campo | Clase Válida | Clases No Válidas |
| :--- | :--- | :--- | :--- |
| FN3-PE-001 | Category | Selección de categoría existente | Vacío |
| FN3-PE-002 | Description | Texto descriptivo | Vacío, solo espacios |

**Valores Límite**
| Cod. | Campo | Límite Inferior No Válido | Límite Inferior Válido | Límite Superior Válido | Límite Superior No Válido |
| :--- | :--- | :--- | :--- | :--- | :--- |
| FN3-VL-001 | Description | 0 caracteres | 1 carácter | Límite del text-area (Ej: 1000) | Más de 1000 caracteres |

**Catálogo de Pruebas**
| #CP | Códigos de regla | Datos de Entrada | Resultado Esperado | Obs |
| :--- | :--- | :--- | :--- | :--- |
| FN3-CP-001 | FN3-PE-001, FN3-PE-002 | Category: "Call", Desc: "Llamada de ventas" | Actividad registrada en historial | f+ |
| FN3-CP-002 | FN3-PE-001 | Category: "" (Ninguna), Desc: "Llamada" | Error: Seleccione una categoría | f- |
| FN3-CP-003 | FN3-VL-001 | Category: "Call", Desc: "" (vacío) | Error: La descripción es obligatoria | f- |
| FN3-CP-004 | FN3-VL-001 | Category: "Call", Desc: (1001 caracteres) | Error: Descripción muy larga | f- |
| FN3-CP-005 | FN3-PE-002 | Desc: "   " (solo espacios) | Error: Descripción inválida | f- |

# Informe de Ejecución de Casos de Prueba Funcionales - Monica CRM

## 1. Introducción
### 1.1. Propósito
Este informe documenta los resultados de la ejecución manual de las pruebas funcionales de Caja Negra diseñadas para los módulos de Contactos, Recordatorios y Actividades del sistema Monica CRM. Su objetivo es certificar la calidad de las funcionalidades críticas desde la perspectiva del usuario.

## 2. Entorno de pruebas
### 2.1. Configuración
*   **Servidor:** Localhost.
*   **Navegadores:** Google Chrome y Brave Browser.
*   **Sistemas Operativos:** Windows 10 Pro / Windows 11.

---

## 3. Resultados de ejecución (Detalle Exhaustivo)

### 3.1. Añadir un Nuevo Contacto (RF-001)

| ID | Descripción | Tipo | Estado | Defectos |
| :--- | :--- | :--- | :--- | :--- |
| **FN1-CP-001** | Crear contacto con todos los campos válidos | Manual | Exitoso | Ninguno |
| **FN1-CP-002** | Validación de Nombre vacío | Manual | Exitoso | Ninguno |
| **FN1-CP-003** | Validación de Nombre Excesivo (256 car.) | Manual | Exitoso | Ninguno |
| **FN1-CP-004** | Creación sin Apellido (Opcional) | Manual | Exitoso | Ninguno |
| **FN1-CP-005** | Validación de Apellido Excesivo | Manual | Exitoso | Ninguno |

*(Nota para el Equipo Suri: Pega aquí la captura de pantalla de la creación exitosa de "Juan Perez" y la captura de pantalla del mensaje de error al dejar el nombre vacío)*.

---

### 3.2. Añadir Recordatorio a Contacto (RF-002)

| ID | Descripción | Tipo | Estado | Defectos |
| :--- | :--- | :--- | :--- | :--- |
| **FN2-CP-001** | Crear recordatorio válido (Mensual) | Manual | Exitoso | Ninguno |
| **FN2-CP-002** | Validación de Título vacío | Manual | Exitoso | Ninguno |
| **FN2-CP-003** | Validación de Título Excesivo (256 car.) | Manual | Exitoso | Ninguno |
| **FN2-CP-004** | Validación de fecha inexistente | Manual | Exitoso | Ninguno |

*(Nota para el Equipo Suri: Pega aquí la captura de pantalla de la alerta de recordatorio guardada con éxito y una captura del error cuando la fecha es inválida)*.

---

### 3.3. Registrar Actividad (RF-003)

| ID | Descripción | Tipo | Estado | Defectos |
| :--- | :--- | :--- | :--- | :--- |
| **FN3-CP-001** | Registrar actividad con descripción válida | Manual | Exitoso | Ninguno |
| **FN3-CP-002** | Validación sin seleccionar categoría | Manual | Exitoso | Ninguno |
| **FN3-CP-003** | Validación de descripción vacía | Manual | Exitoso | Ninguno |
| **FN3-CP-004** | Validación de descripción excesiva | Manual | Exitoso | Ninguno |

*(Nota para el Equipo Suri: Pega aquí la captura de la línea de tiempo mostrando la actividad y una captura mostrando el error del campo descripción)*.

---

## 4. Conclusiones
La ejecución del catálogo extendido de pruebas de Caja Negra (Partición de Equivalencia y Valores Límite) demuestra que la validación en la interfaz de usuario de Monica CRM es robusta. Todos los casos de prueba inválidos (f-) fueron rechazados satisfactoriamente por el sistema sin causar caídas en el servidor.

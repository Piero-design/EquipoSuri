# Plan de Pruebas - Monica CRM

## 1. Introducción
### 1.1. Alcance
Este plan proporciona el marco necesario para planificar, gestionar y ejecutar las pruebas del sistema **Monica CRM** (Personal Relationship Manager). El documento cubre tanto las **Pruebas Unitarias** automatizadas en el backend (PHP/Laravel), como las **Pruebas Funcionales** manuales de Caja Negra en el frontend.

### 1.2. Referencias
*   ISO/IEC/IEEE 29119
*   Documento de Requisitos y Arquitectura de Monica CRM.
*   Documentación técnica de Laravel 10 y PHPUnit.

---

## 2. Contexto de las Pruebas
### 2.1. Proyecto / Subprocesos de Prueba
El esfuerzo de pruebas se concentrará en tres módulos críticos del sistema:
*   **Módulo de Contactos:** Creación, edición y validación de entidades de contactos.
*   **Módulo de Recordatorios:** Asignación de fechas, frecuencias y validaciones lógicas de tiempo.
*   **Módulo de Actividades:** Registro, categorización y límites de descripción para actividades.

### 2.2. Elementos de Prueba
Se realizarán pruebas a los siguientes componentes:
*   **Capa de Presentación (Frontend):** Formularios web, interfaces de usuario y validaciones HTML5/JS.
*   **Capa de Negocio (Backend):** Clases de servicio en `App\Services\...`, validaciones de datos y lógica de excepciones.
*   **Infraestructura:** Flujos de integración continua (CI/CD) mediante GitHub Actions.

### 2.3. Alcance de la Prueba
**Incluido en el alcance:**
*   Diseño y ejecución de pruebas unitarias de valores límite.
*   Diseño y ejecución manual de pruebas funcionales de caja negra.
*   Validación de Integración Continua (Pipeline).

**Excluido del alcance:**
*   Pruebas de estrés y rendimiento.
*   Pruebas de seguridad avanzadas (Pen-testing).

---

## 3. Estrategia de Prueba
### 3.1. Entregables de Prueba
*   Plan de Pruebas (Este documento).
*   Diseño de Casos de Prueba Funcionales.
*   Informe de Ejecución de Pruebas Funcionales.
*   Código automatizado de Pruebas Unitarias (`*BoundaryTest.php`).

### 3.2. Técnicas de diseño de Prueba
*   **Partición de Equivalencia:** Para evaluar clases de datos válidas e inválidas en formularios.
*   **Análisis de Valores Límite:** Esencial para testear los bordes de la lógica de negocio (longitudes máximas de texto de 255 caracteres, formatos mínimos).
*   **Tabla de Decisión:** Para combinaciones lógicas de fechas y asignaciones.

### 3.3. Criterio de Finalización
Las pruebas se considerarán finalizadas cuando:
*   Las pruebas unitarias automatizadas (23 en total) alcancen un 100% de tasa de éxito ("Pass") en GitHub Actions.
*   Se ejecuten y documenten manualmente todos los Casos de Prueba Funcionales diseñados.

---

## 4. Entorno de Pruebas
### 4.1. Ambiente de Pruebas
*   **Entorno Automatizado (Backend):** Servidor Virtual de GitHub Actions (Ubuntu-latest).
*   **Entorno Manual (Frontend):** Despliegue en localhost (Servidor de pruebas) accesible desde navegadores Google Chrome y Brave.
*   **Base de Datos de Pruebas:** Instancia en memoria de SQLite (`database-test.sqlite`) con transacciones aisladas (`DatabaseTransactions`).

### 4.2. Herramientas
*   **PHPUnit:** Motor principal para escribir y asertar las pruebas unitarias.
*   **GitHub Actions:** Plataforma CI/CD para automatizar la ejecución del pipeline.
*   **Navegadores web:** Para la ejecución humana de la batería funcional.

---

## 5. Personal y Roles del "Equipo Suri"
Para garantizar un proceso estructurado, el equipo se distribuye de la siguiente manera:

*   **Piero Javier Bernahola Vilca:** QA Lead / Release Manager (Responsable de la integración de ramas, bases de datos y aprobación del pipeline).
*   **Max Junior Soncco Mamani:** QA Engineer / DevOps (Configuración de entornos CI/CD, Node.js y flujos).
*   **Sebastian Diaz Ticona:** Test Analyst (Diseño y ejecución de pruebas del Módulo de Contactos).
*   **Christian Henry Venero Guevara:** Test Analyst (Diseño y ejecución de pruebas del Módulo de Recordatorios).
*   **Cristian Raul Saya Vargas:** Test Analyst / Documentador (Diseño y ejecución de pruebas del Módulo de Actividades y consolidación de la Wiki).

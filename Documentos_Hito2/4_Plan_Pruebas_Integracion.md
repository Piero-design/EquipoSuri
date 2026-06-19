# Plan de Pruebas de Integración - Monica CRM

## 1. Introducción
### 1.1. Alcance
Este plan proporciona el marco necesario para planificar las Pruebas de Integración del sistema Monica CRM. Las pruebas de integración se enfocarán en validar la correcta comunicación entre los componentes del sistema, específicamente entre los controladores web, la capa de servicios (`App\Services`), y la persistencia en la Base de Datos.

*(Nota: De acuerdo a los lineamientos del Hito 2, este documento abarca únicamente la fase de planificación. La ejecución técnica se realizará en el Hito 3).*

## 2. Contexto de las Pruebas
### 2.1. Elementos de Prueba
Se probará la integración entre los siguientes componentes:
*   **Controlador <-> Capa de Servicio:** Validar que los datos ingresados en el formulario web fluyan correctamente hacia el servicio de creación.
*   **Capa de Servicio <-> Base de Datos (Eloquent/MySQL):** Validar que las transacciones y consultas a la base de datos se ejecuten correctamente y manejen errores de concurrencia.
*   **Aplicación <-> Sistema de Archivos/APIs:** Validar la subida de avatares y exportación de datos.

## 3. Estrategia de Prueba
### 3.1. Enfoque (Top-Down / Bottom-Up)
Se utilizará un enfoque **Bottom-Up**. Las pruebas unitarias (Módulos individuales) ya fueron ejecutadas con éxito (100% de tasa de éxito en el Hito 1/2). Ahora se integrarán gradualmente los módulos de la base de datos hacia arriba hasta llegar a las vistas web.

### 3.2. Técnicas de Prueba
*   **Mocks y Stubs (Mockery):** Para simular respuestas de servicios externos, como el envío de emails o el sistema de almacenamiento en la nube, enfocándonos solo en la integración de nuestro propio código.
*   **Testing de Transacciones de Base de Datos:** Usando los traits de Laravel (`RefreshDatabase` o `DatabaseTransactions`) para asegurar que la integración no corrompa los datos.

## 4. Requisitos del entorno
*   **Framework:** PHPUnit nativo de Laravel.
*   **Entorno:** GitHub Actions (Ubuntu) con base de datos MySQL 8.0 nativa.
*   **Datos de Prueba:** Se usarán `Factories` y `Seeders` de Laravel para generar datos de integración realistas y consistentes antes de cada prueba.

## 5. Cronograma Propuesto (Para el Hito 3)
1.  **Semana 1:** Codificación de pruebas de integración para el Módulo de Contactos y Base de Datos.
2.  **Semana 2:** Codificación de pruebas de integración para Recordatorios y Actividades.
3.  **Semana 3:** Ejecución en pipeline CI/CD y análisis de cobertura (Coverage).
4.  **Semana 4:** Elaboración del Informe Final de Pruebas de Sistema y Aceptación (Entrega Hito 3).

# Presentación del Producto de Software: Monica CRM

Bienvenido al portal oficial del Equipo Suri. Para nuestro proyecto de Ingeniería de Software, hemos seleccionado **Monica CRM**, una de las plataformas de código abierto más aclamadas de la comunidad GitHub.

---

## 🌟 ¿Qué es Monica CRM y por qué lo elegimos?
A diferencia de los CRM tradicionales (Customer Relationship Management) que están diseñados para vender productos a clientes, Monica es un **Personal CRM**. 

Está diseñado exclusivamente para **gestionar relaciones humanas**. Ayuda a las personas a llevar un registro significativo de sus interacciones con amigos, familiares e introducciones de negocios. Permite recordar cumpleaños, documentar conversaciones importantes, registrar regalos dados/recibidos y llevar un control de deudas personales.

**Razones de nuestra elección:**
1. **Dominio Innovador:** Ataca un nicho de mercado real (salud mental y relaciones sociales) alejándose de los aburridos sistemas de inventario o ventas.
2. **Licencia Open Source:** Cuenta con una licencia permisiva (AGPL/MIT) que nos permite hacer *fork*, modificar y analizar su código de manera legal para fines académicos.
3. **Infraestructura Moderna:** Incluye orquestación nativa mediante Docker Compose, facilitando nuestro flujo de DevOps.

---

## 📊 Arquitectura y Complejidad (Métricas)
El proyecto ha sido evaluado bajo estrictas métricas de calidad de software para asegurar que cumple con el grado de complejidad exigido:

* **Stack Tecnológico:** Laravel (PHP) en el Backend + Vue.js/Blade en el Frontend. Base de datos MySQL.
* **Líneas de Código (KLOC):** Al realizar un análisis estático de la capa de lógica de negocio (Core Business Logic ubicado en el directorio `app/`), el sistema cuenta con exactamente **49.1 KLOC (49,193 líneas de código)**.
* **Justificación de Complejidad:** Con sus 49.1 KLOC, Monica se posiciona milimétricamente dentro de nuestra rúbrica de complejidad mediana-alta (10 KLOC - 50 KLOC), garantizando un sistema robusto pero manejable para un equipo de 5 personas.

---

## 🏗️ Arquitectura del Sistema
El proyecto está estructurado bajo el patrón arquitectónico **MVC (Modelo-Vista-Controlador)**, garantizando una alta cohesión y bajo acoplamiento:
*   **Backend (Lógica de Negocio):** Desarrollado en PHP bajo el potente framework **Laravel 9/10**. Utiliza el patrón avanzado de *Services* (`app/Services/`) para mantener los controladores limpios.
*   **Frontend (Interfaz de Usuario):** Construido de forma reactiva utilizando el framework **Vue.js** emparejado con el motor de plantillas de backend *Blade*.
*   **Capa de Persistencia (Datos):** Utiliza bases de datos relacionales (MySQL / SQLite), manejadas íntegramente a través del ORM (Object-Relational Mapping) *Eloquent*.
*   **Capa de API RESTful:** Cuenta con una arquitectura de servicios completamente documentada bajo estándares JSON y protegida por autenticación basada en *Bearer Tokens* (Laravel Passport).

---

## 🧩 Módulos Principales
El sistema se compone de múltiples subsistemas interconectados. Para este ciclo, nuestro equipo analizará los siguientes módulos base:

1. **Módulo de Contactos (Core):** Gestión del ciclo de vida de una persona (datos demográficos, avatares, relaciones familiares e introducciones).
2. **Módulo de Actividades y Tareas:** Sistema de tracking para registrar qué hicimos con un contacto y tareas pendientes relacionadas a esa persona.
3. **Módulo de Recordatorios Inteligentes:** Motor matemático que calcula y alerta sobre eventos recurrentes (aniversarios, cumpleaños).
4. **Módulo de Diario (Journal):** Subsistema para que el usuario documente sus pensamientos o resumen del día a modo de bitácora personal.

---

*Proyecto gestionado por el Equipo Suri mediante metodología Scrum. Todos los planes de prueba y tableros están disponibles en nuestro ecosistema de GitHub.*

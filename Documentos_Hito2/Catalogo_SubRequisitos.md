# Catálogo de Requisitos Funcionales (Sub-requisitos) - Monica CRM

---
### Módulo: RF-001 Gestión de Usuarios y Autenticación

**Requisito:** RF-001.1
**Nombre:** Registro de cuenta
**Descripción:** Permite a un nuevo usuario registrarse en el sistema proporcionando su nombre, correo y contraseña.
**Prioridad:** Alta
**Criterios de Aceptación:**
* El sistema debe validar que el correo tenga formato válido.
* La contraseña debe tener un mínimo de 8 caracteres.
* Si el correo ya existe, mostrar un mensaje de error.
**Requisitos asociados:** RF-001

**Requisito:** RF-001.2
**Nombre:** Inicio de sesión (Login)
**Descripción:** Permite a los usuarios registrados acceder al sistema mediante correo y contraseña.
**Prioridad:** Alta
**Criterios de Aceptación:**
* El sistema debe conceder acceso si las credenciales son correctas.
* El sistema debe denegar el acceso y mostrar alerta si la contraseña es incorrecta.
**Requisitos asociados:** RF-001

**Requisito:** RF-001.3
**Nombre:** Recuperación de contraseña
**Descripción:** Permite solicitar un enlace de recuperación enviado al correo del usuario.
**Prioridad:** Media
**Criterios de Aceptación:**
* El sistema debe enviar un correo electrónico si la dirección está registrada.
* No debe confirmar en pantalla si el correo existe o no (por seguridad).
**Requisitos asociados:** RF-001

---
### Módulo: RF-002 Personalización y Ajustes

**Requisito:** RF-002.1
**Nombre:** Configuración de idioma y zona horaria
**Descripción:** Permite al usuario cambiar el idioma de la interfaz y la zona horaria del sistema.
**Prioridad:** Media
**Criterios de Aceptación:**
* El usuario debe poder seleccionar un idioma de una lista predefinida.
* Las fechas del sistema deben ajustarse a la zona horaria elegida.
**Requisitos asociados:** RF-002

**Requisito:** RF-002.2
**Nombre:** Gestión de categorías del sistema
**Descripción:** Permite al usuario crear, editar o eliminar categorías personalizadas para actividades.
**Prioridad:** Media
**Criterios de Aceptación:**
* El nombre de la categoría no puede estar vacío.
* Se debe reflejar inmediatamente en los formularios de selección.
**Requisitos asociados:** RF-002

---
### Módulo: RF-003 Gestión de Contactos

**Requisito:** RF-003.1
**Nombre:** Añadir nuevo contacto
**Descripción:** Permite registrar el perfil de una persona con su nombre, apellido y datos básicos.
**Prioridad:** Alta
**Criterios de Aceptación:**
* El campo "First name" (Nombre) es obligatorio y no puede estar vacío.
* El sistema debe redirigir al perfil del contacto tras la creación exitosa.
* Los nombres no deben exceder los 255 caracteres.
**Requisitos asociados:** RF-003

**Requisito:** RF-003.2
**Nombre:** Editar información del contacto
**Descripción:** Permite modificar los datos personales de un contacto existente.
**Prioridad:** Alta
**Criterios de Aceptación:**
* Los cambios deben guardarse y reflejarse inmediatamente en el perfil.
* Si se borra el nombre obligatorio, el sistema debe arrojar un error de validación.
**Requisitos asociados:** RF-003

**Requisito:** RF-003.3
**Nombre:** Eliminar contacto
**Descripción:** Permite borrar permanentemente a un contacto y toda su información asociada.
**Prioridad:** Alta
**Criterios de Aceptación:**
* Debe solicitar confirmación antes de eliminar.
* Una vez eliminado, el contacto no debe aparecer en las búsquedas ni en el Dashboard.
**Requisitos asociados:** RF-003

---
### Módulo: RF-004 Gestión de Recordatorios

**Requisito:** RF-004.1
**Nombre:** Añadir recordatorio a contacto
**Descripción:** Permite programar una alerta (recurrente o única) en el perfil de un contacto.
**Prioridad:** Alta
**Criterios de Aceptación:**
* El título y la fecha son obligatorios.
* La fecha ingresada debe tener un formato válido de calendario.
**Requisitos asociados:** RF-004

**Requisito:** RF-004.2
**Nombre:** Editar y Eliminar recordatorio
**Descripción:** Permite modificar la frecuencia/fecha o borrar un recordatorio existente.
**Prioridad:** Media
**Criterios de Aceptación:**
* Las modificaciones deben actualizar el contador de próximos eventos en el Dashboard.
* La eliminación debe ser inmediata tras la confirmación.
**Requisitos asociados:** RF-004

---
### Módulo: RF-005 Gestión de Actividades

**Requisito:** RF-005.1
**Nombre:** Registrar nueva actividad
**Descripción:** Permite guardar el registro de una interacción con el contacto (ej. llamada, reunión).
**Prioridad:** Alta
**Criterios de Aceptación:**
* La descripción y la categoría son campos obligatorios.
* La actividad debe aparecer en el historial cronológico del contacto.
**Requisitos asociados:** RF-005

**Requisito:** RF-005.2
**Nombre:** Editar y Eliminar actividad
**Descripción:** Permite corregir o remover el registro de una actividad.
**Prioridad:** Media
**Criterios de Aceptación:**
* Los cambios en la descripción deben reflejarse en la línea de tiempo.
* Al eliminar, el registro desaparece permanentemente del historial.
**Requisitos asociados:** RF-005

---
### Módulo: RF-006 Gestión de Relaciones

**Requisito:** RF-006.1
**Nombre:** Vincular contacto con relación
**Descripción:** Permite establecer un vínculo familiar o profesional entre dos contactos.
**Prioridad:** Media
**Criterios de Aceptación:**
* Se debe seleccionar el tipo de relación de una lista predefinida (ej. "hermano", "esposo").
* La relación debe mostrarse en los perfiles de ambos contactos (bidireccional).
**Requisitos asociados:** RF-006

**Requisito:** RF-006.2
**Nombre:** Eliminar vínculo de relación
**Descripción:** Permite desvincular a dos contactos.
**Prioridad:** Baja
**Criterios de Aceptación:**
* Al eliminar la relación en un perfil, debe desaparecer automáticamente del perfil del otro contacto.
**Requisitos asociados:** RF-006

---
### Módulo: RF-007 Gestión de Tareas

**Requisito:** RF-007.1
**Nombre:** Crear tarea pendiente
**Descripción:** Permite añadir un ítem a la lista de "To-Do" asociado a un contacto.
**Prioridad:** Media
**Criterios de Aceptación:**
* El título de la tarea no puede estar vacío.
* La tarea debe aparecer en la pestaña "Tasks" del contacto.
**Requisitos asociados:** RF-007

**Requisito:** RF-007.2
**Nombre:** Marcar tarea como completada
**Descripción:** Permite cambiar el estado de la tarea a finalizada.
**Prioridad:** Media
**Criterios de Aceptación:**
* El sistema debe tachar o mover la tarea a la sección de "Completadas".
* La acción debe ser reversible (desmarcar).
**Requisitos asociados:** RF-007

---
### Módulo: RF-008 Gestión de Notas

**Requisito:** RF-008.1
**Nombre:** Añadir nota al contacto
**Descripción:** Permite agregar comentarios extensos de texto libre al perfil de un contacto.
**Prioridad:** Media
**Criterios de Aceptación:**
* El cuerpo de la nota es obligatorio.
* La nota debe mostrar la fecha y hora exacta de su creación.
**Requisitos asociados:** RF-008

**Requisito:** RF-008.2
**Nombre:** Editar y Eliminar nota
**Descripción:** Permite modificar el contenido de una nota previa o borrarla.
**Prioridad:** Baja
**Criterios de Aceptación:**
* La edición debe guardar los cambios correctamente sin perder el formato de texto.
* La nota eliminada no debe poder ser recuperada.
**Requisitos asociados:** RF-008

---
### Módulo: RF-009 Gestión de Diarios (Journal)

**Requisito:** RF-009.1
**Nombre:** Crear entrada de diario
**Descripción:** Permite al usuario redactar una memoria personal en la sección Journal.
**Prioridad:** Media
**Criterios de Aceptación:**
* El campo de texto de la entrada no puede estar vacío.
* La entrada debe guardarse bajo la fecha actual o la fecha seleccionada.
**Requisitos asociados:** RF-009

**Requisito:** RF-009.2
**Nombre:** Editar entrada de diario
**Descripción:** Permite actualizar el texto de una memoria pasada.
**Prioridad:** Baja
**Criterios de Aceptación:**
* Los cambios deben persistir en la base de datos inmediatamente.
**Requisitos asociados:** RF-009

---
### Módulo: RF-010 Panel Principal y Notificaciones

**Requisito:** RF-010.1
**Nombre:** Visualización del Dashboard
**Descripción:** Muestra un resumen general de los próximos recordatorios y contactos recientes.
**Prioridad:** Alta
**Criterios de Aceptación:**
* Los recordatorios cuyas fechas estén próximas (ej. siguientes 7 días) deben aparecer destacados.
* Si no hay eventos, debe mostrar un mensaje de "No hay próximos eventos".
**Requisitos asociados:** RF-010

**Requisito:** RF-010.2
**Nombre:** Visualización de Notificaciones
**Descripción:** Permite ver las alertas del sistema (ej. exportaciones completadas).
**Prioridad:** Media
**Criterios de Aceptación:**
* Debe existir un contador visual (campanita) cuando hay nuevas alertas.
* Las notificaciones leídas deben poder marcarse como "vistas".
**Requisitos asociados:** RF-010

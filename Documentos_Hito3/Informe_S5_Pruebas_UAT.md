# Informe S5 — Pruebas de Aceptación de Usuario (UAT)

**Proyecto:** Aseguramiento de calidad de Monica CRM · **Equipo Suri**
**Responsable:** Piero (QA Lead) · Ejecución manual

## 1. Propósito y alcance

La UAT valida que Monica satisfaga las necesidades de un usuario final real, ejecutando manualmente escenarios representativos sobre la interfaz. Se ejecutaron **13 escenarios** en cuatro áreas: gestión de contactos, recordatorios, actividades y validaciones de frontera.

**Criterio de aprobación:** el resultado observado coincide con el esperado y se cumple el criterio de aceptación, sin errores no controlados ni pérdida de datos.

**Entorno:** Monica CRM (Laravel 9/PHP 8.2/MySQL 8.0) · navegador Chrome · macOS.

## 2. Resumen de métricas

| Métrica | Valor |
|---|---|
| Escenarios ejecutados | 13 |
| Aprobados | 13 |
| Rechazados | 0 |
| **% de aprobación** | **100 %** |

## 3. Gestión de contactos

### UAT-CON-01 — Registrar un contacto nuevo · ✅ APROBADO
- **Precondición:** usuario autenticado en el panel principal.
- **Pasos:** (1) 'Add a contact' → (2) nombre «María», apellido «García» → (3) guardar.
- **Esperado:** redirige a la ficha mostrando «María García».
- **Criterio:** el contacto aparece en el listado y su ficha abre sin errores.

### UAT-CON-02 — Editar un contacto · ✅ APROBADO
- **Pasos:** abrir ficha → 'Edit' → apellido «García López» → guardar.
- **Esperado:** la ficha muestra «María García López». **Criterio:** persiste tras recargar.

### UAT-CON-03 — Eliminar un contacto · ✅ APROBADO
- **Pasos:** abrir ficha → eliminar → confirmar en el diálogo.
- **Esperado:** vuelve al listado sin el contacto. **Criterio:** no aparece en búsquedas.

## 4. Creación de recordatorios

### UAT-REM-01 — Crear recordatorio mensual · ✅ APROBADO
- **Pasos:** ficha del contacto → 'Add a reminder' → título «Llamada mensual de seguimiento» → frecuencia mensual + fecha → guardar.
- **Esperado:** aparece listado en la ficha. **Criterio:** asociado al contacto correcto.

### UAT-REM-02 — Editar frecuencia (mensual → anual) · ✅ APROBADO
- **Esperado:** refleja la nueva frecuencia. **Criterio:** persiste tras recargar.

### UAT-REM-03 — Eliminar recordatorio · ✅ APROBADO
- **Esperado:** desaparece del listado. **Criterio:** no reaparece tras recargar.

## 5. Registro de actividades

### UAT-ACT-01 — Registrar actividad · ✅ APROBADO
- **Pasos:** sección actividades → 'Add an activity' → tipo + resumen «Almuerzo de negocios» + fecha → guardar.
- **Esperado:** aparece en la línea de tiempo del contacto.

### UAT-ACT-02 — Actividad con varios contactos · ✅ APROBADO
- **Esperado:** la actividad «Reunión grupal» aparece en la línea de tiempo de ambos contactos.

### UAT-ACT-03 — Editar y eliminar actividad · ✅ APROBADO
- **Esperado:** refleja el resumen editado; tras eliminar, deja de aparecer.

## 6. Validaciones de frontera

### UAT-VAL-01 — Contacto sin nombre · ✅ APROBADO
- **Esperado:** impide guardar y muestra mensaje de validación claro; no se crea el registro.

### UAT-VAL-02 — Recordatorio sin título · ✅ APROBADO
- **Esperado:** error de validación sobre el título; no se persiste.

### UAT-VAL-03 — Actividad sin resumen · ✅ APROBADO
- **Esperado:** rechaza el envío indicando el campo obligatorio.

### UAT-VAL-04 — Texto largo y caracteres especiales · ✅ APROBADO
- **Pasos:** ingresar «Reunión — café & té (2024)» y valores extensos → guardar → reabrir.
- **Esperado:** guarda íntegro, sin truncar ni corromper la codificación.

## 7. Conclusiones

Los flujos principales de Monica responden a las expectativas del usuario final y las validaciones de frontera protegen la integridad de los datos con mensajes claros. Los módulos evaluados se consideran **aptos para aceptación**.

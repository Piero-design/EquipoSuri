# Informe S8 — Pruebas de Integración (PHPUnit)

**Proyecto:** Aseguramiento de calidad de Monica CRM · **Equipo Suri**
**Responsable:** Piero (QA Lead) · Contactos por Max Soncco
**Ejecución:** automática en CI/CD (GitHub Actions, job `integration-tests`)

## 1. Propósito y alcance

Las pruebas de integración verifican que los componentes de Monica —rutas HTTP, controladores, servicios de dominio, modelos Eloquent y la persistencia en MySQL— colaboren correctamente, ejercitados mediante **peticiones HTTP reales contra la base de datos**.

La suite `Integration` comprende **20 casos**: 7 de Recordatorios y 6 de Actividades (QA Lead) más 7 de Contactos (Max Soncco).

## 2. Justificación de las APIs críticas elegidas

El docente exige seleccionar y justificar las APIs/rutas críticas. Se eligieron las de **Contactos, Recordatorios y Actividades** porque:

1. **Contactos** es la entidad núcleo del CRM: todos los demás módulos (recordatorios, actividades, notas, tareas, regalos) dependen de ella. Una regresión aquí compromete todo el sistema.
2. **Recordatorios** materializa la propuesta de valor de Monica (no olvidar interacciones personales). Involucra lógica de programación de fechas (`ReminderOutbox`) y encadena controlador → servicio → dos tablas.
3. **Actividades** ejercita una **relación muchos-a-muchos** (pivote `activity_contact`) y un contrato de API distinto (respuestas JSON 201/200 en lugar de redirecciones), cubriendo así ambos estilos de respuesta de la aplicación.
4. Las tres rutas cubren los dos mecanismos de **resolución de rutas** del proyecto: hash cifrado con `Route::bind` (`{contact}`) e identificador numérico con binding implícito (`{activity}`) — una diferencia no documentada que, de hecho, produjo un defecto real (ver §6).

## 3. Entorno de ejecución

| Componente | Valor |
|---|---|
| Framework | PHPUnit 9.6.19 · PHP 8.2 |
| Base de datos | MySQL 8.0 (`monica_test`, esquema migrado en limpio) |
| Aislamiento | Trait `RefreshDatabase` |
| Middleware desactivado | `VerifyCsrfToken`, `CheckCompliance` (`withoutMiddleware`) |
| Datos de prueba | Legacy factories `factory(Modelo::class)->create()` |
| Comando | `vendor/bin/phpunit --testsuite Integration --testdox` |
| CI/CD | GitHub Actions · job `integration-tests` · runner ubuntu-latest |

**Metodología:** patrón *Arrange–Act–Assert*. Se preparan datos con factories (misma `account_id`), se emite la petición autenticada (`actingAs`) y se verifica código HTTP + estado de la base (`assertDatabaseHas` / `assertDatabaseMissing`).

## 4. Casos — Módulo Recordatorios (`ReminderIntegrationTest.php`)

Rutas: `POST|PUT|DELETE /people/{contactHashId}/reminders[...]` (respuesta 302).

| ID | Descripción | Esperado | Obtenido | Estado |
|---|---|---|---|---|
| PI-REM-001 | Crear recordatorio mensual | 302; registro con `frequency_type=month` | Conforme | ✅ PASS |
| PI-REM-002 | Crear recordatorio anual | 302; `frequency_type=year` | Conforme | ✅ PASS |
| PI-REM-003 | Crear recordatorio de una sola vez | 302; `frequency_type=one_time` | Conforme | ✅ PASS |
| PI-REM-004 | Rechazar recordatorio sin título | Error de validación en `title`; sin persistencia | Conforme | ✅ PASS |
| PI-REM-005 | Editar frecuencia (month → year) | 302; registro actualizado | Conforme | ✅ PASS |
| PI-REM-006 | Eliminar recordatorio | 302; registro ausente | Conforme | ✅ PASS |
| PI-REM-007 | Aislamiento entre contactos A y B | Registro solo en B | Conforme | ✅ PASS |

## 5. Casos — Módulo Actividades (`ActivityIntegrationTest.php`)

Rutas: `POST /activities` (201 JSON), `PUT|DELETE /activities/{id}` (200).

| ID | Descripción | Esperado | Obtenido | Estado |
|---|---|---|---|---|
| PI-ACT-001 | Registrar actividad con categoría y contacto | 201; registro + pivote | Conforme | ✅ PASS |
| PI-ACT-002 | Registrar con descripción y fecha | 201; campos persistidos | Conforme | ✅ PASS |
| PI-ACT-003 | Rechazar actividad sin `summary` | 422; sin persistencia | Conforme | ✅ PASS |
| PI-ACT-004 | Editar preservando fecha original | 200; `summary` actualizado | Conforme | ✅ PASS |
| PI-ACT-005 | Eliminar con limpieza del pivote | 200; registro y pivote ausentes | Conforme | ✅ PASS |
| PI-ACT-006 | Asociar a múltiples contactos | 201; ambos contactos en pivote | Conforme | ✅ PASS |

## 6. Defecto real detectado y corregido

- **Síntoma:** PI-ACT-004/005 fallaban en CI con `ModelNotFoundException: No query results for model [App\Models\Account\Activity]`.
- **Causa raíz:** las pruebas construían la URL con hash (`IdHasher`), replicando el patrón de Contactos; pero `Activity` extiende `Model` (no `ModelBinding`) y carece de `Route::bind`, por lo que `{activity}` se resuelve por **id numérico**.
- **Corrección:** usar `$activity->id` en las URLs de PUT/DELETE. Commit `a341a24`.
- **Valor:** evidencia del propósito de las pruebas de integración — revelan inconsistencias de convención entre módulos invisibles para pruebas unitarias.

## 7. Métricas de resultado

| Métrica | REM+ACT | Suite completa |
|---|---|---|
| Casos ejecutados | 13 | 20 |
| Exitosos (PASS) | 13 | 20 |
| Fallidos | 0 | 0 |
| % de éxito | **100 %** | **100 %** |

Job `integration-tests`: **SUCCEEDED** (~2 min 20 s) · 20 tests, 42 aserciones, 0 errores.

## 8. Evidencia

- Código: [`tests/Integration/`](../tests/Integration/)
- Ejecuciones: [GitHub Actions](https://github.com/Piero-design/EquipoSuri/actions) (workflow *CI/CD - Equipo Suri*, job `integration-tests`)
- Cobertura PHPUnit: artifact `coverage-report-phpunit` y [GitHub Pages](https://piero-design.github.io/EquipoSuri/)

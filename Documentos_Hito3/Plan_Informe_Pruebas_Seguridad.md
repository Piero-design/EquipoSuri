# Pruebas de Sistema — Atributo 1: SEGURIDAD

**Tipo (high-order testing, Myers):** Security testing
**Herramientas:** PHPUnit (suite ejecutable en CI) + revisión de configuración
**Evidencia ejecutable:** [`tests/Integration/SecurityIntegrationTest.php`](../tests/Integration/SecurityIntegrationTest.php) — corre automáticamente en el job `integration-tests` del pipeline.

## 1. Justificación de la elección

Monica es un CRM **personal**: almacena datos íntimos de contactos (relaciones, salud, regalos, conversaciones). Una falla de seguridad expone información sensible de terceros, por lo que la **confidencialidad y el control de acceso** son el atributo de calidad más crítico del sistema, por encima incluso del rendimiento.

## 2. Objetivos y alcance

Verificar los controles de seguridad de la aplicación en cuatro frentes: autenticación obligatoria, protección de operaciones de escritura, configuración segura de sesión y almacenamiento seguro de credenciales. Complementariamente, el aislamiento entre cuentas ya se verifica en la suite de integración (PI-REM-007 y caso de aislamiento de `ContactIntegrationTest`).

## 3. Casos de prueba y resultados

| ID | Control verificado | Método | Resultado esperado | Estado |
|---|---|---|---|---|
| PS-SEC-001 | Autenticación obligatoria en rutas protegidas (`/people`, `/journal`, `/settings`) | Petición GET sin sesión | Redirección 302 al login; nunca 200 | ✅ PASS |
| PS-SEC-002 | Protección de escritura: visitante no puede crear actividades | POST `/activities` sin sesión | Redirección; **cero** registros persistidos | ✅ PASS |
| PS-SEC-003 | Cookie de sesión segura | Aserción de configuración | `http_only=true` (mitiga XSS) y `same_site=lax` (mitiga CSRF) | ✅ PASS |
| PS-SEC-004 | Credenciales con hash | Inspección del hash de un usuario creado | Formato bcrypt (`$2y$...`), nunca texto plano | ✅ PASS |
| PS-SEC-005 | Aislamiento entre cuentas (cobertura cruzada) | Suite de integración existente | Un usuario no accede a datos de otra cuenta | ✅ PASS |

**Métricas:** 5/5 controles verificados · 100 % de éxito · ejecución reproducible en cada push (CI).

## 4. Controles adicionales observados en el código

- Middleware global `VerifyCsrfToken` activo en producción (token CSRF en formularios).
- Rutas sensibles bajo grupo `['auth', '2fa']` — soporte de **autenticación de dos factores** (`MFA_ENABLED`).
- Identificadores de contacto **hasheados** en URLs (`IdHasher`): dificulta la enumeración de recursos (IDOR).
- Hallazgo documentado (informe E2E): el acceso a un contacto de otra cuenta responde 500 en lugar de 404 limpio — **no expone datos**, pero degrada la experiencia de error; recomendación registrada (agregar `return` al redirect en `RouteServiceProvider`).

## 5. Reproducción

```bash
vendor/bin/phpunit --filter SecurityIntegrationTest --testdox
# o la suite completa:
vendor/bin/phpunit --testsuite Integration --testdox
```

Los resultados quedan visibles en cada ejecución del workflow **CI/CD - Equipo Suri** → job `integration-tests`.

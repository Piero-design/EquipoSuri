# [BORRADOR] Artículo Técnico IEEE — Aseguramiento de Calidad de Monica CRM

> **Instrucciones de uso:** este borrador contiene el contenido; debe volcarse a la **plantilla IEEE** (dos columnas) proporcionada por el docente. Extensión objetivo: 6–8 páginas. Revisar ortografía y citas antes de entregar. *(Plus opcional: versión en inglés.)*

---

**Título propuesto:** Aplicación de un proceso integral de pruebas de software sobre un CRM de código abierto: caso de estudio Monica

**Autores:** Bernahola Vilca P., Díaz Ticona S., Venero Guevara C., Soncco Mamani M., Saya Vargas C. — Escuela Profesional de Ingeniería de Sistemas, Universidad Nacional de San Agustín de Arequipa.

## Resumen (Abstract)

Este trabajo presenta la aplicación de un proceso integral de pruebas de software sobre Monica, un CRM personal de código abierto construido con Laravel 9, PHP 8.2 y MySQL 8.0. El proceso abarcó pruebas unitarias y funcionales, pruebas de integración sobre las API críticas, pruebas de sistema en dos atributos (seguridad y desempeño), pruebas de aceptación de usuario y automatización de extremo a extremo con Cypress, todo integrado en un pipeline de integración y despliegue continuos (CI/CD) con GitHub Actions. Como resultado se ejecutan automáticamente 20 pruebas de integración y 4 escenarios E2E en cada cambio, se detectaron y documentaron defectos reales del producto —incluido un error de programación de recordatorios de alta severidad— y se publican reportes de cobertura de forma automática. El caso demuestra que un proceso de pruebas por capas, automatizado en CI/CD, detecta de forma temprana defectos funcionales y de diseño no visibles mediante pruebas aisladas.

**Palabras clave:** pruebas de software, integración continua, Laravel, PHPUnit, Cypress, K6, pruebas de seguridad, pruebas de desempeño.

## I. Introducción

- Contexto: la calidad en aplicaciones web que gestionan datos personales; costo de los defectos en producción.
- Objetivo: aplicar un proceso completo de pruebas (unitarias → integración → sistema → aceptación → E2E) sobre un producto real de código abierto, automatizado en un pipeline.
- Aporte: evidencia empírica de defectos reales encontrados y corregidos; infraestructura reproducible.

## II. Caso de estudio: Monica CRM

- CRM personal open source (~20k LOC): gestión de contactos, recordatorios, actividades, diario.
- Stack: Laravel 9 (MVC + capa de servicios en `app/Services`), Vue.js en frontend, MySQL 8.0, autenticación con soporte 2FA, identificadores hasheados en URLs (`IdHasher`).
- Justificación de la elección: producto real con suite de pruebas preexistente parcial, complejidad representativa (40+ endpoints internos).

## III. Proceso de pruebas aplicado

### A. Pruebas unitarias y funcionales (PHPUnit)
- Suites Api/Feature/Unit del proyecto ejecutadas en CI con reporte de cobertura HTML publicado en GitHub Pages.
- Cobertura de líneas de la capa de lógica de negocio bajo prueba unitaria (Servicios, Modelos, Helpers, Jobs, Notificaciones): **~93 %**, superando el umbral del 85 %. El alcance de medición se define en `phpunit.xml`; la capa de controladores (HTTP) se valida mediante pruebas de integración y E2E, siguiendo la pirámide de pruebas.

### B. Pruebas de integración de APIs críticas (PHPUnit + MySQL real)
- 20 casos sobre Contactos, Recordatorios y Actividades; justificación de criticidad (entidad núcleo, propuesta de valor, relación N:M y contratos JSON).
- Patrón Arrange–Act–Assert con `actingAs`, `RefreshDatabase`, factories legadas y aserciones de base de datos.
- **Defecto detectado:** inconsistencia de resolución de rutas — `{contact}` usa hash cifrado con `Route::bind`, mientras `{activity}` usa binding implícito por id numérico; produjo `ModelNotFoundException` y se corrigió, documentando la convención.

### C. Pruebas de sistema (dos atributos)
1) **Seguridad:** suite ejecutable en CI (autenticación obligatoria, protección de escritura, cookies HttpOnly/SameSite, hash bcrypt) + aislamiento entre cuentas; hallazgo: respuesta 500 en vez de 404 ante acceso a contactos ajenos (sin fuga de datos; corrección de una línea propuesta).
2) **Desempeño (K6):** smoke test (1 VU) y load test (20 VUs) con umbrales automatizados en GitHub Actions. Resultados: 0 % de errores en ambos; p95 de 71.26 ms (smoke) y 386.24 ms (load), muy por debajo de los umbrales (800 ms / 1500 ms). La latencia media escala de 69 ms a 169 ms con la concurrencia, mostrando degradación controlada sin puntos de quiebre en el rango probado.

### D. Pruebas de aceptación de usuario (UAT)
- 13 escenarios manuales (contactos, recordatorios, actividades, fronteras): 100 % aprobados.

### E. Automatización E2E (Cypress) — *plus*
- 4 escenarios de flujo completo (RF-003/004/005/010) ejecutados en CI contra la aplicación real con MySQL.
- Defectos de infraestructura de prueba corregidos: login de prueba (`cy.request` ante respuesta 204) y aserciones sobre elementos removidos del DOM por Vue (`not.exist` vs `not.be.visible`).
- **Defecto de producto (alta severidad):** `DateHelper::addTimeAccordingToFrequencyType()` no contempla `one_time` y cae al `default` (suma un año): un recordatorio "de una sola vez" con fecha de hoy se agenda silenciosamente para dentro de un año. Reproducción, impacto y corrección propuesta documentadas.

### F. Pipeline CI/CD (GitHub Actions)
- 4 jobs encadenados: `phpunit` (+cobertura), `integration-tests`, `cypress-e2e` y `deploy-pages` (publicación automática si todo pasa).
- Análisis estático preparado con SonarCloud (SAST) como quality gate complementario.

## IV. Resultados

| Indicador | Valor |
|---|---|
| Pruebas de integración | 20/20 PASS (100 %) |
| Controles de seguridad (sistema) | 5/5 PASS |
| Escenarios UAT | 13/13 aprobados |
| Escenarios E2E automatizados en CI | 4/4 PASS |
| Cobertura unitaria/funcional (capa de lógica de negocio) | ~93 % líneas (Services 94.87 %, Models 85.56 %, Helpers 94.67 %) |
| Desempeño — p95 (smoke 1 VU / load 20 VUs) | 71.26 ms / 386.24 ms · 0 % errores |
| Defectos reales documentados | 6 hallazgos (3 defectos de producto, 2 de infraestructura de pruebas, 1 comportamiento no evidente) |

## V. Discusión

- Las pruebas de integración revelaron inconsistencias de convención (hash vs id) invisibles para pruebas unitarias.
- La automatización E2E destapó un defecto silencioso de lógica de negocio (recordatorios `one_time`) que ni unitarias ni integración cubrían: valor de las capas complementarias.
- CI/CD como red de seguridad: todo cambio ejecuta la pirámide completa antes de desplegar.
- Limitaciones: cobertura E2E acotada a specs validados; pruebas de desempeño sobre endpoints públicos.

## VI. Conclusiones

El proceso por capas, automatizado en CI/CD, permitió validar los módulos críticos de un producto real, detectar defectos de distinta naturaleza y severidad, y dejar una infraestructura reproducible (pipeline, suites, umbrales) que protege la calidad ante cambios futuros.

## Referencias (formato IEEE — completar numeración)

[1] G. J. Myers, C. Sandler y T. Badgett, *The Art of Software Testing*, 3.ª ed. Hoboken, NJ: Wiley, 2011.
[2] Monica CRM, "monicahq/monica," GitHub. [En línea]. Disponible: https://github.com/monicahq/monica
[3] Equipo Suri, "EquipoSuri — repositorio del proyecto," GitHub. [En línea]. Disponible: https://github.com/Piero-design/EquipoSuri
[4] Laravel, "Laravel 9.x Documentation." [En línea]. Disponible: https://laravel.com/docs/9.x
[5] Cypress.io, "Cypress Documentation." [En línea]. Disponible: https://docs.cypress.io
[6] Grafana Labs, "K6 Documentation." [En línea]. Disponible: https://k6.io/docs
[7] SonarSource, "SonarCloud Documentation." [En línea]. Disponible: https://docs.sonarcloud.io

# Revisión Final S11 — Checklist de cierre del Hito 3

**Rol:** QA Lead (Piero) · Actualizado tras las indicaciones finales del docente.

## 1. Criterios mínimos del docente

| # | Criterio | Estado | Evidencia |
|---|---|---|---|
| 1 | Pruebas unitarias/funcionales, cobertura ≥ 85 % | ⚠️ **VERIFICAR NÚMERO** | Artifact `coverage-report-phpunit` + GitHub Pages. *Acción: abrir el reporte, anotar el % global y por módulos probados; si < 85 % global, documentar cobertura de los módulos objetivo.* |
| 2 | Pruebas de integración de APIs críticas + justificación | ✅ | [Informe S8](Informe_S8_Pruebas_Integracion.md) §2 — 20 casos, 100 % PASS |
| 3 | Pruebas de sistema — 2 atributos | ✅ | [Seguridad](Plan_Informe_Pruebas_Seguridad.md) (suite en CI) + [Desempeño](Plan_Informe_Pruebas_Desempeno.md) (K6) — *ejecutar workflow K6 y volcar resultados* |
| 4 | Planes/informes en GitHub en `.md` | ✅ | `Documentos_Hito2/` y `Documentos_Hito3/` |
| 5 | Artículo técnico IEEE (6–8 págs.) | 🔧 En curso | [Borrador](Articulo_Tecnico_IEEE_borrador.md) → pasar a plantilla IEEE |
| 6 | Defensa: todos dominan todo el trabajo | 🔧 Preparación | Todos deben leer S5/S8/planes de sistema, el pipeline y el stack (§4) |

## 2. Estado del pipeline y entregables técnicos

| Ítem | Responsable | Estado |
|---|---|---|
| Workflow CI/CD (4 jobs) | Max | ✅ Verde |
| phpunit + cobertura | Equipo | ✅ Verde |
| integration-tests (20 casos + suite seguridad) | Piero / Max | ✅ Verde |
| cypress-e2e (4 specs E2E validados) — **plus automatización** | Sebastián / Piero | ✅ Verde |
| deploy-pages → GitHub Pages | Max | ✅ Publicado |
| Wiki (10 páginas) | Cristian Saya | ✅ |
| Workflow K6 (manual) | Piero | 🔧 Ejecutar y volcar resultados |
| SonarCloud (plus) | Equipo | 🔧 Activar según [guía](Guia_SonarQube.md) |

## 3. Fechas (indicaciones del docente)

- **Hasta el domingo 19/07:** última actualización de GitHub y Drive. Después, congelado.
- **20–22/07:** defensa presencial (~45 min por grupo; el docente pregunta a **todos**; quien diga «yo no hice esa parte» queda desaprobado automáticamente).
- **24/07:** cierre del curso.

## 4. Preparación de la defensa (temas que el docente anunció que preguntará)

1. **Pruebas funcionales/unitarias:** cómo se diseñaron, por qué se logró la cobertura, demostración en vivo desde la laptop.
2. **Pruebas de integración:** por qué esas APIs son críticas (ver S8 §2), cómo se llegó a los resultados, evidencia en CI.
3. **Pruebas de sistema (2 atributos):** diseño, umbrales y evidencia (suite de seguridad en CI; K6 con thresholds).
4. **Arquitectura y stack:** Laravel 9 (MVC + servicios), Vue en frontend, MySQL; cómo se conectan front/back (rutas web + API, respuestas Blade vs JSON); ventajas/desventajas y versiones.
5. **Pipeline:** qué valida cada job, en qué orden, y cómo el push dispara pruebas → despliegue automático a Pages cuando todo pasa.
6. **Buenas prácticas de código:** patrón de servicios (`app/Services`), factories para datos de prueba, `RefreshDatabase`, hash de IDs en rutas.

## 5. Acciones pendientes antes del 19/07

- [ ] Verificar y documentar el **% de cobertura** (criterio 1).
- [ ] Ejecutar workflow **K6** y completar la tabla de resultados del plan de desempeño.
- [ ] Activar **SonarCloud** (SONAR_TOKEN) y correr el análisis (plus).
- [ ] Terminar el **artículo IEEE** y subirlo a Drive en el formato exigido.
- [ ] Ensayo de defensa grupal: cada integrante explica una sección que no escribió.
- [ ] Congelar el repo el 19/07 (último push).

## 6. Veredicto de QA

Los componentes técnicos exigidos están **operativos y en verde** en el pipeline; los documentos están en GitHub en formato `.md` como exige el docente. Quedan las acciones de la sección 5 (evidencias K6/Sonar, número de cobertura y artículo) antes del congelamiento del 19/07.

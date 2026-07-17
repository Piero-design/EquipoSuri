# Hito 3 — Entregable Final · Equipo Suri

Proyecto de aseguramiento de calidad sobre **Monica CRM** (Laravel 9 · PHP 8.2 · MySQL 8.0).

## Fechas clave (indicaciones del docente — sesión final)

| Evento | Fecha |
|---|---|
| Última actualización de GitHub y Drive | **Domingo 19 de julio** (después de esa fecha no se aceptan cambios) |
| Defensa presencial del Hito 3 (~45 min por grupo, participan y responden **todos** los integrantes) | Entre el 20 y 22 de julio |
| Cierre total del curso | 24 de julio |

## Criterios mínimos exigidos

1. **Pruebas unitarias/funcionales** con cobertura **≥ 85 %** (reporte de cobertura publicado como artifact del CI y en GitHub Pages).
2. **Pruebas de integración a nivel de APIs críticas**, con justificación de por qué esas rutas son críticas → [Informe S8](Informe_S8_Pruebas_Integracion.md).
3. **Pruebas de sistema: dos atributos elegidos** (high-order testing, Myers):
   - **Seguridad** → [Plan e informe](Plan_Informe_Pruebas_Seguridad.md) · suite ejecutable `tests/Integration/SecurityIntegrationTest.php` (corre en CI).
   - **Desempeño** → [Plan e informe](Plan_Informe_Pruebas_Desempeno.md) · scripts K6 en `tests/k6/` + workflow manual `performance.yml`.
4. **Todos los planes e informes en GitHub en formato `.md`** (este directorio y `Documentos_Hito2/`). No se requieren PDFs.
5. **Artículo técnico IEEE** (6–8 páginas), único documento externo → [Borrador](Articulo_Tecnico_IEEE_borrador.md).

## Pluses opcionales (suben la nota grupal)

| Plus | Estado en el equipo |
|---|---|
| Automatización E2E (Selenium / Cypress / Playwright) | ✅ **HECHO** — 4 specs Cypress validados corriendo en el pipeline (job `cypress-e2e`) |
| SonarQube/SonarCloud unido al pipeline | 🔧 Preparado — workflow `sonarcloud.yml` + [guía de activación](Guia_SonarQube.md) |
| Artículo y defensa en inglés | Decisión pendiente del equipo |

## Documentos de este directorio

- [Informe S5 — Pruebas de Aceptación de Usuario (UAT)](Informe_S5_Pruebas_UAT.md)
- [Informe S8 — Pruebas de Integración (PHPUnit)](Informe_S8_Pruebas_Integracion.md)
- [Pruebas de Sistema: Seguridad](Plan_Informe_Pruebas_Seguridad.md)
- [Pruebas de Sistema: Desempeño (K6)](Plan_Informe_Pruebas_Desempeno.md)
- [Revisión Final S11 — Checklist de cierre](Revision_Final_S11_Checklist.md)
- [Guía SonarQube/SonarCloud](Guia_SonarQube.md)
- [Borrador del Artículo Técnico IEEE](Articulo_Tecnico_IEEE_borrador.md)

## Enlaces del proyecto

- Repositorio: <https://github.com/Piero-design/EquipoSuri>
- Pipeline CI/CD: <https://github.com/Piero-design/EquipoSuri/actions>
- GitHub Pages (cobertura + documentación): <https://piero-design.github.io/EquipoSuri/>
- Wiki: <https://github.com/Piero-design/EquipoSuri/wiki>

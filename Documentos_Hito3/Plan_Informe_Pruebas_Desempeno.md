# Pruebas de Sistema — Atributo 2: DESEMPEÑO

**Tipo (high-order testing, Myers):** Performance/Load testing
**Herramienta:** [K6](https://k6.io) (herramienta vista en laboratorio)
**Scripts:** [`tests/k6/smoke.js`](../tests/k6/smoke.js) · [`tests/k6/load.js`](../tests/k6/load.js)
**Workflow de evidencia:** `.github/workflows/performance.yml` (ejecución manual: *Actions → Pruebas de Desempeño (K6) → Run workflow*)

## 1. Justificación de la elección

Monica renderiza vistas Blade + componentes Vue con múltiples consultas por página. El tiempo de respuesta percibido es determinante para un CRM de uso cotidiano: si la aplicación se degrada con usuarios concurrentes, pierde su utilidad. Se mide el comportamiento del sistema **bajo carga mínima (smoke)** y **bajo carga creciente (load)** para establecer una línea base y detectar degradación.

## 2. Diseño de las pruebas

### PS-PERF-001 — Smoke test
- **Configuración:** 1 usuario virtual (VU) durante 30 s sobre las páginas públicas (`/` y `/register`).
- **Umbrales (thresholds):** errores < 1 % · p95 de latencia < 800 ms.
- **Objetivo:** verificar estabilidad básica y establecer la línea base de latencia.

### PS-PERF-002 — Load test
- **Configuración:** rampa 0→10 VUs (30 s), meseta de 20 VUs (1 min), rampa de bajada (30 s).
- **Umbrales:** errores < 5 % · p95 < 1500 ms bajo carga.
- **Objetivo:** observar la degradación de latencia y tasa de error con 20 usuarios concurrentes.

Los umbrales hacen que K6 **falle automáticamente** la ejecución si no se cumplen (criterio de aceptación automatizado).

## 3. Entornos de ejecución

| Entorno | Comando |
|---|---|
| Local (servidor en `localhost:8000`) | `k6 run tests/k6/smoke.js` y `k6 run tests/k6/load.js` |
| CI (GitHub Actions) | Workflow manual **Pruebas de Desempeño (K6)** — levanta MySQL + Monica igual que el job de Cypress, corre ambos scripts y publica `results-smoke.json` / `results-load.json` como artifact |
| Otra URL (p. ej. despliegue en la nube) | `k6 run -e BASE_URL=https://<dominio> tests/k6/smoke.js` |

## 4. Resultados

> Ejecutar el workflow **Pruebas de Desempeño (K6)** desde la pestaña Actions y volcar aquí el resumen del artifact.

| Métrica | PS-PERF-001 (smoke) | PS-PERF-002 (load 20 VUs) | Umbral | Estado |
|---|---|---|---|---|
| Peticiones totales | _(completar)_ | _(completar)_ | — | |
| Tasa de error (`http_req_failed`) | _(completar)_ | _(completar)_ | < 1 % / < 5 % | |
| Latencia p95 (`http_req_duration`) | _(completar)_ | _(completar)_ | < 800 ms / < 1500 ms | |
| Latencia media | _(completar)_ | _(completar)_ | — | |

## 5. Interpretación y criterio de aceptación

La ejecución se considera **APROBADA** si K6 finaliza sin violar los umbrales (exit code 0). Ante una violación, K6 reporta qué threshold falló, lo que permite localizar la degradación (latencia vs. errores) y correlacionarla con la carga aplicada.

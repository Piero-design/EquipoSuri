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

Ejecución en GitHub Actions (workflow *Pruebas de Desempeño (K6)*, run #1): **job en verde — ambos umbrales cumplidos**. Métricas del resumen de K6:

| Métrica | PS-PERF-001 (smoke, 1 VU) | PS-PERF-002 (load, 20 VUs) | Umbral | Estado |
|---|---|---|---|---|
| Peticiones totales (`http_reqs`) | 75 (2.48/s) | 1168 (9.59/s) | — | — |
| Iteraciones | 25 | 584 | — | — |
| Checks superados | 100 % (75/75) | 100 % (584/584) | — | ✅ |
| Tasa de error (`http_req_failed`) | **0.00 %** | **0.00 %** | < 1 % / < 5 % | ✅ |
| Latencia media (`http_req_duration` avg) | 69.15 ms | 169.29 ms | — | — |
| Latencia mediana (med) | 69.53 ms | 138.77 ms | — | — |
| **Latencia p95** | **71.26 ms** | **386.24 ms** | < 800 ms / < 1500 ms | ✅ |
| Latencia máxima (max) | 82.41 ms | 563.63 ms | — | — |

## 5. Interpretación y criterio de aceptación

- **Smoke (1 VU):** el sistema responde con latencias muy bajas y estables (p95 71 ms) y **0 % de errores** — línea base saludable.
- **Load (20 VUs):** al escalar la concurrencia, la latencia media sube de 69 ms a 169 ms y el p95 a 386 ms, pero **sin errores (0 %)** y **muy por debajo del umbral de 1500 ms**. El sistema absorbe 20 usuarios concurrentes con degradación controlada.
- **Criterio de aceptación:** K6 finaliza con *exit code 0* (ningún threshold violado) → ejecución **APROBADA** en ambos escenarios. La correlación latencia↔carga (69 ms → 169 ms) evidencia el comportamiento esperado bajo carga creciente, sin puntos de quiebre en el rango probado.

> Evidencia adicional: artifact `k6-performance-results` (`results-smoke.json`, `results-load.json`) del run en GitHub Actions.

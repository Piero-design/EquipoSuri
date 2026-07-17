import http from 'k6/http';
import { check, sleep } from 'k6';

/**
 * Pruebas de sistema — atributo: DESEMPEÑO.
 * PS-PERF-002 (Load test): mide el comportamiento bajo carga creciente,
 * escalando de 0 a 20 usuarios virtuales concurrentes y de vuelta a 0.
 *
 * Ejecución:  k6 run tests/k6/load.js
 * Variable:   BASE_URL (por defecto http://localhost:8000)
 */
export const options = {
  stages: [
    { duration: '30s', target: 10 },  // rampa de subida
    { duration: '1m', target: 20 },   // meseta de carga
    { duration: '30s', target: 0 },   // rampa de bajada
  ],
  thresholds: {
    http_req_failed: ['rate<0.05'],     // < 5 % de errores bajo carga
    http_req_duration: ['p(95)<1500'],  // p95 < 1,5 s bajo carga
  },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';

export default function () {
  const res = http.get(`${BASE_URL}/`);
  check(res, {
    'respuesta 200': (r) => r.status === 200,
  });
  sleep(Math.random() * 2 + 1);
}

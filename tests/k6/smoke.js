import http from 'k6/http';
import { check, sleep } from 'k6';

/**
 * Pruebas de sistema — atributo: DESEMPEÑO.
 * PS-PERF-001 (Smoke test): verifica que la aplicación responde de forma
 * estable y rápida con carga mínima (1 usuario virtual, 30 s).
 *
 * Ejecución:  k6 run tests/k6/smoke.js
 * Variable:   BASE_URL (por defecto http://localhost:8000)
 */
export const options = {
  vus: 1,
  duration: '30s',
  thresholds: {
    http_req_failed: ['rate<0.01'],     // < 1 % de errores
    http_req_duration: ['p(95)<800'],   // p95 < 800 ms
  },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';

export default function () {
  const login = http.get(`${BASE_URL}/`);
  check(login, {
    'login responde 200': (r) => r.status === 200,
    'login carga en < 800 ms': (r) => r.timings.duration < 800,
  });

  const register = http.get(`${BASE_URL}/register`);
  check(register, {
    'registro responde 200': (r) => r.status === 200,
  });

  sleep(1);
}

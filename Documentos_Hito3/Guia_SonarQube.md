# Guía — Plus SonarQube/SonarCloud en el pipeline

El docente ofrece un **plus** por integrar SonarQube (Community) al pipeline. Usamos **SonarCloud**, la edición en la nube del mismo motor, gratuita para repositorios públicos y con integración nativa a GitHub Actions — evita mantener un servidor propio.

## Qué está ya preparado en el repo

- `sonar-project.properties` — configurado para `Piero-design_EquipoSuri` (organización `piero-design`), con fuentes, tests y exclusiones.
- `.github/workflows/sonarcloud.yml` — workflow de ejecución **manual** (no interfiere con el pipeline principal).

## Pasos de activación (una sola vez, ~5 minutos)

1. Entrar a <https://sonarcloud.io> → **Log in with GitHub**.
2. **+ → Analyze new project** → autorizar la organización de GitHub → seleccionar el repo **EquipoSuri**.
   - Si SonarCloud sugiere otra *organization key*, actualizar `sonar.organization` en `sonar-project.properties` para que coincida.
3. En SonarCloud: **My Account → Security → Generate token** (nombre: `equiposuri-ci`). Copiar el token.
4. En GitHub: repo **EquipoSuri → Settings → Secrets and variables → Actions → New repository secret**:
   - Name: `SONAR_TOKEN` · Value: el token copiado.
5. En SonarCloud, en el proyecto: **Administration → Analysis Method** → desactivar *Automatic Analysis* (para usar el análisis desde CI).
6. Ejecutar: **Actions → Análisis SonarCloud → Run workflow**.

## Resultado esperado

El dashboard de SonarCloud mostrará: **vulnerabilidades, security hotspots, bugs, code smells, deuda técnica y duplicación** del código de Monica. Ese dashboard (o capturas) es la evidencia del plus para la defensa.

## Nota para la sustentación

- SonarQube/SonarCloud realiza **análisis estático (SAST)**: complementa las pruebas dinámicas (PHPUnit/Cypress/K6) detectando problemas sin ejecutar el código.
- Integrado al pipeline, actúa como **quality gate**: puede bloquear cambios que introduzcan vulnerabilidades o deuda por encima del umbral.

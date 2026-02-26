# ¿Qué es el Sistema POA?

El **Sistema POA** (Plan Operativo Anual) es una aplicación web institucional desarrollada para la **Alcaldía Municipal de Santa Ana Centro**. Digitaliza el ciclo completo de planificación y seguimiento de actividades de las unidades municipales.

## Problema que resuelve

Antes del sistema, cada unidad llenaba su POA manualmente en hojas de cálculo, sin validaciones ni trazabilidad. El proceso de revisión administrativa era lento y propenso a errores.

El sistema resuelve tres problemas concretos:

| # | Problema | Solución |
|---|---|---|
| 1 | Planificación sin estructura ni validaciones | Wizard guiado de 5 pasos con reglas de negocio |
| 2 | Seguimiento manual sin evidencias formales | Módulo de avances con carga de archivos por mes |
| 3 | Aprobación informal vía email | Panel de Admin con flujo de estados y auditoría |

## Ciclo de vida de un Proyecto POA

```
BORRADOR → ENVIADO → APROBADO
                  ↘ RECHAZADO → (corrección) → ENVIADO → ...
```

::: tip
Solo los proyectos en estado `APROBADO` se contabilizan en el cálculo del cumplimiento general del dashboard.
:::

## Usuarios del sistema

- **Unidad** — Técnico o jefe de una unidad municipal. Crea el POA, registra avances y sube evidencias.
- **Admin** — Administrador institucional. Aprueba/rechaza proyectos y gestiona usuarios, unidades y catálogos.

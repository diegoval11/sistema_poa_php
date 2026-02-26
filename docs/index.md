---
layout: home

hero:
  name: "Sistema POA"
  text: "Plan Operativo Anual"
  tagline: Documentación técnica para desarrolladores — Alcaldía Municipal de Santa Ana Centro
  image:
    src: /hero-illustration.svg
    alt: Sistema POA
  actions:
    - theme: brand
      text: Empezar →
      link: /guide/intro
    - theme: alt
      text: Ver Arquitectura
      link: /architecture/overview
    - theme: alt
      text: GitHub
      link: https://github.com/diegoval11/sistema_poa_php

features:
  - icon: 🧙
    title: Wizard de Planificación
    details: Flujo guiado de 5 pasos para que cada unidad municipal cree su Plan Operativo Anual con metas, actividades y cronograma mensual.
    link: /modules/wizard
    linkText: Ver módulo

  - icon: 📊
    title: Seguimiento de Avances
    details: Las unidades registran la ejecución mensual y adjuntan evidencias (PDFs, fotos, videos, URLs) por actividad.
    link: /modules/avances
    linkText: Ver módulo

  - icon: ✅
    title: Panel de Aprobación
    details: El administrador revisa, aprueba o rechaza proyectos con motivo. Gestiona usuarios, unidades y catálogos.
    link: /modules/admin
    linkText: Ver módulo

  - icon: 📥
    title: Exportaciones Oficiales
    details: Genera reportes Excel (POA Completo y Resumido) con fórmulas de cumplimiento y PDFs institucionales.
    link: /modules/exports
    linkText: Ver módulo

  - icon: 🔒
    title: Seguridad por Roles
    details: Dos roles definidos (admin / unidad) con middleware de control de acceso y cambio de contraseña obligatorio.
    link: /architecture/roles
    linkText: Ver roles

  - icon: 🔌
    title: Alta Extensibilidad
    details: Diseñado para crecer. Agrega nuevas validaciones, roles, formatos de exportación o tipos de evidencia sin romper la arquitectura.
    link: /extend/points
    linkText: Ver puntos de extensión
---

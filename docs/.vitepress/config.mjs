import { defineConfig } from 'vitepress'
import { withMermaid } from 'vitepress-plugin-mermaid'

export default withMermaid({
    title: 'Sistema POA',
    description: 'Documentación técnica del Sistema de Plan Operativo Anual — Alcaldía Municipal de Santa Ana Centro',
    lang: 'es-ES',
    base: '/',

    head: [
        ['link', { rel: 'icon', href: '/favicon.ico' }],
        ['meta', { name: 'theme-color', content: '#1a56db' }],
        ['meta', { name: 'og:type', content: 'website' }],
        ['meta', { name: 'og:title', content: 'Sistema POA — Documentación' }],
    ],

    themeConfig: {
        logo: '/logo.svg',
        siteTitle: 'Sistema POA',

        nav: [
            { text: '🏠 Inicio', link: '/' },
            { text: '📖 Guía', link: '/guide/intro' },
            { text: '🏗️ Arquitectura', link: '/architecture/overview' },
            { text: '⚙️ Módulos', link: '/modules/wizard' },
            { text: '🔌 API Interna', link: '/api/controllers' },
        ],

        sidebar: [
            {
                text: '🚀 Introducción',
                items: [
                    { text: 'Qué es el Sistema POA', link: '/guide/intro' },
                    { text: 'Stack Tecnológico', link: '/guide/stack' },
                    { text: 'Cómo levantar el proyecto', link: '/guide/setup' },
                ]
            },
            {
                text: '🏗️ Arquitectura',
                items: [
                    { text: 'Visión General', link: '/architecture/overview' },
                    { text: 'Modelos y Base de datos', link: '/architecture/models' },
                    { text: 'Roles y Middleware', link: '/architecture/roles' },
                ]
            },
            {
                text: '⚙️ Módulos del Sistema',
                items: [
                    { text: 'Wizard de Planificación', link: '/modules/wizard' },
                    { text: 'Registro de Avances', link: '/modules/avances' },
                    { text: 'Aprobación (Admin)', link: '/modules/admin' },
                    { text: 'Exportaciones Excel/PDF', link: '/modules/exports' },
                    { text: 'Actividades No Planificadas', link: '/modules/unplanned' },
                ]
            },
            {
                text: '🔌 Referencia de Código',
                items: [
                    { text: 'Controladores', link: '/api/controllers' },
                    { text: 'Servicios', link: '/api/services' },
                    { text: 'Rutas (web.php)', link: '/api/routes' },
                ]
            },
            {
                text: '📐 Extensibilidad',
                items: [
                    { text: 'Puntos de Extensión', link: '/extend/points' },
                    { text: 'Ejemplos de Uso', link: '/extend/examples' },
                ]
            }
        ],

        socialLinks: [
            { icon: 'github', link: 'https://github.com/diegoval11/sistema_poa_php' }
        ],

        footer: {
            message: 'Alcaldía Municipal de Santa Ana Centro',
            copyright: `© ${new Date().getFullYear()} Sistema POA — Documentación Técnica`
        },

        search: {
            provider: 'local'
        },

        editLink: {
            pattern: 'https://github.com/diegoval11/sistema_poa_php/edit/main/docs/:path',
            text: 'Editar esta página en GitHub'
        },

        lastUpdated: {
            text: 'Última actualización',
            formatOptions: {
                dateStyle: 'short',
                timeStyle: 'short'
            }
        },

        outline: {
            label: 'En esta página',
            level: [2, 3]
        },

        docFooter: {
            prev: 'Anterior',
            next: 'Siguiente'
        },

        returnToTopLabel: 'Volver arriba',
    },

    markdown: {
        theme: {
            light: 'github-light',
            dark: 'github-dark'
        },
        lineNumbers: true,
    }
})

#!/usr/bin/env python3
"""
build_single.py
Genera un único archivo HTML portátil con toda la documentación del Sistema POA.
- Sin dependencias externas en tiempo de ejecución
- Funciona abriendo con doble clic (protocolo file://)
- Incluye marked.js y mermaid.js descargados e incrustados

Uso: python3 build_single.py
Salida: sistema_poa_docs.html
"""

import json
import os
import urllib.request
import sys

DOCS_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_FILE = os.path.join(DOCS_DIR, "..", "sistema_poa_docs.html")

# ── Estructura de navegación (misma que config.mjs) ──────────────────────────
NAV_STRUCTURE = [
    {
        "group": "🚀 Introducción",
        "items": [
            {"text": "¿Qué es el Sistema POA?", "file": "guide/intro.md",            "id": "guide-intro"},
            {"text": "Stack Tecnológico",        "file": "guide/stack.md",            "id": "guide-stack"},
            {"text": "Cómo levantar el proyecto","file": "guide/setup.md",            "id": "guide-setup"},
        ]
    },
    {
        "group": "🏗️ Arquitectura",
        "items": [
            {"text": "Visión General",           "file": "architecture/overview.md",  "id": "arch-overview"},
            {"text": "Modelos y Base de datos",  "file": "architecture/models.md",    "id": "arch-models"},
            {"text": "Roles y Middleware",       "file": "architecture/roles.md",     "id": "arch-roles"},
        ]
    },
    {
        "group": "⚙️ Módulos del Sistema",
        "items": [
            {"text": "Wizard de Planificación", "file": "modules/wizard.md",         "id": "mod-wizard"},
            {"text": "Registro de Avances",     "file": "modules/avances.md",        "id": "mod-avances"},
            {"text": "Aprobación (Admin)",       "file": "modules/admin.md",          "id": "mod-admin"},
            {"text": "Exportaciones Excel/PDF", "file": "modules/exports.md",        "id": "mod-exports"},
            {"text": "Actividades No Planificadas","file": "modules/unplanned.md",   "id": "mod-unplanned"},
        ]
    },
    {
        "group": "🔌 Referencia de Código",
        "items": [
            {"text": "Controladores",           "file": "api/controllers.md",        "id": "api-controllers"},
            {"text": "Servicios",               "file": "api/services.md",           "id": "api-services"},
            {"text": "Rutas (web.php)",         "file": "api/routes.md",             "id": "api-routes"},
        ]
    },
    {
        "group": "📐 Extensibilidad",
        "items": [
            {"text": "Puntos de Extensión",     "file": "extend/points.md",          "id": "ext-points"},
            {"text": "Ejemplos de Uso",         "file": "extend/examples.md",        "id": "ext-examples"},
        ]
    },
]

# ── Leer archivos markdown ────────────────────────────────────────────────────
def read_pages():
    pages = {}
    for group in NAV_STRUCTURE:
        for item in group["items"]:
            filepath = os.path.join(DOCS_DIR, item["file"])
            if os.path.exists(filepath):
                with open(filepath, "r", encoding="utf-8") as f:
                    pages[item["id"]] = f.read()
            else:
                pages[item["id"]] = f"# {item['text']}\n\n> ⚠️ Archivo no encontrado: `{item['file']}`"
    return pages

# ── Descargar librería JS ──────────────────────────────────────────────────────
def fetch_js(url, name):
    print(f"  📥 Descargando {name}...")
    try:
        req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0"})
        with urllib.request.urlopen(req, timeout=30) as resp:
            content = resp.read().decode("utf-8")
        print(f"  ✅ {name} ({len(content)//1024} KB)")
        return content
    except Exception as e:
        print(f"  ❌ Error descargando {name}: {e}")
        return None

# ── Generar el HTML ────────────────────────────────────────────────────────────
def generate_html(pages, marked_js, mermaid_js, hljs):
    pages_json = json.dumps(pages, ensure_ascii=False)
    nav_json   = json.dumps(NAV_STRUCTURE, ensure_ascii=False)
    first_id   = NAV_STRUCTURE[0]["items"][0]["id"]

    # Sidebar HTML items
    sidebar_html = ""
    for group in NAV_STRUCTURE:
        sidebar_html += f'<div class="nav-group"><div class="nav-group-title">{group["group"]}</div>'
        for item in group["items"]:
            sidebar_html += f'<a class="nav-item" href="#{item["id"]}" data-id="{item["id"]}">{item["text"]}</a>'
        sidebar_html += '</div>'

    return f"""<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistema POA — Documentación Técnica</title>
<style>
/* ── Reset & Variables ───────────────────────────────────────────────── */
*, *::before, *::after {{ box-sizing: border-box; margin: 0; padding: 0; }}

:root {{
  --bg: #ffffff;
  --bg-sidebar: #f6f6f7;
  --bg-code: #f1f3f5;
  --border: #e2e2e3;
  --text: #213547;
  --text-muted: #6b7280;
  --text-sidebar: #4a5568;
  --brand: #3451b2;
  --brand-light: #e8edfb;
  --tip-bg: #e8f5e9;
  --tip-border: #43a047;
  --warn-bg: #fff9e6;
  --warn-border: #f59e0b;
  --info-bg: #e3f2fd;
  --info-border: #1565c0;
  --caution-bg: #fce4ec;
  --caution-border: #c62828;
  --sidebar-width: 272px;
  --header-height: 56px;
  --font: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, sans-serif;
  --font-mono: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
  --radius: 8px;
  --transition: 0.2s ease;
}}

[data-theme="dark"] {{
  --bg: #1b1b1f;
  --bg-sidebar: #161618;
  --bg-code: #2d2d2d;
  --border: #3c3c43;
  --text: #dde1e7;
  --text-muted: #9ca3af;
  --text-sidebar: #c9d1d9;
  --brand: #5c7cfa;
  --brand-light: #1e2a4a;
  --tip-bg: #1a3326;
  --tip-border: #4caf50;
  --warn-bg: #2d2000;
  --warn-border: #f59e0b;
  --info-bg: #0d2137;
  --info-border: #4b9cf5;
  --caution-bg: #2d0a14;
  --caution-border: #ef5350;
}}

html {{ font-size: 16px; scroll-behavior: smooth; }}
body {{
  font-family: var(--font);
  background: var(--bg);
  color: var(--text);
  line-height: 1.7;
  transition: background var(--transition), color var(--transition);
}}

/* ── Layout ──────────────────────────────────────────────────────────── */
#app {{ display: flex; flex-direction: column; min-height: 100vh; }}

header {{
  position: fixed; top: 0; left: 0; right: 0; z-index: 100;
  height: var(--header-height);
  background: var(--bg);
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; padding: 0 24px;
  gap: 16px;
  backdrop-filter: blur(8px);
  background: color-mix(in srgb, var(--bg) 92%, transparent);
}}

.logo {{
  font-size: 1.1rem; font-weight: 700; color: var(--brand);
  text-decoration: none; white-space: nowrap; letter-spacing: -0.3px;
}}
.logo span {{ color: var(--text); font-weight: 400; }}

.header-search {{
  flex: 1; max-width: 320px;
  position: relative;
}}
.header-search input {{
  width: 100%; padding: 6px 12px 6px 34px;
  border: 1px solid var(--border); border-radius: 20px;
  background: var(--bg-code); color: var(--text);
  font-size: 0.85rem; outline: none;
  transition: border-color var(--transition);
}}
.header-search input:focus {{ border-color: var(--brand); }}
.search-icon {{
  position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
  color: var(--text-muted); font-size: 14px;
}}
.search-results {{
  display: none; position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: var(--bg); border: 1px solid var(--border);
  border-radius: var(--radius); max-height: 300px; overflow-y: auto;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 200;
}}
.search-result-item {{
  padding: 8px 14px; cursor: pointer; font-size: 0.85rem;
  border-bottom: 1px solid var(--border);
  transition: background var(--transition);
}}
.search-result-item:last-child {{ border-bottom: none; }}
.search-result-item:hover {{ background: var(--brand-light); color: var(--brand); }}
.search-result-item .result-section {{ font-size: 0.75rem; color: var(--text-muted); }}

.header-actions {{ margin-left: auto; display: flex; align-items: center; gap: 10px; }}

.theme-btn {{
  background: none; border: 1px solid var(--border);
  border-radius: 6px; padding: 5px 10px; cursor: pointer;
  color: var(--text); font-size: 16px; line-height: 1;
  transition: background var(--transition), border-color var(--transition);
}}
.theme-btn:hover {{ background: var(--brand-light); border-color: var(--brand); }}

.version-badge {{
  background: var(--brand-light); color: var(--brand);
  border-radius: 12px; padding: 2px 10px; font-size: 0.75rem; font-weight: 600;
}}

/* ── Main wrapper ────────────────────────────────────────────────────── */
.layout {{
  display: flex; padding-top: var(--header-height); flex: 1;
}}

/* ── Sidebar ─────────────────────────────────────────────────────────── */
.sidebar {{
  width: var(--sidebar-width); min-width: var(--sidebar-width);
  position: fixed; top: var(--header-height); bottom: 0; left: 0;
  overflow-y: auto; padding: 24px 0 40px;
  border-right: 1px solid var(--border);
  background: var(--bg-sidebar);
  transition: transform var(--transition);
  scrollbar-width: thin;
  scrollbar-color: var(--border) transparent;
}}

.nav-group {{ padding: 0 16px 12px; }}
.nav-group-title {{
  font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.08em; color: var(--text-muted);
  padding: 14px 8px 6px; margin-bottom: 2px;
}}
.nav-item {{
  display: block; padding: 6px 10px; border-radius: 6px;
  color: var(--text-sidebar); text-decoration: none;
  font-size: 0.875rem; line-height: 1.5;
  transition: background var(--transition), color var(--transition);
  cursor: pointer;
}}
.nav-item:hover {{ background: var(--brand-light); color: var(--brand); }}
.nav-item.active {{
  background: var(--brand-light); color: var(--brand); font-weight: 600;
  border-left: 3px solid var(--brand); padding-left: 7px;
}}

/* ── Content ─────────────────────────────────────────────────────────── */
.content-wrapper {{
  margin-left: var(--sidebar-width); flex: 1; min-width: 0;
  display: flex; justify-content: center;
}}

.content {{
  width: 100%; max-width: 800px; padding: 40px 48px 80px;
}}

/* ── TOC (in-page outline) ───────────────────────────────────────────── */
.toc-sidebar {{
  width: 220px; min-width: 220px;
  position: sticky; top: calc(var(--header-height) + 24px);
  align-self: flex-start; padding: 0 16px;
  display: none;
}}
@media (min-width: 1280px) {{ .toc-sidebar {{ display: block; }} }}

.toc-title {{
  font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 8px;
}}
.toc-list {{ list-style: none; }}
.toc-list li {{ margin: 3px 0; }}
.toc-list a {{
  font-size: 0.8rem; color: var(--text-muted); text-decoration: none;
  display: block; padding: 2px 6px; border-radius: 4px;
  transition: color var(--transition);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}}
.toc-list a:hover, .toc-list a.active {{ color: var(--brand); }}
.toc-list .h3 {{ padding-left: 12px; }}

/* ── Typography ──────────────────────────────────────────────────────── */
.content h1 {{
  font-size: 2rem; font-weight: 700; line-height: 1.2;
  margin-bottom: 1rem; padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--border); color: var(--text);
}}
.content h2 {{
  font-size: 1.4rem; font-weight: 700;
  margin: 2.4rem 0 0.8rem; padding-top: 0.5rem;
  border-top: 1px solid var(--border); color: var(--text);
}}
.content h3 {{ font-size: 1.1rem; font-weight: 600; margin: 1.6rem 0 0.5rem; }}
.content h4 {{ font-size: 0.95rem; font-weight: 600; margin: 1.2rem 0 0.4rem; }}
.content p {{ margin: 0.8rem 0; }}
.content a {{ color: var(--brand); text-decoration: none; }}
.content a:hover {{ text-decoration: underline; }}

.content ul, .content ol {{
  padding-left: 1.5rem; margin: 0.6rem 0;
}}
.content li {{ margin: 0.25rem 0; }}

.content hr {{
  border: none; border-top: 1px solid var(--border); margin: 2rem 0;
}}

/* ── Code ────────────────────────────────────────────────────────────── */
.content code {{
  background: var(--bg-code); color: #c7254e;
  border-radius: 4px; padding: 2px 6px;
  font-family: var(--font-mono); font-size: 0.875em;
}}
[data-theme="dark"] .content code {{ color: #f9a8d4; }}

.content pre {{
  background: var(--bg-code); border-radius: var(--radius);
  padding: 18px 20px; overflow-x: auto;
  margin: 1rem 0; position: relative;
  border: 1px solid var(--border);
}}
.content pre code {{
  background: none; color: var(--text); padding: 0;
  font-size: 0.875rem; line-height: 1.75;
}}

/* ── Tables ──────────────────────────────────────────────────────────── */
.content table {{
  width: 100%; border-collapse: collapse; margin: 1rem 0;
  font-size: 0.9rem; overflow: hidden; border-radius: var(--radius);
  box-shadow: 0 0 0 1px var(--border);
}}
.content thead th {{
  background: var(--bg-code); color: var(--text);
  padding: 10px 14px; font-weight: 600; text-align: left;
  font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em;
  border-bottom: 1px solid var(--border);
}}
.content tbody tr {{ transition: background var(--transition); }}
.content tbody tr:nth-child(even) {{ background: color-mix(in srgb, var(--bg-sidebar) 50%, transparent); }}
.content tbody tr:hover {{ background: var(--brand-light); }}
.content td {{ padding: 9px 14px; border-bottom: 1px solid var(--border); vertical-align: top; }}
.content tbody tr:last-child td {{ border-bottom: none; }}

/* ── Blockquotes / Alerts ────────────────────────────────────────────── */
.content blockquote {{
  border-left: 4px solid var(--border);
  margin: 1rem 0; padding: 10px 16px;
  color: var(--text-muted); border-radius: 0 var(--radius) var(--radius) 0;
}}

.alert {{
  border-radius: var(--radius); padding: 14px 18px; margin: 1.2rem 0;
  border-left: 4px solid; font-size: 0.9rem;
}}
.alert-tip    {{ background: var(--tip-bg);    border-color: var(--tip-border);    }}
.alert-warn   {{ background: var(--warn-bg);   border-color: var(--warn-border);   }}
.alert-info   {{ background: var(--info-bg);   border-color: var(--info-border);   }}
.alert-caution{{ background: var(--caution-bg);border-color: var(--caution-border);}}
.alert-title  {{ font-weight: 700; margin-bottom: 4px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; }}

/* ── Mermaid ─────────────────────────────────────────────────────────── */
.mermaid {{ text-align: center; margin: 1.5rem 0; overflow-x: auto; }}
.mermaid svg {{ max-width: 100%; height: auto; }}

/* ── Mobile sidebar toggle ───────────────────────────────────────────── */
.sidebar-toggle {{
  display: none; background: none; border: 1px solid var(--border);
  border-radius: 6px; padding: 5px 10px; cursor: pointer; color: var(--text);
  font-size: 18px;
}}
@media (max-width: 768px) {{
  .sidebar-toggle {{ display: flex; align-items: center; }}
  .sidebar {{ transform: translateX(-100%); z-index: 90; }}
  .sidebar.open {{ transform: translateX(0); box-shadow: 4px 0 20px rgba(0,0,0,0.15); }}
  .content-wrapper {{ margin-left: 0; }}
  .content {{ padding: 24px 20px 60px; }}
  .toc-sidebar {{ display: none !important; }}
}}

/* ── Breadcrumb ──────────────────────────────────────────────────────── */
.breadcrumb {{
  font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1rem;
  display: flex; align-items: center; gap: 6px;
}}
.breadcrumb span {{ color: var(--text-muted); }}

/* ── Page footer ─────────────────────────────────────────────────────── */
.page-nav {{
  display: flex; justify-content: space-between; gap: 16px;
  margin-top: 3rem; padding-top: 1.5rem; border-top: 1px solid var(--border);
}}
.page-nav-btn {{
  display: flex; flex-direction: column; padding: 14px 20px;
  border: 1px solid var(--border); border-radius: var(--radius);
  text-decoration: none; color: var(--text); max-width: 48%;
  transition: border-color var(--transition), background var(--transition);
  cursor: pointer; background: none; font-family: var(--font); text-align: left;
}}
.page-nav-btn:hover {{ border-color: var(--brand); background: var(--brand-light); color: var(--brand); }}
.page-nav-btn.next {{ text-align: right; margin-left: auto; }}
.page-nav-label {{ font-size: 0.75rem; color: var(--text-muted); margin-bottom: 4px; }}
.page-nav-title {{ font-size: 0.9rem; font-weight: 600; }}

/* ── Scrollbar ───────────────────────────────────────────────────────── */
::-webkit-scrollbar {{ width: 6px; height: 6px; }}
::-webkit-scrollbar-track {{ background: transparent; }}
::-webkit-scrollbar-thumb {{ background: var(--border); border-radius: 3px; }}
</style>
</head>
<body>
<div id="app">
  <header>
    <button class="sidebar-toggle" id="sidebarToggle" title="Menú">☰</button>
    <a class="logo" href="#">Sistema POA <span>Docs</span></a>
    <div class="header-search">
      <span class="search-icon">🔍</span>
      <input type="text" id="searchInput" placeholder="Buscar en la documentación..." autocomplete="off">
      <div class="search-results" id="searchResults"></div>
    </div>
    <div class="header-actions">
      <span class="version-badge">v1.0</span>
      <button class="theme-btn" id="themeToggle" title="Cambiar tema">🌙</button>
    </div>
  </header>

  <div class="layout">
    <nav class="sidebar" id="sidebar">
      {sidebar_html}
    </nav>

    <div class="content-wrapper">
      <main class="content" id="content">
        <p style="padding:60px 0;text-align:center;color:var(--text-muted)">Cargando...</p>
      </main>

      <aside class="toc-sidebar" id="toc-sidebar">
        <div class="toc-title">En esta página</div>
        <ul class="toc-list" id="toc-list"></ul>
      </aside>
    </div>
  </div>
</div>

<!-- ── marked.js (Markdown parser) ── -->
<script>
{marked_js}
</script>

<!-- ── mermaid.js (Diagrams) ── -->
<script>
{mermaid_js}
</script>

<!-- ── App ── -->
<script>
// ── Pages data ───────────────────────────────────────────────────────────────
const PAGES = {pages_json};
const NAV   = {nav_json};
const FIRST = "{first_id}";

// Flatten nav for search and prev/next
const ALL_ITEMS = NAV.flatMap(g => g.items.map(i => ({{...i, group: g.group}})));

// ── Marked options ────────────────────────────────────────────────────────────
marked.setOptions({{
  breaks: true,
  gfm: true,
}});

// Custom renderer for alerts (:::tip, :::warning, :::info, :::caution)
const renderer = new marked.Renderer();

// Override code to handle mermaid
renderer.code = function(code, lang) {{
  if (lang === 'mermaid') {{
    const id = 'mermaid-' + Math.random().toString(36).substr(2,6);
    return `<div class="mermaid" id="${{id}}">${{code}}</div>`;
  }}
  const escaped = code
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
  return `<pre><code class="language-${{lang || ''}}">${{escaped}}</code></pre>`;
}};

marked.use({{ renderer }});

// Pre-process markdown to handle ::: alert blocks
function preprocessMarkdown(md) {{
  return md.replace(/:::(\s*)(tip|warning|info|caution|important|note|warn)([^\n]*)?\n([\s\S]*?):::/gi, (_, __, type, title, body) => {{
    const map = {{
      tip:'tip', note:'info', info:'info', warning:'warn', warn:'warn',
      caution:'caution', important:'caution'
    }};
    const cls = map[type.toLowerCase()] || 'info';
    const labels = {{ tip:'💡 Tip', note:'📝 Nota', info:'ℹ️ Info',
                       warning:'⚠️ Advertencia', warn:'⚠️ Advertencia',
                       caution:'🚨 Precaución', important:'❗ Importante' }};
    const t = (title && title.trim()) || labels[type.toLowerCase()] || type;
    return `<div class="alert alert-${{cls}}"><div class="alert-title">${{t}}</div>\n\n${{body.trim()}}\n\n</div>`;
  }});
}}

// ── Router ────────────────────────────────────────────────────────────────────
function getPageId() {{
  const hash = location.hash.replace('#','').trim();
  return ALL_ITEMS.find(i => i.id === hash) ? hash : FIRST;
}}

function navigate(id) {{
  history.replaceState(null, '', '#' + id);
  renderPage(id);
}}

function renderPage(id) {{
  const content = document.getElementById('content');
  const md = PAGES[id] || '# Página no encontrada';

  // Process and render markdown
  const processed = preprocessMarkdown(md);
  content.innerHTML = marked.parse(processed);

  // Breadcrumb
  const item = ALL_ITEMS.find(i => i.id === id);
  if (item) {{
    const bc = document.createElement('div');
    bc.className = 'breadcrumb';
    bc.innerHTML = `<span>📘 Docs</span> <span>/</span> <span>${{item.group}}</span> <span>/</span> <strong>${{item.text}}</strong>`;
    content.insertBefore(bc, content.firstChild);
  }}

  // Prev/Next navigation
  const idx = ALL_ITEMS.findIndex(i => i.id === id);
  const prev = ALL_ITEMS[idx - 1];
  const next = ALL_ITEMS[idx + 1];
  const pnDiv = document.createElement('div');
  pnDiv.className = 'page-nav';
  if (prev) {{
    pnDiv.innerHTML += `<button class="page-nav-btn" onclick="navigate('${{prev.id}}')">
      <span class="page-nav-label">← Anterior</span>
      <span class="page-nav-title">${{prev.text}}</span>
    </button>`;
  }}
  if (next) {{
    pnDiv.innerHTML += `<button class="page-nav-btn next" onclick="navigate('${{next.id}}')">
      <span class="page-nav-label">Siguiente →</span>
      <span class="page-nav-title">${{next.text}}</span>
    </button>`;
  }}
  content.appendChild(pnDiv);

  // Update active nav link
  document.querySelectorAll('.nav-item').forEach(a => {{
    a.classList.toggle('active', a.dataset.id === id);
  }});

  // Build TOC
  buildTOC();

  // Render Mermaid diagrams
  renderMermaid();

  // Scroll top
  window.scrollTo({{ top: 0, behavior: 'instant' }});

  // Close mobile sidebar
  document.getElementById('sidebar').classList.remove('open');
}}

// ── TOC ───────────────────────────────────────────────────────────────────────
function buildTOC() {{
  const headings = document.querySelectorAll('#content h2, #content h3');
  const list = document.getElementById('toc-list');
  list.innerHTML = '';
  headings.forEach((h, i) => {{
    const id = 'h-' + i;
    h.id = id;
    const li = document.createElement('li');
    li.className = h.tagName === 'H3' ? 'h3' : '';
    li.innerHTML = `<a href="#${{id}}">${{h.textContent}}</a>`;
    li.querySelector('a').addEventListener('click', (e) => {{
      e.preventDefault();
      h.scrollIntoView({{ behavior: 'smooth' }});
    }});
    list.appendChild(li);
  }});
}}

// ── Mermaid ───────────────────────────────────────────────────────────────────
let mermaidReady = false;
mermaid.initialize({{
  startOnLoad: false,
  theme: document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'default',
  securityLevel: 'loose',
  fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
}});
mermaidReady = true;

function renderMermaid() {{
  if (!mermaidReady) return;
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  mermaid.initialize({{
    startOnLoad: false,
    theme: isDark ? 'dark' : 'default',
    securityLevel: 'loose',
  }});
  document.querySelectorAll('.mermaid').forEach(el => {{
    if (el.getAttribute('data-rendered')) return;
    el.setAttribute('data-rendered', '1');
    const code = el.textContent;
    el.innerHTML = '';
    mermaid.render('mg-' + Math.random().toString(36).substr(2,6), code).then(r => {{
      el.innerHTML = r.svg;
    }}).catch(e => {{
      el.innerHTML = `<pre style="color:red">${{e.message}}</pre>`;
    }});
  }});
}}

// ── Search ────────────────────────────────────────────────────────────────────
function buildSearchIndex() {{
  const index = [];
  for (const [id, md] of Object.entries(PAGES)) {{
    const lines = md.split('\n');
    const item  = ALL_ITEMS.find(i => i.id === id);
    lines.forEach(line => {{
      if (line.trim().length > 20) {{
        index.push({{ id, text: line, section: item?.text || id }});
      }}
    }});
  }}
  return index;
}}

const searchIndex = buildSearchIndex();

document.getElementById('searchInput').addEventListener('input', function() {{
  const q = this.value.toLowerCase().trim();
  const results = document.getElementById('searchResults');
  if (!q || q.length < 2) {{ results.style.display = 'none'; return; }}
  const hits = searchIndex.filter(e => e.text.toLowerCase().includes(q)).slice(0,8);
  if (!hits.length) {{ results.style.display = 'none'; return; }}
  results.style.display = 'block';
  results.innerHTML = hits.map(h => {{
    const preview = h.text.replace(new RegExp(q,'gi'), m => `<mark>${{m}}</mark>`);
    return `<div class="search-result-item" onclick="navigate('${{h.id}}');document.getElementById('searchInput').value='';document.getElementById('searchResults').style.display='none';">
      <div class="result-section">${{h.section}}</div>
      <div>${{preview.substring(0,80)}}</div>
    </div>`;
  }}).join('');
}});

document.addEventListener('click', e => {{
  if (!e.target.closest('.header-search')) {{
    document.getElementById('searchResults').style.display = 'none';
  }}
}});

// ── Dark mode ─────────────────────────────────────────────────────────────────
const THEME_KEY = 'poa-docs-theme';
function applyTheme(dark) {{
  document.documentElement.setAttribute('data-theme', dark ? 'dark' : '');
  document.getElementById('themeToggle').textContent = dark ? '☀️' : '🌙';
  localStorage.setItem(THEME_KEY, dark ? 'dark' : 'light');
}}

document.getElementById('themeToggle').addEventListener('click', () => {{
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  applyTheme(!isDark);
  // Re-render mermaid with new theme
  document.querySelectorAll('.mermaid').forEach(el => el.removeAttribute('data-rendered'));
  renderMermaid();
}});

// Init theme
const savedTheme = localStorage.getItem(THEME_KEY);
const prefersDark = savedTheme ? savedTheme === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
applyTheme(prefersDark);

// ── Sidebar mobile toggle ─────────────────────────────────────────────────────
document.getElementById('sidebarToggle').addEventListener('click', () => {{
  document.getElementById('sidebar').classList.toggle('open');
}});

// ── Nav click handlers ────────────────────────────────────────────────────────
document.querySelectorAll('.nav-item').forEach(a => {{
  a.addEventListener('click', e => {{
    e.preventDefault();
    navigate(a.dataset.id);
  }});
}});

// ── Init ──────────────────────────────────────────────────────────────────────
window.addEventListener('hashchange', () => renderPage(getPageId()));
renderPage(getPageId());
</script>
</body>
</html>"""

# ── Main ──────────────────────────────────────────────────────────────────────
if __name__ == "__main__":
    print("\n🔧 Sistema POA — Generando documentación portátil\n")

    print("📂 Leyendo archivos markdown...")
    pages = read_pages()
    print(f"  ✅ {len(pages)} páginas encontradas")

    print("\n📦 Descargando librerías JS...")
    marked_js  = fetch_js("https://cdn.jsdelivr.net/npm/marked/marked.min.js",   "marked.js")
    mermaid_js = fetch_js("https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js", "mermaid.js")

    if not marked_js or not mermaid_js:
        print("\n❌ No se pudieron descargar las librerías. Verifica tu conexión a internet.")
        sys.exit(1)

    print("\n🖊️ Generando HTML...")
    html = generate_html(pages, marked_js, mermaid_js, None)

    output = os.path.abspath(OUTPUT_FILE)
    with open(output, "w", encoding="utf-8") as f:
        f.write(html)

    size_kb = os.path.getsize(output) // 1024
    print(f"  ✅ Archivo generado: {output}")
    print(f"  📦 Tamaño: {size_kb} KB ({size_kb//1024} MB)\n")
    print("🎉 ¡Listo! Abre el archivo con doble clic en cualquier navegador.")
    print(f"   Ruta: {output}\n")

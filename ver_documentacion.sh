#!/bin/bash
# ─────────────────────────────────────────────────────────────────
#  ver_documentacion.sh
#  Lanza la documentación del Sistema POA en el navegador.
#  - Se auto-construye si la carpeta dist no existe
#  - Abre automáticamente http://localhost:4173
#  - Solo requiere Node.js y Python3 (ya vienen en Linux/Mac)
# ─────────────────────────────────────────────────────────────────

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DOCS_DIR="$SCRIPT_DIR/docs"
DIST_DIR="$DOCS_DIR/.vitepress/dist"
PORT=4173
URL="http://localhost:$PORT"

echo ""
echo "📚 Sistema POA — Documentación Técnica"
echo "───────────────────────────────────────"

# ── 1. Instalar dependencias si node_modules no existe ────────────
if [ ! -d "$DOCS_DIR/node_modules" ]; then
  echo "📦 Instalando dependencias (solo la primera vez)..."
  cd "$DOCS_DIR" && npm install --silent
  if [ $? -ne 0 ]; then
    echo "❌ Error instalando dependencias. Verifica que Node.js esté instalado."
    read -p "Presiona Enter para cerrar..."; exit 1
  fi
  echo "   ✅ Dependencias instaladas."
fi

# ── 2. Construir el sitio si dist no existe ───────────────────────
if [ ! -d "$DIST_DIR" ]; then
  echo "🔨 Construyendo sitio (solo la primera vez)..."
  cd "$DOCS_DIR" && npm run docs:build --silent
  if [ $? -ne 0 ]; then
    echo "❌ Error al construir la documentación."
    read -p "Presiona Enter para cerrar..."; exit 1
  fi
  echo "   ✅ Sitio construido."
fi

echo "🚀 Iniciando servidor en $URL ..."
echo "   Presiona Ctrl+C para detener."
echo ""

# ── 3. Abrir navegador y servir ───────────────────────────────────
(sleep 1.5 && xdg-open "$URL" 2>/dev/null || open "$URL" 2>/dev/null) &

cd "$DIST_DIR"
python3 -m http.server $PORT --bind 127.0.0.1 2>/dev/null \
  || python -m SimpleHTTPServer $PORT  # fallback Python 2

echo ""
echo "✅ Servidor detenido."

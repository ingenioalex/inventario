#!/bin/bash
# Modo Wi-Fi: celular en la misma red + camara (HTTPS local)
cd "$(dirname "$0")"

if [ ! -d ".venv" ]; then
  echo "Creando entorno virtual..."
  python3 -m venv .venv
  .venv/bin/pip install -r requirements.txt -q
fi

if [ ! -f "certs/cert.pem" ]; then
  echo "Generando certificado HTTPS..."
  .venv/bin/python generar_certificado.py
fi

source .venv/bin/activate
export USE_HTTPS=1
export FLASK_DEBUG=1

echo ""
echo "  MODO WI-FI — Celular en la misma red"
echo "  Use la URL https:// que aparece abajo"
echo ""

python app.py

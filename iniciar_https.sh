#!/bin/bash
cd "$(dirname "$0")"

if [ ! -f certs/cert.pem ]; then
  echo "Generando certificado..."
  python generar_certificado.py 2>/dev/null || bash generar_certificado.sh
fi

if [ -d .venv ]; then
  source .venv/bin/activate
fi

export USE_HTTPS=1
export FLASK_DEBUG=1
IP=$(hostname -I 2>/dev/null | awk '{print $1}')

echo ""
echo "============================================"
echo "  Servidor HTTPS (camara habilitada)"
echo "  En este PC:  https://127.0.0.1:5000"
if [ -n "$IP" ]; then
  echo "  En el celular: https://${IP}:5000"
  echo "  (Acepte 'Avanzado' / 'Continuar' si avisa del certificado)"
fi
echo "============================================"
echo ""

python app.py

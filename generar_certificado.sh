#!/bin/bash
# Certificado autofirmado para usar la camara en el celular (HTTPS en red local)
set -e
cd "$(dirname "$0")"
mkdir -p certs

IP="${1:-}"
if [ -z "$IP" ]; then
  IP=$(hostname -I 2>/dev/null | awk '{print $1}')
fi
if [ -z "$IP" ]; then
  IP="127.0.0.1"
  echo "No se detecto IP de red. Usando solo localhost."
fi

echo "Generando certificado para: localhost, 127.0.0.1, $IP"

openssl req -x509 -newkey rsa:2048 -nodes -days 825 \
  -keyout certs/key.pem -out certs/cert.pem \
  -subj "/CN=inventario-local/O=Inventario/C=CL" \
  -addext "subjectAltName=DNS:localhost,DNS:*.local,IP:127.0.0.1,IP:${IP}" 2>/dev/null \
  || openssl req -x509 -newkey rsa:2048 -nodes -days 825 \
  -keyout certs/key.pem -out certs/cert.pem \
  -subj "/CN=${IP}"

chmod 600 certs/key.pem
echo ""
echo "Listo. En el celular abra:"
echo "  https://${IP}:5000"
echo "(Acepte la advertencia de seguridad la primera vez)"

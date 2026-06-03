# Inventario HB 318

Funciona en **dos modos** con el mismo codigo:

| Modo | Como usar | Celular |
|------|-----------|---------|
| **Render (nube)** | [DEPLOY_NUBE.md](DEPLOY_NUBE.md) | Wi-Fi o datos → URL `https://xxx.onrender.com` |
| **Wi-Fi local** | `./iniciar_wifi.sh` en la PC | Misma Wi-Fi → URL `https://IP-PC:5000` |

En ambos: mapa, captura, camara y TXT.

## Wi-Fi local (rapido)

```bash
cd inventario
chmod +x iniciar_wifi.sh
./iniciar_wifi.sh
```

En el celular abra la URL **https://** que muestra la terminal (misma red Wi-Fi).

## Nube (Render + Neon)

Ver [DEPLOY_NUBE.md](DEPLOY_NUBE.md) — gratis, HTTPS automatico, datos en Neon.

## Desarrollo

```bash
pip install -r requirements.txt
python app.py              # HTTP solo PC
./iniciar_wifi.sh          # HTTPS + celular
```

Variables: `DATABASE_URL` (Neon), `SECRET_KEY` (Render).

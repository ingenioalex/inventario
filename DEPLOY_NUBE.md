# Subir el inventario a la nube (gratis)

La misma app sirve para **Render (nube)** y **Wi-Fi local** (`./iniciar_wifi.sh`). En pantalla verá un recuadro segun el modo.

**Nube (gratis):** [Render.com](https://render.com) + [Neon](https://neon.tech)

URL: `https://inventario-hb318.onrender.com` — celular con **Wi-Fi o datos**, camara con HTTPS automatico.

**Wi-Fi local:** no requiere Render; ver [README.md](README.md).

---

Esta app es **Python / Flask**. No funciona en hosting solo PHP.

---

## Requisitos

1. Cuenta en [GitHub](https://github.com) (gratis)
2. Cuenta en [Render](https://render.com) (gratis)
3. Cuenta en [Neon](https://neon.tech) (gratis, para que no se borren los datos)

---

## Paso 1: Subir el código a GitHub

En su PC (donde tenga Git o use la web de GitHub):

```bash
cd inventario
git init
git add .
git commit -m "Inventario HB 318"
```

Cree un repositorio en GitHub y suba el código (botón "Upload" en github.com/new si no puede usar Git).

**No suba:** `.venv/`, `inventario.db`, `certs/`, `exportaciones/`

---

## Paso 2: Base de datos gratis en Neon (recomendado)

1. Entre en [neon.tech](https://neon.tech) → Sign up  
2. **New Project** → nombre `inventario`  
3. Copie la **Connection string** (empieza con `postgresql://...`)  
4. Guárdela para el paso 4

Sin Neon, Render guarda datos en SQLite **temporal** (se pueden perder al reiniciar). Con Neon los registros **permanecen**.

---

## Paso 3: Crear el sitio en Render

1. [dashboard.render.com](https://dashboard.render.com) → **New +** → **Web Service**  
2. Conecte su cuenta de GitHub y elija el repositorio `inventario`  
3. Configuración:

| Campo | Valor |
|--------|--------|
| Name | `inventario-hb318` (o el que quiera) |
| Region | El más cercano a Chile |
| Branch | `main` |
| Runtime | **Python 3** |
| Build Command | `bash build.sh` |
| Start Command | `gunicorn wsgi:application --bind 0.0.0.0:$PORT --workers 2 --timeout 120` |
| Instance type | **Free** |

4. **Environment** → Add variable:

| Key | Value |
|-----|--------|
| `SECRET_KEY` | Una frase larga aleatoria (ej. `mi-clave-secreta-inventario-2026-x7k9`) |
| `FLASK_DEBUG` | `0` |
| `DATABASE_URL` | La connection string de Neon (pegada completa) |

5. **Create Web Service**  
6. Espere 5–10 minutos el primer deploy (plan gratis es lento al arrancar).

Su URL será: **`https://NOMBRE.onrender.com`**

---

## Paso 4: Usar desde el celular

1. Abra Chrome o Safari  
2. Entre a `https://su-app.onrender.com`  
3. No hace falta misma Wi‑Fi que un PC (funciona con datos móviles)  
4. Permita **cámara** cuando pregunte  
5. Trabaje normal: usuario, área, mapa, escanear, TXT  

**Nota plan gratis Render:** si nadie usa la app ~15 min, se “duerme” y la primera carga puede tardar **30–60 segundos**. Es normal en el tier gratuito.

---

## Respaldo de datos

- Con **Neon**: datos en la nube de forma persistente  
- Siempre use **Generar TXT** al terminar cada pallet (copia de seguridad descargable)  
- Los TXT en Render también son temporales en disco; la descarga al celular/PC es el respaldo

---

## ¿Y Webnode / hosting “gratuito” común?

| Tipo de hosting | ¿Sirve? |
|-----------------|--------|
| Solo PHP (muchos “gratis”) | **No** — esta app es Python |
| **Render** | **Sí** — recomendado |
| **PythonAnywhere** gratis | Sí, con límites (ver abajo) |
| **Railway** | Crédito limitado, luego pago |
| **VPS** (~5 USD/mes) | Sí, más trabajo de configuración |

### Alternativa: PythonAnywhere (gratis)

1. [pythonanywhere.com](https://www.pythonanywhere.com) → cuenta gratuita  
2. Suba el código (Files o Git)  
3. En **Web** → Add new web app → Manual configuration → Python 3.12  
4. **WSGI** apunte a `wsgi.py` (copie el path de su carpeta)  
5. En consola Bash: `pip install -r requirements.txt` y `python -c "import database; database.init_db()"`  
6. URL: `https://usuario.pythonanywhere.com`  

Límite gratis: tráfico y una app; suficiente para un equipo pequeño.

---

## Variables de entorno (resumen)

| Variable | Obligatoria | Descripción |
|----------|-------------|-------------|
| `SECRET_KEY` | Sí | Clave de sesiones Flask |
| `FLASK_DEBUG` | No | Use `0` en producción |
| `DATABASE_URL` | Recomendada | URL PostgreSQL de Neon |

---

## Problemas frecuentes

| Problema | Solución |
|----------|----------|
| Deploy falla | Revise logs en Render → Logs; falta `DATABASE_URL` mal copiada |
| Página lenta al entrar | Plan gratis “despierta” el servidor; espere y recargue |
| Cámara no funciona | Debe ser `https://` (Render ya lo da) |
| Datos desaparecieron | Configure Neon + `DATABASE_URL` |

---

## Costo mínimo si crece

| Servicio | Precio aprox. |
|----------|----------------|
| Render free + Neon free | **$0** |
| Render Starter (sin dormir) | ~7 USD/mes |
| Solo Neon free + Render free | **$0** (con espera al despertar) |

Para inventario en empresa, **Render + Neon gratis** suele bastar.

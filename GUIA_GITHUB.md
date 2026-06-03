# Subir a GitHub (usuario ingenioalex) con 2FA

GitHub **no acepta contraseña normal** en terminal desde 2021. Con codigo 2FA debe usar un **Token (PAT)**.

Repositorio: https://github.com/ingenioalex/inventario

---

## IMPORTANTE — Seguridad

Si compartio su contraseña en un chat, **cambiela ya** en:

GitHub → Settings → Password → Change password

Nunca pegue la contraseña en terminal ni se la de a nadie.

---

## Paso 1: Crear token (sustituto de clave)

1. Entre a https://github.com/settings/tokens  
2. **Generate new token** → **Generate new token (classic)**  
3. Nombre: `inventario-pc`  
4. Marque solo: **repo** (acceso completo a repositorios)  
5. **Generate token**  
6. **Copie el token** (empieza con `ghp_...`) — solo se muestra una vez

---

## Paso 2: Subir desde su PC (terminal)

Abra terminal en la carpeta del proyecto:

```bash
cd /ruta/a/inventario
```

Si pide actualizar GitHub con su version local (la mas completa):

```bash
git push --force-with-lease origin main
```

Cuando pida credenciales:

| Campo | Que poner |
|-------|-----------|
| Username | `ingenioalex` |
| Password | **El token `ghp_...`** (NO su contraseña de GitHub) |

---

## Paso 3: Si pide codigo 2FA en el navegador

Algunos flujos abren el navegador:

1. Inicie sesion en GitHub  
2. Escriba el codigo de la app (Google Authenticator / SMS)  
3. Autorice el acceso  

Alternativa mas facil — **GitHub CLI**:

```bash
sudo apt install gh   # o descargue desde github.com/cli/cli
gh auth login
```

Elija: GitHub.com → HTTPS → Login with browser → copie el codigo que muestra.

Luego:

```bash
cd inventario
git push origin main
```

---

## Verificar

Abra https://github.com/ingenioalex/inventario  
Debe verse la carpeta con `app.py`, `render.yaml`, `DEPLOY_NUBE.md`, etc.

---

## Siguiente paso: internet (Render)

Cuando el codigo este en GitHub, siga **DEPLOY_NUBE.md** (Render + Neon).

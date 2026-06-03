# Que se sube a GitHub y que no

| Carpeta / archivo | En GitHub | Motivo |
|-------------------|-----------|--------|
| `app.py`, `templates/`, `static/` | Si | Codigo de la app |
| `exportaciones/` (carpeta vacia) | Si | Se crea en el servidor; los `.txt` no se suben |
| `exportaciones/*.txt` | No | Generados al usar la app |
| `__pycache__/` | No | Cache automatico de Python; no debe versionarse |
| `.venv/` | No | Entorno virtual de su PC |
| `inventario.db` | No | Base local; en nube use Neon |
| `certs/*.pem` | No | Certificados locales HTTPS |

Los TXT aparecen en su PC dentro de `exportaciones/` al generarlos, pero no van al repositorio.

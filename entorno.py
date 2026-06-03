"""Detecta Render (nube) vs Wi-Fi local para mensajes y HTTPS."""
import os
import socket
from pathlib import Path

BASE_DIR = Path(__file__).parent
CERT_FILE = BASE_DIR / "certs" / "cert.pem"
KEY_FILE = BASE_DIR / "certs" / "key.pem"


def es_render() -> bool:
    return bool(
        os.environ.get("RENDER")
        or os.environ.get("RENDER_EXTERNAL_URL")
        or os.environ.get("RENDER_SERVICE_NAME")
    )


def ip_wifi_local() -> str | None:
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.settimeout(1)
        s.connect(("8.8.8.8", 80))
        ip = s.getsockname()[0]
        s.close()
        return ip
    except OSError:
        return None


def puerto_app() -> str:
    return os.environ.get("PORT", "5000")


def url_wifi_celular() -> str | None:
    if es_render():
        return None
    ip = ip_wifi_local()
    if not ip:
        return None
    return f"https://{ip}:{puerto_app()}"


def ssl_local_habilitado() -> bool:
    if es_render():
        return False
    modo = os.environ.get("USE_HTTPS", "").lower()
    if modo in ("0", "false", "no"):
        return False
    if modo in ("1", "true", "yes"):
        return True
    return CERT_FILE.is_file() and KEY_FILE.is_file()


def ssl_context_local():
    if ssl_local_habilitado():
        if CERT_FILE.is_file() and KEY_FILE.is_file():
            return str(CERT_FILE), str(KEY_FILE)
        if os.environ.get("USE_HTTPS", "").lower() in ("1", "true", "yes"):
            return "adhoc"
    return None

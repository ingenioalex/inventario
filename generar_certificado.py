#!/usr/bin/env python3
"""Genera certificado HTTPS local (Windows / Linux) para usar la camara en el celular."""

import ipaddress
import socket
from datetime import datetime, timedelta, timezone
from pathlib import Path

from cryptography import x509
from cryptography.hazmat.primitives import hashes, serialization
from cryptography.hazmat.primitives.asymmetric import rsa
from cryptography.x509.oid import NameOID


def ips_locales() -> list[str]:
    ips = ["127.0.0.1"]
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.settimeout(1)
        s.connect(("8.8.8.8", 80))
        ips.append(s.getsockname()[0])
        s.close()
    except OSError:
        pass
    return list(dict.fromkeys(ips))


def generar(ips: list[str]) -> tuple[Path, Path]:
    base = Path(__file__).parent / "certs"
    base.mkdir(exist_ok=True)
    cert_path = base / "cert.pem"
    key_path = base / "key.pem"

    key = rsa.generate_private_key(public_exponent=65537, key_size=2048)
    nombre = x509.Name([x509.NameAttribute(NameOID.COMMON_NAME, "inventario-local")])
    ahora = datetime.now(timezone.utc)

    san = x509.SubjectAlternativeName(
        [x509.DNSName("localhost"), x509.DNSName("*.local")]
        + [x509.IPAddress(ipaddress.ip_address(ip)) for ip in ips]
    )

    cert = (
        x509.CertificateBuilder()
        .subject_name(nombre)
        .issuer_name(nombre)
        .public_key(key.public_key())
        .serial_number(x509.random_serial_number())
        .not_valid_before(ahora)
        .not_valid_after(ahora + timedelta(days=825))
        .add_extension(san, critical=False)
        .sign(key, hashes.SHA256())
    )

    key_path.write_bytes(
        key.private_bytes(
            encoding=serialization.Encoding.PEM,
            format=serialization.PrivateFormat.TraditionalOpenSSL,
            encryption_algorithm=serialization.NoEncryption(),
        )
    )
    cert_path.write_bytes(cert.public_bytes(serialization.Encoding.PEM))
    return cert_path, key_path


def main():
    ips = ips_locales()
    cert_path, key_path = generar(ips)
    print("Certificado creado:")
    print(f"  {cert_path}")
    print(f"  {key_path}")
    print("\nIPs incluidas:", ", ".join(ips))
    wifi_ip = next((i for i in ips if i != "127.0.0.1"), None)
    if wifi_ip:
        print(f"\nEn el celular (misma Wi-Fi) abra:")
        print(f"  https://{wifi_ip}:5000")
    print("\nSi el navegador advierte: Avanzado -> Continuar / Aceptar riesgo.")


if __name__ == "__main__":
    main()

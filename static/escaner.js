(function () {
  "use strict";

  const modal = document.getElementById("modal-escaner");
  if (!modal || typeof Html5Qrcode === "undefined") return;

  const readerId = "escaner-reader";
  const estadoEl = document.getElementById("escaner-estado");
  const ayudaHttpsEl = document.getElementById("escaner-ayuda-https");
  const textoModoEl = document.getElementById("escaner-texto-modo");
  const body = document.body;

  const esRender = body.dataset.esRender === "1";
  const urlWifi = body.dataset.urlWifi || "";
  const urlRender = body.dataset.urlRender || "";

  let html5Qr = null;
  let escaneando = false;
  let inputActivo = null;

  const formatos = [
    Html5QrcodeSupportedFormats.EAN_13,
    Html5QrcodeSupportedFormats.EAN_8,
    Html5QrcodeSupportedFormats.UPC_A,
    Html5QrcodeSupportedFormats.UPC_E,
    Html5QrcodeSupportedFormats.CODE_128,
    Html5QrcodeSupportedFormats.CODE_39,
    Html5QrcodeSupportedFormats.ITF,
  ];

  function setEstado(msg, tipo) {
    if (!estadoEl) return;
    estadoEl.textContent = msg;
    estadoEl.className = "escaner-estado" + (tipo ? " " + tipo : "");
  }

  function urlHttpsSugerida() {
    if (esRender && urlRender) return urlRender;
    if (urlWifi) return urlWifi;
    const host = location.hostname || "127.0.0.1";
    const port = location.port || "5000";
    return "https://" + host + ":" + port + location.pathname;
  }

  function textoAyudaModo() {
    if (esRender) {
      return "En Render ya usa HTTPS. Recargue la pagina con la URL https:// de su sitio.";
    }
    return "En la PC ejecute: ./iniciar_wifi.sh (misma red Wi-Fi que el celular).";
  }

  function mostrarAyudaHttps(visible) {
    if (!ayudaHttpsEl) return;
    ayudaHttpsEl.hidden = !visible;
    if (visible) {
      if (textoModoEl) textoModoEl.textContent = textoAyudaModo();
      const link = ayudaHttpsEl.querySelector("[data-url-https]");
      if (link) {
        const url = urlHttpsSugerida();
        link.href = url;
        link.textContent = url;
      }
    }
  }

  function obtenerInput(target) {
    if (target && target.classList && target.classList.contains("ean-input")) {
      return target;
    }
    const enfocado = document.querySelector(".ean-input:focus");
    if (enfocado) return enfocado;
    return document.getElementById("ean");
  }

  async function detenerCamara() {
    if (html5Qr && escaneando) {
      try {
        await html5Qr.stop();
        await html5Qr.clear();
      } catch (e) {
        /* ignorar */
      }
      escaneando = false;
    }
  }

  function cerrarModal() {
    detenerCamara();
    modal.hidden = true;
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modal-escaner-abierto");
    mostrarAyudaHttps(false);
    inputActivo?.focus();
  }

  function aplicarCodigo(codigo) {
    const limpio = String(codigo).trim();
    if (!inputActivo || !limpio) return;
    inputActivo.value = limpio;
    inputActivo.dispatchEvent(new Event("input", { bubbles: true }));
    setEstado("Codigo leido: " + limpio, "ok");
    setTimeout(cerrarModal, 400);
    const cantidad = document.getElementById("cantidad");
    if (cantidad && inputActivo.id === "ean") {
      setTimeout(() => cantidad.focus(), 500);
    }
  }

  async function iniciarCamara() {
    setEstado("Iniciando camara...");
    if (!html5Qr) {
      html5Qr = new Html5Qrcode(readerId, { formatsToSupport: formatos });
    }

    const config = {
      fps: 12,
      qrbox: (w, h) => ({
        width: Math.min(Math.floor(w * 0.92), 320),
        height: Math.min(Math.floor(h * 0.45), 140),
      }),
      aspectRatio: 1.5,
    };

    const camaras = await Html5Qrcode.getCameras();
    let cameraId = null;
    if (camaras && camaras.length) {
      const trasera = camaras.find(
        (c) =>
          /back|rear|environment|trasera|trás/i.test(c.label) ||
          /back|rear|environment/i.test(c.id || "")
      );
      cameraId = (trasera || camaras[camaras.length - 1]).id;
    }

    const camConfig = cameraId
      ? { deviceId: { exact: cameraId } }
      : { facingMode: { ideal: "environment" } };

    await html5Qr.start(
      camConfig,
      config,
      (texto) => {
        if (escaneando) aplicarCodigo(texto);
      },
      () => {}
    );
    escaneando = true;
    setEstado("Enfocando codigo de barras...");
    mostrarAyudaHttps(false);
  }

  function mensajeErrorCamara(err) {
    const nombre = (err && err.name) || "";
    if (!window.isSecureContext) {
      mostrarAyudaHttps(true);
      if (esRender) {
        return "Abra la URL https:// de Render (no http).";
      }
      return "Use HTTPS en el celular (enlace abajo) o ./iniciar_wifi.sh en la PC.";
    }
    if (nombre === "NotAllowedError") {
      return "Permita acceso a la camara en el navegador.";
    }
    if (nombre === "NotFoundError") {
      return "No se encontro camara en este dispositivo.";
    }
    return "No se pudo abrir la camara. Permita el acceso o recargue.";
  }

  async function abrirEscaner(input) {
    inputActivo = obtenerInput(input);
    if (!inputActivo) {
      setEstado("No hay campo de codigo activo.", "error");
      return;
    }

    modal.hidden = false;
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("modal-escaner-abierto");

    if (!window.isSecureContext) {
      mostrarAyudaHttps(true);
      setEstado("Se necesita HTTPS para la camara.", "error");
      if (!esRender) return;
    }

    try {
      await detenerCamara();
      await iniciarCamara();
    } catch (err) {
      console.error(err);
      setEstado(mensajeErrorCamara(err), "error");
    }
  }

  document.querySelectorAll("[data-escanear]").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const wrap = btn.closest("[data-ean-wrapper]");
      const input = wrap ? wrap.querySelector(".ean-input") : null;
      abrirEscaner(input);
    });
  });

  document.querySelectorAll(".ean-input").forEach((input) => {
    const wrap = input.closest("[data-ean-wrapper]");
    const ayuda = wrap?.querySelector("[data-ean-ayuda]");

    input.addEventListener("focus", () => {
      wrap?.classList.add("ean-enfocado");
      if (ayuda) {
        ayuda.hidden = false;
        if (!window.isSecureContext && !esRender) {
          ayuda.innerHTML =
            'Wi-Fi: abra <strong>https://</strong> en el celular (vea el recuadro arriba) o toque <strong>📷</strong>.';
        } else if (esRender && !window.isSecureContext) {
          ayuda.innerHTML =
            'Use la URL <strong>https://</strong> de Render o toque <strong>📷</strong>.';
        }
      }
    });
    input.addEventListener("blur", () => {
      wrap?.classList.remove("ean-enfocado");
      setTimeout(() => {
        if (!wrap?.contains(document.activeElement) && window.isSecureContext) {
          if (ayuda) ayuda.hidden = true;
        }
      }, 200);
    });
  });

  modal.querySelectorAll("[data-cerrar-escaner]").forEach((el) => {
    el.addEventListener("click", cerrarModal);
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !modal.hidden) cerrarModal();
  });
})();

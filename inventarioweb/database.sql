-- Inventario HB 318 — Importar en phpMyAdmin (Miarroba)
-- Crear antes la base de datos vacia y seleccionarla

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS sesiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(120) NOT NULL,
    area VARCHAR(120) NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    activa TINYINT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS registros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sesion_id INT NOT NULL,
    usuario VARCHAR(120) NOT NULL,
    fecha DATETIME NOT NULL,
    area VARCHAR(120) NOT NULL,
    pallet VARCHAR(3) NOT NULL,
    ean VARCHAR(64) NOT NULL,
    cantidad INT NOT NULL,
    numero_caja VARCHAR(32) NOT NULL,
    FOREIGN KEY (sesion_id) REFERENCES sesiones(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS zonas_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jerarquia VARCHAR(80) NOT NULL,
    ubicacion VARCHAR(80) NOT NULL,
    cantidad INT NOT NULL,
    rango_inicio INT NOT NULL,
    rango_fin INT NOT NULL,
    color_clase VARCHAR(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pallet_zonas (
    pallet VARCHAR(3) PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    jerarquia VARCHAR(80) NOT NULL,
    ubicacion VARCHAR(80) NOT NULL,
    color_clase VARCHAR(32) NOT NULL,
    rango_inicio INT NOT NULL,
    rango_fin INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Los datos de zonas se cargan automaticamente al abrir la app (install.php o primera visita)

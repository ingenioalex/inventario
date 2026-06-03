# Inventario Web — PHP + MySQL (Miarroba / Wecindario)

Version para **hosting.miarroba.com** y hosting PHP + MySQL.

## Requisitos

- PHP 7.4 o superior (con PDO MySQL)
- MySQL 5.7+
- HTTPS recomendado (camara del celular)

## Paso 1: Base de datos en el panel Miarroba

1. Entre al panel de **Miarroba** / **Wecindario**
2. **Bases de datos MySQL** → cree una base (ej. `miarroba_inventario`)
3. Anote: **host**, **usuario**, **clave**, **nombre de la base**
4. Abra **phpMyAdmin** → seleccione la base → pestaña **Importar**
5. Suba el archivo `database.sql` (opcional; la app tambien crea tablas sola)

## Paso 2: Configuracion

1. Copie `config.php.example` como `config.php`
2. Edite con sus datos MySQL:

```php
define('DB_HOST', 'localhost');  // o el host que indique Miarroba
define('DB_NAME', 'su_base');
define('DB_USER', 'su_usuario');
define('DB_PASS', 'su_clave');
define('BASE_PATH', '/inventarioweb/');  // carpeta donde suba los archivos
define('APP_SECRET', 'una-frase-secreta-larga');
```

**BASE_PATH:** si sube todo a la raiz del sitio use `''` o `'/'`.  
Si la URL es `https://tudominio.com/inventarioweb/` use `'/inventarioweb/'`.

## Paso 3: Subir archivos por FTP

Suba **todo el contenido** de la carpeta `inventarioweb/` a:

`public_html/inventarioweb/`  
(o la ruta que use su hosting)

Estructura en el servidor:

```
inventarioweb/
  index.php
  mapa.php
  inventario.php
  acciones.php
  descargar.php
  install.php
  config.php
  includes/
  assets/
  exportaciones/   (permiso escritura 755 o 775)
```

Permisos: carpeta `exportaciones/` debe permitir **escritura** (para generar TXT).

## Paso 4: Instalar tablas y datos del mapa

Abra en el navegador:

`https://tudominio.com/inventarioweb/install.php`

Si dice "OK. Tablas listas. Pallets: 200", **borre install.php** del servidor.

## Paso 5: Usar la app

`https://tudominio.com/inventarioweb/`

- Usuario + area (sin contraseña)
- Mapa de pallets
- Captura con camara (HTTPS)
- Generar TXT / Cerrar pallet (botones separados)

## Funciones (igual que version Python)

| Funcion | Si |
|---------|-----|
| Sesion usuario + area | Si |
| Mapa HB 318 coloreado | Si |
| Registro EAN, cantidad, caja | Si |
| Camara codigo barras | Si (HTTPS) |
| MySQL persistente | Si |
| Exportar TXT | Si |

## Problemas frecuentes

| Error | Solucion |
|-------|----------|
| Pantalla blanca / Error BD | Revise config.php y que la base exista |
| No genera TXT | Permisos escritura en `exportaciones/` |
| Camara no funciona | Active SSL/HTTPS en el hosting |
| Enlaces rotos | Ajuste `BASE_PATH` en config.php |

## Seguridad

- No deje `config.php` publico (`.htaccess` lo protege en Apache)
- Borre `install.php` despues de instalar
- Use HTTPS en produccion

# Generador de Esquema de Base de Datos desde CSV

Esta herramienta CLI permite generar automáticamente esquemas SQL para **SQLite**, **MySQL** y **PostgreSQL** a partir de la definición de tus tablas en un archivo CSV estructurado de forma sencilla.

La herramienta está pensada para que cualquier persona, sin importar su nivel técnico, pueda diseñar modelos relacionales complejos sin preocuparse por la sintaxis DDL de cada motor.

---

## Columnas del Archivo CSV

El archivo CSV de entrada debe contener exactamente las siguientes columnas en su cabecera (en inglés):

1. **`table`**: Nombre de la tabla (ej. `producto`).
2. **`attribute`**: Nombre de la columna / atributo (ej. `precio`).
3. **`type`**: Tipo de datos genérico o específico (ej. `INTEGER`, `VARCHAR(100)`, `TEXT`).
4. **`pk`**: Clave Primaria. Utiliza `Y` o `1` para activarlo, `N` o `0` para desactivarlo.
5. **`null`**: Si permite valores nulos (`NULL`). Utiliza `Y` o `1` para permitirlo, `N` o `0` para que sea obligatorio (`NOT NULL`).
6. **`unique`**: Si requiere que el valor sea único. Utiliza `Y` o `1` para activarlo, `N` o `0` para desactivarlo.
7. **`default`** (Opcional): Valor por defecto para la columna (ej. `1`, `'pendiente'`, `CURRENT_TIMESTAMP`).
8. **`references`** (Opcional): Establece relaciones / Claves Foráneas indicando la tabla y columna destino (ej. `empresa(id)` o simplemente `empresa`).

---

## Características de Nivel Experto Incluidas

* **Ordenamiento Topológico (DAG):** Si la tabla `pedido` hace referencia a la tabla `cliente`, el generador colocará automáticamente la creación de la tabla `cliente` antes de `pedido` para evitar errores relacionales.
* **Eliminación Segura (DROP TABLES):** Los archivos SQL generados incluyen sentencias `DROP TABLE IF EXISTS` al inicio ordenadas en reversa (primero elimina las tablas hijas y luego las padres) para no violar restricciones de integridad referencial.
* **Validación de Palabras Reservadas:** La herramienta analiza nombres de tablas y columnas y arrojará un error si intentas usar nombres no válidos como `select`, `table`, `group`, etc.
* **Integridad del Framework:** Si el archivo CSV no define la tabla `usuario`, la herramienta agregará automáticamente la definición por defecto de la tabla `usuario` y su semilla (`INSERT`) para asegurar que el sistema de autenticación de Parina y los tests sigan funcionando correctamente.

---

## Uso desde la Consola

Para generar los esquemas SQL, ejecuta la herramienta pasándole la ruta de tu archivo CSV:

```bash
php bin/generate-schema.php ruta/a/tu/archivo.csv
```

### Interactividad
Por defecto, tras compilar exitosamente las sentencias y guardarlas en la carpeta `database/` (como `schema.sqlite.sql`, `schema.mysql.sql` y `schema.pgsql.sql`), la herramienta te preguntará si deseas inicializar e importar de inmediato el nuevo esquema en tu base de datos SQLite local.

Si estás automatizando procesos y no deseas interacción, puedes usar la bandera `--no-interaction`:
```bash
php bin/generate-schema.php ruta/a/tu/archivo.csv --no-interaction
```

---

## 3 Ejemplos Prácticos

### Ejemplo 1: Estructura Básica Simple (Inventario)

Define un catálogo de productos e inventario sencillo.

**Archivo `inventario.csv`:**
```csv
table,attribute,type,pk,null,unique,default,references
producto,id,INTEGER,Y,N,N,,
producto,nombre,VARCHAR(100),N,N,Y,,
producto,precio,DECIMAL(10,2),N,N,N,0.00,
producto,descripcion,TEXT,N,Y,N,,
producto,creado_at,timestamp,N,N,N,CURRENT_TIMESTAMP,
```

**Esquema SQLite Generado:**
```sql
PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS producto;
DROP TABLE IF EXISTS usuario;

CREATE TABLE IF NOT EXISTS producto (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    descripcion TEXT DEFAULT NULL,
    creado_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS usuario ( ... ); -- Autoinyectada por el framework
```

---

### Ejemplo 2: Relaciones Uno a Muchos y Muchos a Muchos (e-Commerce)

Define clientes, pedidos vinculados a clientes, e ítems de pedido que vinculan productos y pedidos. Demuestra claves foráneas y el ordenamiento topológico automático (`cliente` y `producto` se crean antes que `pedido`, y `pedido` se crea antes que `pedido_item`).

**Archivo `ecommerce.csv`:**
```csv
table,attribute,type,pk,null,unique,default,references
cliente,id,INTEGER,Y,N,N,,
cliente,nombre,VARCHAR(100),N,N,N,,
cliente,email,VARCHAR(150),N,N,Y,,
pedido,id,INTEGER,Y,N,N,,
pedido,cliente_id,INTEGER,N,N,N,,cliente(id)
pedido,monto_total,DECIMAL(10,2),N,N,N,0.00,
pedido,creado_at,timestamp,N,N,N,CURRENT_TIMESTAMP,
pedido_item,id,INTEGER,Y,N,N,,
pedido_item,pedido_id,INTEGER,N,N,N,,pedido
pedido_item,producto_id,INTEGER,N,N,N,,producto(id)
pedido_item,cantidad,INTEGER,N,N,N,1,
producto,id,INTEGER,Y,N,N,,
producto,nombre,VARCHAR(100),N,N,N,,
producto,precio,DECIMAL(10,2),N,N,N,0.00,
```

**Esquema PostgreSQL Generado:**
```sql
DROP TABLE IF EXISTS pedido_item CASCADE;
DROP TABLE IF EXISTS pedido CASCADE;
DROP TABLE IF EXISTS producto CASCADE;
DROP TABLE IF EXISTS cliente CASCADE;
DROP TABLE IF EXISTS usuario CASCADE;

CREATE TABLE IF NOT EXISTS cliente (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS producto (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00
);

CREATE TABLE IF NOT EXISTS pedido (
    id SERIAL PRIMARY KEY,
    cliente_id INT NOT NULL,
    monto_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    creado_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES cliente(id)
);

CREATE TABLE IF NOT EXISTS pedido_item (
    id SERIAL PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    FOREIGN KEY (pedido_id) REFERENCES pedido(id),
    FOREIGN KEY (producto_id) REFERENCES producto(id)
);
```

---

### Ejemplo 3: Personalización de la Tabla de Usuarios

Si decides personalizar o expandir la tabla `usuario` requerida por el framework, simplemente defínela en tu CSV y el generador utilizará tu estructura en lugar de la básica por defecto.

**Archivo `usuarios_personalizados.csv`:**
```csv
table,attribute,type,pk,null,unique,default,references
usuario,id,INTEGER,Y,N,N,,
usuario,username,VARCHAR(100),N,N,Y,,
usuario,password,VARCHAR(255),N,N,N,,
usuario,email,VARCHAR(255),N,N,N,,
usuario,rol,VARCHAR(50),N,N,N,'operador',
usuario,ultimo_login,TIMESTAMP,N,Y,N,,
usuario,is_active,TINYINT,N,N,N,1,
```

**Esquema MySQL Generado:**
```sql
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS usuario;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE IF NOT EXISTS usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    rol VARCHAR(50) NOT NULL DEFAULT 'operador',
    ultimo_login TIMESTAMP DEFAULT NULL,
    is_active TINYINT NOT NULL DEFAULT 1
);
```

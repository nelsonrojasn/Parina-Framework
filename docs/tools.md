# Herramientas de Consola (CLI Tools)

Parina Framework incluye un conjunto de herramientas de línea de comandos en el directorio `bin/` para automatizar tareas comunes como la generación de código (scaffolding), el listado de rutas, el linting de arquitectura y el reinicio del proyecto.

Este documento explica cómo funciona cada una de estas herramientas y proporciona ejemplos prácticos de uso.

---

## 1. Andamiaje de Rutas y Features: `bin/scaffold.php`

La herramienta **[scaffold.php](../bin/scaffold.php)** te permite generar de forma automatizada los controladores (Handlers), las vistas correspondientes y la estructura de directorios de tus features basándose en una definición previa en un archivo CSV.

### ¿Cómo funciona?
1.  Lee el archivo CSV (por ejemplo, `routes.csv`) que define los verbos HTTP, rutas, features, middlewares y descripciones.
2.  Para cada feature definida, **crea de forma automática la estructura de carpetas modular (FDA)**:
    *   `src/Features/{FeatureName}/Handlers/`
    *   `src/Features/{FeatureName}/Views/`
    *   `src/Features/{FeatureName}/Commands/`
    *   `src/Features/{FeatureName}/Queries/`
    *   `src/Features/{FeatureName}/Services/`
    *   `src/Features/{FeatureName}/Interfaces/`
    *   Y sus respectivas carpetas en la suite de pruebas bajo `tests/Features/{FeatureName}/`.
3.  Crea stubs iniciales para cada Handler y su respectivo test unitario de integración.
4.  Genera de forma dinámica el archivo de rutas activo `config/routes.php`.

### Uso
```bash
php bin/scaffold.php routes.csv
```

---

## 2. Listado Dinámico de Rutas: `bin/routes-list.php`

La herramienta **[routes-list.php](../bin/routes-list.php)** permite previsualizar en la terminal una tabla ordenada y a color con todas las rutas que se encuentran activas en la aplicación.

### ¿Cómo funciona?
*   Lee dinámicamente el archivo de configuración activo `config/routes.php`.
*   Extrae el nombre de la Feature analizando la ruta del namespace del Handler.
*   Muestra los nombres cortos de los middlewares asignados a cada ruta.
*   **Utiliza Reflexión de PHP:** Carga dinámicamente la clase del Handler y examina su comentario de bloque (DocBlock) en busca de la etiqueta `Description:` para mostrar qué hace el endpoint directamente en la tabla.

### Uso
```bash
php bin/routes-list.php
```

### Ejemplo de salida en consola:
```text
+--------+----------------------------+----------------+---------------------+-----------------------+--------------------------------+
| Method | Path                       | Feature        | HandlerName         | Middlewares           | Description                    |
+--------+----------------------------+----------------+---------------------+-----------------------+--------------------------------+
| GET    | /                          | Marketing      | HomeHandler         |                       |                                |
| GET    | /comprar/credito/auto/{id} | AutoPurchase   | AutoPurchaseHandler | Auth                  | Comprar auto                   |
| GET    | /admin/users/{hash}        | UserManagement | UsersListHandler    | RateLimit, Csrf, Auth |                                |
| GET    | /setup                     | Database       | SetupHandler        |                       | Inicializar la base de datos   |
+--------+----------------------------+----------------+---------------------+-----------------------+--------------------------------+
```

---

## 3. Generación de Comandos CQS: `bin/generate-command.php`

Siguiendo el principio de **Segregación de Comandos y Consultas (CQS)**, las operaciones que modifican la base de datos (escrituras, actualizaciones y borrados) se gestionan mediante repositorios de comandos.

La herramienta **[generate-command.php](../bin/generate-command.php)** genera estas clases dentro de la Feature.

### ¿Cómo funciona?
1.  Crea la interfaz del repositorio en `src/Features/{Feature}/Commands/{RepositoryName}CommandRepositoryInterface.php`.
2.  Crea la implementación concreta con soporte para transacciones en `src/Features/{Feature}/Commands/Db{RepositoryName}CommandRepository.php`.
3.  Crea una prueba de integración con base de datos SQLite en memoria en `tests/Features/{Feature}/Commands/Db{RepositoryName}CommandRepositoryTest.php`.
4.  **Auto-registro:** Añade de forma automática la relación interfaz-concreto dentro del bloque de CQS de [config/dependencies.php](../config/dependencies.php).

### Uso
```bash
php bin/generate-command.php <feature> <name> [table_name]
```

*   **Ejemplo:**
    ```bash
    php bin/generate-command.php UserManagement Profile user_profiles
    ```
    *Generará la interfaz `ProfileCommandRepositoryInterface`, el repositorio `DbProfileCommandRepository` mapeado a la tabla `user_profiles`, y sus pruebas.*

---

## 4. Generación de Consultas CQS: `bin/generate-query.php`

Las operaciones que leen datos de la base de datos sin alterar su estado se gestionan mediante repositorios de consultas (Queries).

La herramienta **[generate-query.php](../bin/generate-query.php)** automatiza su creación dentro de la Feature.

### ¿Cómo funciona?
1.  Crea la interfaz del repositorio en `src/Features/{Feature}/Queries/{RepositoryName}QueryRepositoryInterface.php`.
2.  Crea la implementación concreta con consultas `SELECT` preparadas en `src/Features/{Feature}/Queries/Db{RepositoryName}QueryRepository.php`.
3.  Crea una prueba de integración con base de datos SQLite en memoria en `tests/Features/{Feature}/Queries/Db{RepositoryName}QueryRepositoryTest.php`.
4.  **Auto-registro:** Registra de forma automática la dependencia en [config/dependencies.php](../config/dependencies.php).

### Uso
```bash
php bin/generate-query.php <feature> <name> [table_name]
```

*   **Ejemplo:**
    ```bash
    php bin/generate-query.php UserManagement Profile user_profiles
    ```

---

## 5. Linter de Arquitectura: `bin/linter.php`

El linter **[linter.php](../bin/linter.php)** garantiza que la arquitectura modular y los principios de diseño de Parina Framework no se corrompan a medida que crece el proyecto.

### Reglas que valida:
1.  **Sintaxis PHP:** Analiza sintácticamente todos los archivos `.php` del proyecto para evitar errores de compilación o de sintaxis.
2.  **Estabilidad del Grafo DI (DAG):** Comprueba que la inyección de dependencias configurada en `config/dependencies.php` no tenga bucles o referencias circulares (por ejemplo, que la Clase A inyecte la Clase B, y la Clase B inyecte la Clase A).
3.  **Aislamiento CQS:** 
    *   Verifica que las clases de consulta (Queries) no llamen a métodos mutadores como `insert`, `update` o `delete`.
    *   Verifica que los métodos de consulta no retornen `void`.
    *   Verifica que los métodos de comandos solo retornen tipos permitidos (`void`, `bool`, `int`).
    *   Verifica que los controladores (Handlers) no inyecten directamente el `DatabaseAdapter` (forzándolos a consumir la base de datos mediante repositorios CQS). *Los repositorios CQS dentro de la Feature están legítimamente exceptuados de esta regla.*

### Uso
```bash
php bin/linter.php
```

---

## 6. Restablecer el Canvas: `bin/cleanup.php`

La herramienta **[cleanup.php](../bin/cleanup.php)** está diseñada para restablecer el framework a un estado inicial completamente limpio ("lienzo en blanco").

### ¿Qué hace?
*   Elimina de forma recursiva todos los directorios de Features de demostración (`Dashboard`, `UserManagement`, `Authentication`, `AutoPurchase`, `Database`) tanto de `src/Features/` como de `tests/Features/` (incluyendo sus comandos, consultas y vistas).
*   Elimina archivos de demostración aislados y sus pruebas en la feature `Marketing`.
*   Borra la base de datos local SQLite si existiera.
*   Restablece `config/routes.php` y `routes.csv` a su estado original (solo con la ruta raíz `/`).

### Uso
```bash
php bin/cleanup.php
```
*(Puedes añadir el parámetro `--force` para evitar la confirmación interactiva en terminal).*

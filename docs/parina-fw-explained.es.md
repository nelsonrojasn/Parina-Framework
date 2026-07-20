---

# 1. Ideología: Menos es Más (La Revolución de la Servilleta)

La ideología de Parina no surge de la limitación, sino de la **intencionalidad**. Está gobernada por tres principios filosóficos:

* **KISS (Keep It Simple, Stupid) & YAGNI (You Aren't Gonna Need It)**: La mayor parte de la complejidad en los frameworks modernos es accidental. Parina se pregunta: *¿cuál es la estructura mínima necesaria para construir una aplicación web segura, mantenible y de alto rendimiento?* Su peso en RAM (~0.05 MB) y tiempo de ejecución (~0.0007 segundos) son consecuencias directas de esta filosofía.
* **Explicitud sobre "Magia" (No-Magic)**: Evita ciclos de vida ocultos, ORMs pesados o archivos de configuración gigantescos. Lo que se lee en el código es exactamente lo que se ejecuta.
* **Desacoplamiento Pragmático (SOLID)**: Parina favorece la **Inversión de Control (IoC)** y la **Segregación de Interfaces**. A través de su contenedor DI basado en Reflection y servicios orientados a interfaces, permite cambiar implementaciones concretas (bases de datos, firmadores, autenticadores, generadores SQL) sin tocar el núcleo del framework ni los controladores.

---

# 2. Flujo de Ejecución: Ciclo de Vida de la Petición

El flujo de Parina es un **pipeline secuencial y síncrono** que implementa el patrón **Front Controller**:

```
HTTP Request
   │
   ▼
[public/index.php] (Front Controller)
   │
   ├──> Registra métricas (PIN_START_TIME, PIN_START_MEM) e inicia sesión PHP
   ├──> Carga Autoloader nativo (src/autoload.php) y helper global h()
   ├──> Instancia [Container] y carga config/dependencies.php (IoC)
   ├──> Registra servicios globales compartidos en [View::share()] (auth, cipher, config)
   └──> Inicializa [Router] y registra el mapa de rutas desde config/routes.php
   │
   ▼
[Kernel] (Dispatcher)
   │
   ├──> Captura la petición HTTP mediante [Request::capture()]
   │
   ├──> [Pipeline de Middlewares] (Filtros de interceptación)
   │       └──> Si un middleware retorna un [Response] (ej. error 401), el flujo se interrumpe inmediatamente.
   │
   ├──> [Container::get()] (Resolución DI basada en Reflection)
   │       └──> Instancia el Handler resolviendo sus dependencias de constructor recursivamente.
   │
   ├──> [Handler::handle(RequestInterface)] (Controlador de Feature)
   │       └──> Ejecuta la lógica y retorna un objeto que implementa [Response] (HtmlResponse, JsonResponse, etc.)
   │
   ▼
[ResponseEmitter] (Emisión)
   └──> Envía cabeceras HTTP, código de estado y emite el contenido al cliente.
```

---

# 3. Diseño de Directorios: Feature-Driven Architecture (FDA)

A diferencia de los frameworks tradicionales que segregan el código por responsabilidad técnica (ej. todos los controladores en una carpeta, todas las vistas en otra) o por rol de acceso (`Public`/`Admin`), Parina implementa **Feature-Driven Architecture (FDA)**.

Todo el código que pertenece a una característica cohesiva de negocio se agrupa modularmente bajo `src/Features/`:

```
src/Features/
└── [NombreCaracteristica]/
    ├── Handlers/      <-- Controladores HTTP de entrada para la característica
    ├── Views/         <-- Plantillas HTML específicas de la característica
    ├── Commands/      <-- Repositorios e interfaces CQS para mutación de datos (Escritura)
    ├── Queries/       <-- Repositorios e interfaces CQS para consulta de datos (Lectura)
    ├── Services/      <-- Servicios de dominio propios de la característica
    └── Interfaces/    <-- Contratos e interfaces locales de la característica
```

Además, cada característica cuenta con su suite de pruebas de integración simétrica en `tests/Features/[NombreCaracteristica]/`.

### Beneficios:
* **Alta Cohesión:** Los controladores, repositorios, servicios y vistas que colaboran juntos viven en el mismo espacio modular.
* **Eliminación de Código Muerto:** Borrar una funcionalidad es tan sencillo como eliminar su subcarpeta en `src/Features/`.
* **Enrutamiento Seguro:** El control de acceso (admin vs público) se desacopla de la estructura física de directorios y se gestiona limpiamente mediante middlewares de ruta (`Auth`, `Acl`, `RateLimit`).

---

# 4. Seguridad: Arquitectura Defensiva e Interfaces Puras

La seguridad en Parina se organiza en capas y se ejecuta principalmente en el pipeline de middlewares, garantizando que el tráfico malicioso nunca alcance a los controladores de negocio:

* **Autenticación sin Estado**:
  * **JWT**: El middleware [JwtAuth](../src/Shared/Middlewares/JwtAuth.php) extrae tokens mediante `$request->bearerToken()`, los valida usando `TokenServiceInterface` e inyecta la identidad en la bolsa de contexto local de la petición (`$request->setAttribute('user_id', ...)`).
  * **Basic Auth**: El middleware [BasicAuth](../src/Shared/Middlewares/BasicAuth.php) valida credenciales mediante `UserQueryRepositoryInterface` y `password_verify()` directamente en el middleware, separando estrictamente la lógica de autenticación de las consultas a base de datos.
* **Firma Criptográfica de URL**: El middleware [ValidateHash](../src/Shared/Middlewares/ValidateHash.php) analiza firmas temporales (TTL) de enlaces sensibles mediante una instancia inyectada de `CipherInterface`, validando la integridad del enlace antes de enrutar la petición.
* **Control de Acceso (ACL)**: Basado en `AclInterface`, permite validar permisos dinámicos. Sus dependencias (como `Logger`) son strictly requeridas en el constructor mediante DI, eliminando fallbacks de fachadas estáticas.
* **Prevención de XSS y CSRF**:
  * **CSRF**: Un token inyectado en formularios y validado en middlewares de interceptación protege contra la falsificación de peticiones.
  * **XSS**: El helper global `h($variable)` actúa como un sanitizador de escape nativo en las vistas PHP (`htmlspecialchars`).

---

# 5. Acceso a Datos: CQS Puro y Adaptadores de BD

Parina prescinde totalmente de ORMs o modelos mágicos Active Record. La persistencia sigue de forma estricta el patrón **CQS (Command Query Segregation)**:

### A. Segregación de Comandos y Consultas (CQS)
Las operaciones de base de datos se dividen explícitamente en dos tipos de repositorios:
* **Consultas / Lectura (`*QueryRepositoryInterface`)**: Operaciones puras de consulta que nunca mutan el estado de la base de datos (ej. `findById()`, `findByUsername()`, `all()`).
* **Comandos / Escritura (`*CommandRepositoryInterface`)**: Operaciones de mutación de estado ejecutadas bajo transacciones explícitas (ej. `save()`, `delete()`).
* **Lógica de credenciales:** El repositorio devuelve únicamente datos planos (desacoplamiento). La comparación criptográfica de contraseñas se realiza en las capas de aplicación/middleware para preservar una separación limpia.

### B. Generador SQL Multi-Dialecto (`SqlGeneratorInterface`)
* Los repositorios CQS no escriben SQL acoplado a un motor específico. Inyectan `SqlGeneratorInterface` para compilar dinámicamente sentencias preparadas para SQLite, MySQL o PostgreSQL.

### C. Adaptador de Base de Datos (Adapter Pattern & OCP)
* El motor final de base de datos se resuelve dinámicamente a través de la interfaz `DatabaseAdapter` registrada en el contenedor DI ([config/dependencies.php](../config/dependencies.php)).
* Soporta adaptadores concretos (`MySqlAdapter`, `PostgreSqlAdapter`, `SqliteAdapter`).
* Cumple con el **Principio Abierto/Cerrado (OCP)**: añadir un nuevo motor solo requiere implementar `DatabaseAdapter` y registrar la fábrica en `dependencies.php`.

---

# 6. Herramientas CLI, Scaffolding y Auditor de Arquitectura (Guardian)

Un pilar fundamental de la arquitectura de Parina es su ecosistema de automatización en `bin/`, diseñado para mantener la integridad del diseño a medida que la aplicación escala:

* **Scaffolding de Features (`bin/scaffold.php`)**: Genera de forma automatizada la estructura modular FDA (carpetas, Handlers, Vistas y Tests unitarios) a partir de definiciones CSV (`routes.csv`).
* **Generadores CQS (`bin/generate-command.php` y `bin/generate-query.php`)**: Crean interfaces de repositorio, clases concretas con transacciones o sentencias preparadas, tests de integración con SQLite en memoria y realizan el auto-registro en `config/dependencies.php`.
* **Generador de Esquema por Estrategias (`bin/generate-schema.php`)**: Compila esquemas DDL multi-dialecto (SQLite, MySQL, PostgreSQL) desde CSV. Aplica **Ordenamiento Topológico (DAG)** para resolver dependencias de claves foráneas y generar sentencias `DROP TABLE` en cascada.
* **Auditor Arquitectónico y Linter (`bin/guardian.php` / `bin/linter.php`)**: Herramienta de auditoría estática (*Parina Guardian*) que garantiza la salud del sistema mediante 5 fases de verificación:
  1. **Sintaxis PHP y Estabilidad Core:** Analiza sintácticamente todo el código del proyecto.
  2. **Grafo DI (DAG) y Pureza DIP:** Comprueba que no existan bucles ni referencias circulares en el contenedor y valida la abstracción mediante interfaces.
  3. **Aislamiento CQS y Agnosticismo HTTP:** Garantiza que las consultas no muten ni retornen `void`, los comandos solo retornan tipos permitidos (`void`, `bool`, `int`), y los repositorios no dependan de HTTP.
  4. **Límites de Handlers delgados y Vistas:** Evita lógica o maquetación HTML inline dentro de los Handlers y prohíbe la inyección directa de `DatabaseAdapter` en controladores de entrada.
  5. **Aislamiento de Features y Métricas Monolíticas:** Evalúa el acoplamiento y el balance de código entre características para detectar el crecimiento desproporcionado de módulos.
* **Orquestador de Construcción (`bin/orchestrator.php`)**: Unifica en 4 fases la construcción completa del proyecto (Cleanup -> Scaffold -> DB Schema -> Batch CQS).
* **Inspección de Rutas (`bin/routes-list.php`)**: Visualiza en terminal la tabla de rutas activas extrayendo por reflexión los middlewares y el DocBlock `@Description` de cada Handler.

---

### Diagnóstico Final del Arquitecto:
Parina Framework demuestra que la simplicidad extrema no está reñida con los buenos patrones de diseño. Su arquitectura desacoplada por Inyección de Dependencias (DIP), su organización física por características (FDA), su segregación estricta de datos (CQS) y su validación estática continua (Guardian) lo convierten en un motor de aplicaciones PHP extremadamente ágil, seguro, predecible y fácil de testear.
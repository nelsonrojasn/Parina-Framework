---

# 1. Ideología: Menos es Más (La Revolución de la Servilleta)

La ideología de Parina no surge de la limitación, sino de la **intencionalidad**. Está gobernada por tres principios filosóficos:

* **KISS (Keep It Simple, Stupid) & YAGNI (You Aren't Gonna Need It)**: La mayor parte de la complejidad en los frameworks modernos es accidental. Parina se pregunta: *¿cuál es la estructura mínima necesaria para construir una aplicación web segura, mantenible y de alto rendimiento?* Su peso en RAM (~0.05 MB) y tiempo de ejecución (~0.0007 segundos) son consecuencias de esta filosofía.
* **Explicitud sobre "Magia" (No-Magic)**: Evita ciclos de vida ocultos o archivos de configuración gigantescos. Lo que se lee en el código es exactamente lo que se ejecuta.
* **Desacoplamiento Pragmático (SOLID)**: Parina favorece la **Inversión de Control (IoC)** y la **Segregación de Interfaces**. A través de su contenedor DI y servicios basados en interfaces, permite cambiar implementaciones concretas (bases de datos, firmadores, autenticadores) sin tocar el núcleo del framework ni los controladores.

---

# 2. Flujo de Ejecución: Ciclo de Vida de la Petición

El flujo de Parina es un **pipeline secuencial y síncrono** que implementa el patrón **Front Controller**:

```
HTTP Request
   │
   ▼
[public/index.php] (Front Controller)
   │
   ├──> Carga Autoloader y helper global h()
   ├──> Instancia [Container] y carga config/dependencies.php (IoC)
   ├──> Inicializa [Db] con el [DatabaseAdapter] resuelto dinámicamente (OCP)
   └──> Inicializa [Router] y registra config/routes.php
   │
   ▼
[Kernel] (Dispatcher)
   │
   ├──> Captura superglobales en un objeto [Request] (Value Object)
   │
   ├──> [Pipeline de Middlewares] (Filtros de interceptación)
   │       └──> Si un middleware retorna un [Response] (ej. error 401), el flujo se interrumpe.
   │
   ├──> [Container::get()] (Resolución DI basada en Reflection)
   │       └──> Instancia el Handler resolviendo sus dependencias recursivamente.
   │
   ├──> [Handler::handle(Request)] (Controlador)
   │       └──> Ejecuta la lógica y retorna un objeto que implementa [Response]
   │
   ▼
[Kernel::send()] (Emisión)
   └──> Envía cabeceras HTTP, código de estado y emite el contenido.
```

---

# 3. Diseño de Directorios: Feature-Driven Architecture (FDA)

A diferencia de los frameworks tradicionales que segregan el código por responsabilidad técnica (ej. todos los controladores en una carpeta, todas las vistas en otra) o por rol de acceso (`Public`/`Admin`), Parina implementa **Feature-Driven Architecture (FDA)**.

Todo el código que pertenece a una característica cohesiva de negocio se agrupa bajo `src/Features/`:

```
src/Features/
└── [NombreCaracteristica]/
    ├── Handlers/      <-- Controladores de entrada para la característica
    └── Views/         <-- Plantillas HTML específicas de la característica
```

### Beneficios:
* **Alta Cohesión:** Los controladores y las vistas que colaboran juntos viven en el mismo directorio.
* **Eliminación de Código Muerto:** Borrar una funcionalidad es tan sencillo como eliminar su subcarpeta.
* **Enrutamiento Seguro:** El control de acceso (admin vs público) se desacopla de la estructura física de directorios y se gestiona limpiamente mediante middlewares de ruta (`Auth`, `Acl`).

---

# 4. Seguridad: Arquitectura Defensiva e Interfaces Puras

La seguridad en Parina se organiza en capas y se ejecuta principalmente en el pipeline de middlewares, garantizando que el tráfico malicioso nunca alcance a los controladores de negocio:

* **Autenticación sin Estado**:
  * **JWT**: El middleware [JwtAuth](file:///Users/nelson/repos/parina-fw/src/Shared/Middlewares/JwtAuth.php) extrae tokens usando el helper `$request->bearerToken()`, los valida mediante `TokenServiceInterface`, e inyecta la identidad en los atributos locales de la petición (`$request->setAttribute('user_id')`).
  * **Basic Auth**: El middleware [BasicAuth](file:///Users/nelson/repos/parina-fw/src/Shared/Middlewares/BasicAuth.php) valida credenciales usando `UserQueryRepositoryInterface` y la función nativa `password_verify()` directamente en el middleware, separando estrictamente la lógica de autenticación de las consultas SQL.
* **Firma de URL Criptográfica**: El middleware [ValidateHash](file:///Users/nelson/repos/parina-fw/src/Shared/Middlewares/ValidateHash.php) analiza firmas temporales (TTL) de enlaces sensibles mediante una instancia inyectada de `CipherInterface`, validando la integridad del enlace antes de enrutar la petición.
* **Control de Acceso (ACL)**: Basado en `AclInterface`, permite validar permisos dinámicos. Sus dependencias (como `Logger`) son estrictamente requeridas en el constructor, eliminando fallbacks de fachadas estáticas.
* **Prevención de XSS y CSRF**:
  * **CSRF**: Un token inyectado en formularios y validado en middlewares protege contra la falsificación de peticiones.
  * **XSS**: El helper global `h($variable)` actúa como un sanitizador de escape nativo en las vistas PHP (`htmlspecialchars`).

---

# 5. Acceso a Datos: Abstracciones Puras y CQS

Parina ofrece dos estrategias de persistencia en la base de datos:

### A. Persistencia por Repositorio (CQS - Command Query Segregation)
Es el enfoque desacoplado del framework. Las operaciones se separan en interfaces de consultas y comandos:
* **Lectura (`UserQueryRepositoryInterface`)**: Operaciones puras de consulta (`findById()`, `findByUsername()`, `all()`).
* **Escritura (`UserCommandRepositoryInterface`)**: Operaciones de mutación de estado (`save()`, `delete()`).
* **Implementación (`DbUserQueryRepository` y `DbUserCommandRepository`)**: Clases desacopladas que inyectan `SqlGeneratorInterface` para la interacción con la base de datos.
* **Lógica de credenciales:** Removida de la capa de repositorios. El repositorio únicamente devuelve los registros planos y la comparación criptográfica de claves se realiza en las capas de aplicación (como `LoginCheckHandler`) para preservar una separación limpia.

### B. Persistencia por Active Record (`BaseModel`)
* Las clases modelo heredan de `BaseModel` y mapean directamente a las tablas de la base de datos.
* Desacoplado de la lógica de compilación de SQL consumiendo la interfaz `SqlGeneratorInterface` inyectada y resuelta por el contenedor DI.

### C. Adaptador de Base de Datos (Adapter Pattern)
* El motor final de base de datos (SQLite, MySQL o PostgreSQL) se resuelve dinámicamente mediante la interfaz `DatabaseAdapter` registrada en el contenedor.
* Cumple con el **Principio Abierto/Cerrado (OCP)**: añadir un nuevo motor de base de datos solo requiere crear una clase que implemente `DatabaseAdapter` y registrarla en `dependencies.php`.

---

### Diagnóstico Final del Arquitecto:
Parina Framework demuestra que la simplicidad extrema no está reñida con los buenos patrones de diseño. Su arquitectura moderna en desacoplamiento de dependencias (DIP), organización física (FDA) y segregación de interfaces de datos (CQS) lo convierten en un motor de aplicaciones PHP ágil, seguro y extremadamente fácil de testear.
---

# 1. Ideología: Menos es Más (The Napkin Revolution)

La ideología de Parina no nace de la limitación, sino de la **intencionalidad**. Se rige por tres principios filosóficos:

* **KISS (Keep It Simple, Stupid) y YAGNI (You Aren't Gonna Need It)**: La mayoría de la complejidad en los frameworks modernos es accidental. Parina se pregunta: *¿cuál es la estructura mínima necesaria para construir una aplicación web segura, mantenible y de alto rendimiento?* Su peso en RAM (~0.05 MB) y tiempo de ejecución (~0.0007 segundos) son consecuencia de esta filosofía.
* **Explicidad sobre "Magia" (No-Magic)**: Evita ciclos de vida ocultos o archivos de configuración inmensos. Lo que se lee en el código es exactamente lo que se ejecuta.
* **Desacoplamiento Pragmático (SOLID)**: Parina favorece la **Inversión de Control (IoC)** y la **Segregación de Interfaces**. A través de su contenedor DI y servicios basados en interfaces, permite cambiar las implementaciones concretas (bases de datos, cifradores, autenticadores) sin tocar el núcleo del framework ni los controladores.

---

# 2. Flujo de Ejecución: El Ciclo de Vida de la Petición

El flujo de Parina es un **pipeline secuencial y síncrono** que implementa el patrón **Front Controller**:

```
Petición HTTP
   │
   ▼
[public/index.php] (Front Controller)
   │
   ├──> Carga Autoloader y Helper global h()
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
   │       └──> Si un middleware retorna [Response] (ej: error 401), se corta el flujo.
   │
   ├──> [Container::get()] (Resolución DI por Reflection)
   │       └──> Instancia el Handler resolviendo recursivamente sus dependencias.
   │
   ├──> [Handler::handle(Request)] (Controller)
   │       └──> Ejecuta lógica y retorna un objeto que implementa [Response]
   │
   ▼
[Kernel::send()] (Emisión)
   └──> Envía cabeceras HTTP, status code y hace echo del contenido.
```

---

# 3. Seguridad: Arquitectura Defensiva e Interfaces Puras

La seguridad de Parina se organiza en capas y se ejecuta principalmente en el pipeline de middlewares, garantizando que el tráfico malicioso nunca llegue a los controladores de negocio:

* **Autenticación sin Estado (Stateless)**:
  * **JWT**: El middleware [JwtAuth](file:///home/nelson/repos/Parina-Framework/src/Shared/Middlewares/JwtAuth.php) extrae tokens usando el helper `$request->bearerToken()`, los valida mediante `TokenServiceInterface` e inyecta la identidad en los atributos locales del Request (`$request->setAttribute('user_id')`).
  * **Basic Auth**: El middleware [BasicAuth](file:///home/nelson/repos/Parina-Framework/src/Shared/Middlewares/BasicAuth.php) valida credenciales usando `UserQueryRepositoryInterface::checkCredentials()`, lo que previene la creación innecesaria de cookies y sesiones web en APIs REST.
* **Firmado Criptográfico de URLs**: El middleware [ValidateHash](file:///home/nelson/repos/Parina-Framework/src/Shared/Middlewares/ValidateHash.php) inyecta `CipherInterface` para parsear firmas temporales (TTL) de enlaces sensibles, validando la integridad del enlace antes de rutear la petición.
* **Control de Accesos (ACL)**: Basado en la interfaz `AclInterface`, permite validar permisos dinámicos e inyectar fácilmente implementaciones de prueba (mocks) en el entorno de testing.
* **Prevención de XSS y CSRF**:
  * **CSRF**: Un token inyectado en formularios y validado en middlewares protege contra falsificación de peticiones.
  * **XSS**: El helper global `h($variable)` actúa como sanitizador de escape nativo en las vistas PHP (`htmlspecialchars`).

---

# 4. Acceso y Modificación de Datos: El Doble Estrato de Persistencia

Parina ofrece flexibilidad al desarrollador permitiendo dos aproximaciones de persistencia:

### A. Persistencia por Repositorio (CQS - Command Query Segregation)
Es la aproximación moderna y limpia del framework. Divide las operaciones en interfaces de consulta y de escritura:
* **Lectura (`UserQueryRepositoryInterface`)**: Retorna datos planos u objetos de valor específicos. Optimizado para consultas complejas y velocidad.
* **Escritura (`UserCommandRepositoryInterface`)**: Persiste y modifica el estado del sistema.
* **DbUserRepository**: Implementación que centraliza el SQL.
* *Beneficio*: Desacoplamiento de la base de datos de la sesión HTTP (SRP) y pruebas unitarias 100% en memoria mediante mocks.

### B. Persistencia por Active Record (`BaseModel`)
* Las clases como `User` heredan directamente de `BaseModel`. Mapean propiedades de clases a columnas de tablas y proveen métodos CRUD directos (`all()`, `find()`, `create()`).
* Es una opción ideal para prototipado rápido y operaciones CRUD muy sencillas.

### C. Abstracción del Driver (Adapter Pattern)
* El motor de base de datos final (SQLite, MySQL o PostgreSQL) se inyecta dinámicamente mediante la interfaz `DatabaseAdapter` registrada en el contenedor.
* Cumple con el **Principio Abierto/Cerrado (OCP)**: si necesitas migrar de base de datos o añadir un motor no soportado (como SQL Server), solo debes crear una clase que implemente `DatabaseAdapter` y registrarla en `dependencies.php`, sin cambiar una sola línea de código interno del framework.

---

### Diagnóstico Final del Arquitecto:
Parina Framework demuestra que la simplicidad extrema no está peleada con los buenos patrones de diseño. Su arquitectura moderna en desacoplamiento de dependencias (DIP) y la segregación de interfaces de datos (CQS) lo convierten en un motor de aplicaciones en PHP ágil, seguro y sumamente fácil de probar.
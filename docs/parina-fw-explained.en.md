---

# 1. Ideology: Less is More (The Napkin Revolution)

Parina's ideology does not stem from limitation, but from **intentionality**. It is governed by three philosophical principles:

* **KISS (Keep It Simple, Stupid) & YAGNI (You Aren't Gonna Need It)**: Most complexity in modern frameworks is accidental. Parina asks: *what is the minimum structure required to build a secure, maintainable, and high-performance web application?* Its RAM weight (~0.05 MB) and execution time (~0.0007 seconds) are consequences of this philosophy.
* **Explicitness over "Magic" (No-Magic)**: Avoids hidden lifecycles or huge configuration files. What is read in the code is exactly what is executed.
* **Pragmatic Decoupling (SOLID)**: Parina favors **Inversion of Control (IoC)** and **Interface Segregation**. Through its DI container and interface-based services, it allows changing concrete implementations (databases, signers, authenticators) without touching the framework core or controllers.

---

# 2. Execution Flow: The Request Lifecycle

Parina's flow is a **sequential and synchronous pipeline** that implements the **Front Controller** pattern:

```
HTTP Request
   │
   ▼
[public/index.php] (Front Controller)
   │
   ├──> Load Autoloader and global h() helper
   ├──> Instantiate [Container] and load config/dependencies.php (IoC)
   ├──> Initialize [Db] with the dynamically resolved [DatabaseAdapter] (OCP)
   └──> Initialize [Router] and register config/routes.php
   │
   ▼
[Kernel] (Dispatcher)
   │
   ├──> Receive request object implementing [RequestInterface]
   │
   ├──> [Middlewares Pipeline] (Intercepting filters)
   │       └──> If a middleware returns [Response] (e.g. 401 error), the flow cuts short.
   │
   ├──> [Container::get()] (Reflection-based DI Resolution)
   │       └──> Instantiate the Handler resolving its dependencies recursively.
   │
   ├──> [Handler::handle(RequestInterface)] (Controller)
   │       └──> Execute logic and return an object implementing [Response]
   │
   ▼
[ResponseEmitter] (Emission)
   └──> Send HTTP headers, status code, and echo the content.
```

---

# 3. Directory Layout: Feature-Driven Architecture (FDA)

Unlike traditional frameworks that segregate code by technical responsibility (e.g., all controllers in one folder, all views in another) or by access role (`Public`/`Admin` folders), Parina implements **Feature-Driven Architecture (FDA)**.

All code belonging to a cohesive business feature is grouped together under `src/Features/`:

```
src/Features/
└── [FeatureName]/
    ├── Handlers/      <-- Input controllers for the feature
    └── Views/         <-- HTML layout templates specific to the feature
```

### Benefits:
* **High Cohesion:** Handlers and views that work together live together.
* **Dead Code elimination:** Deleting a feature is as simple as deleting its subdirectory.
* **Secured Routing:** Access control (admin vs public) is decoupled from the folder structure and is managed cleanly by route middlewares (`Auth`, `Acl`).

---

# 4. Security: Defensive Architecture and Pure Interfaces

Parina's security is organized in layers and executes mainly in the middleware pipeline, guaranteeing that malicious traffic never reaches the business controllers:

* **Stateless Authentication**:
  * **JWT**: The [JwtAuth](file:///Users/nelson/repos/parina-fw/src/Shared/Middlewares/JwtAuth.php) middleware extracts tokens using the `$request->bearerToken()` helper, validates them via `TokenServiceInterface`, and injects the identity into the local request attributes (`$request->setAttribute('user_id')`).
  * **Basic Auth**: The [BasicAuth](file:///Users/nelson/repos/parina-fw/src/Shared/Middlewares/BasicAuth.php) middleware validates credentials using `UserQueryRepositoryInterface` and native PHP `password_verify()` inside the middleware itself, strictly separating business authentication logic from database queries.
* **Cryptographic URL Signing**: The [ValidateHash](file:///Users/nelson/repos/parina-fw/src/Shared/Middlewares/ValidateHash.php) middleware parses temporary signatures (TTL) of sensitive links using an injected `CipherInterface` instance, validating the integrity of the link before routing the request.
* **Access Control (ACL)**: Based on `AclInterface`, it allows validating dynamic permissions. Dependencies like `Logger` are strictly injected in its constructor, with no static facade fallbacks.
* **XSS and CSRF Prevention**:
  * **CSRF**: A token injected in forms and validated in middlewares protects against request forgery.
  * **XSS**: The global helper `h($variable)` acts as a native escaping sanitizer in PHP views (`htmlspecialchars`).

---

# 5. Data Access: Pure Abstractions & CQS

Parina offers two database persistence strategies:

### A. Persistence by Repository (CQS - Command Query Segregation)
This is the framework's decoupled approach. Operations are separated into distinct query and command interfaces:
* **Reading (`UserQueryRepositoryInterface`)**: Pure query operations (`findById()`, `findByUsername()`, `all()`).
* **Writing (`UserCommandRepositoryInterface`)**: State mutation operations (`save()`, `delete()`).
* **Implementation (`DbUserQueryRepository` & `DbUserCommandRepository`)**: Decoupled classes that inject `SqlGeneratorInterface` for database interaction.
* **Credentials logic:** Removed from the repository layer. The repository only returns the raw user record, and the password hashing/verification is performed in the application layers (e.g. `LoginCheckHandler`) to preserve clean separation.

### B. Persistence by Active Record (`BaseModel`)
* Model classes inherit from `BaseModel` and map directly to database tables.
* Decoupled from SQL compilation logic by consuming the injected `SqlGeneratorInterface` resolved via the DI Container.

### C. Database Adapter Pattern (OCP)
* The final database engine (SQLite, MySQL, or PostgreSQL) is dynamically resolved through the `DatabaseAdapter` interface registered in the container.
* Complies with the **Open/Closed Principle (OCP)**: adding a new database engine only requires creating a class that implements `DatabaseAdapter` and registering it in `dependencies.php`.

---

### Final Architect's Diagnosis:
Parina Framework proves that extreme simplicity is not at odds with good design patterns. Its modern architecture in dependency decoupling (DIP), directory organization (FDA), and data interface segregation (CQS) make it an agile, secure, and extremely easy-to-test PHP application engine.
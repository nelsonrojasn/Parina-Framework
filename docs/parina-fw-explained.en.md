---

# 1. Ideology: Less is More (The Napkin Revolution)

Parina's ideology does not stem from limitation, but from **intentionality**. It is governed by three philosophical principles:

* **KISS (Keep It Simple, Stupid) & YAGNI (You Aren't Gonna Need It)**: Most complexity in modern frameworks is accidental. Parina asks: *what is the minimum structure required to build a secure, maintainable, and high-performance web application?* Its RAM weight (~0.05 MB) and execution time (~0.0007 seconds) are direct consequences of this philosophy.
* **Explicitness over "Magic" (No-Magic)**: Avoids hidden lifecycles, heavy ORMs, or huge configuration files. What is read in the code is exactly what is executed.
* **Pragmatic Decoupling (SOLID)**: Parina favors **Inversion of Control (IoC)** and **Interface Segregation**. Through its Reflection-based DI container and interface-oriented services, it allows changing concrete implementations (databases, signers, authenticators, SQL generators) without touching the framework core or controllers.

---

# 2. Execution Flow: The Request Lifecycle

Parina's flow is a **sequential and synchronous pipeline** that implements the **Front Controller** pattern:

```
HTTP Request
   │
   ▼
[public/index.php] (Front Controller)
   │
   ├──> Register performance metrics (PIN_START_TIME, PIN_START_MEM) and start PHP session
   ├──> Load native Autoloader (src/autoload.php) and global h() helper
   ├──> Instantiate [Container] and load config/dependencies.php (IoC)
   ├──> Register global shared services in [View::share()] (auth, cipher, config)
   └──> Initialize [Router] and register the route map from config/routes.php
   │
   ▼
[Kernel] (Dispatcher)
   │
   ├──> Capture the HTTP request via [Request::capture()]
   │
   ├──> [Middlewares Pipeline] (Intercepting filters)
   │       └──> If a middleware returns a [Response] (e.g. 401 error), the flow cuts short immediately.
   │
   ├──> [Container::get()] (Reflection-based DI Resolution)
   │       └──> Instantiate the Handler resolving its constructor dependencies recursively.
   │
   ├──> [Handler::handle(RequestInterface)] (Feature Controller)
   │       └──> Execute business logic and return an object implementing [Response] (HtmlResponse, JsonResponse, etc.)
   │
   ▼
[ResponseEmitter] (Emission)
   └──> Send HTTP headers, status code, and echo the content to the client.
```

---

# 3. Directory Layout: Feature-Driven Architecture (FDA)

Unlike traditional frameworks that segregate code by technical responsibility (e.g., all controllers in one folder, all views in another) or by access role (`Public`/`Admin`), Parina implements **Feature-Driven Architecture (FDA)**.

All code belonging to a cohesive business feature is modularly grouped under `src/Features/`:

```
src/Features/
└── [FeatureName]/
    ├── Handlers/      <-- Input HTTP controllers for the feature
    ├── Views/         <-- HTML layout templates specific to the feature
    ├── Commands/      <-- CQS repositories & interfaces for data mutation (Write)
    ├── Queries/       <-- CQS repositories & interfaces for data queries (Read)
    ├── Services/      <-- Domain services specific to the feature
    └── Interfaces/    <-- Feature local contracts and interfaces
```

Additionally, each feature maintains a symmetric integration test suite under `tests/Features/[FeatureName]/`.

### Benefits:
* **High Cohesion:** Handlers, repositories, services, and views that collaborate live in the same modular space.
* **Dead Code Elimination:** Deleting a feature is as simple as removing its subdirectory under `src/Features/`.
* **Secured Routing:** Access control (admin vs public) is decoupled from the directory structure and managed cleanly via route middlewares (`Auth`, `Acl`, `RateLimit`).

---

# 4. Security: Defensive Architecture and Pure Interfaces

Parina's security is organized in layers and executes mainly in the middleware pipeline, guaranteeing that malicious traffic never reaches business controllers:

* **Stateless Authentication**:
  * **JWT**: The [JwtAuth](../src/Shared/Middlewares/JwtAuth.php) middleware extracts tokens using `$request->bearerToken()`, validates them via `TokenServiceInterface`, and injects the identity into the local request context bag (`$request->setAttribute('user_id', ...)`).
  * **Basic Auth**: The [BasicAuth](../src/Shared/Middlewares/BasicAuth.php) middleware validates credentials via `UserQueryRepositoryInterface` and `password_verify()` directly inside the middleware, strictly separating authentication logic from database queries.
* **Cryptographic URL Signing**: The [ValidateHash](../src/Shared/Middlewares/ValidateHash.php) middleware parses temporary signatures (TTL) of sensitive links using an injected `CipherInterface` instance, validating the integrity of the link before routing the request.
* **Access Control (ACL)**: Based on `AclInterface`, it allows validating dynamic permissions. Dependencies like `Logger` are strictly injected into its constructor via DI, eliminating static facade fallbacks.
* **XSS and CSRF Prevention**:
  * **CSRF**: A token injected in forms and validated in intercepting middlewares protects against request forgery.
  * **XSS**: The global helper `h($variable)` acts as a native escaping sanitizer in PHP views (`htmlspecialchars`).

---

# 5. Data Access: Pure CQS & Database Adapters

Parina completely dispenses with ORMs or magic Active Record models. Persistence strictly follows the **CQS (Command Query Segregation)** pattern:

### A. Command and Query Segregation (CQS)
Database operations are explicitly divided into two types of repositories:
* **Queries / Reading (`*QueryRepositoryInterface`)**: Pure query operations that never mutate database state (e.g. `findById()`, `findByUsername()`, `all()`).
* **Commands / Writing (`*CommandRepositoryInterface`)**: State mutation operations executed under explicit database transactions (e.g. `save()`, `delete()`).
* **Credentials Logic:** Repositories return raw data only (decoupling). Cryptographic password comparison is performed in application/middleware layers to preserve clean separation.

### B. Multi-Dialect SQL Compiler (`SqlGeneratorInterface`)
* CQS repositories do not write vendor-locked SQL. They inject `SqlGeneratorInterface` to dynamically compile prepared statements for SQLite, MySQL, or PostgreSQL.

### C. Database Adapter Pattern (OCP)
* The final database engine is dynamically resolved through the `DatabaseAdapter` interface registered in the DI container ([config/dependencies.php](../config/dependencies.php)).
* Supports concrete adapters (`MySqlAdapter`, `PostgreSqlAdapter`, `SqliteAdapter`).
* Complies with the **Open/Closed Principle (OCP)**: adding a new database engine only requires implementing `DatabaseAdapter` and registering the factory in `dependencies.php`.

---

# 6. CLI Tools, Scaffolding, and Architecture Auditor (Guardian)

A fundamental pillar of Parina's architecture is its automation ecosystem in `bin/`, designed to maintain design integrity as the application scales:

* **Feature Scaffolding (`bin/scaffold.php`)**: Automatically generates FDA modular structure (folders, Handlers, Views, and unit test stubs) from CSV definitions (`routes.csv`).
* **CQS Generators (`bin/generate-command.php` & `bin/generate-query.php`)**: Create repository interfaces, concrete classes with transactions or prepared statements, in-memory SQLite integration tests, and auto-register bindings in `config/dependencies.php`.
* **Strategy-based Schema Generator (`bin/generate-schema.php`)**: Compiles multi-dialect DDL schemas (SQLite, MySQL, PostgreSQL) from CSV. Applies **Topological Sorting (DAG)** to resolve foreign key dependencies and generate cascading `DROP TABLE` statements.
* **Architecture Auditor & Linter (`bin/guardian.php` / `bin/linter.php`)**: Static auditor tool (*Parina Guardian*) ensuring system health through 5 verification phases:
  1. **PHP Syntax & Core Stability:** Syntactically checks all project PHP code.
  2. **DI Graph DAG & DIP Interface Purity:** Ensures no cycles or circular references exist in the container and validates interface abstractions.
  3. **CQS Isolation & Domain HTTP Agnosticism:** Guarantees query methods do not mutate state or return `void`, commands only return permitted types (`void`, `bool`, `int`), and domain repositories remain HTTP-agnostic.
  4. **Slim Handlers & View Layer Boundaries:** Prevents HTML markup inline inside Handlers and forbids direct `DatabaseAdapter` injection in HTTP controllers.
  5. **Feature Isolation & Monolith Metrics:** Evaluates feature coupling and code distribution balance to detect oversized modules.
* **Build Orchestrator (`bin/orchestrator.php`)**: Unifies full project compilation in 4 phases (Cleanup -> Scaffold -> DB Schema -> Batch CQS).
* **Route Inspection (`bin/routes-list.php`)**: Visualizes active route tables in the terminal, extracting middlewares and DocBlock `@Description` tags via Reflection.

---

### Final Architect's Diagnosis:
Parina Framework proves that extreme simplicity is not at odds with good design patterns. Its decoupled architecture via Dependency Injection (DIP), physical organization by features (FDA), strict data segregation (CQS), and continuous static validation (Guardian) make it an agile, secure, predictable, and easy-to-test PHP application engine.
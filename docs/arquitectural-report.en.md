---

# 1. Execution Flow: From Request to Response

Parina's flow is a **linear, synchronous, and highly predictable lifecycle**. It follows the **Front Controller** pattern in a sequential pipeline:

```
HTTP Request
   │
   ▼
[public/index.php] ──(1. Bootstrap)──> Load Autoload, Global Helpers (h())
   │
   ▼
[Container] ─────────(2. Configuration)──> Load config/dependencies.php (DI)
   │
   ▼
[Db::init()] ────────(3. Data Layer)──> Inject resolved DatabaseAdapter
   │
   ▼
[Router] ────────────(4. Routing)──> Match method and URI (regex params)
   │
   ▼
[Kernel] ────────────(5. Dispatch)──> Convert to Request (Value Object)
   │
   ├─> [Middlewares] ──(6. Filters Pipeline)──> (Short-circuits if returning Response)
   │
   ▼
[Container::get()] ──(7. DI Resolution)──> Instantiate Handler injecting dependencies
   │
   ▼
[Handler::handle()] ─(8. Controller Logic)──> Return Response object
   │
   ▼
[Kernel::send()] ────(9. Render & Emit)──> Send HTTP headers, status, and echo body
```

### Archaeological Finding in the Flow:
* In the initial stratum, the Kernel instantiated middlewares and handlers directly by calling `new $className()`.
* In the modern stratum, the Kernel delegates this to the `Container`. This allows any controller to declare what interfaces it needs in its constructor (dependency injection) and the framework resolves them via recursive reflection before running the request.

---

# 2. Security and Access: The System Walls

Parina's security has evolved from "coupling security" (where layers were mixed) to a defensive architecture based on **interfaces and segregation**:

### A. Authentication and Session Control
* **The ancient fossil**: The `User` database model manipulated the global session `$_SESSION['user_id'] = ...` directly. This violates clean architecture principles, as the database should not know about HTTP cookies or web sessions.
* **The modern structure**: We introduced `AuthInterface` and `SessionAuth`. Now, the login is an injectable service. The Auth middleware and `LoginCheckHandler` simply ask the service `isLoggedIn()` or call `login()`. In tests, we can mock that a user is authenticated without creating actual PHP sessions on disk.

### B. Access Control (ACL)
* **The ancient fossil**: The `Acl` class contained a `setMockHasPermissions` method to alter its state from unit tests. This is a test smell in production code.
* **The modern structure**: The `Acl` middleware receives an `AclInterface` via the constructor. All static logic and test shortcuts were removed from `Acl` production code. Tests use native PHPUnit mocks.

### C. Input and Output Defenses (CSRF and XSS)
* **CSRF (Cross-Site Request Forgery)**: Managed by the `Csrf::token()` token, injected in forms and validated by the CSRF middleware.
* **XSS (Cross-Site Scripting)**: The incorporation of the global `h()` helper in the autoloader allows PHP templates to escape dangerous HTML characters (`htmlspecialchars($value, ENT_QUOTES)`) easily, ensuring that visual output does not execute JavaScript injected by third parties.

---

# 3. Data Access and Modification: The Dual Stratum of Persistence

In the data layer is where the framework's archaeological transition is most evident:

```
                  ┌──────────────────────────────────────────┐
                  │                 CLIENT                   │
                  └────────────────────┬─────────────────────┘
                                       │
                    ┌──────────────────┴──────────────────┐
                    ▼                                     ▼
        [Active Record (Legacy)]                [CQS (Modern)]
        Uses static BaseModel and              Uses segregated interfaces
        direct instantiation.                  for Reading and Writing.
                    │                                     │
                    │                                     ▼
                    │                       [UserQueryRepositoryInterface]
                    │                       [UserCommandRepositoryInterface]
                    │                                     │
                    ▼                                     ▼
             [Db::query()]                       [DbUserRepository]
                    │                                     │
                    └──────────────────┬──────────────────┘
                                       ▼
                              [DatabaseAdapter] (Interface)
                                       │
                        ┌──────────────┼──────────────┐
                        ▼              ▼              ▼
                 [SqliteAdapter] [MySqlAdapter] [PostgreSqlAdapter]
```

### A. The Active Record Stratum (`BaseModel`)
* Models inherit from `BaseModel` and map 1-to-1 to SQLite/MySQL tables.
* It is an ideal approach for hyper-fast development (KISS), but mixes data representation with storage methods (violating SRP).

### B. The CQS Stratum (Command Query Segregation)
* To break Active Record coupling, we introduced the segregation of read and write interfaces:
  - `UserQueryRepositoryInterface`: Provides optimized methods to read data (e.g. `checkCredentials`, `findByUsername`).
  - `UserCommandRepositoryInterface`: Provides methods to write, update, or delete data.
* Both are implemented by `DbUserRepository`, which communicates with the database.
* This allows changing the storage engine of an entity completely (e.g. to MongoDB or an external API) by modifying only the repository, without altering entities or controller logic.

### C. The Adapter Pattern in the Connection
* The database is not hardcoded. The DI container resolves the `DatabaseAdapter` interface using a factory in `dependencies.php` that reads the active database configuration.
* Strictly complies with the **Open/Closed Principle (OCP)**: the framework is closed to internal modifications but open to developers adding new SQL adapters simply by registering them in the external configuration.

---

### Final Archeologist's Diagnosis:
Parina Framework is an excellent example of how a "pragmatic and static" framework can be refined into an "enterprise-grade" design (complete SOLID) without sacrificing execution speed and maintaining full backward compatibility with legacy code through dynamic facades (`__callStatic`).
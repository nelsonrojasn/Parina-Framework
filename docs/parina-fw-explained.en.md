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
   ├──> Capture superglobals in a [Request] object (Value Object)
   │
   ├──> [Middlewares Pipeline] (Intercepting filters)
   │       └──> If a middleware returns [Response] (e.g. 401 error), the flow cuts short.
   │
   ├──> [Container::get()] (Reflection-based DI Resolution)
   │       └──> Instantiate the Handler resolving its dependencies recursively.
   │
   ├──> [Handler::handle(Request)] (Controller)
   │       └──> Execute logic and return an object implementing [Response]
   │
   ▼
[Kernel::send()] (Emission)
   └──> Send HTTP headers, status code, and echo the content.
```

---

# 3. Security: Defensive Architecture and Pure Interfaces

Parina's security is organized in layers and executes mainly in the middleware pipeline, guaranteeing that malicious traffic never reaches the business controllers:

* **Stateless Authentication**:
  * **JWT**: The [JwtAuth](file:///home/nelson/repos/Parina-Framework/src/Shared/Middlewares/JwtAuth.php) middleware extracts tokens using the `$request->bearerToken()` helper, validates them via `TokenServiceInterface`, and injects the identity into the local request attributes (`$request->setAttribute('user_id')`).
  * **Basic Auth**: The [BasicAuth](file:///home/nelson/repos/Parina-Framework/src/Shared/Middlewares/BasicAuth.php) middleware validates credentials using `UserQueryRepositoryInterface::checkCredentials()`, which prevents the unnecessary creation of cookies and server sessions in REST APIs.
* **Cryptographic URL Signing**: The [ValidateHash](file:///home/nelson/repos/Parina-Framework/src/Shared/Middlewares/ValidateHash.php) middleware injects `CipherInterface` to parse temporary signatures (TTL) of sensitive links, validating the integrity of the link before routing the request.
* **Access Control (ACL)**: Based on the `AclInterface` interface, it allows validating dynamic permissions and easily injecting mock implementations in the testing environment.
* **XSS and CSRF Prevention**:
  * **CSRF**: A token injected in forms and validated in middlewares protects against request forgery.
  * **XSS**: The global helper `h($variable)` acts as a native escaping sanitizer in PHP views (`htmlspecialchars`).

---

# 4. Data Access and Modification: The Dual Stratum of Persistence

Parina offers flexibility to the developer by allowing two persistence approaches:

### A. Persistence by Repository (CQS - Command Query Segregation)
This is the framework's modern and clean approach. It divides operations into query and write interfaces:
* **Reading (`UserQueryRepositoryInterface`)**: Returns flat data or specific value objects. Optimized for complex queries and speed.
* **Writing (`UserCommandRepositoryInterface`)**: Persists and modifies the system state.
* **DbUserRepository**: Implementation that centralizes SQL access.
* *Benefit*: Decoupling of the database from the HTTP session (SRP) and 100% in-memory unit tests using mocks.

### B. Persistence by Active Record (`BaseModel`)
* Classes like `User` inherit directly from `BaseModel`. They map class properties to table columns and provide direct CRUD methods (`all()`, `find()`, `create()`).
* It is an ideal option for rapid prototyping and very simple CRUD operations.

### C. Driver Abstraction (Adapter Pattern)
* The final database engine (SQLite, MySQL, or PostgreSQL) is dynamically injected through the `DatabaseAdapter` interface registered in the container.
* Complies with the **Open/Closed Principle (OCP)**: if you need to migrate databases or add an unsupported database engine (like SQL Server), you only need to create a class that implements `DatabaseAdapter` and register it in `dependencies.php`, without changing a single line of framework internal code.

---

### Final Architect's Diagnosis:
Parina Framework proves that extreme simplicity is not at odds with good design patterns. Its modern architecture in dependency decoupling (DIP) and data interface segregation (CQS) make it an agile, secure, and extremely easy-to-test PHP application engine.
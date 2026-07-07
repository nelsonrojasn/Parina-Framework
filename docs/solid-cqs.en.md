---

# 1. Implementing SOLID Principles in Parina

SOLID is the pillar that transformed Parina from a coupled monolithic script into a flexible and modular framework:

### **S – Single Responsibility Principle (SRP)**
Every class in Parina has **exactly one reason to change**.
* **Before**: The `User` model mapped the database and controlled HTTP session state (`$_SESSION`).
* **Now**: We separated persistence into the repository (`DbUserRepository`) and session management into the `SessionAuth` service.
* **Middlewares**: Each middleware (`RateLimit`, `RequestSize`, `Csrf`) encapsulates a specific security rule, keeping the `Kernel` class focused solely on dispatching the HTTP request.

### **O – Open/Closed Principle (OCP)**
Code is **open to extension, but closed to modification**.
* **Example (Database)**: The `DatabaseAdapter` interface abstracts the different SQL drivers. If a developer wants to support Oracle or SQL Server, they do not need to modify the Parina core. They simply create a class implementing `DatabaseAdapter` and bind it dynamically in the external `config/dependencies.php` file.

### **L – Liskov Substitution Principle (LSP)**
Any subclass must be able to replace its base class without altering the correct behavior of the program.
* **Refactoring of `Response`**: The original `Response.php` interface contained a fixed constructor signature. This forced classes like `RedirectResponse` or `JsonResponse` to receive parameters they did not need, violating LSP. We removed the constructor from the interface, allowing the Kernel to handle any response (Html, Json, Redirect) uniformly.

### **I – Interface Segregation Principle (ISP)**
Clients must not be forced to depend on interfaces they do not use.
* **Repository Segregation**: We split user data access into two interfaces: `UserQueryRepositoryInterface` and `UserCommandRepositoryInterface`.
* **Usage**: The `BasicAuth` middleware only needs to verify credentials (Read). Instead of receiving a repository with methods like `save()` or `delete()`, it only injects `UserQueryRepositoryInterface`, limiting its actions to the bare minimum.

### **D – Dependency Inversion Principle (DIP)**
High-level modules must not depend on low-level modules; both must depend on abstractions.
* **Reflection DI Container**: Parina controllers and middlewares no longer instantiate their dependencies using the `new` keyword. Instead, they declare interfaces in their constructors (e.g. `ConfigInterface`, `Logger`, `TokenServiceInterface`, `CipherInterface`). The `Container` component analyzes these signatures via reflection at runtime and injects the resolved dependencies.

---

# 2. Implementing the CQS (Command Query Segregation) Pattern

The CQS pattern states that **a method must either be a command** (performing an action that mutates the state of the system) **or a query** (returning data to the client with no side effects), but never both.

In Parina Framework, CQS is implemented at the data and service layer:

```
                            [Controller / Handler]
                           /                       \
        Injects Query     /                         \    Injects Command
                         ▼                           ▼
      [UserQueryRepositoryInterface]      [UserCommandRepositoryInterface]
      * findById()                        * save()
      * findByUsername()                  * delete()
      * checkCredentials()
                         \                           /
                          ▼                         ▼
                      ┌─────────────────────────────────┐
                      │        DbUserRepository         │
                      │ (Implements both interfaces)    │
                      └─────────────────────────────────┘
```

### A. Queries Layer
Represented by the `UserQueryRepositoryInterface` interface.
* **Methods**: `findById()`, `findByUsername()`, `checkCredentials()`.
* **Behavior**: Pure read-only methods. They query the SQL database and return raw associative arrays or null. **They are strictly forbidden from altering the system state** (they do not write to tables or inject data into the global `$_SESSION`).

### B. Commands Layer
Represented by the `UserCommandRepositoryInterface` interface.
* **Methods**: `save()`, `delete()`.
* **Behavior**: Write/mutation operations. They modify physical records in the database engine and report success or failure (`bool`).

### C. Consequence in Testing Design
Thanks to CQS, in `LoginCheckHandlerTest.php`, the test only simulates the query (`checkCredentials()`) by injecting a lightweight mock of the Query interface. This allows unit tests to run instantly in memory, completely isolated from the physical SQLite database on disk.
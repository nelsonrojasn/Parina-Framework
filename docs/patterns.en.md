---

# 1. Implementing SOLID Principles in Parina

SOLID is the pillar that transformed Parina from a coupled monolithic script into a flexible, maintainable, and modular framework:

### **S – Single Responsibility Principle (SRP)**
Every class in Parina has **exactly one reason to change**.
* **Database Init Separation**: We removed `SetupHandler` completely, shifting database schema instantiation and seeding to the user/consumer of the framework.
* **Security & Roles**: Access control and user permissions are managed cleanly in route configuration and middleware pipelines rather than being coupled to physical directory layouts.
* **Separation of Concerns**: We separated persistence into Query/Command repositories and session state management into `SessionAuth`.

### **O – Open/Closed Principle (OCP)**
Code is **open to extension, but closed to modification**.
* **Database Driver Adapters**: The `DatabaseAdapter` interface abstracts the different SQL drivers. Developers can add new engines by creating a class implementing it and registering it in `config/dependencies.php` without altering core code.
* **SQL Query Generation**: The `SqlGeneratorInterface` allows developers to swap the query generator strategy or customize SQL grammar compilation dynamically.

### **L – Liskov Substitution Principle (LSP)**
Any subclass must be able to replace its base class without altering the correct behavior of the program.
* **Refactoring of `Response`**: The original `Response` interface contained a rigid constructor. We removed the constructor signature from the interface, allowing subclasses like `RedirectResponse` or `JsonResponse` to define their parameters freely, enabling uniform handling in the Kernel.

### **I – Interface Segregation Principle (ISP)**
Clients must not be forced to depend on interfaces they do not use.
* **Repository Segregation**: We split data access into two interfaces: `UserQueryRepositoryInterface` and `UserCommandRepositoryInterface`.
* **Middlewares and Services**: Authentication middleware only queries data (Read) and thus only depends on `UserQueryRepositoryInterface`, preventing unauthorized access to command actions.

### **D – Dependency Inversion Principle (DIP)**
High-level modules must not depend on low-level modules; both must depend on abstractions.
* **Reflection DI Container**: Handlers and middlewares do not instantiate their dependencies using `new`. Instead, they declare constructor parameters of interface types (e.g. `ConfigInterface`, `Logger`, `TokenServiceInterface`, `CipherInterface`, `SqlGeneratorInterface`).
* **Required Constructors**: Middlewares and services declare required, non-optional constructor parameters, ensuring the container resolves their dependencies explicitly instead of relying on local fallback instances.

---

# 2. Implementing the CQS (Command Query Segregation) Pattern

The CQS pattern states that **a method must either be a command** (performing an action that mutates state) **or a query** (returning data with no side effects), but never both.

In Parina Framework, CQS is implemented at the data and service layer:

```
                            [Controller / Handler]
                           /                       \
        Injects Query     /                         \    Injects Command
                         ▼                           ▼
      [UserQueryRepositoryInterface]      [UserCommandRepositoryInterface]
      * findById()                        * save()
      * findByUsername()                  * delete()
      * all()
                         \                           /
                          ▼                         ▼
              ┌────────────────────────┐      ┌────────────────────────┐
              │ DbUserQueryRepository  │      │DbUserCommandRepository │
              └────────────────────────┘      └────────────────────────┘
```

### A. Queries Layer
Represented by the `UserQueryRepositoryInterface` interface.
* **Methods**: `findById()`, `findByUsername()`, `all()`.
* **Behavior**: Pure read-only queries returning arrays or null. They are strictly forbidden from writing to tables or injecting data into sessions.
* **Credential Verification**: Excluded from repository queries. Cryptographic verification is done locally in handlers using `password_verify` on queried entity data to keep DB queries pure.

### B. Commands Layer
Represented by the `UserCommandRepositoryInterface` interface.
* **Methods**: `save()`, `delete()`.
* **Behavior**: State mutations on database records, reporting success/failure (`bool`).

---

# 3. Implementing Feature-Driven Architecture (FDA)

Feature-Driven Architecture is a design pattern that groups files by cohesive business domains (vertical slices) instead of horizontal technical layers (all views in one folder, all handlers in another) or role-based directories.

### FDA Organization:
* Each business slice (e.g. `Authentication`, `UserManagement`, `Marketing`) is self-contained.
* It includes its own Handlers and Views under a single directory in `src/Features/`.
* Test files are mapped symmetrically under `tests/Features/`.
* CLI scaffolding tools (`scaffold.php` and `cleanup.php`) automatically honor and support this modular feature layout.
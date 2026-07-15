# Console Tools (CLI Tools)

Parina Framework includes a set of command-line tools in the `bin/` directory to automate common tasks such as code generation (scaffolding), route listing, architectural linting, and project resetting.

This document explains how each of these tools works and provides practical usage examples.

---

## 1. Route and Feature Scaffolding: `bin/scaffold.php`

The **[scaffold.php](../bin/scaffold.php)** tool allows you to automatically generate Handlers (controllers), their corresponding views, and your feature directory structure based on a prior definition in a CSV file.

### How does it work?
1. It reads the CSV file (e.g., `routes.csv`) that defines the HTTP verbs, paths, features, middlewares, and descriptions.
2. For each defined feature, it **automatically creates the modular folder structure (FDA)**:
    * `src/Features/{FeatureName}/Handlers/`
    * `src/Features/{FeatureName}/Views/`
    * `src/Features/{FeatureName}/Commands/`
    * `src/Features/{FeatureName}/Queries/`
    * `src/Features/{FeatureName}/Services/`
    * And their respective folders in the test suite under `tests/Features/{FeatureName}/`.
3. It creates initial stubs for each Handler and its respective integration unit test.
4. It dynamically generates the active route file `config/routes.php`.

### Usage
```bash
php bin/scaffold.php routes.csv
```

---

## 2. Dynamic Route Listing: `bin/routes-list.php`

The **[routes-list.php](../bin/routes-list.php)** tool allows you to preview a colorful, ordered table in your terminal showing all active routes in the application.

### How does it work?
* It dynamically reads the active configuration file `config/routes.php`.
* It extracts the Feature name by analyzing the namespace path of the Handler.
* It displays the short names of the middlewares assigned to each route.
* **It uses PHP Reflection:** It dynamically loads the Handler class and examines its DocBlock for the `Description:` tag to show what the endpoint does directly in the table.

### Usage
```bash
php bin/routes-list.php
```

### Example console output:
```text
+--------+----------------------------+----------------+---------------------+-----------------------+--------------------------------+
| Method | Path                       | Feature        | HandlerName         | Middlewares           | Description                    |
+--------+----------------------------+----------------+---------------------+-----------------------+--------------------------------+
| GET    | /                          | Marketing      | HomeHandler         |                       |                                |
| GET    | /buy/credit/auto/{id}      | AutoPurchase   | AutoPurchaseHandler | Auth                  | Buy a car                      |
| GET    | /admin/users/{hash}        | UserManagement | UsersListHandler    | RateLimit, Csrf, Auth |                                |
| GET    | /setup                     | Database       | SetupHandler        |                       | Initialize the database        |
+--------+----------------------------+----------------+---------------------+-----------------------+--------------------------------+
```

---

## 3. CQS Command Generation: `bin/generate-command.php`

Following the principle of **Command Query Segregation (CQS)**, operations that modify the database (writes, updates, and deletes) are managed using command repositories.

The **[generate-command.php](../bin/generate-command.php)** tool generates these classes inside the Feature.

### How does it work?
1. It creates the repository interface in `src/Features/{Feature}/Commands/{RepositoryName}CommandRepositoryInterface.php`.
2. It creates the concrete implementation with transaction support in `src/Features/{Feature}/Commands/Db{RepositoryName}CommandRepository.php`.
3. It creates an integration test with an in-memory SQLite database in `tests/Features/{Feature}/Commands/Db{RepositoryName}CommandRepositoryTest.php`.
4. **Auto-registration:** It automatically adds the interface-concrete relation inside the CQS block of [config/dependencies.php](../config/dependencies.php).

### Usage
```bash
php bin/generate-command.php <feature> <name> [table_name]
```

* **Example:**
    ```bash
    php bin/generate-command.php UserManagement Profile user_profiles
    ```
    *This will generate the interface `ProfileCommandRepositoryInterface`, the repository `DbProfileCommandRepository` mapped to the `user_profiles` table, and their tests.*

---

## 4. CQS Query Generation: `bin/generate-query.php`

Operations that read data from the database without altering its state are managed using query repositories (Queries).

The **[generate-query.php](../bin/generate-query.php)** tool automates their creation within the Feature.

### How does it work?
1. It creates the repository interface in `src/Features/{Feature}/Queries/{RepositoryName}QueryRepositoryInterface.php`.
2. It creates the concrete implementation with prepared `SELECT` queries in `src/Features/{Feature}/Queries/Db{RepositoryName}QueryRepository.php`.
3. It creates an integration test with an in-memory SQLite database in `tests/Features/{Feature}/Queries/Db{RepositoryName}QueryRepositoryTest.php`.
4. **Auto-registration:** It automatically registers the dependency in [config/dependencies.php](../config/dependencies.php).

### Usage
```bash
php bin/generate-query.php <feature> <name> [table_name]
```

* **Example:**
    ```bash
    php bin/generate-query.php UserManagement Profile user_profiles
    ```

---

## 5. Architectural Linter: `bin/linter.php`

The **[linter.php](../bin/linter.php)** linter guarantees that Parina Framework's modular architecture and design principles do not become corrupted as the project grows.

### Rules it validates:
1. **PHP Syntax:** It syntactically analyzes all `.php` files in the project to prevent syntax or compilation errors.
2. **DI Graph Stability (DAG):** It checks that the dependency injection configured in `config/dependencies.php` has no loops or circular references (e.g., Class A injecting Class B, and Class B injecting Class A).
3. **CQS Isolation:** 
    * It verifies that query classes (Queries) do not call mutator methods like `insert`, `update`, or `delete`.
    * It verifies that query methods do not return `void`.
    * It verifies that command methods only return allowed types (`void`, `bool`, `int`).
    * It verifies that controllers (Handlers) do not directly inject the `DatabaseAdapter` (forcing them to consume the database via CQS repositories). *CQS repositories within the Feature are legitimately exempt from this rule.*

### Usage
```bash
php bin/linter.php
```

---

## 6. Resetting the Canvas: `bin/cleanup.php`

The **[cleanup.php](../bin/cleanup.php)** tool is designed to reset the framework to a completely clean initial state ("blank canvas").

### What does it do?
* Recursively removes all demo Feature directories (`Dashboard`, `UserManagement`, `Authentication`, `AutoPurchase`, `Database`) from both `src/Features/` and `tests/Features/` (including their commands, queries, and views).
* Removes isolated demo files and their tests in the `Marketing` feature.
* Deletes the local SQLite database if it exists.
* Resets `config/routes.php` and `routes.csv` to their original state (only containing the root route `/`).
* Resets `config/dependencies.php` to its original dependency configuration.

### Usage
```bash
php bin/cleanup.php
```
*(You can append the `--force` parameter to skip interactive terminal confirmation).*

---

## 7. Full Initialization Orchestrator: `bin/orchestrator.php`

The **[orchestrator.php](../bin/orchestrator.php)** tool unifies and coordinates the entire build and deployment process of the project in a single console command. It is the ultimate tool to stand up the project's architecture in a single step.

### What does it do? (The 4 Build Phases)
1. **Phase 1 (Cleanup):** Invokes `bin/cleanup.php --force` to reset the environment to a blank canvas (with a preventive backup of your route definition in memory).
2. **Phase 2 (Scaffolding):** Runs `bin/scaffold.php` using the route file (CSV) to rebuild all Handlers, views, route files, and base directories of the Features.
3. **Phase 3 (Database):** Spins up the framework's dependency container and runs the SQL database schema (`database/schema.[driver].sql`) corresponding to the configured driver (`sqlite`, `mysql`, or `pgsql`).
4. **Phase 4 (Batch CQS Generation):** Reads a CQS configuration CSV file (by default `cqs.csv`) and batch-generates all interfaces, Command/Query repositories, and integration tests directly in the corresponding Feature folders.

### `cqs.csv` Structure
The file must have the columns `Feature,Name,Table,Type` to tell the orchestrator which data repositories to build:
```csv
Feature,Name,Table,Type
UserManagement,User,usuario,both
AutoPurchase,Auto,auto,both
```
* `Type` can be `command`, `query`, or `both` (to generate both write and read repositories).

### Usage
```bash
php bin/orchestrator.php [routes_csv] [cqs_csv]
```
*(If no arguments are specified, it will look for the `routes.csv` and `cqs.csv` files in the project root by default).*

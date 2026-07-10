# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

🇺🇸 **English** | 🇪🇸 [Español](docs/README.es.md)

### *Altiplano Edition: Less is more. The web framework for clear thinking.*

---

## 💡 What is Parina?

Parina is a minimal micro-framework for modern PHP applications. It provides just enough structure to build applications with clarity, control, and peak performance, adhering to Feature-Driven Architecture and clean design patterns.

---

## 🛠️ Key Features

* **DI Container with Reflection**: Automatic resolution and constructor injection of dependencies for Handlers and Middlewares.
* **Feature-Driven Architecture**: Handlers, views, and tests organized by cohesive business features (e.g. `Authentication`, `UserManagement`, `Marketing`) instead of role-based folders or separate technical layers.
* **Stateless HTTP Request (`Request`)**: Unified payload input (`input()`), simple HTTP header fetching (`header()`), and local request context attributes (`setAttribute()`) for clean middleware-to-handler data sharing.
* **CQS & Adapter Patterns**: Separation of read queries and write commands inside Repositories (validated automatically by the system [Linter](file:///home/nelson/repos/Parina-Framework/bin/linter.php)), coupled with dynamic database driver adapters (SQLite, MySQL, PostgreSQL) adhering to the Open/Closed Principle. See [CQS Recommendations](file:///home/nelson/repos/Parina-Framework/docs/parina-recomendations.md#data-abstraction-without-magical-orm-cqs) for detailed rules.
* **XSS Protection**: Secure variable escaping inside templates using the global helper function `h()`.

---

## 🌄 Philosophy

**Clarity over abstraction. Control over convenience.**

Parina focuses on:
* **Explicit design:** No magic, no hidden lifecycles.
* **Minimal overhead:** Every byte and millisecond counts.
* **Predictable flow:** What you see is exactly what executes.

---

## 🧱 Architecture in 10 Lines

1. A request enters through a front controller.
2. It goes through the middleware pipeline.
3. Middleware can block or pass.
4. It reaches the registered handler.
5. Handler executes core logic.
6. Returns a standard response.
7. No heavy magic.
8. No hidden framework lifecycles.
9. No unnecessary abstractions.
10. Just clear, linear execution.

---

## 🔄 Request Lifecycle

```
[ Request ] ───> [ Middleware Pipeline ] ───> [ Handler ]
                          │                       │
                          │ (Returns Response)    │ (Returns Response)
                          ▼                       ▼
                    [ Response ] <────────────────┘
```

### Middleware Model
Each middleware layer follows a simple binary rule:
* **Returns `Response`** → Stop execution and emit response.
* **Returns `null`** → Continue to the next layer.

#### Middleware Example
```php
namespace Parina\Shared\Middlewares;

use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\ErrorResponse;

class SimpleAuth implements Middleware
{
    public function handle(RequestInterface $request): ?Response
    {
        if (!isset($_SESSION['user'])) {
            return new ErrorResponse("Unauthorized", 401);
        }
        return null; // Move to the next layer
    }
}
```

---

## 🔒 Security

Security is first-class and lives exactly where it belongs: in the middleware pipeline.

* Rate limiting
* Request size validation
* CSRF protection
* Same-origin policy (CORS)
* Authentication (Basic / JWT)
* Authorization (ACL)

---

## ⚡ Performance

Designed for minimal overhead and microsecond-accuracy:

* **~0.0007 seconds** per request execution.
* **~0.05 MB** RAM footprint.
* Fully Opcache friendly.

---

## 🚀 Example (Bootstrapping)

```php
// public/index.php
use Parina\Core\Router;
use Parina\Core\Kernel;
use Parina\Core\Container;
use Parina\Core\Config;
use Parina\Shared\Infrastructure\Db;

require_once __DIR__ . '/../src/autoload.php';

// Instantiate DI container & load dynamic dependencies
$container = new Container();
if (file_exists(__DIR__ . '/../config/dependencies.php')) {
    $container->load(require __DIR__ . '/../config/dependencies.php');
}

// Initialize database with dynamically resolved adapter (OCP)
Db::setConfig(Config::getDbConfig());
Db::init($container->get(\Parina\Shared\Infrastructure\DatabaseAdapter::class));

$router = new Router();
$routes = require '../config/routes.php';
foreach ($routes as $route) {
    $router->add($route['method'], $route['path'], $route['handler'], $route['middleware'] ?? []);
}

$request = \Parina\Core\Request::capture();
$kernel = new Kernel($router, $container);
$response = $kernel->handle($request);

$emitter = new \Parina\Core\ResponseEmitter();
$emitter->emit($response);
```

## 🏠 Minimal Handler Example
```php
namespace Parina\Features\UserManagement\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\View;
use Parina\Shared\Services\UserQueryRepositoryInterface;

class UsersListHandler implements Handler
{
    // Resolved and injected automatically by the DI Container via Reflection
    public function __construct(private UserQueryRepositoryInterface $userRepo) {}

    public function handle(RequestInterface $request): Response
    {
        $users = $this->userRepo->all();
        // Secure HTML output using the global h() helper to prevent XSS
        $content = View::renderWithLayout("UserManagement/Views/list", "default", ['users' => $users]);
        return new HtmlResponse($content, 200);
    }
}
```

## 🖼 Minimal View Example
```php
<!-- Features/UserManagement/Views/list.php -->
<h1>Users List</h1>
<ul>
  <?php foreach ($users as $user): ?>
    <li><?= h($user['username']) ?></li>
  <?php endforeach; ?>
</ul>
```

---

## 🛠️ CLI Scaffolding

Parina includes a CLI tool to generate routing configurations, handler classes, and unit tests directly from a CSV file.

1. Define your routes in a CSV file (e.g., `routes.csv`):
   ```csv
   Method,Path,HandlerClass,Middlewares,Description
   GET,/,Parina\Features\Marketing\Handlers\HomeHandler,,Home page
   GET,/about,Parina\Features\Marketing\Handlers\AboutHandler,,About us
   ```

2. Run the scaffolding tool:
   ```bash
   php bin/scaffold.php routes.csv
   ```

This will automatically generate:
* Route configurations in `config/routes.php`.
* Missing Handler classes in `src/Features/`.
* Basic unit tests in `tests/Features/` to verify your handlers.

---

## 🧪 Included Tests

Parina is developed with PHPUnit, focusing on complete coverage.

```
tests/
 ├── KernelTest.php
 ├── RouterTest.php
 ├── ContainerTest.php
 └── Features/
```

---

## 🧘 Why Parina Exists

Most complexity in software is accidental. Parina asks:

What is the smallest structure that still works correctly, securely, and fast?

Parina is not minimal by limitation. It is minimal by intention. It removes everything you do not actually need.

For a detailed explanation of the core philosophy and how the entire framework fits in a paper napkin diagram, see [THE-NAPKIN-REVOLUTION.md](THE-NAPKIN-REVOLUTION.md).

---

## 📦 Deployment & Installation

### Production Deployment
For directory layout, permissions, and production tips, see [DEPLOY.md](DEPLOY.md).

### Cleanup & Reset
To remove all demo files and reset the framework to a fresh state, see [CLEANUP.md](CLEANUP.md).

### Quick Start / Local Installation

To run the framework locally using PHP's built-in development server:

```bash
git clone https://github.com/nelsonrojasn/Parina-Framework.git
cd Parina-Framework
php -S localhost:8000 -t public
```

---

## 🪶 License

MIT License.
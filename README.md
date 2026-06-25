# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

🇺🇸 **English** | 🇪🇸 [Español](README.es.md) | 🇫🇷 [Français](README.fr.md) | 🇵🇹 [Português](README.pt.md)

### *Altiplano Edition: Less is more. The web framework for clear thinking.*

---

## 💡 What is Parina?

Parina is a minimal micro-framework for modern PHP applications. It provides just enough structure to build applications with clarity, control, and peak performance.

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

use Parina\Core\Request;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\ErrorResponse;

class SimpleAuth implements Middleware
{
    public function handle(Request $request): ?Response
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
use Parina\Modules\Public\HomeHandler;

require_once '../vendor/autoload.php';

$router = new Router();
$router->add('GET', '/', HomeHandler::class);

$kernel = new Kernel($router);
$kernel->run();
```

## 🏠 Minimal Handler Example
```php
namespace Parina\Modules\Public;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Request;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\View;

class HomeHandler implements Handler
{
    public function handle(Request $request): Response
    {
        $content = View::renderWithLayout("Public/Views/home", "default", ['title' => 'Parina']);
        return new HtmlResponse($content, 200);
    }
}
```

## 🖼 Minimal View Example
```php
<!-- Modules/Public/Views/home.php -->
<h1><?= $title ?></h1>
<p>Welcome to Parina Framework.</p>
```

---

## 🧪 Included Tests

Parina is developed with PHPUnit, focusing on complete coverage.

```
tests/
 ├── KernelTest.php
 ├── RouterTest.php
 ├── HandlerTest.php
 └── Handlers/FakeHandler.php
```

---

## 🧘 Why Parina Exists

Most complexity in software is accidental. Parina asks:

What is the smallest structure that still works correctly, securely, and fast?

Parina is not minimal by limitation. It is minimal by intention. It removes everything you do not actually need.

---

## 📦 Deployment & Installation

### Production Deployment
For directory layout, permissions, and production tips, see [DEPLOY.md](DEPLOY.md).

### Quick Start / Local Installation

To run the framework locally using PHP's built-in development server:

```bash
git clone https://github.com/nelsonrojasn/Parina-Framework.git
cd Parina-Framework
composer install
php -S localhost:8000 -t public
```

### Dependency Manager
Packagist Soon.

---

## 🪶 License

MIT License.
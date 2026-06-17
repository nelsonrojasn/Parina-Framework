# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)
### *Altiplano Edition: Less is more. The web framework for clear thinking.*

Parina is a micro-framework for web applications written in modern PHP.
It doesn't try to do everything. It brings no magic. It hides no decisions.

Its goal is simple:

> **Provide only the essential pieces so every developer can build the rest with clarity.**

## 🌄 Philosophy

Parina proposes the opposite of excess:

- **Single Entry Point (Front Controller)**
- **Explicit Router**
- **Simple Dispatching Kernel**
- **Handlers as minimum units of action**
- **Lightweight Views**
- **Clear Responses**
- **Optional Infrastructure**

When there is less, **it looks better**.

## 🧱 Architecture

Parina is composed of decoupled elements:

```
Handler           → unique contract
Router            → mapping URL → handler
Kernel            → dispatcher
Response          → output representation
View              → simple PHP templates
Session           → session control
```

Additional infrastructure (like database access) lives outside the core.

## 🚀 Minimum Example

```php
// public/index.php
use Parina\Core\Router;
use Parina\Core\Kernel;

require 'vendor/autoload.php';

$router = new Router();

// Public routes
$router->add('GET', '/', Parina\Modules\Public\HomeHandler::class);

$kernel = new Kernel($router);
$kernel->run();
```

## 🏠 Handler

```php
namespace Parina\Modules\Public;

use Parina\Core\View;
use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\HtmlResponse;

class HomeHandler implements Handler
{
    public function handle(Request $request): Response
    {
        $content = View::renderWithLayout("Public/Views/home", "default", ['title' => 'Parina']);
        return (new HtmlResponse($content, 200));
    }
}
```

## 🖼 Views

```php
<!-- Modules/Public/views/home.php -->
<h1><?= $title ?></h1>
<p>Welcome to Parina Framework.</p>
```

## 🧪 Tests included

Parina framework has been tested using PHPUnit.

```
tests/
 ├── KernelTest.php
 ├── RouterTest.php
 ├── HandlerTest.php
 └── Handlers/FakeHandler.php
```

## 🔒 Security

Including optional security measures:

- CSRF tokens
- Same-Origin defense
- Middlewares applicables on the kernel

## 🗄 Infraestructure

You can check on `Shared/Infrastructure/Db.php` and you will get a simple layer to:

- connect to database
- prepare queries
- execute commands

## 🧘 Why Parina Framework?

Because programming is not just putting libraries together.  
Programming is **clear thinking**.

Parina Framework exists to remind us that:

> **A web application doesn't need to be complicated to be powerful.**

## 🏃 Run the server

Parina Framework works with the PHP built-in server.  
To run it, simply execute:

```bash
php -S localhost:8000 -t public
```

## 📦 Installation

Packagist Soon.

## 🪶 Licencia

MIT.

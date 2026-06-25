# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

🇺🇸 [English](../README.md) | 🇪🇸 [Español](README.es.md) | 🇫🇷 [Français](README.fr.md) | 🇵🇹 [Português](README.pt.md) | 🇮🇹 [Italiano](README.it.md) | 🇩🇪 [Deutsch](README.de.md) | 🇦ym **Aymara** | 🦙 [Quechua](README.qu.md) | 🇨🇳 [简体中文](README.zh.md) | 🇯🇵 [日本語](README.ja.md)

### *Altiplano Edition: Kawkhantix juk'akiw juk'ampïki. Llikacha p'iqimpi amuyt'añataki.*

---

## 💡 ¿Kuns Parinax?

Parinaqa mä juk'a llikachawa (micro-framework) modern php lurañataki. Wakiskiri churayi phiskhu lurawimpi, p'iqiñchawimpi ukat jach'a performance churawimpi.

---

## 🌄 Filosofía

**Qhananchawi aswan uñch'ukita. P'iqiñchawi aswan k'achachata.**

Parina amtaxa:
* **Qhananchat lurawi:** Janiw layqa utjkiti, janiw imantat lifecycles utjkiti.
* **Juk'a overhead:** Sapa byte ukat millisecondux wakisiwa.
* **Amuyat thakhi:** Kawkharutix uñjta ukapachaw lurayi.

---

## 🧱 Uñch'ukiwi 10 Qillqata taypina

1. Mayiqa nayriri p'iqiñchiriru (front controller) manti.
2. Ukat middleware thakhinak taypi sarayi.
3. Middleware-ax lanti jan ukax sarawayayi.
4. Ukat uñt'at luririru (handler) puri.
5. Luririx pachpa lurawip lurayi.
6. Ukat mä standard response kutt'ayi.
7. Janiw ch'ama layqawinakäkiti.
8. Janiw imantat lifecycles llikachankkiti.
9. Janiw ina uñch'ukiwinakäkiti.
10. Sapa qhananchat thakhi lurawipacha.

---

## 🔄 Mayit lurawi (Request Lifecycle)

```
[ Request ] ───> [ Middleware Pipeline ] ───> [ Handler ]
                          │                       │
                          │ (Response kutt'ayi)   │ (Response kutt'ayi)
                          ▼                       ▼
                    [ Response ] <────────────────┘
```

### Middleware Uñacht'awi
Sapa middleware layer mä juk'a amta lurayi:
* **`Response` kutt'ayi** → Lurawi sayt'ayi ukat response kutt'ayi.
* **`null` kutt'ayi** → Yaqha layer thakhiru mantayi.

#### Middleware uñacht'ayawi
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
            return new ErrorResponse("Janiw khusäkiti", 401);
        }
        return null; // Yaqha thakhuru mantayi
    }
}
```

---

## 🔒 Jach'anchawi (Security)

Jach'anchawix wakiskiripuniwa ukat middleware layer taypinkiwa:

* Rate limiting
* Request size validation
* CSRF protection
* Same-origin policy (CORS)
* Authentication (Basic / JWT)
* Authorization (ACL)

---

## ⚡ Performance

Juk'a overhead ukat microsecond amtampi lurt'ata:

* **~0.0007 saniñanaka** sapa mayit lurawipacha.
* **~0.05 MB** RAM footprint.
* Opcache khusa lurayiri.

---

## 🚀 Uñacht'ayawi (Bootstrapping)

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

## 🏠 Handler uñacht'ayawi
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

## 🖼 View uñacht'ayawi
```php
<!-- Modules/Public/Views/home.php -->
<h1><?= $title ?></h1>
<p>Parina Llikacharu katuqt'añani.</p>
```

---

## 🛠️ CLI Scaffolding (Código lurayiri)

Parinaqa mä CLI thakhi churayi routes, handlers ukat tests qillqanaka CSV taypit lurañataki.

1. Routes qillqata CSV lurañataki (e.g. `routes.csv`):
   ```csv
   Method,Path,HandlerClass,Middlewares,Description
   GET,/,Parina\Modules\Public\HomeHandler,,Home page
   GET,/about,Parina\Modules\Public\AboutHandler,,About us
   ```

2. Scaffolding tool lurayiri:
   ```bash
   php bin/scaffold.php routes.csv
   ```

Ukhamatwa lurani:
* Route configurations config/routes.php.
* Handlers qillqanaka src/ ukan.
* Unit tests tests/Handlers/ ukan.

---

## 🧪 Tests

Parinaqa PHPUnit thakhi taypin uñch'ukita.

```
tests/
 ├── KernelTest.php
 ├── RouterTest.php
 ├── HandlerTest.php
 └── Handlers/FakeHandler.php
```

---

## 🧘 Kutix utjayi Parina

Taqi ch'axwawinakax ina ch'amawa. Parina jiskt'i:

¿Kawkiri juk'a uñch'ukiwipachas khusa, jach'anchata ukat jank'a lurayiri?

Parinaqa janiw juk'akiti pisichawitaki. Amtapuni phiskhu uñch'ukita. Mayjt'ayi taqi ina ch'ama ch'axwawinaka.

---

## 📦 Deployment & Installation

### Production Deployment
Yaqha thakhinaka, uñachayirinaka, uñt'asna [DEPLOY.md](DEPLOY.md).

### Quick Start / Local Installation

Local thakhin php server taypi lurayiri:

```bash
git clone https://github.com/nelsonrojasn/Parina-Framework.git
cd Parina-Framework
composer install
php -S localhost:8000 -t public
```

### Dependency Manager
Packagist jank'akiwa.

---

## 🪶 License

MIT License.

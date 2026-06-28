# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

🇺🇸 [English](../README.md) | 🇪🇸 [Español](README.es.md) | 🇫🇷 [Français](README.fr.md) | 🇵🇹 [Português](README.pt.md) | 🇮🇹 [Italiano](README.it.md) | 🇩🇪 [Deutsch](README.de.md) | 🇦ym [Aymara](README.ay.md) | 🦙 **Quechua** | 🇨🇳 [简体中文](README.zh.md) | 🇯🇵 [日本語](README.ja.md)

### *Altiplano Edition: Maypichus pisi kachkan, chaypichus aswan kachkan. Sumaq PHP ruranapaq.*

---

## 💡 ¿Imataq Parina?

Parinaqa huk huch'uy Framework (micro-framework) k'apak PHP ruranakuna hatarichinapaq. Chayqa k'apak thakhillatam qun ruranaykita sumaqta, sut'ita, hinaspa utqaylla apanaykipaq.

---

## 🌄 Filosofía

**Sut'i kaqqa ch'aqwamanta aswan allinmi. Tukuy makinpi hap'iyqa layqakuymanta aswan allinmi.**

Parina sunqunqa:
* **Sut'i ruray:** Manam layqakunachu, manam pakashqa lifecycle llikachakunachu.
* **Pisilla Overhead:** Sapa byte sapa millisecond-pas chaniyuqmi.
* **Yachasqa thakhi:** Chay rikusqaykillam apakun.

---

## 🧱 Uñachaynin 10 siq'ipi

1. Huk Request yaykun front controller nisqaman.
2. Chayqa Middleware Pipeline thakhita purin.
3. Middlewareqa Request yaykuchiqta saqinman utaq sayachinman.
4. Riqsisqa Handler nisqaman chayan.
5. Handlerqa rurananta apan.
6. Huk standard Response nisqata kutichin.
7. Manam ch'aqwa layqakunachu.
8. Manam pakashqa lifecycle llikachakunachu.
9. Manam mana allin abstract kaqkunachu.
10. Sut'i, chiqan puriylla.

---

## 🔄 Request Lifecycle

```
[ Request ] ───> [ Middleware Pipeline ] ───> [ Handler ]
                             │                         │
                             │ (Response kutichin)     │ (Response kutichin)
                             ▼                         ▼
                       [ Response ] <──────────────────┘
```

### Middleware Uñachaynin
Sapa Middleware layer kay kamachiyta purin:
* **`Response` kutichin** → Llamk'ayta sayachin hinaspa Response kutichin.
* **`null` kutichin** → Qhipa layer thakhiru rinanpaq saqin.

#### Middleware uñachaynin
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
            return new ErrorResponse("Manam qispiyaychu", 401);
        }
        return null; // Qhipa layer thakhiru rinanpaq saqin
    }
}
```

---

## 🔒 Amachay (Security)

Amachaypaq kaqkunataqa sumaqtam rikukun sapa Middleware Pipeline thakhipi:

* Rate limiting
* Request size validation
* CSRF protection
* Same-origin policy (CORS)
* Authentication (Basic / JWT)
* Authorization (ACL)

---

## ⚡ Performance

Huch'uy Overhead ukat microsecond k'apaklla rurana:

* **~0.0007 seconds** sapa Request kaqpi.
* **~0.05 MB** RAM llasaynin.
* Opcache friendly nisqa.

---

## 🚀 Bootstrapping

```php
// public/index.php
use Parina\Core\Router;
use Parina\Core\Kernel;
use Parina\Modules\Public\HomeHandler;

require_once '../src/autoload.php';

$router = new Router();
$router->add('GET', '/', HomeHandler::class);

$kernel = new Kernel($router);
$kernel->run();
```

## 🏠 Handler uñachaynin
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

## 🖼 View uñachaynin
```php
<!-- Modules/Public/Views/home.php -->
<h1><?= $title ?></h1>
<p>Parina Frameworkman allillam hamurqunki.</p>
```

---

## 🛠️ CLI Scaffolding

Parinaqa kanmi huk CLI Scaffolding tool, routes, handlers, pruebas basicas-pas CSV qillqamanta ruranapaq.

1. Routes qillqata CSVpi allichay (e.g. `routes.csv`):
   ```csv
   Method,Path,HandlerClass,Middlewares,Description
   GET,/,Parina\Modules\Public\HomeHandler,,Home page
   GET,/about,Parina\Modules\Public\AboutHandler,,About us
   ```

2. CLI Scaffolding tool nisqata apachiy:
   ```bash
   php bin/scaffold.php routes.csv
   ```

Kaykunatam allichapunqa:
* Route allichaykunata config/routes.php p'anqapi.
* Handlers p'anqakunata src/ ukhupi.
* Unit testkunata tests/Handlers/ ukhupi.

---

## 🧪 Tests

Parinaqa PHPUnitwan allichasqam, hunt'asqa coveragewan.

```
tests/
 ├── KernelTest.php
 ├── RouterTest.php
 ├── HandlerTest.php
 └── Handlers/FakeHandler.php
```

---

## 🧘 Imarayku Parina kachkan

Tukuy ch'aqwaqa mana allin ch'amapaqmi. Parinaqa tapukunmi:

¿Ima huch'uy llikacharaq allinta, utqaylla, amachasqata-pas rurakunman?

Parinaqa manam huch'uyllachu pisillapaq. Yuyaypi k'apak allichasqam. Ruraqkunamanmi ch'ampayta qun allin yuyaywan hatarichinankupaq.

---

## 📦 Deployment & Installation

### Production Deployment
Allichaymanta, permisokunamanta, uñachay [DEPLOY.qu.md](DEPLOY.qu.md).

### Cleanup & Reset (Demo pichay)
Tukuy demo p'anqakuna pichanapaq, uñachay [CLEANUP.qu.md](CLEANUP.qu.md).

### Quick Start / Local Installation

PHP serverwan ruranaykita qallarinapaq:

```bash
git clone https://github.com/nelsonrojasn/Parina-Framework.git
cd Parina-Framework
# No composer needed
php -S localhost:8000 -t public
```

### Dependency Manager
Packagist pisi tiempollapi jamunqa.

---

## 🪶 License

MIT License.

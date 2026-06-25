# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

🇺🇸 [English](../README.md) | 🇪🇸 [Español](README.es.md) | 🇫🇷 [Français](README.fr.md) | 🇵🇹 [Português](README.pt.md) | 🇮🇹 [Italiano](README.it.md) | 🇩🇪 [Deutsch](README.de.md) | 🇦ym [Aymara](README.ay.md) | 🦙 **Quechua** | 🇨🇳 [简体中文](README.zh.md) | 🇯🇵 [日本語](README.ja.md)

### *Altiplano Edition: Maypichus pisilla aswan achka kan. Sumaq php ruranapaq.*

---

## 💡 ¿Imataq Parina?

Parinaqa huk huch'uy llikacham (micro-framework) k'apak php ruranakuna hatarichinapaq. Chayqa k'apak thakhillatam qun ruranaykita sumaqta, sut'ita, ukat jank'ata apanaykipaq.

---

## 🌄 Filosofía

**Sut'i kaqkuna aswan ch'aqwamanta. Tukuy allichay makinpi aswan allinchasqamanta.**

Parina sunqunqa:
* **Sut'inchasqa ruray:** Manam layqasqachu, manam pakashqa kawsaykunachu.
* **Pisilla overhead:** Sapa byte ukat sapa millisecond valenmi.
* **Yachasqa thakhi:** Chay rikusqaykillam apakun.

---

## 🧱 Uñachaynin 10 siq'ipi

1. Huk request yaykun huk front controller nisqaman.
2. Chayqa middleware pipeline thakhita purin.
3. Middlewareqa yaykuchiqta saqinman utaq wisq'anman.
4. Riqsisqa handler nisqaman chayan.
5. Handlerqa rurananta apan.
6. Huk standard response nisqata kutichin.
7. Manam ch'aqwa layqakunachu.
8. Manam pakashqa lifecycle llikachakunachu.
9. Manam mana allin abstract kaqkunachu.
10. Sut'i, chiqan puriylla.

---

## 🔄 Request Lifecycle (Mañakuypa Kawsaynin)

```
[ Request ] ───> [ Pipeline de Middlewares ] ───> [ Handler ]
                             │                          │
                             │ (Response kutichin)      │ (Response kutichin)
                             ▼                          ▼
                       [ Response ] <───────────────────┘
```

### Middleware Uñachaynin
Sapa middleware layer kay kamachiyta purin:
* **`Response` kutichin** → Rurayta sayachin ukat kutichiyta apan.
* **`null` kutichin** → Qhipa thakhiman ruranapaq saqin.

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
        return null; // Qhipaman rinanpaq saqin
    }
}
```

---

## 🔒 Jach'anchasqa (Security)

Jach'anchasqa kaqkunataqa sumaqtam uñachakun sapa middleware pipeline thakhipi:

* Rate limiting
* Request size validation
* CSRF protection
* Same-origin policy (CORS)
* Authentication (Basic / JWT)
* Authorization (ACL)

---

## ⚡ Performance (Ruraynin)

Huch'uy overhead ukat microsecond k'apaklla rurana:

* **~0.0007 seconds** sapa mañakuypi.
* **~0.05 MB** RAM llasaynin.
* Opcache friendly nisqa.

---

## 🚀 Qallariynin (Bootstrapping)

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
<p>Parina Llikachaman allin hamusqa kanki.</p>
```

---

## 🛠️ CLI Scaffolding (Código ruraq)

Parina llikachapiqa kanmi huk CLI thakhi routes, handlers, ukat pruebas basicas CSV qillqamanta ruranapaq.

1. Routes qillqata CSVpi allichay (e.g. `routes.csv`):
   ```csv
   Method,Path,HandlerClass,Middlewares,Description
   GET,/,Parina\Modules\Public\HomeHandler,,Home page
   GET,/about,Parina\Modules\Public\AboutHandler,,About us
   ```

2. Scaffolding tool nisqata apachiy:
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

¿Ima huch'uy llikacharaq allinta, jank'ata ukat jach'anchasqata rurakunman?

Parinaqa manam huch'uyllachu pisillapaq. Amtapuni k'apak allichasqa. Churaqkuna p'itiy ruranakunatam qispichin.

---

## 📦 Deployment & Installation

### Production Deployment
Allichaymanta, permisokunamanta, uñachay [DEPLOY.md](DEPLOY.md).

### Quick Start / Local Installation

PHP serverwan ruranaykita qallarinapaq:

```bash
git clone https://github.com/nelsonrojasn/Parina-Framework.git
cd Parina-Framework
composer install
php -S localhost:8000 -t public
```

### Dependency Manager
Packagist pisi tiempollapi.

---

## 🪶 License

MIT License.

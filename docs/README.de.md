# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

🇺🇸 [English](../README.md) | 🇪🇸 [Español](README.es.md) | 🇫🇷 [Français](README.fr.md) | 🇵🇹 [Português](README.pt.md) | 🇮🇹 [Italiano](README.it.md) | 🇩🇪 **Deutsch** | 🇦ym [Aymara](README.ay.md) | 🦙 [Quechua](README.qu.md) | 🇨🇳 [简体中文](README.zh.md) | 🇯🇵 [日本語](README.ja.md)

### *Altiplano-Edition: Weniger ist mehr. Das Web-Framework für klares Denken.*

---

## 💡 Was ist Parina?

Parina ist ein minimales Micro-Framework für moderne PHP-Anwendungen. Es bietet gerade genug Struktur, um Anwendungen mit Klarheit, Kontrolle und maximaler Leistung zu entwickeln.

---

## 🌄 Philosophie

**Klarheit vor Abstraktion. Kontrolle vor Komfort.**

Parina konzentriert sich auf:
* **Explizites Design:** Keine Magie, keine versteckten Lebenszyklen.
* **Minimaler Overhead:** Jedes Byte und jede Millisekunde zählt.
* **Vorhersehbarer Ablauf:** Was Sie sehen, wird auch genau so ausgeführt.

---

## 🧱 Architektur in 10 Zeilen

1. Eine Anfrage geht durch einen Front-Controller ein.
2. Sie durchläuft die Middleware-Pipeline.
3. Die Middleware kann blockieren oder passieren lassen.
4. Sie erreicht den registrierten Handler.
5. Der Handler führt die Kernlogik aus.
6. Er gibt eine Standard-Antwort (Response) zurück.
7. Keine schwere Magie.
8. Keine versteckten Framework-Lebenszyklen.
9. Keine unnötigen Abstraktionen.
10. Nur klare, lineare Ausführung.

---

## 🔄 Lebenszyklus einer Anfrage

```
[ Request ] ───> [ Middleware-Pipeline ] ───> [ Handler ]
                            │                          │
                            │ (Gibt Response zurück)   │ (Gibt Response zurück)
                            ▼                          ▼
                      [ Response ] <───────────────────┘
```

### Middleware-Ebenen-Modell
Jede Middleware-Ebene folgt einer einfachen binären Regel:
* **Gibt `Response` zurück** → Stoppt die Ausführung und sendet die Antwort.
* **Gibt `null` zurück** → Fährt mit der nächsten Ebene fort.

#### Middleware-Beispiel
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
            return new ErrorResponse("Nicht autorisiert", 401);
        }
        return null; // Weiter zur nächsten Ebene
    }
}
```

---

## 🔒 Sicherheit

Sicherheit hat höchste Priorität und befindet sich genau dort, wo sie hingehört: in der Middleware-Pipeline.

* Ratenbegrenzung (Rate limiting)
* Validierung der Anfragegröße
* CSRF-Schutz
* Same-Origin-Richtlinie (CORS)
* Authentifizierung (Basic / JWT)
* Autorisierung (ACL)

---

## ⚡ Leistung

Entwickelt für minimalen Overhead und Mikrosekunden-Präzision:

* **~0,0007 Sekunden** pro Anfrageausführung.
* **~0,05 MB** RAM-Speicherbedarf.
* Vollständig Opcache-freundlich.

---

## 🚀 Beispiel (Bootstrapping / Einstiegspunkt)

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

## 🏠 Minimales Handler-Beispiel
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

## 🖼 Minimales View-Beispiel
```php
<!-- Modules/Public/Views/home.php -->
<h1><?= $title ?></h1>
<p>Willkommen beim Parina Framework.</p>
```

---

## 🛠️ CLI-Scaffolding (Code-Generierung)

Parina enthält ein Befehlszeilen-Tool (CLI), um Routen, Handler und Unit-Tests aus einer CSV-Datei zu generieren.

1. Definiere deine Routen in einer CSV-Datei (z. B. `routes.csv`):
   ```csv
   Method,Path,HandlerClass,Middlewares,Description
   GET,/,Parina\Modules\Public\HomeHandler,,Startseite
   GET,/about,Parina\Modules\Public\AboutHandler,,Über uns
   ```

2. Führe das Scaffolding-Tool aus:
   ```bash
   php bin/scaffold.php routes.csv
   ```

Dies erstellt automatisch:
* Routenkonfigurationen in `config/routes.php`.
* Fehlende Handler-Klassen in `src/`.
* Einfache Unit-Tests in `tests/Handlers/`, um deine Handler zu überprüfen.

---

## 🧪 Enthaltene Tests

Parina wird mit PHPUnit entwickelt, mit dem Fokus auf vollständige Abdeckung.

```
tests/
 ├── KernelTest.php
 ├── RouterTest.php
 ├── HandlerTest.php
 └── Handlers/FakeHandler.php
```

---

## 🧘 Warum es Parina gibt

Die meiste Komplexität in Software ist zufällig. Parina stellt die Frage:

Was ist die kleinste Struktur, die immer noch korrekt, sicher und schnell funktioniert?

Parina ist nicht aus Mangel minimal. Es ist mit Absicht minimal. Es entfernt alles, was Sie eigentlich nicht benötigen.

---

## 📦 Bereitstellung & Installation

### Bereitstellung in der Produktion
Für Verzeichnislayout, Berechtigungen und Produktionstipps siehe [DEPLOY.de.md](DEPLOY.de.md).

### Schnellstart / Lokale Installation

So führen Sie das Framework lokal mit dem integrierten PHP-Entwicklungsserver aus:

```bash
git clone https://github.com/nelsonrojasn/Parina-Framework.git
cd Parina-Framework
composer install
php -S localhost:8000 -t public
```

### Abhängigkeitsmanager
Demnächst auf Packagist.

---

## 🪶 Lizenz

MIT-Lizenz.

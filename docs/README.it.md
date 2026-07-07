# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

🇺🇸 [English](../README.md) | 🇪🇸 [Español](README.es.md) | 🇫🇷 [Français](README.fr.md) | 🇵🇹 [Português](README.pt.md) | 🇮🇹 **Italiano** | 🇩🇪 [Deutsch](README.de.md) | 🇨🇳 [简体中文](README.zh.md) | 🇯🇵 [日本語](README.ja.md)

### *Edizione Altiplano: Meno è meglio. Il framework web per pensare con chiarezza.*

---

## 💡 Cos'è Parina?

Parina è un micro-framework minimalista per moderne applicazioni PHP. Fornisce la struttura strettamente necessaria per creare applicazioni con chiarezza, controllo e massime prestazioni.

---

## 🛠️ Funzionalità Chiave

* **Contenitore DI con Reflection**: Risoluzione automatica e iniezione delle dipendenze nel costruttore per Handler e Middleware.
* **Richiesta HTTP senza stato (`Request`)**: Input unificato dei dati (`input()`), lettura semplice degli header (`header()`) e attributi di contesto locali (`setAttribute()`) per la condivisione dei dati.
* **Pattern CQS e Adapter**: Separazione di query di lettura e comandi di scrittura nei Repository, insieme ad adattatori per database (SQLite, MySQL, PostgreSQL) conformi al principio Aperto/Chiuso.
* **Protezione XSS**: Escape sicuro delle variabili nei template utilizzando la funzione di aiuto globale `h()`.

---

## 🌄 Filosofia

**Chiarezza prima dell'astrazione. Controllo prima della comodità.**

Parina si concentra su:
* **Design esplicito:** Nessuna magia, nessun ciclo di vita nascosto.
* **Sopraccaricamento minimo:** Ogni byte e millisecondo conta.
* **Flusso prevedibile:** Ciò che vedi è esattamente ciò che viene eseguito.

---

## 🧱 Architettura in 10 Righe

1. Una richiesta entra attraverso un front controller.
2. Passa attraverso la pipeline dei middleware.
3. I middleware possono bloccare o far passare la richiesta.
4. Raggiunge il gestore (Handler) registrato.
5. Il gestore esegue la logica centrale.
6. Ritorna una risposta standard (Response).
7. Nessuna magia pesante.
8. Nessun ciclo di vita del framework nascosto.
9. Nessuna astrazione non necessaria.
10. Solo un'esecuzione chiara e lineare.

---

## 🔄 Ciclo di vita della richiesta

```
[ Request ] ───> [ Pipeline dei Middleware ] ───> [ Handler ]
                            │                          │
                            │ (Ritorna Response)       │ (Ritorna Response)
                            ▼                          ▼
                      [ Response ] <───────────────────┘
```

### Modello di Middleware
Ogni strato di middleware segue una semplice regola binaria:
* **Ritorna `Response`** → Ferma l'esecuzione ed emette la risposta.
* **Ritorna `null`** → Continua verso lo strato successivo.

#### Esempio di Middleware
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
            return new ErrorResponse("Non autorizzato", 401);
        }
        return null; // Continua verso lo strato successivo
    }
}
```

---

## 🔒 Sicurezza

La sicurezza è di prim'ordine e risiede esattamente dove deve: nella pipeline dei middleware.

* Limitazione della frequenza (Rate limiting)
* Validazione della dimensione della richiesta
* Protezione CSRF
* Politica di stessa origine (CORS)
* Autenticação (Basic / JWT)
* Autorizzazione (ACL)

---

## ⚡ Prestazioni

Progettato per un sovraccarico minimo e una precisione al microsecondo:

* **~0.0007 secondi** per esecuzione della richiesta.
* **~0.05 MB** di memoria RAM utilizzata.
* Completamente compatibile con Opcache.

---

## 🚀 Esempio (Punto di Ingresso / Bootstrapping)

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

$kernel = new Kernel($router, $container);
$kernel->run();
```

## 🏠 Esempio di Handler Minimo
```php
namespace Parina\Modules\Public;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Request;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\View;
use Parina\Shared\Services\UserQueryRepositoryInterface;

class UsersListHandler implements Handler
{
    // Resolved and injected automatically by the DI Container via Reflection
    public function __construct(private UserQueryRepositoryInterface $userRepo) {}

    public function handle(Request $request): Response
    {
        $users = $this->userRepo->getActiveUsersList();
        // Secure HTML output using the global h() helper to prevent XSS
        $content = View::renderWithLayout("Admin/Views/users/list", "default", ['users' => $users]);
        return new HtmlResponse($content, 200);
    }
}
```

## 🖼 Esempio di Vista Minima
```php
<!-- Modules/Admin/Views/users/list.php -->
<h1>Users List</h1>
<ul>
  <?php foreach ($users as $user): ?>
    <li><?= h($user['username']) ?></li>
  <?php endforeach; ?>
</ul>
```

---

## 🛠️ CLI Scaffolding (Generazione di Codice)

Parina include uno strumento da riga di comando per generare rotte, gestori (handlers) e unit test a partire da un file CSV.

1. Definisci le tue rotte in un file CSV (ad esempio, `routes.csv`):
   ```csv
   Method,Path,HandlerClass,Middlewares,Description
   GET,/,Parina\Modules\Public\HomeHandler,,Home page
   GET,/about,Parina\Modules\Public\AboutHandler,,Chi siamo
   ```

2. Esegui lo strumento di scaffolding:
   ```bash
   php bin/scaffold.php routes.csv
   ```

Questo creerà automaticamente:
* La configurazione delle rotte in `config/routes.php`.
* Le classi dei gestori (Handlers) mancanti in `src/`.
* Unit test di base in `tests/Handlers/` per verificare i tuoi gestori.

---

## 🧪 Test Inclusi

Parina è sviluppato con PHPUnit, focalizzandosi su una copertura completa.

```
tests/
 ├── KernelTest.php
 ├── RouterTest.php
 ├── HandlerTest.php
 └── Handlers/FakeHandler.php
```

---

## 🧘 Perché esiste Parina

La maggior parte della complessità nel software è accidentale. Parina si chiede:

Qual è la struttura più piccola che funziona ancora correttamente, in modo sicuro e veloce?

Parina non è minimalista per limitazione. È minimalista per intenzione. Rimuove tutto ciò di cui non hai realmente bisogno.

Per una spiegazione dettagliata della filosofia di fondo e di come l'intero framework trovi posto nel diagramma di un tovagliolo di carta, vedere [THE-NAPKIN-REVOLUTION.it.md](THE-NAPKIN-REVOLUTION.it.md).

---

## 📦 Distribuzione & Installazione

### Distribuzione in Produzione
Per la struttura delle directory, i permessi e i consigli di produzione, vedi [DEPLOY.it.md](DEPLOY.it.md).

### Pulizia e Ripristino
Per rimuovere tutti i file demo e ripristinare il framework, consulta [CLEANUP.it.md](CLEANUP.it.md).

### Avvio Rapido / Installazione Locale

Per eseguire il framework localmente utilizzando il server di sviluppo integrato di PHP:

```bash
git clone https://github.com/nelsonrojasn/Parina-Framework.git
cd Parina-Framework
# No composer needed
php -S localhost:8000 -t public
```

### Gestore di Dipendenze
Presto su Packagist.

---

## 🪶 Licenza

Licenza MIT.

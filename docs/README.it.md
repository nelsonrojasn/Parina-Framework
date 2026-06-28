# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

🇺🇸 [English](../README.md) | 🇪🇸 [Español](README.es.md) | 🇫🇷 [Français](README.fr.md) | 🇵🇹 [Português](README.pt.md) | 🇮🇹 **Italiano** | 🇩🇪 [Deutsch](README.de.md) | 🇦ym [Aymara](README.ay.md) | 🦙 [Quechua](README.qu.md) | 🇨🇳 [简体中文](README.zh.md) | 🇯🇵 [日本語](README.ja.md)

### *Edizione Altiplano: Meno è meglio. Il framework web per pensare con chiarezza.*

---

## 💡 Cos'è Parina?

Parina è un micro-framework minimalista per moderne applicazioni PHP. Fornisce la struttura strettamente necessaria per creare applicazioni con chiarezza, controllo e massime prestazioni.

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
use Parina\Modules\Public\HomeHandler;

require_once '../src/autoload.php';

$router = new Router();
$router->add('GET', '/', HomeHandler::class);

$kernel = new Kernel($router);
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

class HomeHandler implements Handler
{
    public function handle(Request $request): Response
    {
        $content = View::renderWithLayout("Public/Views/home", "default", ['title' => 'Parina']);
        return new HtmlResponse($content, 200);
    }
}
```

## 🖼 Esempio di Vista Minima
```php
<!-- Modules/Public/Views/home.php -->
<h1><?= $title ?></h1>
<p>Benvenuto in Parina Framework.</p>
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

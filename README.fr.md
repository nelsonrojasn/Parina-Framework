# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

🇺🇸 [English](README.md) | 🇪🇸 [Español](README.es.md) | 🇫🇷 **Français** | 🇵🇹 [Português](README.pt.md) | 🇮🇹 [Italiano](README.it.md) | 🇩🇪 [Deutsch](README.de.md)

### *Édition Altiplano : Moins, c'est plus. Le framework web pour penser clairement.*

---

## 💡 Qu'est-ce que Parina ?

Parina est un micro-framework minimaliste pour les applications PHP modernes. Il fournit juste assez de structure pour construire des applications avec clarté, contrôle et des performances de pointe.

---

## 🌄 Philosophie

**La clarté plutôt que l'abstraction. Le contrôle plutôt que la commodité.**

Parina se concentre sur :
* **Une conception explicite :** Pas de magie, pas de cycles de vie cachés.
* **Une surcharge minimale :** Chaque octet et milliseconde compte.
* **Un flux prévisible :** Ce que vous voyez est exactement ce qui s'exécute.

---

## 🧱 L'architecture en 10 lignes

1. Une requête entre par un contrôleur frontal (Front Controller).
2. Elle passe par le pipeline de middlewares.
3. Les middlewares peuvent bloquer ou laisser passer la requête.
4. Elle atteint le gestionnaire (Handler) enregistré.
5. Le gestionnaire exécute la logique métier.
6. Il retourne une réponse standard (Response).
7. Pas de magie lourde.
8. Pas de cycles de vie de framework cachés.
9. Pas d'abstractions inutiles.
10. Juste une exécution claire et linéaire.

---

## 🔄 Cycle de vie de la requête

```
[ Request ] ───> [ Pipeline de Middlewares ] ───> [ Handler ]
                            │                          │
                            │ (Retourne Response)      │ (Retourne Response)
                            ▼                          ▼
                      [ Response ] <───────────────────┘
```

### Modèle de Middleware
Chaque couche de middleware suit une règle binaire simple :
* **Retourne `Response`** → Arrête l'exécution et émet la réponse.
* **Retourne `null`** → Continue vers la couche suivante.

#### Exemple de Middleware
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
            return new ErrorResponse("Non autorisé", 401);
        }
        return null; // Continue vers la couche suivante
    }
}
```

---

## 🔒 Sécurité

La sécurité est de premier ordre et réside exactement là où elle doit : dans le pipeline de middlewares.

* Limitation de débit (Rate limiting)
* Validation de la taille de la requête
* Protection CSRF
* Politique de même origine (CORS)
* Authentification (Basic / JWT)
* Autorisation (ACL)

---

## ⚡ Performances

Conçu pour une surcharge minimale et une précision à la microseconde :

* **~0.0007 seconde** par exécution de requête.
* **~0.05 Mo** d'empreinte RAM.
* Entièrement compatible avec Opcache.

---

## 🚀 Exemple (Point d'entrée / Bootstrapping)

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

## 🏠 Exemple de gestionnaire minimal (Handler)
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

## 🖼 Exemple de vue minimale
```php
<!-- Modules/Public/Views/home.php -->
<h1><?= $title ?></h1>
<p>Bienvenue sur Parina Framework.</p>
```

---

## 🧪 Tests inclus

Parina est développé avec PHPUnit, en se concentrant sur une couverture complète.

```
tests/
 ├── KernelTest.php
 ├── RouterTest.php
 ├── HandlerTest.php
 └── Handlers/FakeHandler.php
```

---

## 🧘 Pourquoi Parina existe

La plupart de la complexité dans le logiciel est accidentelle. Parina se demande :

Quelle est la plus petite structure qui fonctionne toujours correctement, de manière sécurisée et rapide ?

Parina n'est pas minimaliste par limitation. Il est minimaliste par intention. Il supprime tout ce dont vous n'avez pas réellement besoin.

---

## 📦 Déploiement & Installation

### Déploiement en production
Pour la disposition des répertoires, les permissions et les conseils de production, voir [DEPLOY.fr.md](DEPLOY.fr.md).

### Démarrage rapide / Installation locale

Pour exécuter le framework localment à l'aide du serveur de développement intégré de PHP :

```bash
git clone https://github.com/nelsonrojasn/Parina-Framework.git
cd Parina-Framework
composer install
php -S localhost:8000 -t public
```

### Gestionnaire de dépendances
Bientôt sur Packagist.

---

## 🪶 Licence

Licence MIT.

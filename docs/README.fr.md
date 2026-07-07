# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

🇺🇸 [English](../README.md) | 🇪🇸 [Español](README.es.md) | 🇫🇷 **Français** | 🇵🇹 [Português](README.pt.md) | 🇮🇹 [Italiano](README.it.md) | 🇩🇪 [Deutsch](README.de.md) | 🇨🇳 [简体中文](README.zh.md) | 🇯🇵 [日本語](README.ja.md)

### *Édition Altiplano : Moins, c'est plus. Le framework web pour penser clairement.*

---

## 💡 Qu'est-ce que Parina ?

Parina est un micro-framework minimaliste pour les applications PHP modernes. Il fournit juste assez de structure pour construire des applications avec clarté, contrôle et des performances de pointe.

---

## 🛠️ Fonctionnalités Clés

* **Conteneur DI avec Réflexion** : Résolution automatique et injection de dépendances par constructeur dans les contrôleurs et middlewares de manière récursive.
* **Requête HTTP sans état (`Request`)** : Entrée unifiée des données (`input()`), récupération simple des en-têtes (`header()`) et attributs de contexte locaux (`setAttribute()`) pour le partage de données.
* **Patrons CQS et Adapter** : Séparation des requêtes de lecture et des commandes d'écriture dans les dépôts, ainsi que des adaptateurs de base de données (SQLite, MySQL, PostgreSQL) conformes au principe Ouvert/Fermé.
* **Sécurité XSS** : Échappement sécurisé des variables dans les vues à l'aide de la fonction d'aide globale `h()`.

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

## 🏠 Exemple de gestionnaire minimal (Handler)
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

## 🖼 Exemple de vue minimale
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

## 🛠️ CLI Scaffolding (Génération de Code)

Parina inclut un outil en ligne de commande pour générer des routes, des gestionnaires (handlers) et des tests unitaires à partir d'un fichier CSV.

1. Définissez vos routes dans un fichier CSV (par exemple, `routes.csv`) :
   ```csv
   Method,Path,HandlerClass,Middlewares,Description
   GET,/,Parina\Modules\Public\HomeHandler,,Page d'accueil
   GET,/about,Parina\Modules\Public\AboutHandler,,À propos
   ```

2. Exécutez l'outil de scaffolding :
   ```bash
   php bin/scaffold.php routes.csv
   ```

Cela créera automatiquement :
* La configuration des routes dans `config/routes.php`.
* Les classes de gestionnaires (Handlers) manquantes dans `src/`.
* Des tests unitaires de base dans `tests/Handlers/` pour vérifier vos gestionnaires.

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

Pour une explication détaillée de la philosophie fondamentale et de la manière dont l'ensemble du framework tient dans le schéma d'une serviette en papier, voir [THE-NAPKIN-REVOLUTION.fr.md](THE-NAPKIN-REVOLUTION.fr.md).

---

## 📦 Déploiement & Installation

### Déploiement en production
Pour la disposition des répertoires, les permissions et les conseils de production, voir [DEPLOY.fr.md](DEPLOY.fr.md).

### Nettoyage & Réinitialisation
Pour supprimer tous les fichiers de démonstration et réinitialiser le framework, consultez [CLEANUP.fr.md](CLEANUP.fr.md).

### Démarrage rapide / Installation locale

Pour exécuter le framework localment à l'aide du serveur de développement intégré de PHP :

```bash
git clone https://github.com/nelsonrojasn/Parina-Framework.git
cd Parina-Framework
# No composer needed
php -S localhost:8000 -t public
```

### Gestionnaire de dépendances
Bientôt sur Packagist.

---

## 🪶 Licence

Licence MIT.

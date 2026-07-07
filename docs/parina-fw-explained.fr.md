---

# 1. Idéologie : Moins, c'est Plus (The Napkin Revolution)

L'idéologie de Parina ne découle pas d'une limitation, mais d'une **intentionnalité**. Elle est régie par trois principes philosophiques :

* **KISS (Keep It Simple, Stupid) & YAGNI (You Aren't Gonna Need It)** : La majeure partie de la complexité dans les frameworks modernes est accidentelle. Parina se demande : *quelle est la structure minimale requise pour construire une application web sécurisée, maintenable et performante ?* Son poids en RAM (~0.05 Mo) et son temps d'exécution (~0.0007 seconde) sont les conséquences de cette philosophie.
* **Explicité contre "Magie" (No-Magic)** : Évite les cycles de vie cachés ou les fichiers de configuration géants. Ce qui est écrit dans le code est exactement ce qui est exécuté.
* **Découplage Pragmatique (SOLID)** : Parina favorise l'**Inversion de Contrôle (IoC)** et la **Ségrégation des Interfaces**. Grâce à son conteneur DI et à ses services basés sur des interfaces, il permet de modifier les implémentations concrètes (bases de données, signataires, authentificateurs) sans toucher au cœur du framework ni aux contrôleurs.

---

# 2. Flux d'Exécution : Le Cycle de Vie de la Requête

Le flux de Parina est un **pipeline séquentiel et synchrone** qui implémente le modèle **Front Controller** :

```
Requête HTTP
   │
   ▼
[public/index.php] (Front Controller)
   │
   ├──> Charge l'Autoloader et le helper global h()
   ├──> Instancie [Container] et charge config/dependencies.php (IoC)
   ├──> Initialise [Db] avec le [DatabaseAdapter] résolu dynamiquement (OCP)
   └──> Initialise [Router] et enregistre config/routes.php
   │
   ▼
[Kernel] (Dispatcher)
   │
   ├──> Capture les superglobales dans un objet [Request] (Value Object)
   │
   ├──> [Pipeline des Middlewares] (Filtres d'interception)
   │       └──> Si un middleware retourne [Response] (ex : erreur 401), le flux est interrompu.
   │
   ├──> [Container::get()] (Résolution DI basée sur la Réflexion)
   │       └──> Instancie le Handler en résolvant ses dépendances de manière récursive.
   │
   ├──> [Handler::handle(Request)] (Controller)
   │       └──> Exécute la logique et retourne un objet implémentant [Response]
   │
   ▼
[Kernel::send()] (Émission)
   └──> Envoie les en-têtes HTTP, le code de statut et fait l'echo du contenu.
```

---

# 3. Sécurité : Architecture Défensive et Interfaces Pures

La sécurité de Parina est organisée en couches et s'exécute principalement dans le pipeline des middlewares, garantissant que le trafic malveillant n'atteigne jamais les contrôleurs d'activité :

* **Authentification Stateless** :
  * **JWT** : Le middleware [JwtAuth](file:///home/nelson/repos/Parina-Framework/src/Shared/Middlewares/JwtAuth.php) extrait les jetons à l'aide du helper `$request->bearerToken()`, les valide via `TokenServiceInterface`, et injecte l'identité dans les attributs locaux de la requête (`$request->setAttribute('user_id')`).
  * **Basic Auth** : Le middleware [BasicAuth](file:///home/nelson/repos/Parina-Framework/src/Shared/Middlewares/BasicAuth.php) valide les identifiants à l'aide de `UserQueryRepositoryInterface::checkCredentials()`, ce qui évite la création inutile de cookies et de sessions serveur dans les API REST.
* **Signature Cryptographique d'URLs** : Le middleware [ValidateHash](file:///home/nelson/repos/Parina-Framework/src/Shared/Middlewares/ValidateHash.php) injecte `CipherInterface` pour analyser les signatures temporaires (TTL) des liens sensibles, validant l'intégrité du lien avant de router la requête.
* **Contrôle d'Accès (ACL)** : Basé sur l'interface `AclInterface`, il permet de valider les autorisations dynamiques et d'injecter facilement des implémentations simulées (mocks) dans l'environnement de test.
* **Prévention du XSS et CSRF** :
  * **CSRF** : Un jeton injecté dans les formulaires et validé dans les middlewares protège contre la falsification de requêtes.
  * **XSS** : Le helper global `h($variable)` agit comme un outil d'échappement natif dans les vues PHP (`htmlspecialchars`).

---

# 4. Accès et Modification des Données : Le Double Niveau de Persistance

Parina offre de la flexibilité au développeur en permettant deux approches de persistance :

### A. Persistance par Dépôt (CQS - Command Query Segregation)
Il s'agit de l'approche moderne et propre du framework. Elle divise les opérations en interfaces de requête et d'écriture :
* **Lecture (`UserQueryRepositoryInterface`)** : Retourne des données plates ou des objets de valeur spécifiques. Optimisé pour les requêtes complexes et la rapidité.
* **Écriture (`UserCommandRepositoryInterface`)** : Persiste et modifie l'état du système.
* **DbUserRepository** : Implémentation qui centralise l'accès SQL.
* *Bénéfice* : Découplage de la base de données de la session HTTP (SRP) et tests unitaires 100 % en mémoire à l'aide de mocks.

### B. Persistance par Active Record (`BaseModel`)
* Les classes comme `User` héritent directement de `BaseModel`. Elles mappent les propriétés des classes aux colonnes de table et fournissent des méthodes CRUD directes (`all()`, `find()`, `create()`).
* C'est une option idéale pour le prototypage rapide et les opérations CRUD très simples.

### C. Abstraction du Pilote (Modèle Adapter)
* Le moteur de base de données final (SQLite, MySQL ou PostgreSQL) est injecté dynamiquement via l'interface `DatabaseAdapter` enregistrée dans le conteneur.
* Conforme au **Principe Ouvert/Fermé (OCP)** : si vous devez migrer des bases de données ou ajouter un moteur de base de données non pris en charge (comme SQL Server), vous devez simplement créer une classe qui implémente `DatabaseAdapter` et l'enregistrer dans `dependencies.php`, sans modifier une seule ligne du code interne du framework.

---

### Diagnostic Final de l'Architecte :
Parina Framework prouve que l'extrême simplicité n'est pas incompatible avec de bons design patterns. Son architecture moderne en matière de découplage de dépendances (DIP) et de ségrégation des interfaces de données (CQS) en fait un moteur d'applications PHP agile, sécurisé et extrêmement facile à tester.
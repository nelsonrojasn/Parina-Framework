---

# 1. Flux d'Exécution : De la Requête à la Réponse

Le flux de Parina est un **cycle de vie linéaire, synchrone et hautement prédictible**. Il suit le modèle **Front Controller** dans un pipeline séquentiel :

```
Requête HTTP
   │
   ▼
[public/index.php] ──(1. Bootstrap)──> Charge l'Autoload, Helpers globaux (h())
   │
   ▼
[Container] ─────────(2. Configuration)──> Charge config/dependencies.php (DI)
   │
   ▼
[Db::init()] ────────(3. Couche de Données)──> Injecte le DatabaseAdapter résolu
   │
   ▼
[Router] ────────────(4. Routage)──> Cherche une correspondance de méthode et d'URI (regex params)
   │
   ▼
[Kernel] ────────────(5. Dispatch)──> Convertit en Request (Value Object)
   │
   ├─> [Middlewares] ──(6. Pipeline de Filtres)──> (Interrompt le flux si retourne Response)
   │
   ▼
[Container::get()] ──(7. Résolution DI)──> Instancie le Handler en injectant les dépendances
   │
   ▼
[Handler::handle()] ─(8. Logique Contrôleur)──> Retourne un objet Response
   │
   ▼
[Kernel::send()] ────(9. Rendu et Envoi)──> Émet les en-têtes HTTP, le statut et fait l'echo du corps
```

### Découverte Archéologique dans le Flux :
* Dans la couche initiale, le Kernel instanciait les middlewares et les handlers directement en faisant `new $className()`.
* Dans la couche moderne, le Kernel délègue cela au `Container`. Cela permet à n'importe quel contrôleur de déclarer les interfaces dont il a besoin dans son constructeur (injection de dépendances) et au framework de les résoudre par réflexion récursive avant d'exécuter la requête.

---

# 2. Sécurité et Accès : Les Murailles du Système

La sécurité de Parina a évolué de la "sécurité par couplage" (où les couches étaient mélangées) vers une architecture défensive basée sur des **interfaces et la ségrégation** :

### A. Authentification et Contrôle de Session
* **Le fossile ancien** : Le modèle de base de données `User` manipulait directement la session globale `$_SESSION['user_id'] = ...`. Cela viole les principes de la clean architecture, car la base de données ne devrait pas connaître l'existence de cookies HTTP ou de sessions web.
* **La structure moderne** : Nous avons introduit `AuthInterface` et `SessionAuth`. Désormais, la connexion est un service injectable. Le middleware Auth et le `LoginCheckHandler` demandent simplement au service `isLoggedIn()` ou appellent `login()`. Dans les tests, nous pouvons simuler qu'un utilisateur est authentifié sans créer de réelles sessions PHP sur disque.

### B. Contrôle d'Accès (ACL)
* **Le fossile ancien** : La classe `Acl` contenait une méthode `setMockHasPermissions` pour modifier son état à partir des tests unitaires. C'est un code smell de test dans le code de production.
* **La structure moderne** : Le middleware `Acl` reçoit une `AclInterface` par constructeur. Toute la logique statique et les raccourcis de test ont été éradiqués du code de production d'`Acl`. Les tests utilisent des mocks PHPUnit natifs.

### C. Défenses d'Entrée et de Sortie (CSRF et XSS)
* **CSRF (Cross-Site Request Forgery)** : Géré via le jeton `Csrf::token()`, injecté dans les formulaires et validé par le middleware CSRF.
* **XSS (Cross-Site Scripting)** : L'intégration du helper global `h()` dans l'autoloader permet aux templates PHP d'échapper facilement les caractères HTML dangereux (`htmlspecialchars($value, ENT_QUOTES)`), garantissant que la sortie visuelle n'exécute pas de code JavaScript injecté par des tiers.

---

# 3. Accès et Modification des Données : Le Double Niveau de Persistance

C'est dans la couche de données que la transition archéologique du framework est la plus évidente :

```
                  ┌──────────────────────────────────────────┐
                  │                 CLIENT                   │
                  └────────────────────┬─────────────────────┘
                                       │
                    ┌──────────────────┴──────────────────┐
                    ▼                                     ▼
        [Active Record (Hérité)]                [CQS (Moderne)]
        Utilise BaseModel statique              Utilise des interfaces ségréguées
        et instanciation directe.               pour la Lecture et l'Écriture.
                    │                                     │
                    │                                     ▼
                    │                       [UserQueryRepositoryInterface]
                    │                       [UserCommandRepositoryInterface]
                    │                                     │
                    ▼                                     ▼
             [Db::query()]                       [DbUserRepository]
                    │                                     │
                    └──────────────────┬──────────────────┘
                                       ▼
                              [DatabaseAdapter] (Interface)
                                       │
                        ┌──────────────┼──────────────┐
                        ▼              ▼              ▼
                 [SqliteAdapter] [MySqlAdapter] [PostgreSqlAdapter]
```

### A. Le Niveau Active Record (`BaseModel`)
* Les modèles héritent de `BaseModel` et se mappent de manière 1-à-1 sur des tables SQLite/MySQL.
* C'est une approche idéale pour un développement hyper-rapide (KISS), mais elle mélange la représentation des données avec les méthodes de stockage (violant l'SRP).

### B. Le Niveau CQS (Command Query Segregation)
* Pour briser le couplage d'Active Record, nous avons introduit la ségrégation des interfaces de lecture et d'écriture :
  - `UserQueryRepositoryInterface` : Fournit des méthodes optimisées pour lire les informations (ex. `checkCredentials`, `findByUsername`).
  - `UserCommandRepositoryInterface` : Fournit des méthodes pour écrire, mettre à jour ou supprimer des informations.
* Les deux sont implémentées par `DbUserRepository`, qui communique avec la base de données.
* Cela permet de changer complètement le moteur de stockage d'une entité (ex. vers MongoDB ou une API externe) en modifiant uniquement le dépôt, sans altérer les entités ni la logique du contrôleur.

### C. Le Modèle Adapter dans la Connexion
* La base de données n'est pas instanciée de manière figée dans le code. Le conteneur DI résout l'interface `DatabaseAdapter` à l'aide d'une factory dans `dependencies.php` qui lit la configuration active de la base de données.
* Respecte strictement le **Principe Ouvert/Fermé (OCP)** : le framework est fermé aux modifications internes mais ouvert aux développeurs qui souhaitent ajouter de nouveaux adaptateurs SQL en les enregistrant simplement dans la configuration externe.

---

### Diagnostic Final de l'Archéologue :
Parina Framework est un excellent exemple de la façon dont un framework « pragmatique et statique » peut être affiné vers un design de « niveau entreprise » (SOLID complet) sans sacrifier la vitesse d'exécution et en maintenant une compatibilité ascendante totale avec le code hérité via des façades dynamiques (`__callStatic`).
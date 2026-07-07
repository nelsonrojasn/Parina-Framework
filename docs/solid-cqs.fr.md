---

# 1. Implémentation des Principes SOLID dans Parina

SOLID est le pilier qui a transformé Parina d'un script monolithique couplé en un framework flexible et modulaire :

### **S – Single Responsibility Principle (SRP)**
Chaque classe de Parina a **exactement une raison de changer**.
* **Avant** : Le modèle `User` mappait la base de données et contrôlait l'état de la session HTTP (`$_SESSION`).
* **Maintenant** : Nous avons séparé la persistance dans le dépôt (`DbUserRepository`) et la gestion de la session dans le service `SessionAuth`.
* **Middlewares** : Chaque middleware (`RateLimit`, `RequestSize`, `Csrf`) encapsule une règle de sécurité spécifique, permettant à la classe `Kernel` de se concentrer uniquement sur le dispatching de la requête HTTP.

### **O – Open/Closed Principle (OCP)**
Le code est **ouvert à l'extension, mais fermé à la modification**.
* **Exemple (Base de données)** : L'interface `DatabaseAdapter` abstrait les différents pilotes SQL. Si un développeur souhaite prendre en charge Oracle ou SQL Server, il n'a pas besoin de modifier le cœur de Parina. Il crée simplement une classe implémentant `DatabaseAdapter` et la lie de manière dynamique dans le fichier externe `config/dependencies.php`.

### **L – Liskov Substitution Principle (LSP)**
Toute sous-classe doit pouvoir remplacer sa classe de base sans altérer le comportement correct du programme.
* **Refactoring de `Response`** : L'interface `Response.php` d'origine contenait une signature de constructeur fixe. Cela obligeait les classes comme `RedirectResponse` ou `JsonResponse` à recevoir des paramètres dont elles n'avaient pas besoin, violant le LSP. Nous avons supprimé le constructeur de l'interface, permettant au Kernel de gérer n'importe quelle réponse (Html, Json, Redirect) de manière uniforme.

### **I – Interface Segregation Principle (ISP)**
Les clients ne doivent pas être contraints de dépendre d'interfaces qu'ils n'utilisent pas.
* **Ségrégation des dépôts** : Nous avons divisé l'accès aux données utilisateur en deux interfaces : `UserQueryRepositoryInterface` et `UserCommandRepositoryInterface`.
* **Utilisation** : Le middleware `BasicAuth` a uniquement besoin de vérifier les identifiants (Lecture). Au lieu de recevoir un dépôt avec des méthodes comme `save()` ou `delete()`, il injecte uniquement `UserQueryRepositoryInterface`, limitant ses actions au strict minimum.

### **D – Dependency Inversion Principle (DIP)**
Les modules de haut niveau ne doivent pas dépendre de modules de bas niveau ; les deux doivent dépendre d'abstractions.
* **Conteneur DI par Réflexion** : Les contrôleurs et middlewares de Parina ne créent plus leurs dépendances à l'aide du mot-clé `new`. Au lieu de cela, ils déclarent des interfaces dans leurs constructeurs (ex : `ConfigInterface`, `Logger`, `TokenServiceInterface`, `CipherInterface`). Le composant `Container` analyse ces signatures par réflexion lors de l'exécution et injecte les dépendances résolues.

---

# 2. Implémentation du Modèle CQS (Command Query Segregation)

Le modèle CQS stipule qu'**une méthode doit être soit une commande** (exécuter une action qui modifie l'état du système) **soit une requête** (renvoyer des données au client sans effets secondaires), mais jamais les deux.

Dans Parina Framework, le CQS est implémenté au niveau de la couche de données et de services :

```
                            [Contrôleur / Handler]
                           /                       \
        Injecte Query     /                         \    Injecte Command
                         ▼                           ▼
      [UserQueryRepositoryInterface]      [UserCommandRepositoryInterface]
      * findById()                        * save()
      * findByUsername()                  * delete()
      * checkCredentials()
                         \                           /
                          ▼                         ▼
                      ┌─────────────────────────────────┐
                      │        DbUserRepository         │
                      │ (Implémente les deux            │
                      │           interfaces)           │
                      └─────────────────────────────────┘
```

### A. Couche Requêtes
Représentée par l'interface `UserQueryRepositoryInterface`.
* **Méthodes** : `findById()`, `findByUsername()`, `checkCredentials()`.
* **Comportement** : Méthodes pures en lecture seule. Elles interrogent la base de données SQL et renvoient des tableaux associatifs bruts ou null. **Il leur est strictement interdit de modifier l'état du système** (elles n'écrivent pas dans les tables et n'injectent pas de données dans la session globale `$_SESSION`).

### B. Couche Commandes
Représentée par l'interface `UserCommandRepositoryInterface`.
* **Méthodes** : `save()`, `delete()`.
* **Comportement** : Opérations d'écriture/modification. Elles modifient les enregistrements physiques dans le moteur de base de données et signalent le succès ou l'échec (`bool`).

### C. Conséquence sur la Conception des Tests
Grâce au CQS, dans `LoginCheckHandlerTest.php`, le test simule uniquement la requête (`checkCredentials()`) en injectant un mock léger de l'interface Query. Cela permet aux tests unitaires de s'exécuter instantanément en mémoire, de manière complètement isolée de la base de données SQLite physique sur disque.
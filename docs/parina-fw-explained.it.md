---

# 1. Ideologia: Meno è Meglio (The Napkin Revolution)

L'ideologia di Parina non nasce da una limitazione, ma dall'**intenzionalità**. È governata da tre principi filosofici:

* **KISS (Keep It Simple, Stupid) e YAGNI (You Aren't Gonna Need It)**: La maggior parte della complessità nei framework moderni è accidentale. Parina si chiede: *qual è la struttura minima necessaria per costruire un'applicazione web sicura, manutenibile e ad alte prestazioni?* Il suo consumo di RAM (~0.05 MB) e il tempo di esecuzione (~0.0007 secondi) sono conseguenze di questa filosofia.
* **Esplicitezza rispetto alla "Magia" (No-Magic)**: Evita cicli di vita nascosti o file di configurazione enormi. Ciò che si legge nel codice è esattamente ciò che viene eseguito.
* **Disaccoppiamento Pragmatico (SOLID)**: Parina favorisce l'**Inversione del Controllo (IoC)** e la **Segregazione delle Interfacce**. Attraverso il suo contenitore DI e servizi basati su interfacce, consente di modificare le implementazioni concrete (database, cifratori, autenticatori) senza toccare il nucleo del framework o i controller.

---

# 2. Flusso di Esecuzione: Il Ciclo di Vita della Richiesta

Il flusso di Parina è una **pipeline sequenziale e sincrona** che implementa il pattern **Front Controller**:

```
Richiesta HTTP
   │
   ▼
[public/index.php] (Front Controller)
   │
   ├──> Carica l'Autoloader e l'helper global h()
   ├──> Istanzia [Container] e carica config/dependencies.php (IoC)
   ├──> Inizializza [Db] con il [DatabaseAdapter] risolto dinamicamente (OCP)
   └──> Inizializza [Router] e registra config/routes.php
   │
   ▼
[Kernel] (Dispatcher)
   │
   ├──> Cattura le superglobali in un oggetto [Request] (Value Object)
   │
   ├──> [Pipeline dei Middleware] (Filtri di intercettazione)
   │       └──> Se un middleware restituisce [Response] (es: errore 401), il flusso si interrompe.
   │
   ├──> [Container::get()] (Risoluzione DI basata su Reflection)
   │       └──> Istanzia l'Handler risolvendo le sue dipendenze in modo ricorsivo.
   │
   ├──> [Handler::handle(Request)] (Controller)
   │       └──> Esegue la logica e restituisce un oggetto che implementa [Response]
   │
   ▼
[Kernel::send()] (Emissione)
   └──> Invia gli header HTTP, il codice di stato ed esegue l'echo del contenuto.
```

---

# 3. Sicurezza: Architettura Difensiva e Interfacce Pure

La sicurezza di Parina è organizzata in livelli ed è eseguita principalmente nella pipeline dei middleware, garantendo che il traffico dannoso non raggiunga mai i controller aziendali:

* **Autenticazione Stateless**:
  * **JWT**: Il middleware [JwtAuth](file:///home/nelson/repos/Parina-Framework/src/Shared/Middlewares/JwtAuth.php) estrae i token utilizzando l'helper `$request->bearerToken()`, li valida tramite `TokenServiceInterface` e inetta l'identità negli attributi locali della richiesta (`$request->setAttribute('user_id')`).
  * **Basic Auth**: Il middleware [BasicAuth](file:///home/nelson/repos/Parina-Framework/src/Shared/Middlewares/BasicAuth.php) valida le credenziali utilizzando `UserQueryRepositoryInterface::checkCredentials()`, impedendo la creazione non necessaria di cookie e sessioni server nelle API REST.
* **Firma Crittografica degli URL**: Il middleware [ValidateHash](file:///home/nelson/repos/Parina-Framework/src/Shared/Middlewares/ValidateHash.php) inietta `CipherInterface` per analizzare le firme temporanee (TTL) dei link sensibili, convalidando l'integrità del link prima di instradare la richiesta.
* **Controllo degli Accessi (ACL)**: Basato sull'interfaccia `AclInterface`, consente di convalidare i permessi dinamici e iniettare facilmente implementazioni mock nell'ambiente di test.
* **Prevenzione di XSS e CSRF**:
  * **CSRF**: Un token inserito nei form e validato nei middleware protegge dalla falsificazione delle richieste.
  * **XSS**: L'helper globale `h($variable)` funge da sanificatore di escape nativo nelle viste PHP (`htmlspecialchars`).

---

# 4. Accesso e Modifica dei Dati: Il Doppio Livello di Persistenza

Parina offre flessibilità allo sviluppatore consentendo due approcci di persistenza:

### A. Persistenza tramite Repository (CQS - Command Query Segregation)
Questo è l'approccio moderno e pulito del framework. Divide le operazioni in interfacce di lettura (query) e scrittura (command):
* **Lettura (`UserQueryRepositoryInterface`)**: Restituisce dati piatti o oggetti valore specifici. Ottimizzato per query complesse e velocità.
* **Scrittura (`UserCommandRepositoryInterface`)**: Persiste e modifica lo stato del sistema.
* **DbUserRepository**: Implementazione che centralizza l'accesso SQL.
* *Beneficio*: Disaccoppiamento del database dalla sessione HTTP (SRP) e test unitari al 100% in memoria utilizzando i mock.

### B. Persistenza tramite Active Record (`BaseModel`)
* Classi come `User` ereditano direttamente da `BaseModel`. Mappano le proprietà della classe sulle colonne della tabella e forniscono metodi CRUD diretti (`all()`, `find()`, `create()`).
* È un'opzione ideale per la prototipazione rapida e operazioni CRUD molto semplici.

### C. Astrazione del Driver (Pattern Adapter)
* Il motore di database finale (SQLite, MySQL o PostgreSQL) viene iniettato dinamicamente tramite l'interfaccia `DatabaseAdapter` registrata nel contenitore.
* Conforme al **Principio Aperto/Chiuso (OCP)**: se è necessario migrare i database o aggiungere un motore di database non supportato (come SQL Server), è sufficiente creare una classe che implementi `DatabaseAdapter` e registrarla in `dependencies.php`, senza modificare una sola riga di codice interno del framework.

---

### Diagnosi Finale dell'Architetto:
Parina Framework dimostra che l'estrema semplicità non è in contrasto con i buoni design pattern. La sua architettura moderna nel disaccoppiamento delle dipendenze (DIP) e nella segregazione delle interfacce dei dati (CQS) lo rende un motore di applicazioni PHP agile, sicuro e facilissimo da testare.
---

# 1. Il Flusso di Esecuzione: Dalla Richiesta alla Risposta

Il flusso di Parina è un **ciclo di vita lineare, sincrono e altamente prevedibile**. Segue il pattern **Front Controller** in una pipeline sequenziale:

```
Richiesta HTTP
   │
   ▼
[public/index.php] ──(1. Bootstrap)──> Carica Autoload, Helper globali (h())
   │
   ▼
[Container] ─────────(2. Configurazione)──> Carica config/dependencies.php (DI)
   │
   ▼
[Db::init()] ────────(3. Livello Dati)──> Inietta il DatabaseAdapter risolto
   │
   ▼
[Router] ────────────(4. Routing)──> Cerca corrispondenza di metodo e URI (regex params)
   │
   ▼
[Kernel] ────────────(5. Dispatch)──> Converte in Request (Value Object)
   │
   ├─> [Middlewares] ──(6. Pipeline di Filtri)──> (Interrompe il flusso se ritorna Response)
   │
   ▼
[Container::get()] ──(7. Risoluzione DI)──> Istanzia l'Handler iniettando le dipendenze
   │
   ▼
[Handler::handle()] ─(8. Logica Controller)──> Ritorna un oggetto Response
   │
   ▼
[Kernel::send()] ────(9. Rendering e Invio)──> Invia gli header HTTP, lo status e fa l'echo del body
```

### Scoperta Archeologica nel Flusso:
* Nella fase iniziale, il Kernel istanziava i middleware e gli handler direttamente chiamando `new $className()`.
* Nella fase moderna, il Kernel delega questo compito al `Container`. Questo consente a qualsiasi controller di dichiarare quali interfacce richiede nel proprio costruttore (dependency injection) e al framework di risolverle tramite riflessione ricorsiva prima di eseguire la richiesta.

---

# 2. Sicurezza e Accesso: Le Mura del Sistema

La sicurezza di Parina si è evoluta dalla "sicurezza per accoppiamento" (dove i livelli erano mescolati) a un'architettura difensiva basata su **interfacce e segregazione**:

### A. Autenticazione e Controllo della Sessione
* **Il fossile antico**: Il modello di database `User` manipolava direttamente la sessione globale `$_SESSION['user_id'] = ...`. Questo viola i principi della clean architecture, poiché il database non dovrebbe conoscere l'esistenza di cookie HTTP o sessioni web.
* **La struttura moderna**: Abbiamo introdotto `AuthInterface` e `SessionAuth`. Ora, il login è un servizio iniettabile. Il middleware Auth e il `LoginCheckHandler` si limitano a chiedere al servizio `isLoggedIn()` o a chiamare `login()`. Nei test, possiamo simulare che un utente sia autenticato senza creare reali sessioni PHP su disco.

### B. Controllo degli Accessi (ACL)
* **Il fossile antico**: La classe `Acl` conteneva un metodo `setMockHasPermissions` per alterare il proprio stato dai test unitari. Questo è un test smell nel codice di produzione.
* **La struttura moderna**: Il middleware `Acl` riceve un'interfaccia `AclInterface` tramite il costruttore. Tutta la logica statica e le scorciatoie per i test sono state rimosse dal codice di produzione di `Acl`. I test utilizzano mock nativi di PHPUnit.

### C. Difese in Ingresso e in Uscita (CSRF e XSS)
* **CSRF (Cross-Site Request Forgery)**: Gestito tramite il token `Csrf::token()`, iniettato nei form e validato dal middleware CSRF.
* **XSS (Cross-Site Scripting)**: L'integrazione dell'helper globale `h()` nell'autoloader consente ai template PHP di eseguire l'escape dei caratteri HTML pericolosi (`htmlspecialchars($value, ENT_QUOTES)`) in modo semplice, garantendo che l'output visivo non esegua codice JavaScript iniettato da terze parti.

---

# 3. Accesso e Modifica dei Dati: Il Doppio Livello di Persistenza

È nel livello dei dati che la transizione archeologica del framework è più evidente:

```
                  ┌──────────────────────────────────────────┐
                  │                 CLIENTE                  │
                  └────────────────────┬─────────────────────┘
                                       │
                    ┌──────────────────┴──────────────────┐
                    ▼                                     ▼
        [Active Record (Legacy)]                [CQS (Moderno)]
        Utilizza BaseModel statico              Usa interfacce segregate
        e istanziazione diretta.                per Lettura e Scrittura.
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
                              [DatabaseAdapter] (Interfaccia)
                                       │
                        ┌──────────────┼──────────────┐
                        ▼              ▼              ▼
                 [SqliteAdapter] [MySqlAdapter] [PostgreSqlAdapter]
```

### A. Il Livello Active Record (`BaseModel`)
* I modelli ereditano da `BaseModel` e si mappano 1-a-1 sulle tabelle SQLite/MySQL.
* È un approccio ideale per uno sviluppo ultra-rapido (KISS), ma mescola la rappresentazione dei dati con i metodi di archiviazione (violando l'SRP).

### B. Il Livello CQS (Command Query Segregation)
* Per rompere l'accoppiamento di Active Record, abbiamo introdotto la segregazione delle interfacce di lettura e scrittura:
  - `UserQueryRepositoryInterface`: Fornisce metodi ottimizzati per la lettura delle informazioni (es. `checkCredentials`, `findByUsername`).
  - `UserCommandRepositoryInterface`: Fornisce metodi per scrivere, aggiornare o eliminare informazioni.
* Entrambe sono implementate da `DbUserRepository`, che comunica con il database.
* Questo consente di cambiare completamente il motore di archiviazione di un'entità (es. passando a MongoDB o a un'API esterna) modificando solo il repository, senza alterare le entità o la logica del controller.

### C. Il Pattern Adapter nella Connessione
* Il database non viene istanziato in modo rigido nel codice. Il contenitore DI risolve l'interfaccia `DatabaseAdapter` utilizzando una factory in `dependencies.php` che legge la configurazione attiva del database.
* Rispetta rigorosamente il **Principio Aperto/Chiuso (OCP)**: il framework è chiuso alle modifiche interne ma aperto agli sviluppatori che desiderano aggiungere nuovi adattatori SQL semplicemente registrandoli nella configurazione esterna.

---

### Diagnosi Finale dell'Archeologo:
Parina Framework è un eccellente esempio di come un framework "pragmatico e statico" possa essere perfezionato in un design "enterprise-grade" (SOLID completo) senza sacrificare la velocità di esecuzione e mantenendo la piena compatibilità con il codice legacy tramite facciate dinamiche (`__callStatic`).
---

# 1. Implementazione dei Principi SOLID in Parina

SOLID è il pilastro che ha trasformato Parina da uno script monolitico accoppiato a un framework flessibile e modulare:

### **S – Single Responsibility Principle (SRP)**
Ogni classe in Parina ha **esattamente un motivo per cambiare**.
* **Prima**: Il modello `User` mappava il database e controllava lo stato della sessione HTTP (`$_SESSION`).
* **Ora**: Abbiamo separato la persistenza nel repository (`DbUserRepository`) e la gestione della sessione nel servizio `SessionAuth`.
* **Middleware**: Ogni middleware (`RateLimit`, `RequestSize`, `Csrf`) racchiude una specifica regola di sicurezza, mantenendo la classe `Kernel` focalizzata esclusivamente sul dispatching della richiesta HTTP.

### **O – Open/Closed Principle (OCP)**
Il codice è **aperto alle estensioni, ma chiuso alle modifiche**.
* **Esempio (Database)**: L'interfaccia `DatabaseAdapter` astrae i diversi driver SQL. Se uno sviluppatore desidera supportare Oracle o SQL Server, non ha bisogno di modificare il core di Parina. È sufficiente creare una classe che implementi `DatabaseAdapter` e associarla dinamicamente nel file esterno `config/dependencies.php`.

### **L – Liskov Substitution Principle (LSP)**
Qualsiasi sottoclasse deve essere in grado di sostituire la sua classe base senza alterare il corretto comportamento del programma.
* **Refactoring di `Response`**: L'interfaccia `Response.php` originale conteneva una firma del costruttore fissa. Ciò costringeva classi come `RedirectResponse` o `JsonResponse` a ricevere parametri di cui non avevano bisogno, violando l'LSP. Abbiamo rimosso il costruttore dall'interfaccia, consentendo al Kernel di gestire qualsiasi risposta (Html, Json, Redirect) in modo uniforme.

### **I – Interface Segregation Principle (ISP)**
I client non devono essere forzati a dipendere da interfacce che non utilizzano.
* **Segregazione dei Repository**: Abbiamo diviso l'accesso ai dati utente in due interfacce: `UserQueryRepositoryInterface` e `UserCommandRepositoryInterface`.
* **Utilizzo**: Il middleware `BasicAuth` ha solo bisogno di verificare le credenziali (Lettura). Invece di ricevere un repository con metodi come `save()` o `delete()`, inietta solo `UserQueryRepositoryInterface`, limitando le sue azioni al minimo indispensabile.

### **D – Dependency Inversion Principle (DIP)**
I moduli di alto livello no devono dipendere da moduli di basso livello; entrambi devono dipendere da astrazioni.
* **Contenitore DI con Reflection**: I controller e i middleware di Parina non istanziano più le loro dipendenze usando la parola chiave `new`. Dichiarano invece le interfacce nei loro costruttori (es: `ConfigInterface`, `Logger`, `TokenServiceInterface`, `CipherInterface`). Il componente `Container` analizza queste firme tramite reflection in fase di esecuzione e inetta le dipendenze risolte.

---

# 2. Implementazione del Pattern CQS (Command Query Segregation)

Il pattern CQS stabilisce che **un metodo deve essere un comando** (eseguire un'azione che muta lo stato del sistema) **o una query** (restituire dati al client senza effetti collaterali), ma mai entrambi.

In Parina Framework, il CQS è implementato a livello di dati e servizi:

```
                            [Controller / Handler]
                           /                       \
        Inietta Query     /                         \    Inietta Command
                         ▼                           ▼
      [UserQueryRepositoryInterface]      [UserCommandRepositoryInterface]
      * findById()                        * save()
      * findByUsername()                  * delete()
      * checkCredentials()
                         \                           /
                          ▼                         ▼
                      ┌─────────────────────────────────┐
                      │        DbUserRepository         │
                      │ (Implementa entrambe le         │
                      │           interfacce)           │
                      └─────────────────────────────────┘
```

### A. Livello Queries
Rappresentato dall'interfaccia `UserQueryRepositoryInterface`.
* **Metodi**: `findById()`, `findByUsername()`, `checkCredentials()`.
* **Comportamento**: Metodi puri di sola lettura. Interrogano il database SQL e restituiscono array associativi grezzi o null. **È severamente vietato alterare lo stato del sistema** (non scrivono nelle tabelle né inseriscono dati nella sessione globale `$_SESSION`).

### B. Livello Commands
Rappresentato dall'interfaccia `UserCommandRepositoryInterface`.
* **Metodi**: `save()`, `delete()`.
* **Comportamento**: Operazioni di scrittura/mutazione. Modificano i record fisici nel motore del database e segnalano il successo o il fallimento (`bool`).

### C. Conseguenza nella Progettazione dei Test
Grazie al CQS, in `LoginCheckHandlerTest.php`, il test simula solo la query (`checkCredentials()`) iniettando un mock leggero dell'interfaccia Query. Ciò consente ai test unitari di essere eseguiti istantaneamente in memoria, completamente isolati dal database SQLite fisico su disco.
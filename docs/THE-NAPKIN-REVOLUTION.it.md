# La rivoluzione del tovagliolo
## Una proposta per lo sviluppo di software sostenibile e resiliente

Di Nelson Rojas Nuñez

🇺🇸 [English](../THE-NAPKIN-REVOLUTION.md) | 🇪🇸 [Español](THE-NAPKIN-REVOLUTION.es.md) | 🇫🇷 [Français](THE-NAPKIN-REVOLUTION.fr.md) | 🇵🇹 [Português](THE-NAPKIN-REVOLUTION.pt.md) | 🇮🇹 **Italiano** | 🇩🇪 [Deutsch](THE-NAPKIN-REVOLUTION.de.md) | 🇨🇳 [简体中文](THE-NAPKIN-REVOLUTION.zh.md) | 🇯🇵 [日本語](THE-NAPKIN-REVOLUTION.ja.md)

---

Prima di tutto, mi scuso. Non voglio sembrare offensivo e non mi aspetto che vi sentiate presi in causa, perché come ha detto la grande saggia Veronica, i vostri strumenti vanno benissimo.

Per molto tempo sono stato dietro la tastiera a scrivere codice. Non appena sono entrato nel mondo del lavoro, mi sono ripromesso di adottare le migliori pratiche, di non commettere i 10 classici errori del linguaggio del momento, e sono andato avanti così per un bel pezzo.

All'improvviso ho deciso di dedicarmi allo sviluppo web, ma mi annoiavo parecchio a continuare a martellare sulla tastiera, usando le stesse teorie e ripetendo quello che facevo in ufficio. Volevo qualcosa di nuovo, qualcosa che fosse facile da seguire.

Ora, come chi chiede ottiene, mi sono imbattuto in molti strumenti miracolosi, generatori di codice che rendevano il processo divertente, con una certa sintonia, una scintilla.

Ho deciso quindi di tornare all'università per raccontare alle nuove generazioni che esistevano strumenti miracolosi... e mi hanno persino invitato diverse volte... anche fuori dalla mia città.

Subito dopo mi sono unito a una community in lingua spagnola, ho registrato alcuni video che ho caricato su YouTube e ho dedicato parte del mio tempo a spiegare le cose ai principianti—coloro che sono entrati nella programmazione per passione, per curiosità e per cercare soluzioni ai propri problemi.

Ho scritto diversi articoli per questa community, ma sentivo ancora il desiderio dell'insegnante: volevo mostrare alle nuove generazioni come si fanno le ruote, come si progettano e cosa c'è al loro interno.

Poiché non riuscivamo a trovarci d'accordo, ho deciso di mettermi in proprio. Ho creato diverse ruote simili a quelle della community che mi aveva dato tanto affetto, e ho cercato di crearne una copia, ma non era altrettanto buona. Dopotutto, loro erano anni luce avanti a me.

Facendo una pausa lungo il cammino, un giorno ho acquistato un corso sul PHP moderno e sui principi SOLID, e poi ho letto alcuni libri sull'architettura, il pattern CQRS e il codice manutenibile.

E così ho deciso di semplificare, di tornare a un'idea semplice che chiunque potesse comprendere.

A forza di elaborare e rielaborare, si è accesa la lampadina. Sono giunto alla conclusione che tutto ciò che facciamo non è altro che elaborare testo. Una bella novità, vero? Se vieni da Unix è una cosa che tutti capiscono. Ma la gente comune, quella che cammina per strada, che riceve la pubblicità dell'ennesimo miracolo tecnologico del momento... lo capisce?

Nasce così l'idea di racchiudere un intero framework in un diagramma che non superi le dimensioni di un tovagliolo di carta.

But non un giocattolo, perché il corso di Secure Code Warrior mi ha segnato profondamente. Qualcosa che fosse sicuro, di alta qualità, testabile e, soprattutto, comprensibile ed estensibile.

Sono molte restrizioni per un diagramma così piccolo, vero?

Primo passo, un concentratore di richieste: il front controller. Ha un nome carino, ma non è altro che un modo per dire al server di indirizzare tutto ciò che arriva verso lo stesso file (un semplice index.php).

Fatto, tutto il traffico arriva al concentratore.

Secondo passo, ogni richiesta ha un'unica responsabilità (la S di SOLID) e qui ho semplificato al massimo: un handler (gestore). Tutto qui.

Mi fermo un attimo, perché l'handler riceve ciò che arriva nella richiesta (request), elabora e risponde (testo), cioè genera la risposta (response).

E in teoria... questo è tutto!

Ma Nelson! Hai detto che ci sarebbero state sicurezza, estensibilità, qualità del codice e che sarebbe stato testabile!

Uno dei principi di SOLID riguarda l'uso delle Interfacce, che non sono altro che una sorta di progetto inutile, ma che presentano contratti (metodi) che chi le usa deve rispettare (implementare).

A cosa serve un'interfaccia allora? In parole semplici, e dal mio punto di vista, è la base per creare cose che hanno comportamenti simili, ma che li elaborano con alcune variazioni (polimorfismo).

E cosa permette un'interfaccia? Di forzare l'estendibilità e la standardizzazione!

Ora, ci sono alcuni elementi di base. Abbiamo bisogno di qualcosa che definisca le rotte che i nostri utenti possono raggiungere (un Router). Abbiamo bisogno di qualcosa che smisti ciò che arriva come richiesta in base all'elenco delle rotte o che attivi messaggi di errore (un dispatcher), che nel mio caso ho deciso di chiamare Kernel.

Se hai mai lavorato con .NET, Java o JSP, darai per scontato che esista una cosa chiamata Request e un'altra chiamata Response, che tradotte sono 'la richiesta del cliente' e 'la risposta che gli inviamo in risposta'.

Esempi di richiesta:
- Un link semplice
- Un link con parametri
- Un modulo con testi
- Uno con allegato

Esempi di risposta:
- Un codice di stato
- Testo normale
- HTML
- JSON
- XML
- Allegati
- Un reindirizzamento
- ecc., ecc., ecc.

E la sicurezza?
Un giorno mi sono reso conto che se una richiesta deve entrare, una serie di condizioni deve decidere se viene elaborata o meno. È la guardia del palazzo o, in gergo ingegneristico, un Middleware (è come l'hamburger nel sandwich): non puoi raggiungere l'altra fetta di pane senza passarci attraverso.

E cosa fa un middleware? Esamina la richiesta, esegue validazioni, modifica o pulisce il contenuto, permettendo di respingere vettori di attacco.

Ma le richieste possono avere una lista di guardiani nel mezzo che cercano di fermarle, o nessuno, tutto dipende da quanto è complessa o semplice la richiesta e dal luogo che si aspetta di raggiungere per ricevere risposta.

Di conseguenza, ogni volta che definisco una rotta dico: Guarda, se qualcuno porta una richiesta con l'indirizzo della porta A, e arriva lungo il sentiero giallo (GET), e quella porta non richiede alcuna guardia, allora non metto ostacoli e gli do una mappa per raggiungere la porta (l'handler che riceverà ed elaborerà la richiesta).

Se al contrario qualcuno porta una richiesta per la porta B, e questa porta richiede che tu abbia un biglietto dorato con la frase Wonka, allora metto un portiere che chiede e controlla il tuo biglietto dorato (un middleware). Se il portiere decide che puoi passare, allora gli do la mappa per raggiungere la porta B (l'handler che farà l'elaborazione e ti restituirà qualcosa).

Ogni handler riceverà la richiesta, con i dettagli necessari, ed elaborerà in base a ciò che deve fare, ma invierà sempre, sempre, sempre un tipo di risposta che deriva da un'altra Interfaccia: Response.

Ora le risposte sono estensibili, puoi creare il tipo di risposta di cui hai bisogno purché tu rispetti il contratto (i metodi dell'interfaccia Response).

Per evitare inutili sprechi di CPU, se il portiere decide che non soddisfi le condizioni ti elimina in un colpo solo, bruscamente e senza voltarti indietro. La richiesta e la sua storia si perdono nel tempo.

E quindi, e qui chiudo con la spiegazione sul tovagliolo, questi sono i passaggi sequenziali:

1. Richiesta (Request)
2. Front Controller
3. Caricamento Rotte (Metodo, Rotta, Handler responsabile, Elenco dei Middleware) (Route)
4. Dispatcher (Kernel) che utilizza l'elenco delle rotte per farle corrispondere alla richiesta. Se esistono dei middleware, li esegue in modo sequenziale. Se tutti danno il via libera, prosegue; se uno non è d'accordo, interrompe il circuito all'istante.
5. Risposta (Response)

Questo è Parina Framework, è un sistema lineare, rigido, estensibile, altamente testabile e straordinariamente amichevole con chi verrà dopo a fare manutenzione, perché ogni richiesta può ricevere risposta solo da un handler che ha un unico metodo: handle.

Ecco perché entra in un tovagliolo. È mentalmente facile da digerire e ogni volta che sentirai il bisogno di migliorare qualcosa, sicuramente la tua testa si abituerà sempre alla stessa conclusione: aggiungiamo un altro middleware, perché sono loro i responsabili del flusso, del blocco di input pericolosi, della limitazione del numero di richieste al secondo o del reindirizzamento di utenti non autorizzati o senza permessi.

Inoltre, quel piccolo pezzo di carta mi ha dato alcune lezioni inaspettate: un ciclo di richiesta/risposta ultraveloce e con un'impronta di memoria ridicola.

Su una macchina del 2003, con 512 MB di RAM e un processore Celeron, un server che eseguiva Parina era in grado di rispondere a più di 200 richieste al secondo, consumando appena circa 40 KB di memoria.

E così ho concluso che avevo uno strumento chiaro, sicuro, testabile e facile da comprendere a livello concettuale. E che grazie alle poche risorse di cui necessitava, poteva diventare uno strumento per le persone dimenticate del settore, per comunità con accessi internet lenti o con computer vecchi di oltre 15 anni.

Ti invito a scaricare questo progetto.

Un abbraccio dal sud del Cile!

# Die Servietten-Revolution
## Ein Entwurf für eine nachhaltige und widerstandsfähige Softwareentwicklung

Von Nelson Rojas Nuñez

🇺🇸 [English](../THE-NAPKIN-REVOLUTION.md) | 🇪🇸 [Español](THE-NAPKIN-REVOLUTION.es.md) | 🇫🇷 [Français](THE-NAPKIN-REVOLUTION.fr.md) | 🇵🇹 [Português](THE-NAPKIN-REVOLUTION.pt.md) | 🇮🇹 [Italiano](THE-NAPKIN-REVOLUTION.it.md) | 🇩🇪 **Deutsch** | 🇨🇳 [简体中文](THE-NAPKIN-REVOLUTION.zh.md) | 🇯🇵 [日本語](THE-NAPKIN-REVOLUTION.ja.md)

---

Zuallererst möchte ich mich entschuldigen. Ich will nicht beleidigend klingen und ich hoffe nicht, dass sich jemand angegriffen fühlt, denn wie die große Weise Veronica sagte: Eure Werkzeuge sind völlig in Ordnung.

Lange Zeit saß ich hinter der Tastatur und schrieb Code. Sobald ich in der Berufswelt Fuß gefasst hatte, nahm ich mir vor, Best Practices zu befolgen und nicht die 10 klassischen Fehler der jeweiligen Programmiersprache zu machen, und so verging eine ganze Weile.

Plötzlich packte mich das Interesse an der Webentwicklung, aber ich war es ziemlich leid, weiter auf die Tastatur einzuhämmern, dieselben Theorien anzuwenden und das zu wiederholen, was ich ohnehin im Büro tat. Ich wollte etwas Neues, etwas, das leicht zu verstehen war.

Nun, wer sucht, der findet: Ich stieß auf viele Wundertools und Codegeneratoren, die den Prozess unterhaltsam machten und für eine gewisse Harmonie und einen Funken Begeisterung sorgten.

Also beschloss ich, an die Universität zurückzukehren, um der neuen Generation von diesen Wundertools zu erzählen ... und ich wurde sogar mehrmals eingeladen ... sogar außerhalb meiner eigenen Stadt.

Gleich danach schloss ich mich einer spanischsprachigen Community an, nahm einige Videos auf, die ich auf YouTube hochlud, und widmete einen Teil meiner Zeit der Erklärung für Einsteiger—diejenigen, die aus purem Interesse und Neugier zur Programmierung kamen, um Lösungen für ihre eigenen Probleme zu finden.

Ich schrieb mehrere Artikel für diese Community, aber ich hatte immer noch diese Sehnsucht eines Lehrers: Ich wollte den neuen Generationen zeigen, wie man das Rad baut, wie man es entwirft und was in ihm steckt.

Da wir uns nicht einig wurden, beschloss ich, meinen eigenen Weg zu gehen. Ich entwickelte mehrere Räder, die denen der Community ähnelten, die mir so viel Herzlichkeit entgegengebracht hatte, und versuchte, eine Kopie zu erstellen, aber sie war nicht so gut. Schließlich waren sie mir um Jahre voraus.

Auf meinem Weg legte ich eine Pause ein, kaufte mir eines Tages einen Kurs über modernes PHP und die SOLID-Prinzipien und las dann einige Bücher über Architektur, das CQRS-Muster und wartungsfreundlichen Code.

Und dann beschloss ich, mich zu vereinfachen, zu einer einfachen Idee zurückzukehren, die jeder im Kopf behalten konnte.

Nach so viel Nachdenken ging mir ein Licht auf. Ich kam zu dem Schluss, dass alles, was wir tun, nichts anderes als Textverarbeitung ist. Ziemlich neuartig, oder? Wenn man aus der Unix-Welt kommt, versteht das jeder. Aber versteht das auch der Durchschnittsmensch, der auf der Straße herumläuft und mit Werbung für das neueste glänzende Wundertool überhäuft wird?

So entstand die Idee, ein ganzes Framework in ein Diagramm zu packen, das nicht größer als eine Papierserviette ist.

Aber kein Spielzeug, denn der Kurs von Secure Code Warrior hat mich tief geprägt. Es sollte sicher, qualitativ hochwertig, testbar und vor allem verständlich und erweiterbar sein.

Das sind ziemlich viele Einschränkungen für so ein kleines Diagramm, oder?

Erster Schritt, ein Anfrage-Konzentrator: der Front Controller. Er hat einen schönen Namen, ist aber nichts anderes als eine Methode, dem Server mitzuteilen, dass er alles, was ankommt, an dieselbe Datei (eine einfache index.php) weiterleiten soll.

Fertig, der gesamte Datenverkehr geht an den Konzentrator.

Zweiter Schritt: Jede Anfrage hat eine einzige Verantwortung (das S in SOLID), und hier habe ich mich extrem vereinfacht: ein Handler. Das ist alles.

Ich halte kurz inne, denn der Handler empfängt das, was in der Anfrage (request) ankommt, verarbeitet es und antwortet (Text), das heißt, er erzeugt die Antwort (response).

Und theoretisch ... war das schon alles!

Aber Nelson! Du hast gesagt, es gäbe Sicherheit, Erweiterbarkeit, Codequalität und dass es testbar sein würde!

Eines der SOLID-Prinzipien befasst sich mit der Verwendung von Interfaces (Schnittstellen). Diese sind im Grunde nichts anderes als eine Art nutzloser Bauplan, der jedoch Verträge (Methoden) enthält, die von denjenigen, die sie verwenden, erfüllt (implementiert) werden müssen.

Wozu dient also eine Schnittstelle? Einfach ausgedrückt und aus meiner Sicht ist sie die Basis, um Dinge mit ähnlichem Verhalten zu erstellen, die dieses Verhalten jedoch mit gewissen Abweichungen verarbeiten (Polymorphie).

Und was ermöglicht eine Schnittstelle? Sie erzwingt Erweiterbarkeit und Standardisierung!

Es gibt nun einige grundlegende Bausteine. Wir brauchen etwas, das die Routen definiert, die unsere Benutzer aufrufen können (ein Router). Wir brauchen etwas, das die eingehenden Anfragen gemäß der Routenliste verteilt oder Fehlermeldungen auslöst (einen Dispatcher), den ich in meinem Fall Kernel genannt habe.

Wenn Sie jemals mit .NET, Java oder JSP gearbeitet haben, werden Sie davon ausgehen, dass es etwas namens Request und etwas namens Response gibt, was übersetzt 'die Anfrage des Clients' und 'die Antwort, die wir zurücksenden' bedeutet.

Beispiele für eine Anfrage:
- Ein einfacher Link
- Ein Link mit Parametern
- Ein Formular mit Textfeldern
- Ein Formular mit Anhang

Beispiele für eine Antwort:
- Ein Statuscode
- Klartext
- HTML
- JSON
- XML
- Anhänge
- Eine Weiterleitung
- usw., usw., usw.

Und die Sicherheit?
Eines Tages wurde mir klar: Wenn eine Anfrage eingeht, muss eine Reihe von Bedingungen darüber entscheiden, ob sie verarbeitet wird oder nicht. Das ist die Palastwache oder in der Sprache der Softwaretechnik ein Middleware (wie das Patty im Sandwich) – man kommt nicht auf die andere Seite des Brötchens, ohne daran vorbeizugehen.

Und was macht ein Middleware? Es prüft die Anfrage, führt Validierungen durch, modifiziert oder bereinigt den Inhalt und hilft, Angriffsvektoren abzuwehren.

Anfragen können jedoch eine Liste von Wächtern in der Mitte haben, die versuchen, sie aufzuhalten, oder gar keine, je nachdem, wie komplex oder einfach die Anfrage ist und wo sie eine Antwort erwartet.

Dementsprechend sage ich jedes Mal, wenn ich eine Route definiere: Schau, wenn jemand eine Anfrage für Tür A bringt und über den gelben Pfad (GET) kommt, und diese Tür keinen Wächter benötigt, dann lege ich keine Steine in den Weg und gebe ihm einen Plan, um zur Tür zu gelangen (der Handler, der die Anfrage empfängt und verarbeitet).

Wenn andererseits jemand eine Anfrage für Tür B bringt und diese Tür ein goldenes Ticket mit der Aufschrift Wonka erfordert, dann stelle ich einen Pförtner auf, der nach deinem goldenen Ticket fragt und es überprüft (ein Middleware). Wenn der Pförtner entscheidet, dass du passieren darfst, dann gebe ich ihm den Plan für Tür B (der Handler, der die Verarbeitung übernimmt und dir etwas zurückgibt).

Jeder Handler empfängt die Anfrage mit den erforderlichen Details und verarbeitet sie entsprechend, sendet aber immer, immer, immer eine Antwort, die von einer anderen Schnittstelle abgeleitet ist: Response.

Antworten sind nun erweiterbar; Sie können die Art von Antwort erstellen, die Sie benötigen, solange Sie den Vertrag (die Methoden der Response-Schnittstelle) erfüllen.

Um unnötigen CPU-Aufwand zu vermeiden, wirst du, wenn der Pförtner entscheidet, dass du die Bedingungen nicht erfüllst, mit einem Schlag eliminiert, auf der Stelle und ohne zurückzublicken. Die Anfrage und ihre Geschichte gehen im Laufe der Zeit verloren.

Und so, und damit schließe ich die Erklärung auf der Serviette ab, sind dies die aufeinanderfolgenden Schritte:

1. Anfrage (Request)
2. Front Controller
3. Routen laden (Methode, Route, verantwortlicher Handler, Liste der Middlewares) (Route)
4. Dispatcher (Kernel), der die Routenliste verwendet, um sie mit der Anfrage abzugleichen. Wenn Middlewares vorhanden sind, führt er sie nacheinander aus. Wenn alle ihre Zustimmung geben, geht es weiter; wenn einer nicht einverstanden ist, unterbricht er den Vorgang sofort.
5. Antwort (Response)

Das ist Parina Framework, ein lineares, striktes, erweiterbares, hochgradig testbares und ungemein freundliches System für jeden, der es später warten muss, da jede Anfrage nur von einem Handler beantwortet werden kann, der eine einzige Methode hat: handle.

Deshalb passt es auf eine Serviette. Es ist gedanklich leicht verdaulich, und jedes Mal, wenn Sie das Gefühl haben, etwas verbessern zu müssen, wird sich Ihr Kopf sicherlich an dieselbe Schlussfolgerung gewöhnen: Fügen wir ein weiteres Middleware hinzu, denn diese sind für den Ablauf zuständig, blockieren gefährliche Eingaben, begrenzen übermäßige Anfragen pro Sekunde oder leiten nicht autorisierte Benutzer ohne Berechtigungen weiter.

Dieses kleine Stück Papier hat mir jedoch einige unerwartete Lektionen erteilt: ein ultraschneller Anfrage-Antwort-Zyklus mit einem auf ein Minimum reduzierten Speicherbedarf.

Auf einem Computer aus dem Jahr 2003 mit 512 MB RAM und einem Celeron-Prozessor konnte ein Server, auf dem Parina lief, mehr als 200 Anfragen pro Sekunde beantworten und verbrauchte dabei gerade einmal 40 KB Speicher.

Und so kam ich zu dem Schluss, dass ich ein klares, sicheres, testbares und konzeptionell leicht verständliches Werkzeug hatte. Und dass es dank der wenigen Ressourcen, die es benötigte, zu einem Werkzeug für die vergessenen Menschen der Branche werden könnte, für Gemeinschaften mit langsamem Internetzugang oder Computern, die älter als 15 Jahre sind.

Ich lade dich ein, dieses Projekt herunterzuladen.

Herzliche Grüße aus dem Süden Chiles!

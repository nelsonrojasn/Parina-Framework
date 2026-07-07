# La révolution de la serviette
## Une proposition pour le développement de logiciels durables et résilients

Par Nelson Rojas Nuñez

🇺🇸 [English](../THE-NAPKIN-REVOLUTION.md) | 🇪🇸 [Español](THE-NAPKIN-REVOLUTION.es.md) | 🇫🇷 **Français** | 🇵🇹 [Português](THE-NAPKIN-REVOLUTION.pt.md) | 🇮🇹 [Italiano](THE-NAPKIN-REVOLUTION.it.md) | 🇩🇪 [Deutsch](THE-NAPKIN-REVOLUTION.de.md) | 🇨🇳 [简体中文](THE-NAPKIN-REVOLUTION.zh.md) | 🇯🇵 [日本語](THE-NAPKIN-REVOLUTION.ja.md)

---

Tout d'abord, je m'excuse. Je ne veux pas paraître offensant, et je n'espère pas que vous vous sentiez visé, car comme l'a dit la grande sage Veronica, vos outils sont très bien.

Pendant longtemps, j'ai été derrière le clavier à écrire du code. Dès que je suis entré dans le monde professionnel, je me suis engagé à adopter les meilleures pratiques, à ne pas commettre les 10 erreurs classiques du langage du moment, et je suis resté ainsi pendant un bon moment.

Soudain, j'ai eu envie de me lancer dans le développement web, mais je m'ennuyais assez de continuer à marteler le clavier, en utilisant les mêmes théories et en répétant ce que je faisais au bureau. Je voulais quelque chose de nouveau, quelque chose de simple à suivre.

Puis, comme on dit, demandez et vous recevrez : je suis tombé sur de nombreux outils miracles, des générateurs de code qui rendaient le processus amusant, avec une certaine harmonie, une étincelle.

J'ai alors décidé de retourner à l'université pour dire à la nouvelle génération qu'il existait des outils miracles... et j'ai même été invité plusieurs fois... même en dehors de ma propre ville.

Juste après, j'ai rejoint une communauté hispanophone, j'ai enregistré quelques vidéos que j'ai publiées sur YouTube, et j'ai consacré une partie de mon temps à expliquer des choses aux débutants—ceux qui sont entrés dans la programmation par plaisir, par curiosité et en cherchant des solutions à leurs propres problèmes.

J'ai écrit plusieurs articles pour cette communauté, mais j'avais toujours cette envie d'enseigner, je voulais expliquer aux nouvelles générations comment on fabrique les roues, comment on les conçoit et ce qu'il y a à l'intérieur.

Comme nos chemins ne se croisaient pas, j'ai décidé de me lancer de mon côté. J'ai créé plusieurs roues similaires à celles que j'avais dans cette communauté qui m'avait témoigné tant d'affection, et j'ai essayé d'en faire une copie, mais elle n'était pas aussi bonne. Après tout, ils avaient des années d'avance sur moi.

En faisant une pause en chemin, j'ai acheté un jour un cours sur PHP moderne et les principes SOLID, puis j'ai lu quelques livres sur l'architecture, le pattern CQRS, et le code maintenable.

Et puis j'ai décidé de simplifier, de revenir à une idée simple, que chacun puisse garder en tête.

À force de traiter et de retraiter, la lumière s'est allumée. Je suis arrivé à la conclusion que tout ce que nous faisons n'est rien d'autre que du traitement de texte. Rien de bien nouveau, n'est-ce pas ? Si vous venez d'Unix, c'est quelque chose que tout le monde comprend. Mais le commun des mortels, celui qui marche dans la rue, qui reçoit la publicité pour le dernier outil miracle brillant à la mode... comprend-il cela ?

C'est ainsi qu'est née l'idée de faire tenir tout un framework dans un schéma ne dépassant pas la taille d'une serviette en papier.

Mais pas un jouet, car le cours de Secure Code Warrior m'a profondément marqué. Quelque chose de sécurisé, de haute qualité, testable et surtout compréhensible et extensible.

Cela fait beaucoup de contraintes pour un si petit schéma, n'est-ce pas ?

Première étape, un concentrateur de requêtes : le contrôleur frontal (front controller). Il a un joli nom, mais ce n'est rien de plus qu'un moyen de dire au serveur de diriger tout ce qui arrive vers le même fichier (un simple index.php).

Et voilà, tout le trafic arrive au concentrateur.

Deuxième étape, chaque requête a une unique responsabilité (le S de SOLID), et là je me suis simplifié à l'extrême : un handler (gestionnaire). C'est tout.

Je m'arrête un instant, car le handler reçoit ce qui arrive dans la requête (request), traite et répond (texte), c'est-à-dire qu'il génère la réponse (response).

Et en théorie... c'est tout !

Mais Nelson ! Tu as dit qu'il y aurait de la sécurité, de l'extensibilité, de la qualité de code et que ce serait testable !

L'un des principes de SOLID concerne l'utilisation d'Interfaces, qui ne sont rien d'autre qu'une sorte de plan inutile, mais qui contient des contrats (méthodes) et ceux qui les utilisent doivent les respecter (implémenter).

À quoi sert une interface alors ? Simplement, et de mon point de vue, c'est la base pour créer des choses qui ont des comportements similaires, mais qui traitent ce comportement avec quelques variations (polymorphisme).

Et que permet une interface ? Forcer l'extensibilité et la standardisation !

Maintenant, il y a quelques éléments de base. Nous avons besoin de quelque chose pour définir les routes que nos utilisateurs peuvent atteindre (un Router). Nous avons besoin de quelque chose pour distribuer ce qui arrive sous forme de requête en fonction de la liste des routes ou pour déclencher des messages d'erreur (un dispatcher), que j'ai décidé d'appeler Kernel.

Si vous avez déjà travaillé avec .NET, Java ou JSP, vous supposerez qu'il existe une chose appelée Request et une autre appelée Response, qui se traduisent par 'la requête du client' et 'la réponse que nous renvoyons'.

Exemples de requête :
- Un lien simple
- Un lien avec paramètres
- Un formulaire avec des champs texte
- Un formulaire avec pièce jointe

Exemples de réponse :
- Un code de statut
- Du texte brut
- HTML
- JSON
- XML
- Pièces jointes
- Une redirection
- etc., etc., etc.

Et la sécurité ?
Un jour, je me suis rendu compte que si une requête devait entrer, une liste de conditions devait décider si elle était traitée ou non. C'est la garde du palais ou, en langage d'ingénierie, un Middleware (c'est comme le steak du hamburger) : on ne peut pas arriver de l'autre côté du pain sans passer par lui.

Et que fait un middleware ? Il vérifie la requête, effectue des validations, modifie ou nettoie le contenu, et permet de neutraliser les vecteurs d'attaque.

Mais les requêtes peuvent avoir une liste de gardiens au milieu essayant de les arrêter, ou aucun, tout dépend de la complexité ou de la simplicité de la requête et de l'endroit où elle s'attend à recevoir une réponse.

Par conséquent, chaque fois que je définis une route, je me dis : Écoute, si quelqu'un apporte une requête avec l'adresse de la porte A, et vient par le chemin jaune (GET), et que cette porte n'a besoin d'aucun garde, alors je ne pose aucun obstacle et je lui donne une carte pour arriver à la porte (le handler qui recevra et traitera la requête).

Si, au contraire, quelqu'un apporte une requête avec l'adresse de la porte B, et que cette porte nécessite d'avoir une invitation dorée avec la phrase Wonka, alors je place un portier qui demande et vérifie votre invitation dorée (un middleware). Si le portier décide que vous pouvez passer, alors je lui remets la carte pour arriver à la porte B (le handler qui fera le traitement et vous renverra quelque chose).

Chaque handler recevra la requête, avec les détails nécessaires, et traitera selon ce qu'il a à faire, mais il enverra toujours, toujours, toujours un type de réponse qui dérive d'une autre interface : Response.

Maintenant, les réponses sont extensibles, vous pouvez créer le type de réponse dont vous avez besoin tant que vous respectez le contrat (les méthodes de l'interface Response).

Pour éviter des dépenses de CPU inutiles, si le portier décide que vous ne remplissez pas les conditions, il vous élimine d'un coup de plume, immédiatement et sans regarder en arrière. La requête et son histoire se perdent dans le temps.

Et donc, et je termine ici l'explication sur la serviette, voici les étapes séquentielles :

1. Requête (Request)
2. Contrôleur Frontal (Front Controller)
3. Charger les Routes (Méthode, Route, Handler responsable, Liste des Middlewares) (Route)
4. Dispatcher (Kernel) qui utilise la liste des routes pour la faire correspondre à la requête. S'il existe des middlewares, il les exécute de manière séquentielle. Si tous lui donnent la permission, il continue, et si l'un d'eux n'est pas d'accord, il coupe le circuit sur-le-champ.
5. Réponse (Response)

C'est cela Parina Framework, un système linéaire, strict, extensible, hautement testable et extrêmement bienveillant envers celui qui viendra le maintenir par la suite, car chaque requête ne peut être répondue que par un handler qui a une unique méthode : handle.

C'est pour cela qu'il tient sur une serviette. C'est mentalement simple à digérer, et chaque fois que vous sentirez le besoin d'améliorer quelque chose, votre esprit s'habituera sûrement à la même conclusion : ajoutons un autre middleware, car ce sont eux qui sont chargés du flux, de bloquer les entrées dangereuses, de limiter l'excès de requêtes par seconde, de rediriger les utilisateurs non autorisés ou sans permissions.

Cependant, ce petit morceau de papier m'a enseigné quelques leçons inattendues : un cycle requête/réponse ultra-rapide et une empreinte mémoire ridicule.

Sur une machine de l'année 2003, avec 512 Mo de RAM et un processeur Celeron, un serveur exécutant Parina pouvait répondre à plus de 200 requêtes par seconde, en consommant à peine environ 40 Ko de mémoire.

J'en ai donc conclu que j'avais un outil clair, sûr, testable et facile à comprendre d'un point de vue conceptuel. Et grâce aux faibles ressources qu'il nécessitait, il pourrait devenir un outil pour les oubliés de l'industrie, pour les communautés disposant d'un accès internet lent ou de ordinateurs de plus de 15 ans.

Je vous invite à télécharger ce projet.

Amitiés du sud du Chili !

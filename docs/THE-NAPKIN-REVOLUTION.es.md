# La revolución de la servilleta
## Una propuesta para el desarrollo de software sostenible y resiliente

Por Nelson Rojas Nuñez

🇺🇸 [English](../THE-NAPKIN-REVOLUTION.md) | 🇪🇸 **Español** | 🇫🇷 [Français](THE-NAPKIN-REVOLUTION.fr.md) | 🇵🇹 [Português](THE-NAPKIN-REVOLUTION.pt.md) | 🇮🇹 [Italiano](THE-NAPKIN-REVOLUTION.it.md) | 🇩🇪 [Deutsch](THE-NAPKIN-REVOLUTION.de.md) | 🇨🇳 [简体中文](THE-NAPKIN-REVOLUTION.zh.md) | 🇯🇵 [日本語](THE-NAPKIN-REVOLUTION.ja.md)

---

Primero que todo, me disculpo. No querré sonar ofensivo, no espero hacerte sentir aludido, porque como dijo la gran sabia Veronica, tus herramientas están bien.

Durante mucho tiempo estuve detrás del teclado escribiendo código. Apenas salí a producir al mundo laboral, me propuse abrazar las mejores prácticas, no cometer los 10 errores clásicos del lenguaje de turno, y así estuve durante un buen tiempo.

De pronto me dio por entrar en el desarrollo web, pero me aburria bastante de seguir dándole martillazos al teclado, usando las mismas teorias, y repitiendo lo mismo que hacia en la oficina. Quería algo nuevo, algo que fuera fácil de seguir.

Ahora, como el que pide recibe, me encontré con muchas herramientas milagrosas, con generadores de código que hacían que el proceso fuera divertido, que hubiera una cierta sintonía, chispa.

Decidí entonces volver a la academia para contarle a la nueva generación que habían herramientas milagrosas... y hasta me invitaron varias veces... incluso fuera de mi propia ciudad.

Acto seguido me uní a una comunidad en español, y grabé algunos videos que subí a Youtube, y dediqué parte de mi tiempo a explicarle cosas a los más nuevos, a los que entraron en la programación por el gusto, por inquietos y como buscadores de soluciones para sus propios problemas. 

Escribí varios artículos para esta comunidad, pero yo seguía teniendo ese anhelo del que enseña, quería contarle a las nuevas generaciones como se hacen las ruedas, como se diseñan, que hay dentro de ellas.

Como no llegamos a coincidir, decidí ir por mi cuenta, cree varias ruedas parecidas a la que tenía en la comunidad que tanto cariño me había entregado, y trate de crear una copia, pero no era tan buena. Más que mal, ellos me llevaban años de ventaja.

Haciendo una pausa en el camino, un día compré un curso sobre PHP moderno y el patrón SOLID, y luego leí algunos libros sobre arquitectura y el patron CQRS, y luego código mantenible.

Y entonces decidí simplificarme, volver a una idea sencilla, que cualquiera pudiera meter en su cabeza.

De tanto procesar y procesar, se me iluminó la ampolleta. Llegué a la conclusión de que todo lo que hacemos no es más que procesar texto. ¿Qué novedoso no? Si vienes de Unix eso es algo que todos entienden. Pero el común de la gente, esa que anda caminando por la calle, la que recibe la publicidad del milagro brillante de turno... ¿entiende eso?

Así nace la idea de meter todo un framework cuyo diagrama no superara el tamaño de una servilleta de papel.

Pero no un juguete, porque el curso de Secure Code Warrior me caló hondo. Algo que fuera seguro, de alta calidad, testeable y sobre todo entendible y extensible.

Son muchas restricciones para un pequeño diagrama ¿no?

Primer paso, un concentrador de peticiones: el controlador frontal. Que tiene un nombre bonito, pero no es más que una forma de decirle al servidor que haga pasar todo lo que llega hacia el mismo archivo (un simple index.php).

Listo, todo el tráfico viene al concentrador.

Segundo paso, cada petición tiene una única responsabilidad (la S de Solid) y aquí me simplifiqué en extremo: un handler o un manejador. Eso es todo.

Me detengo un poco, porque el handler recibe lo que venga en la petición (request), procesa y responde (texto), es decir, genera la respuesta (response)

Y en teoria... ¡eso es todo!

Pero Nelson! Dijiste que habría seguridad, que habría extensibilidad, que habría calidad de código y que sería testeable!

Uno de los principios de SOLID tiene relación con usar Interfaces, que no son nada más que una especie de plano inútil, pero que tiene contratos (metodos) y quienes los usan deben cumplir (implementar). 

¿Para qué es una interfaz entonces? En sencillo, y desde mi punto de vista, es la base para crear cosas que tiene comportamientos similares, pero que procesan ese comportamiento con algunas variaciones (polimorfismo)

¿Y qué permite una interfaz? ¡Forzar la extensibilidad y la estandarización!

Ahora, hay algunas piezas que son básicas. Necesitamos algo que se encargue de definir las rutas a las que pueden golpear nuestros usuarios (entiéndase por un Router). Necesitamos algo que se encargue de despachar lo que llega como petición según la lista de rutas o gatillar mensajes de error (entiéndase por un despachador) que en mi caso decidi llamar Kernel.

Si alguna vez has trabajado con .NET, Java o JSP, asumirás que existe una cosa llamada Request y otra llamada Response, que traducidos son "la petición del cliente" y "la respuesta que le enviamos de regreso".

Ejemplos de petición:
Un link simple
Un link con parámetros
Un formulario con textos
Uno con adjunto

Ejemplos de respuesta
Un código de estado
Un texto plano
Html
Json
Xml
Adjuntos
Una Redirección
etc, etc, etc.

¿Y la seguridad?
Un día me di cuenta que si una petición va a entrar, una lista de condiciones deben decidir si llega a procesarse o no. Es la guardia del palacio o en lenguaje de ingeniería, un Middleware (es como la hamburguesa del sandwich) no puedes llegar al otro lado del pan sin pasar por ella.

Y qué hace un middleware, revisa la petición, hace validaciones, modifica o limpia el contenido, permite despejar vectores de ataque. 

Pero las peticiones pueden tener una lista de guardianes en el medio intentando detenerla, o ninguno, todo de pende de lo complejo o simple que sea la solicitud y el lugar al que espera llegar para que le respondan.

En consecuencia, cada vez que defino una ruta le digo: Mira, si alguien trae una solicitud con la dirección de la la puerta A, y viene por el camino amarillo (GET), y esa puerta no necesita ningun guardia, entonces no pongo trabas y le doy un mapa para llegar a la puerta (el handler que recibirá y procesará la petición)

Si por el contrario, alguien trae una solicitud con la dirección de la puerta B, y esta puerta requiere que tengas una invitación dorada con la frase Wonka, entonces pongo un portero que pregunta y revisa tu invitación dorada (un middleware). Si el portero decide que puedes pasar, entonces le entrego el mapa para llegar a la puerta B (el handler que hara el proces y te devolverá algo)

Cada handler recibirá la solicitud, con los detalles necesarios y procesará de acuerdo a lo que le corresponda hacer, pero siempre, siempre, siempre, enviará un tipo de respuesta que deriva de otra Interfaz: Response.

Ahora las respuestas son extensibles, puedes crear el sabor de respuesta que necesites siempre que cumplas con el contrato (los métodos de la intefaz Response).

Para evitar gastos innecesarios de CPU, si el portero decide que no cumples con las condiciones te elimina de un plumazo, de golpe y sin mirar atrás. La solicitud y su historia se pierden en el tiempo.

Y entonces, y aquí cierro con la explicación en la servilleta, estos son los pasos secuenciales:

1. Solicitud (Request)
2. Concentrador Frontal (Front Controller)
3. Cargar Rutas (Método, Ruta, Handler Responsable, Lista de Middlewares) (Route) 
4. Despachador (Kernel) que usa la lista de rutas para hacerla coincidir con la petición. Si existen middlewares los ejecuta en forma secuencial. Si todos le dan permiso continúa, y si uno no está de acuerdo, corta el circuito en el acto. 
5. Salida (Response)

Esto es Parina Framework, es un sistema lineal, estricto, extensible, altamente testeable y tremendamente amable con quien vendrá después a mantenerlo, porque cada petición solo puede ser respondida por un handler que tiene un único método: handle.

Por eso cabe en una servilleta. Es mentalmente sencillo de digerir y cada vez que sientas que necesitas mejorar algo, seguro que tu cabeza se acostumbrará con la misma conclusión siempre: agreguemos otro middleware, porque ellos son los encargados del flujo, de detener entradas peligrosas, de cortar el exceso de peticiones por segundo, de redirigir a usuarios no autorizados, o sin permisos.


Ahora, ese pequeño trozo de papel me entregó algunas lecciones que no esperaba: un ciclo de petición / respuesta ultra rápido y con un footprint de memoria ridículo.

En una máquina del año 2003, con 512mb de ram y un procesador Celeron, un servidor corriendo Parina, podía responder a más de 200 peticiones por segundo, consumiendo apenas unos 40kb de memoria.

Y entonces concluí que tenía una herramienta clara, segura, testeable y fácil de entender en términos conceptuales. Y que gracias a los pocos recursos que requería, podría convertirse en una herramienta para la gente olvidada de la industria, para comunidades con accesos lentos a internet, o con computadoras de más de 15 años.

Te invito a descargar este proyecto.

Un abrazo desde el sur de Chile!
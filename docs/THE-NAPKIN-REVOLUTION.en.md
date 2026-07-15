# The Napkin Revolution
## A proposal for sustainable and resilient software development

By Nelson Rojas Nuñez

🇺🇸 **English** | 🇪🇸 [Español](THE-NAPKIN-REVOLUTION.es.md) | 🇫🇷 [Français](THE-NAPKIN-REVOLUTION.fr.md) | 🇵🇹 [Português](THE-NAPKIN-REVOLUTION.pt.md) | 🇮🇹 [Italiano](THE-NAPKIN-REVOLUTION.it.md) | 🇩🇪 [Deutsch](THE-NAPKIN-REVOLUTION.de.md) | 🇨🇳 [简体中文](THE-NAPKIN-REVOLUTION.zh.md) | 🇯🇵 [日本語](THE-NAPKIN-REVOLUTION.ja.md)

---

First of all, I apologize. I don't want to sound offensive, and I don't expect to make you feel targeted, because as the great wise Veronica said, your tools are fine.

For a long time, I was behind the keyboard writing code. As soon as I entered the professional world, I set out to embrace best practices, avoid committing the 10 classic mistakes of whatever language was in vogue, and I stayed like that for quite a while.

Suddenly, I felt like getting into web development, but I got quite bored of hammering away at the keyboard, using the same theories, and repeating the same things I did at the office. I wanted something new, something that was easy to follow.

Now, as they say, ask and you shall receive: I came across many miraculous tools, code generators that made the process fun, creating a certain harmony, a spark.

So I decided to return to academia to tell the new generation that there were miraculous tools... and I was even invited several times... even outside my own city.

Right after that, I joined a Spanish-speaking community, recorded some videos that I uploaded to YouTube, and dedicated part of my time to explaining things to beginners—those who entered programming out of sheer interest, curiosity, and as searchers for solutions to their own problems.

I wrote several articles for this community, but I still had that teacher's yearning: I wanted to show the new generations how wheels are made, how they are designed, and what is inside them.

Since we didn't align, I decided to go on my own. I created several wheels similar to the ones I had in the community that had given me so much affection, and I tried to create a copy, but it wasn't as good. After all, they were years ahead of me.

Pausing along the way, one day I bought a course on modern PHP and the SOLID principles, and then I read some books on architecture, the CQRS pattern, and maintainable code.

And then I decided to simplify, to return to a simple idea that anyone could hold in their head.

After so much processing, the lightbulb turned on. I came to the conclusion that everything we do is nothing more than processing text. Not very novel, is it? If you come from Unix, that's something everyone understands. But does the average person, the one walking down the street, receiving the advertising for the latest shiny miracle tool... do they understand that?

That is how the idea was born: to fit an entire framework into a diagram no larger than a paper napkin.

But not a toy, because the Secure Code Warrior course left a deep impression on me. It had to be secure, high quality, testable, and above all, understandable and extensible.

That's a lot of constraints for a small diagram, isn't it?

First step, a request concentrator: the front controller. It has a fancy name, but it is nothing more than a way to tell the server to route everything that arrives to the same file (a simple index.php).

Done, all traffic goes to the concentrator.

Second step, each request has a single responsibility (the S in SOLID), and here I simplified to the extreme: a handler. That's it.

Let's pause for a moment, because the handler receives whatever comes in the request, processes it, and responds (text)—that is, it generates the response.

And in theory... that's it!

But Nelson! You said there would be security, extensibility, code quality, and that it would be testable!

One of the SOLID principles is about using Interfaces, which are nothing more than a sort of useless blueprint, but one that has contracts (methods) that those who use them must fulfill (implement).

What is an interface for then? In simple terms, and from my point of view, it is the base to create things that have similar behaviors, but process that behavior with some variations (polymorphism).

And what does an interface allow? It forces extensibility and standardization!

Now, there are some basic components. We need something in charge of defining the routes our users can hit (a Router). We need something to dispatch what comes in as a request according to the list of routes or trigger error messages (a dispatcher), which in my case I decided to call the Kernel.

If you have ever worked with .NET, Java, or JSP, you will assume that there is something called Request and another called Response, which translate to 'the client's request' and 'the response we send back'.

Examples of a request:
- A simple link
- A link with parameters
- A form with text fields
- One with an attachment

Examples of a response:
- A status code
- Plain text
- HTML
- JSON
- XML
- Attachments
- A redirection
- etc., etc., etc.

And security?
One day I realized that if a request is going to enter, a list of conditions must decide whether it gets processed or not. It's the palace guard, or in engineering terms, a Middleware (like the patty in a sandwich)—you can't get to the other side of the bread without going through it.

And what does a middleware do? It reviews the request, runs validations, modifies or cleans the content, and helps neutralize attack vectors.

But requests can have a list of guardians in the middle trying to stop them, or none at all, depending on how complex or simple the request is and the destination where it expects an answer.

Consequently, every time I define a route, I say: Look, if someone brings a request for door A, and comes via the yellow path (GET), and that door doesn't need any guards, then I don't set any obstacles and I give them a map to the door (the handler that will receive and process the request).

If, on the contrary, someone brings a request for door B, and this door requires you to have a golden ticket with the phrase 'Wonka', then I put a doorkeeper who asks for and checks your golden ticket (a middleware). If the doorkeeper decides you can pass, then I give you the map to door B (the handler that will process it and return something to you).

Each handler will receive the request, with the necessary details, and process it accordingly, but it will always, always, always send a type of response that derives from another interface: Response.

Now responses are extensible; you can create whatever flavor of response you need as long as you fulfill the contract (the methods of the Response interface).

To avoid unnecessary CPU overhead, if the doorkeeper decides you don't meet the conditions, you are discarded immediately, in one fell swoop and without looking back. The request and its history are lost in time.

And so, and here I close with the explanation on the napkin, these are the sequential steps:

1. Request
2. Front Controller
3. Load Routes (Method, Route, Responsible Handler, List of Middlewares) (Route)
4. Dispatcher (Kernel) which uses the list of routes to match the request. If there are middlewares, it executes them sequentially. If all of them grant permission, it continues; if one disagrees, it cuts the circuit immediately.
5. Response

This is Parina Framework. It is a linear, strict, extensible, highly testable, and incredibly friendly system for whoever comes next to maintain it, because each request can only be answered by a handler that has a single method: handle.

That is why it fits on a napkin. It is mentally easy to digest, and every time you feel you need to improve something, your head will surely align with the same conclusion: let's add another middleware, because they are responsible for the flow, for stopping dangerous inputs, for limiting excessive requests per second, or for redirecting unauthorized users.

Now, that small piece of paper taught me some unexpected lessons: an ultra-fast request/response cycle with a ridiculously low memory footprint.

On a machine from the year 2003, with 512MB of RAM and a Celeron processor, a server running Parina could respond to more than 200 requests per second, consuming barely 40KB of memory.

And so I concluded that I had a tool that was clear, secure, testable, and easy to understand in conceptual terms. And thanks to the few resources it required, it could become a tool for the forgotten people of the industry, for communities with slow internet access or with computers over 15 years old.

I invite you to download this project.

A warm embrace from the south of Chile!

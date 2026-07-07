# A revolução do guardanapo
## Uma proposta para o desenvolvimento de software sustentável e resiliente

Por Nelson Rojas Nuñez

🇺🇸 [English](../THE-NAPKIN-REVOLUTION.md) | 🇪🇸 [Español](THE-NAPKIN-REVOLUTION.es.md) | 🇫🇷 [Français](THE-NAPKIN-REVOLUTION.fr.md) | 🇵🇹 **Português** | 🇮🇹 [Italiano](THE-NAPKIN-REVOLUTION.it.md) | 🇩🇪 [Deutsch](THE-NAPKIN-REVOLUTION.de.md) | 🇨🇳 [简体中文](THE-NAPKIN-REVOLUTION.zh.md) | 🇯🇵 [日本語](THE-NAPKIN-REVOLUTION.ja.md)

---

Antes de tudo, peço desculpas. Não quero parecer ofensivo, nem espero fazer você se sentir aludido, porque, como disse a grande sábia Veronica, suas ferramentas estão ótimas.

Durante muito tempo, estive atrás do teclado escrevendo código. Assim que entrei no mercado de trabalho, decidi adotar as melhores práticas, não cometer os 10 erros clássicos da linguagem da vez, e assim permaneci por um bom tempo.

De repente, decidi entrar no desenvolvimento web, mas me entediava bastante continuar martelando o teclado, usando as mesmas teorias e repetindo o mesmo que fazia no escritório. Eu queria algo novo, algo que fosse fácil de seguir.

Agora, como quem pede recebe, encontrei muitas ferramentas milagrosas, geradores de código que tornavam o processo divertido, trazendo uma certa sintonia, uma faísca.

Decidi, então, voltar à academia para contar à nova geração que existiam ferramentas milagrosas... e até fui convidado várias vezes... inclusive fora da minha própria cidade.

Em seguida, juntei-me a uma comunidade em espanhol, gravei alguns vídeos que enviei para o YouTube e dediquei parte do meu tempo a explicar coisas aos iniciantes—aqueles que entraram na programação por prazer, por curiosidade e como buscadores de soluções para os seus próprios problemas.

Escrevi vários artigos para essa comunidade, mas continuava com aquele anseio de quem ensina: queria mostrar às novas gerações como as rodas são feitas, como são projetadas e o que há dentro delas.

Como não chegamos a um acordo, decidi seguir por minha conta. Criei várias rodas parecidas com as que tinha na comunidade que tanto carinho havia me dado e tentei criar uma cópia, mas não era tão boa. Afinal, eles estavam anos à minha frente.

Fazendo uma pausa no caminho, um dia comprei um curso sobre PHP moderno e o padrão SOLID, e depois li alguns livros sobre arquitetura, o padrão CQRS e código de fácil manutenção.

E então decidi simplificar, voltar a uma ideia simples que qualquer um pudesse colocar na cabeça.

De tanto processar e processar, a luz acendeu. Cheguei à conclusão de que tudo o que fazemos não passa de processar texto. Que novidade, não? Se você vem do Unix, isso é algo que todos entendem. Mas as pessoas comuns, que andam pela rua, recebendo a publicidade do milagre brilhante da vez... entendem isso?

Assim nasce a ideia de colocar todo um framework cujo diagrama não superasse o tamanho de um guardanapo de papel.

Mas não um brinquedo, porque o curso da Secure Code Warrior me marcou profundamente. Algo que fosse seguro, de alta qualidade, testável e, acima de tudo, compreensível e extensível.

São muitas restrições para um diagrama tão pequeno, não é?

Primeiro passo, um concentrador de requisições: o controlador frontal (front controller). Tem um nome bonito, mas não passa de uma forma de dizer ao servidor para encaminhar tudo o que chega para o mesmo arquivo (um simples index.php).

Pronto, todo o tráfego vai para o concentrador.

Segundo passo, cada requisição tem uma única responsabilidade (o S do SOLID) e aqui simplifiquei ao extremo: um handler (manipulador). Só isso.

Paro um pouco, porque o handler recebe o que vier na requisição (request), processa e responde (texto), ou seja, gera a resposta (response).

E, em teoria... isso é tudo!

Mas Nelson! Você disse que haveria segurança, extensibilidade, qualidade de código e que seria testável!

Um dos princípios do SOLID relaciona-se com o uso de Interfaces, que nada mais são do que uma espécie de projeto inútil, mas que possui contratos (métodos) que quem os utiliza deve cumprir (implementar).

Para que serve uma interface, então? De forma simples, e sob a minha perspectiva, é a base para criar coisas que têm comportamentos semelhantes, mas que processam esse comportamento com algumas variações (polimorfismo).

E o que uma interface permite? Forçar a extensibilidade e a padronização!

Agora, existem algumas peças básicas. Precisamos de algo que se encarregue de definir as rotas que nossos usuários podem acessar (um Router). Precisamos de algo que se encarregue de despachar o que chega como requisição de acordo com a lista de rotas ou disparar mensagens de erro (um despachador), que no meu caso decidi chamar de Kernel.

Se você já trabalhou com .NET, Java ou JSP, assumirá que existe algo chamado Request e outro chamado Response, que traduzidos são 'a requisição do cliente' e 'a resposta que enviamos de volta'.

Exemplos de requisição:
- Um link simples
- Um link com parâmetros
- Um formulário com textos
- Um com anexo

Exemplos de resposta:
- Um código de status
- Um texto simples
- HTML
- JSON
- XML
- Anexos
- Um redirecionamento
- etc., etc., etc.

E a segurança?
Um dia percebi que, se uma requisição vai entrar, uma lista de condições deve decidir se ela chega a ser processada ou não. É a guarda do palácio ou, em linguagem de engenharia, um Middleware (é como o hambúrguer no sanduíche) — você não pode chegar ao outro lado do pão sem passar por ele.

E o que faz um middleware? Ele revisa a requisição, faz validações, modifica ou limpa o conteúdo, ajudando a neutralizar vetores de ataque.

Mas as requisições podem ter uma lista de guardiões no meio tentando pará-la, ou nenhum, tudo dependendo de quão complexa ou simples é a solicitação e do destino onde ela espera receber uma resposta.

Consequentemente, cada vez que defino uma rota, digo: Olha, se alguém trouxer uma solicitação com o endereço da porta A, e vier pelo caminho amarelo (GET), e essa porta não precisar de nenhum guarda, então não coloco obstáculos e entrego um mapa para chegar à porta (o handler que receberá e processará a requisição).

If, por outro lado, alguém trouxer uma solicitação com o endereço da porta B, e esta porta exigir que você tenha um convite dourado com a frase Wonka, então coloco um porteiro que pergunta e revisa seu convite dourado (um middleware). Se o porteiro decidir que você pode passar, então entrego o mapa para chegar à porta B (o handler que fará o processo e retornará algo).

Cada handler receberá a requisição, com os detalhes necessários, e processará de acordo com o que lhe cabe fazer, mas sempre, sempre, sempre enviará um tipo de resposta que deriva de outra interface: Response.

Agora as respostas são extensíveis; você pode criar o sabor de resposta que precisar, desde que cumpra o contrato (los métodos da interface Response).

Para evitar gastos desnecessários de CPU, se o porteiro decidir que você não cumpre as condições, ele o elimina de um golpe só, abruptamente e sem olhar para trás. A requisição e seu histórico se perdem no tempo.

E então, e aqui encerro a explicação no guardanapo, estes são os passos sequenciais:

1. Requisição (Request)
2. Concentrador Frontal (Front Controller)
3. Carregar Rotas (Método, Rota, Handler Responsável, Lista de Middlewares) (Route)
4. Despachador (Kernel) que usa a lista de rotas para fazê-la coincidir com a requisição. Se existirem middlewares, executa-os de forma sequencial. Se todos derem permissão, continua; se um não concordar, corta o circuito imediatamente.
5. Resposta (Response)

Isto é Parina Framework: é um sistema linear, estrito, extensível, altamente testável e incrivelmente amigável com quem virá depois mantê-lo, porque cada requisição só pode ser respondida por um handler que possui um único método: handle.

Por isso cabe em um guardanapo. É mentalmente simples de digerir e, toda vez que você sentir que precisa melhorar algo, com certeza sua cabeça se acostumará com la conclusão de sempre: vamos adicionar outro middleware, porque eles são os responsáveis pelo fluxo, por deter entradas perigosas, por limitar o excesso de requisições por segundo, ou por redirecionar usuários não autorizados ou sem permissões.

Além disso, aquele pequeno pedaço de papel me trouxe algumas lições inesperadas: um ciclo de requisição/resposta ultra rápido e com um consumo de memória ridículo.

Em uma máquina do ano de 2003, com 512 MB of RAM e um processador Celeron, um servidor rodando Parina conseguia responder a mais de 200 requisições por segundo, consumindo apenas cerca de 40 KB de memória.

E então concluí que tinha uma ferramenta clara, segura, testável e fácil de entender em termos conceituais. E que, graças aos poucos recursos que exigia, poderia se tornar uma ferramenta para as pessoas esquecidas da indústria, para comunidades com acesso lento à internet ou com computadores de mais de 15 anos.

Convido você a baixar este projeto.

Um abraço do sul do Chile!

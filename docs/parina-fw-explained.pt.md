---

# 1. Ideologia: Menos é Mais (The Napkin Revolution)

A ideologia do Parina não decorre de limitação, mas sim de **intencionalidade**. É governado por três princípios filosóficos:

* **KISS (Keep It Simple, Stupid) e YAGNI (You Aren't Gonna Need It)**: A maior parte da complexidade nos frameworks modernos é acidental. O Parina pergunta: *qual é a estrutura mínima necessária para construir uma aplicação web segura, de fácil manutenção e alto desempenho?* Seu consumo de RAM (~0.05 MB) e tempo de execução (~0.0007 segundos) são consequências dessa filosofia.
* **Explicidade sobre "Magia" (No-Magic)**: Evita ciclos de vida ocultos ou arquivos de configuração gigantes. O que se lê no código é exatamente o que é executado.
* **Desacoplamento Pragmático (SOLID)**: O Parina favorece a **Inversão de Controle (IoC)** e a **Segregação de Interfaces**. Através do seu contêiner DI e serviços baseados em interfaces, permite alterar as implementações concretas (bancos de dados, assinadores, autenticadores) sem tocar no núcleo do framework ou nos controladores.

---

# 2. Fluxo de Execução: O Ciclo de Vida da Requisição

O fluxo do Parina é um **pipeline sequencial e síncrono** que implementa o padrão **Front Controller**:

```
Requisição HTTP
   │
   ▼
[public/index.php] (Front Controller)
   │
   ├──> Carrega Autoloader e helper global h()
   ├──> Instancia [Container] e carrega config/dependencies.php (IoC)
   ├──> Inicializa [Db] com o [DatabaseAdapter] resolvido dinamicamente (OCP)
   └──> Inicializa [Router] e registra config/routes.php
   │
   ▼
[Kernel] (Dispatcher)
   │
   ├──> Captura superglobais em um objeto [Request] (Value Object)
   │
   ├──> [Pipeline de Middlewares] (Filtros de interceptação)
   │       └──> Se um middleware retornar [Response] (ex: erro 401), o fluxo é interrompido.
   │
   ├──> [Container::get()] (Resolução DI baseada em Reflection)
   │       └──> Instancia o Handler resolvendo suas dependências recursivamente.
   │
   ├──> [Handler::handle(Request)] (Controller)
   │       └──> Executa a lógica e retorna um objeto que implementa [Response]
   │
   ▼
[Kernel::send()] (Emissão)
   └──> Envia cabeçalhos HTTP, código de status e faz echo do conteúdo.
```

---

# 3. Segurança: Arquitetura Defensiva e Interfaces Puras

A segurança do Parina é organizada em camadas e executada principalmente no pipeline de middlewares, garantindo que o tráfego malicioso nunca chegue aos controladores de negócios:

* **Autenticação Stateless**:
  * **JWT**: O middleware [JwtAuth](file:///home/nelson/repos/Parina-Framework/src/Shared/Middlewares/JwtAuth.php) extrai tokens usando o helper `$request->bearerToken()`, valida-os através do `TokenServiceInterface` e injeta a identidade nos atributos locais da requisição (`$request->setAttribute('user_id')`).
  * **Basic Auth**: O middleware [BasicAuth](file:///home/nelson/repos/Parina-Framework/src/Shared/Middlewares/BasicAuth.php) valida credenciais usando `UserQueryRepositoryInterface::checkCredentials()`, o que evita a criação desnecessária de cookies e sessões de servidor em APIs REST.
* **Assinatura Criptográfica de URLs**: O middleware [ValidateHash](file:///home/nelson/repos/Parina-Framework/src/Shared/Middlewares/ValidateHash.php) injeta `CipherInterface` para processar assinaturas temporárias (TTL) de links confidenciais, validando a integridade do link antes de rotear a requisição.
* **Controle de Acesso (ACL)**: Baseado na interface `AclInterface`, permite validar permissões dinâmicas e injetar facilmente implementações mock no ambiente de teste.
* **Prevenção de XSS e CSRF**:
  * **CSRF**: Um token injetado em formulários e validado em middlewares protege contra falsificação de requisições.
  * **XSS**: O helper global `h($variable)` atua como um higienizador de escape nativo em views PHP (`htmlspecialchars`).

---

# 4. Acesso e Modificação de Dados: O Duplo Estrato de Persistência

O Parina oferece flexibilidade ao desenvolvedor ao permitir duas abordagens de persistência:

### A. Persistência por Repositório (CQS - Command Query Segregation)
Esta é a abordagem moderna e limpa do framework. Divide as operações em interfaces de consulta e gravação:
* **Leitura (`UserQueryRepositoryInterface`)**: Retorna dados planos ou objetos de valor específicos. Otimizado para consultas complexas e velocidade.
* **Escrita (`UserCommandRepositoryInterface`)**: Persiste e modifica o estado do sistema.
* **DbUserRepository**: Implementação que centraliza o acesso SQL.
* *Benefício*: Desacoplamento do banco de dados da sessão HTTP (SRP) e testes unitários 100% em memória usando mocks.

### B. Persistência por Active Record (`BaseModel`)
* Classes como `User` herdam diretamente de `BaseModel`. Elas mapeiam propriedades de classe para colunas de tabela e fornecem métodos CRUD diretos (`all()`, `find()`, `create()`).
* É uma opção ideal para prototipagem rápida e operações CRUD muito simples.

### C. Abstração do Driver (Padrão Adapter)
* O mecanismo de banco de dados final (SQLite, MySQL ou PostgreSQL) é injetado dinamicamente através da interface `DatabaseAdapter` registrada no contêiner.
* Está em conformidade com o **Princípio Aberto/Fechado (OCP)**: se você precisar migrar bancos de dados ou adicionar um mecanismo de banco de dados não suportado (como o SQL Server), só precisará criar uma classe que implemente `DatabaseAdapter` e registrá-la em `dependencies.php`, sem alterar uma única linha do código interno do framework.

---

### Diagnóstico Final do Arquiteto:
O Parina Framework prova que a extrema simplicidade não está em conflito com bons padrões de projeto. Sua arquitetura moderna em desacoplamento de dependências (DIP) e segregação de interfaces de dados (CQS) torna-o um mecanismo de aplicação PHP ágil, seguro e extremamente fácil de testar.
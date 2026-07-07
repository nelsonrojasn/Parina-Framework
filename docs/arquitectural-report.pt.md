---

# 1. O Fluxo de Execução: Da Requisição à Resposta

O fluxo do Parina é um **ciclo de vida linear, síncrono e altamente previsível**. Ele segue o padrão **Front Controller** em um pipeline sequencial:

```
Requisição HTTP
   │
   ▼
[public/index.php] ──(1. Bootstrap)──> Carrega Autoload, Helpers globais (h())
   │
   ▼
[Container] ─────────(2. Configuração)──> Carrega config/dependencies.php (DI)
   │
   ▼
[Db::init()] ────────(3. Camada de Dados)──> Injeta DatabaseAdapter resolvido
   │
   ▼
[Router] ────────────(4. Roteamento)──> Busca correspondência de método e URI (regex params)
   │
   ▼
[Kernel] ────────────(5. Despacho)──> Converte para Request (Value Object)
   │
   ├─> [Middlewares] ──(6. Pipeline de Filtros)──> (Corta fluxo se retornar Response)
   │
   ▼
[Container::get()] ──(7. Resolução DI)──> Instancia o Handler injetando dependências
   │
   ▼
[Handler::handle()] ─(8. Lógica do Controlador)──> Retorna objeto Response
   │
   ▼
[Kernel::send()] ────(9. Renderização e Envio)──> Emite cabeçalhos HTTP, status e body echo
```

### Descoberta Arqueológica no Fluxo:
* Na camada inicial, o Kernel instanciava os middlewares e handlers diretamente fazendo `new $className()`.
* Na camada moderna, o Kernel delega isso ao `Container`. Isso permite que qualquer controlador declare quais interfaces precisa no seu construtor (injeção de dependências) e o framework as resolva por reflexão recursiva antes de executar a requisição.

---

# 2. Segurança e Acesso: As Muralhas do Sistema

A segurança do Parina evoluiu da "segurança por acoplamento" (onde as camadas se misturavam) para uma arquitetura defensiva baseada em **interfaces e segregação**:

### A. Autenticação e Controle de Sessão
* **O fóssil antigo**: O modelo de banco de dados `User` manipulava diretamente a sessão global `$_SESSION['user_id'] = ...`. Isso viola os princípios de arquitetura limpa, pois o banco de dados não deve saber que existe um cookie HTTP ou uma sessão web.
* **A estrutura moderna**: Introduzimos `AuthInterface` e `SessionAuth`. Agora, o login é um serviço injetável. O middleware de Auth e o `LoginCheckHandler` simplesmente perguntam ao serviço `isLoggedIn()` ou chamam `login()`. Nos testes, podemos simular que um usuário está autenticado sem criar sessões PHP reais em disco.

### B. Controle de Acesso (ACL)
* **O fóssil antigo**: A classe `Acl` continha um método `setMockHasPermissions` para alterar seu estado a partir de testes unitários. Isso é um code smell de teste no código de produção.
* **A estrutura moderna**: O middleware `Acl` recebe uma `AclInterface` por construtor. Toda a lógica estática e os atalhos para testes foram erradicados do código de produção da `Acl`. Os testes usam mocks nativos do PHPUnit.

### C. Defesas de Entrada e Saída (CSRF e XSS)
* **CSRF (Cross-Site Request Forgery)**: Gerenciado pelo token `Csrf::token()`, injetado em formulários e validado pelo middleware de CSRF.
* **XSS (Cross-Site Scripting)**: A incorporação do helper global `h()` no autoloader permite que os templates PHP escapem caracteres HTML perigosos (`htmlspecialchars($value, ENT_QUOTES)`) de forma simples, garantindo que a saída visual não execute código JavaScript injetado por terceiros.

---

# 3. Acesso e Modificação de Dados: O Duplo Estrato de Persistência

É na camada de dados onde a transição arqueológica do framework fica mais evidente:

```
                  ┌──────────────────────────────────────────┐
                  │                 CLIENTE                  │
                  └────────────────────┬─────────────────────┘
                                       │
                    ┌──────────────────┴──────────────────┐
                    ▼                                     ▼
        [Active Record (Legado)]                [CQS (Moderno)]
        Utiliza BaseModel estático              Usa interfaces segregadas
        e instanciação direta.                  para Leitura e Escrita.
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
                              [DatabaseAdapter] (Interface)
                                       │
                        ┌──────────────┼──────────────┐
                        ▼              ▼              ▼
                 [SqliteAdapter] [MySqlAdapter] [PostgreSqlAdapter]
```

### A. O Estrato Active Record (`BaseModel`)
* Os modelos herdam de `BaseModel` e mapeiam de forma 1-para-1 para tabelas SQLite/MySQL.
* É uma abordagem ideal para desenvolvimento hiper-rápido (KISS), mas mistura a representação dos dados com os métodos de armazenamento (violando SRP).

### B. O Estrato CQS (Command Query Segregation)
* Para quebrar o acoplamento do Active Record, introduzimos a segregação de interfaces de leitura e escrita:
  - `UserQueryRepositoryInterface`: Fornece métodos otimizados para ler informações (ex. `checkCredentials`, `findByUsername`).
  - `UserCommandRepositoryInterface`: Fornece métodos para escrever, atualizar ou excluir informações.
* Ambos são implementados pelo `DbUserRepository`, que se comunica com o banco de dados.
* Isso permite alterar completamente o mecanismo de armazenamento de uma entidade (ex: para MongoDB ou uma API externa) modificando apenas o repositório, sem alterar as entidades nem a lógica do controlador.

### C. O Padrão Adapter na Conexão
* O banco de dados não é instanciado de forma fixa. O contêiner DI resolve a interface `DatabaseAdapter` usando uma factory em `dependencies.php` que lê a configuração do banco de dados ativa.
* Cumpre estritamente o **Princípio Aberto/Fechado (OCP)**: o framework está fechado para modificações internas, mas aberto para que os desenvolvedores adicionem novos adaptadores SQL simplesmente registrando-os na configuração externa.

---

### Diagnóstico Final do Arqueólogo:
O Parina Framework é um excelente exemplo de como um framework "pragmático e estático" pode ser refinado para um design de "nível empresarial" (SOLID completo) sem sacrificar a velocidade de execução e mantendo total compatibilidade com o código legado através de fachadas dinâmicas (`__callStatic`).
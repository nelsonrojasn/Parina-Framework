---

# 1. Implementação dos Princípios SOLID no Parina

SOLID é o pilar que transformou o Parina de um script monolítico acoplado em um framework flexível e modular:

### **S – Single Responsibility Principle (Princípio da Responsabilidade Única)**
Cada classe no Parina tem **uma única razão para ser alterada**.
* **Antes**: O modelo `User` mapeava o banco de dados e controlava o estado da sessão HTTP (`$_SESSION`).
* **Agora**: Separamos a persistência no repositório (`DbUserRepository`) e a gestão da sessão no serviço `SessionAuth`.
* **Middlewares**: Cada middleware (`RateLimit`, `RequestSize`, `Csrf`) encapsula uma regra de segurança específica, mantendo a classe `Kernel` focada exclusivamente no despacho da requisição HTTP.

### **O – Open/Closed Principle (Princípio do Aberto/Fechado)**
O código está **aberto para extensão, mas fechado para modificação**.
* **Exemplo (Banco de Dados)**: A interface `DatabaseAdapter` abstrai os diferentes drivers SQL. Se um desenvolvedor quiser suportar Oracle ou SQL Server, não precisa modificar o núcleo do Parina. Ele simplesmente cria uma classe implementando `DatabaseAdapter` e a vincula dinamicamente no arquivo externo `config/dependencies.php`.

### **L – Liskov Substitution Principle (Princípio da Substituição de Liskov)**
Qualquer subclasse deve ser capaz de substituir sua classe base sem alterar o comportamento correto do programa.
* **Refatoração de `Response`**: A interface `Response.php` original continha uma assinatura de construtor fixa. Isso forçou classes como `RedirectResponse` ou `JsonResponse` a receber parâmetros que não precisavam, violando o LSP. Removemos o construtor da interface, permitindo que o Kernel trate qualquer resposta (Html, Json, Redirect) uniformemente.

### **I – Interface Segregation Principle (Princípio da Segregação de Interfaces)**
Os clientes não devem ser forçados a depender de interfaces que não utilizam.
* **Segregação de Repositórios**: Dividimos o acesso aos dados do usuário em duas interfaces: `UserQueryRepositoryInterface` e `UserCommandRepositoryInterface`.
* **Uso**: O middleware `BasicAuth` apenas precisa verificar credenciais (Leitura). Em vez de receber um repositório com métodos como `save()` ou `delete()`, ele apenas injeta `UserQueryRepositoryInterface`, limitando suas ações ao mínimo.

### **D – Dependency Inversion Principle (Princípio da Inversão de Dependência)**
Módulos de alto nível não devem depender de módulos de baixo nível; ambos devem depender de abstrações.
* **Contêiner DI com Reflection**: Os controladores e middlewares do Parina não instanciam mais suas dependências usando a palavra-chave `new`. Em vez disso, declaram interfaces em seus construtores (ex: `ConfigInterface`, `Logger`, `TokenServiceInterface`, `CipherInterface`). O componente `Container` analisa essas assinaturas via reflexão em tempo de execução e injeta as dependências resolvidas.

---

# 2. Implementação do Padrão CQS (Command Query Segregation)

O padrão CQS estabelece que **um método deve ser um comando** (realizar uma ação que altera o estado do sistema) **ou uma consulta** (retornar dados ao cliente sem efeitos colaterais), mas nunca ambos.

No Parina Framework, o CQS é implementado na camada de dados e serviços:

```
                            [Controlador / Handler]
                           /                       \
        Injeta Query      /                         \    Injeta Command
                         ▼                           ▼
      [UserQueryRepositoryInterface]      [UserCommandRepositoryInterface]
      * findById()                        * save()
      * findByUsername()                  * delete()
      * checkCredentials()
                         \                           /
                          ▼                         ▼
                      ┌─────────────────────────────────┐
                      │        DbUserRepository         │
                      │ (Implementa ambas as interfaces)│
                      └─────────────────────────────────┘
```

### A. Camada de Consultas (Queries)
Representada pela interface `UserQueryRepositoryInterface`.
* **Métodos**: `findById()`, `findByUsername()`, `checkCredentials()`.
* **Comportamento**: Métodos puros de somente leitura. Eles consultam o banco de dados SQL e retornam arrays associativos brutos ou nulos. **Eles são estritamente proibidos de alterar o estado do sistema** (não gravam em tabelas nem injetam dados na sessão global `$_SESSION`).

### B. Camada de Comandos (Commands)
Representada pela interface `UserCommandRepositoryInterface`.
* **Métodos**: `save()`, `delete()`.
* **Comportamento**: Operações de gravação/mutação. Eles modificam registros físicos no banco de dados e relatam sucesso ou falha (`bool`).

### C. Consequência no Design de Testes
Graças ao CQS, em `LoginCheckHandlerTest.php`, o teste apenas simula a consulta (`checkCredentials()`) injetando um mock leve da interface Query. Isso permite que os testes unitários sejam executados instantaneamente na memória, completamente isolados do banco de dados físico SQLite em disco.
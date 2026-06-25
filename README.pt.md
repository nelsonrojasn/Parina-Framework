# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

🇺🇸 [English](README.md) | 🇪🇸 [Español](README.es.md) | 🇫🇷 [Français](README.fr.md) | 🇵🇹 **Português**

### *Altiplano Edition: Menos é mais. O framework web para pensar com clareza.*

---

## 💡 O que é o Parina?

O Parina é um micro-framework minimalista para aplicações PHP modernas. Ele fornece apenas a estrutura necessária para construir aplicações com clareza, controle e desempenho máximo.

---

## 🌄 Filosofia

**Clareza sobre abstração. Controle sobre conveniência.**

O Parina foca em:
* **Design explícito:** Sem mágica, sem ciclos de vida ocultos.
* **Sobrecarga mínima:** Cada byte e milissegundo conta.
* **Fluxo previsível:** O que você vê é exatamente o que é executado.

---

## 🧱 Arquitetura em 10 Linhas

1. Uma requisição entra através de um controlador frontal (Front Controller).
2. Passa pelo pipeline de middlewares.
3. Os middlewares podem bloquear ou permitir a requisição.
4. Chega ao manipulador (Handler) registrado.
5. O Handler executa a lógica de negócios.
6. Retorna uma resposta padrão (Response).
7. Sem mágica complexa.
8. Sem ciclos de vida ocultos do framework.
9. Sem abstrações desnecessárias.
10. Apenas uma execução clara e linear.

---

## 🔄 Ciclo de Vida da Requisição

```
[ Request ] ───> [ Pipeline de Middlewares ] ───> [ Handler ]
                            │                          │
                            │ (Retorna Response)       │ (Retorna Response)
                            ▼                          ▼
                      [ Response ] <───────────────────┘
```

### Modelo de Middleware
Cada camada de middleware segue uma regra binária simples:
* **Retorna `Response`** → Para a execução e emite a resposta.
* **Retorna `null`** → Continua para a próxima camada.

#### Exemplo de Middleware
```php
namespace Parina\Shared\Middlewares;

use Parina\Core\Request;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\ErrorResponse;

class SimpleAuth implements Middleware
{
    public function handle(Request $request): ?Response
    {
        if (!isset($_SESSION['user'])) {
            return new ErrorResponse("Não autorizado", 401);
        }
        return null; // Continua para a próxima camada
    }
}
```

---

## 🔒 Segurança

A segurança é prioritária e reside exatamente onde deve: no pipeline de middlewares.

* Limitação de requisições (Rate limiting)
* Validação do tamanho da requisição
* Proteção CSRF
* Política de mesma origem (CORS)
* Autenticação (Basic / JWT)
* Autorização (ACL)

---

## ⚡ Desempenho

Projetado para sobrecarga mínima e precisão de microssegundos:

* **~0.0007 segundos** por execução de requisição.
* **~0.05 MB** de uso de memória RAM.
* Totalmente compatível com Opcache.

---

## 🚀 Exemplo (Ponto de Entrada / Bootstrapping)

```php
// public/index.php
use Parina\Core\Router;
use Parina\Core\Kernel;
use Parina\Modules\Public\HomeHandler;

require_once '../vendor/autoload.php';

$router = new Router();
$router->add('GET', '/', HomeHandler::class);

$kernel = new Kernel($router);
$kernel->run();
```

## 🏠 Exemplo de Handler Mínimo
```php
namespace Parina\Modules\Public;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Request;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\View;

class HomeHandler implements Handler
{
    public function handle(Request $request): Response
    {
        $content = View::renderWithLayout("Public/Views/home", "default", ['title' => 'Parina']);
        return new HtmlResponse($content, 200);
    }
}
```

## 🖼 Exemplo de View Mínima
```php
<!-- Modules/Public/Views/home.php -->
<h1><?= $title ?></h1>
<p>Bem-vindo ao Parina Framework.</p>
```

---

## 🧪 Testes Incluídos

O Parina é desenvolvido com PHPUnit, focando em cobertura completa.

```
tests/
 ├── KernelTest.php
 ├── RouterTest.php
 ├── HandlerTest.php
 └── Handlers/FakeHandler.php
```

---

## 🧘 Por que o Parina existe

A maior parte de la complexidade no software é acidental. O Parina pergunta:

Qual é a menor estrutura que ainda funciona de forma correta, segura e rápida?

O Parina não é minimalista por limitação. É minimalista por intenção. Ele remove tudo o que você realmente não precisa.

---

## 📦 Implantação & Instalação

### Implantação em Produção
Para layout de diretórios, permissões e dicas de produção, consulte [DEPLOY.md](DEPLOY.md).

### Início Rápido / Instalação Local

Para executar o framework localmente usando o servidor de desenvolvimento embutido do PHP:

```bash
git clone https://github.com/nelsonrojasn/Parina-Framework.git
cd Parina-Framework
composer install
php -S localhost:8000 -t public
```

### Gerenciador de Dependências
Em breve no Packagist.

---

## 🪶 Licença

Licença MIT.

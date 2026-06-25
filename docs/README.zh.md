# Parina 框架
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

🇺🇸 [English](../README.md) | 🇪🇸 [Español](README.es.md) | 🇫🇷 [Français](README.fr.md) | 🇵🇹 [Português](README.pt.md) | 🇮🇹 [Italiano](README.it.md) | 🇩🇪 [Deutsch](README.de.md) | 🇦ym [Aymara](README.ay.md) | 🦙 [Quechua](README.qu.md) | 🇨🇳 **简体中文** | 🇯🇵 [日本語](README.ja.md)

### *阿尔蒂普拉诺版 —— 极简即是极致。旨在清晰思考的 Web 微框架。*

---

## 💡 什么是 Parina？

Parina 是一个面向现代 PHP 应用的极简微框架。它提供了恰到好处的结构，让您在构建应用时保持清晰的思维、高度的控制力以及极致的性能。

---

## 🌄 哲学

**清晰胜于抽象。控制胜于便利。**

Parina 专注于：
* **显式设计：** 无魔法，无隐藏生命周期。
* **极低开销：** 每一字节、每一毫秒都至关重要。
* **可预测的流程：** 你所看到的，就是实际执行的。

---

## 🧱 10 行代码说清架构

1. 请求通过前端控制器进入。
2. 穿过中间件管道。
3. 中间件可以阻止请求或允许通过。
4. 到达注册的处理器 (handler)。
5. 处理器执行核心逻辑。
6. 返回标准响应。
7. 无繁重的魔法。
8. 没有隐藏的框架生命周期。
9. 没有不必要的抽象。
10. 只有清晰、线性的执行流程。

---

## 🔄 请求生命周期

```
[ Request ] ───> [ 中间件管道 ] ───> [ 处理器 ]
                       │                   │
                       │ (返回响应)        │ (返回响应)
                       ▼                   ▼
                 [ Response ] <────────────┘
```

### 中间件模型
每一层中间件都遵循一个简单的二元规则：
* **返回 `Response`** → 停止执行并输出响应。
* **返回 `null`** → 继续执行下一层。

#### 中间件示例
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
            return new ErrorResponse("未授权", 401);
        }
        return null; // 进入下一层
    }
}
```

---

## 🔒 安全性

安全在中间件管道中处于第一优先级，并严格履行其职责：

* 速率限制 (Rate limiting)
* 请求大小验证
* CSRF 防护
* 同源策略 (CORS)
* 身份验证 (Basic / JWT)
* 授权控制 (ACL)

---

## ⚡ 性能

为极低开销和微秒级精度而设计：

* 每次请求执行约 **0.0007 秒**。
* 内存占用约 **0.05 MB** RAM。
* 对 Opcache 极其友好。

---

## 🚀 示例 (引导启动 / Bootstrapping)

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

## 🏠 极简处理器示例
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

## 🖼 极简视图示例
```php
<!-- Modules/Public/Views/home.php -->
<h1><?= $title ?></h1>
<p>欢迎使用 Parina 框架。</p>
```

---

## 🛠️ CLI 脚手架 (CLI Scaffolding)

Parina 包含一个 CLI 工具，可直接从 CSV 文件生成路由配置、处理器类和单元测试。

1. 在 CSV 文件中定义路由 (例如 `routes.csv`)：
   ```csv
   Method,Path,HandlerClass,Middlewares,Description
   GET,/,Parina\Modules\Public\HomeHandler,,Home page
   GET,/about,Parina\Modules\Public\AboutHandler,,About us
   ```

2. 运行脚手架工具：
   ```bash
   php bin/scaffold.php routes.csv
   ```

这将自动生成：
* `config/routes.php` 中的路由配置。
* `src/` 中缺失的处理器类。
* `tests/Handlers/` 中的基础单元测试，以验证处理器。

---

## 🧪 包含的测试

Parina 使用 PHPUnit 进行开发，专注于高测试覆盖率。

```
tests/
 ├── KernelTest.php
 ├── RouterTest.php
 ├── HandlerTest.php
 └── Handlers/FakeHandler.php
```

---

## 🧘 为什么存在 Parina

软件中的大部分复杂性都是偶然的。Parina 反思：

什么是既能正确、安全、快速运行，又最小的软件结构？

Parina 不是因能力有限而极简，而是有意为之。它移除了你并不真正需要的一切。

---

## 📦 部署与安装

### 生产环境部署
有关目录布局、权限以及生产环境建议，请参阅 [DEPLOY.md](../DEPLOY.md)。

### 快速开始 / 本地运行

使用 PHP 内置的开发服务器本地运行框架：

```bash
git clone https://github.com/nelsonrojasn/Parina-Framework.git
cd Parina-Framework
composer install
php -S localhost:8000 -t public
```

### 依赖管理器
即将发布于 Packagist。

---

## 🪶 许可证

MIT 许可证。

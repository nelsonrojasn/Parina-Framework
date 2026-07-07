# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

🇺🇸 [English](../README.md) | 🇪🇸 [Español](README.es.md) | 🇫🇷 [Français](README.fr.md) | 🇵🇹 [Português](README.pt.md) | 🇮🇹 [Italiano](README.it.md) | 🇩🇪 [Deutsch](README.de.md) | 🇨🇳 [简体中文](README.zh.md) | 🇯🇵 **日本語**

### *アルティプラーノ版：最小限こそが豊かさ。クリアな思考のための Web マイクロフレームワーク。*

---

## 💡 Parina とは？

Parina は、モダンな PHP アプリケーションのための极限までシンプルなマイクロフレームワークです。アプリケーションを明快さ、制御、そして最高のパフォーマンスで構築するために必要十分な構造のみを提供します。

---

## 🛠️ 主な機能

* **リフレクションによるDIコンテナ**: ハンドラーおよびミドルウェアの依存関係を自動解決し、コンストラクタインジェクションを実行します。
* **ステートレスなHTTPリクエスト (`Request`)**: 統一された入力メソッド (`input()`)、シンプルなHTTPヘッダー取得 (`header()`)、およびミドルウェアからハンドラーへコンテキストを共有するリクエスト属性 (`setAttribute()`)。
* **CQSとアダプターパターン**: リポジトリ内での読み取りクエリと書き込みコマンドの分離、およびオープン・クローズドの原則に準拠した動的データベースドライバーアダプター（SQLite、MySQL、PostgreSQL）。
* **XSS保護**: グローバルヘルパー関数 `h()` を使用した、テンプレート内変数の安全なエスケープ。

---

## 🌄 哲学

**抽象よりも明快さを。利便性よりも制御を。**

Parina が重視すること：
* **明示的な設計：** 魔法や隠されたライフサイクルはありません。
* **最小限のオーバーヘッド：** すべてのバイトとミリ秒が重要です。
* **予測可能なフロー：** 目に見えるものだけが、正確に実行されます。

---

## 🧱 10行で表すアーキテクチャ

1. リクエストはフロントコントローラーから入ります。
2. 中間件（ミドルウェア）のパイプラインを通過します。
3. ミドルウェアは処理をブロックするか、通過させることができます。
4. 登録されたハンドラー（Handler）に到達します。
5. ハンドラーがコアロジックを実行します。
6. 標準のレスポンス（Response）を返します。
7. 重苦しい魔法はありません。
8. 隠されたライフサイクルはありません。
9. 不要な抽象化はありません。
10. 明快で線形な実行フローのみが存在します。

---

## 🔄 リクエストライフサイクル

```
[ Request ] ───> [ ミドルウェアパイプライン ] ───> [ ハンドラー ]
                            │                               │
                            │ (レスポンスを返す)            │ (レスポンスを返す)
                            ▼                               ▼
                      [ Response ] <────────────────────────┘
```

### ミドルウェアモデル
各ミドルウェアレイヤーは、シンプルな二値ルールに従います：
* **`Response` を返す** → 実行を停止し、レスポンスを出力します。
* **`null` を返す** → 次のレイヤーへ進みます。

#### ミドルウェアの例
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
            return new ErrorResponse("Unauthorized", 401);
        }
        return null; // 次のレイヤーへ進む
    }
}
```

---

## 🔒 セキュリティ

セキュリティは最優先事項であり、ミドルウェアパイプラインの中で明示的に機能します：

* レート制限 (Rate limiting)
* リクエストサイズの検証
* CSRF 保護
* 同一生成元ポリシー (CORS)
* 認証 (Basic / JWT)
* 認可 (ACL)

---

## ⚡ パフォーマンス

最小限のオーバーヘッドとマイクロ秒精度のために設計されています：

* リクエストあたり約 **0.0007 秒** の実行時間。
* メモリフットプリントは約 **0.05 MB** RAM。
* Opcache に完全に最適化されています。

---

## 🚀 起動例 (引导启动 / Bootstrapping)

```php
// public/index.php
use Parina\Core\Router;
use Parina\Core\Kernel;
use Parina\Core\Container;
use Parina\Core\Config;
use Parina\Shared\Infrastructure\Db;

require_once __DIR__ . '/../src/autoload.php';

// Instantiate DI container & load dynamic dependencies
$container = new Container();
if (file_exists(__DIR__ . '/../config/dependencies.php')) {
    $container->load(require __DIR__ . '/../config/dependencies.php');
}

// Initialize database with dynamically resolved adapter (OCP)
Db::setConfig(Config::getDbConfig());
Db::init($container->get(\Parina\Shared\Infrastructure\DatabaseAdapter::class));

$router = new Router();
$routes = require '../config/routes.php';
foreach ($routes as $route) {
    $router->add($route['method'], $route['path'], $route['handler'], $route['middleware'] ?? []);
}

$kernel = new Kernel($router, $container);
$kernel->run();
```

## 🏠 最小限のハンドラー例
```php
namespace Parina\Modules\Public;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Request;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\View;
use Parina\Shared\Services\UserQueryRepositoryInterface;

class UsersListHandler implements Handler
{
    // Resolved and injected automatically by the DI Container via Reflection
    public function __construct(private UserQueryRepositoryInterface $userRepo) {}

    public function handle(Request $request): Response
    {
        $users = $this->userRepo->getActiveUsersList();
        // Secure HTML output using the global h() helper to prevent XSS
        $content = View::renderWithLayout("Admin/Views/users/list", "default", ['users' => $users]);
        return new HtmlResponse($content, 200);
    }
}
```

## 🖼 最小限のビュー例
```php
<!-- Modules/Admin/Views/users/list.php -->
<h1>Users List</h1>
<ul>
  <?php foreach ($users as $user): ?>
    <li><?= h($user['username']) ?></li>
  <?php endforeach; ?>
</ul>
```

---

## 🛠️ CLI スカフォールディング (CLI Scaffolding)

Parina には、CSV ファイルからルート設定、ハンドラークラス、およびユニットテストを直接生成する CLI ツールが含まれています。

1. CSV ファイルでルートを定義します (例: `routes.csv`):
   ```csv
   Method,Path,HandlerClass,Middlewares,Description
   GET,/,Parina\Modules\Public\HomeHandler,,Home page
   GET,/about,Parina\Modules\Public\AboutHandler,,About us
   ```

2. スカフォールディングツールを実行します:
   ```bash
   php bin/scaffold.php routes.csv
   ```

これにより、以下が自动生成されます：
* `config/routes.php` 内のルート設定。
* `src/` 内の不足しているハンドラークラス。
* ハンドラーを検証するための `tests/Handlers/` 内の基本的なユニットテスト。

---

## 🧪 含まれるテスト

Parina は PHPUnit を用いて開発されており、完全なテストカバレッジに焦点を当てています。

```
tests/
 ├── KernelTest.php
 ├── RouterTest.php
 ├── HandlerTest.php
 └── Handlers/FakeHandler.php
```

---

## 🧘 Parina が存在する理由

ソフトウェアの複雑さの大部分は偶発的なものです。Parina は問いかけます：

正しく、安全に、そして高速に動作する、最小限の構造とは何か？

Parina は制限によって最小限なのではなく、意図的に最小限に設計されています。不要なものをすべて排除しています。

コアとなる哲学と、フレームワーク全体がどのように紙ナプキンの図に収まるかについての詳細な説明は、[THE-NAPKIN-REVOLUTION.ja.md](THE-NAPKIN-REVOLUTION.ja.md) を参照してください。

---

## 📦 デプロイとインストール

### 本番環境へのデプロイ
ディレクトリのレイアウト、権限、および本番環境のヒントについては、[DEPLOY.md](../DEPLOY.md) を参照してください。

### クリーンアップとリセット
すべてのデモファイルを削除してフレームワークをリセットするには、[CLEANUP.ja.md](CLEANUP.ja.md) を参照してください。

### クイックスタート / ローカルインストール

PHP の組み込み開発サーバーを使用してフレームワークをローカルで実行するには：

```bash
git clone https://github.com/nelsonrojasn/Parina-Framework.git
cd Parina-Framework
# No composer needed
php -S localhost:8000 -t public
```

### 依存関係マネージャー
近々 Packagist に登録予定。

---

## 🪶 ライセンス

MIT ライセンス。

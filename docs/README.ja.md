# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

🇺🇸 [English](../README.md) | 🇪🇸 [Español](README.es.md) | 🇫🇷 [Français](README.fr.md) | 🇵🇹 [Português](README.pt.md) | 🇮🇹 [Italiano](README.it.md) | 🇩🇪 [Deutsch](README.de.md) | 🇦ym [Aymara](README.ay.md) | 🦙 [Quechua](README.qu.md) | 🇨🇳 [简体中文](README.zh.md) | 🇯🇵 **日本語**

### *アルティプラーノ版：最小限こそが豊かさ。クリアな思考のための Web マイクロフレームワーク。*

---

## 💡 Parina とは？

Parina は、モダンな PHP アプリケーションのための极限までシンプルなマイクロフレームワークです。アプリケーションを明快さ、制御、そして最高のパフォーマンスで構築するために必要十分な構造のみを提供します。

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
use Parina\Modules\Public\HomeHandler;

require_once '../src/autoload.php';

$router = new Router();
$router->add('GET', '/', HomeHandler::class);

$kernel = new Kernel($router);
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

class HomeHandler implements Handler
{
    public function handle(Request $request): Response
    {
        $content = View::renderWithLayout("Public/Views/home", "default", ['title' => 'Parina']);
        return new HtmlResponse($content, 200);
    }
}
```

## 🖼 最小限のビュー例
```php
<!-- Modules/Public/Views/home.php -->
<h1><?= $title ?></h1>
<p>Parina Framework へようこそ。</p>
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

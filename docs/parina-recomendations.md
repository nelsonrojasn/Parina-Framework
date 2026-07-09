# Parina Recommendations  
## *Tips and Shortcuts for Intentional Development in Parina Framework*

This section aims to present tips, conventions, and shortcuts to developers new to **Parina Framework**, helping them build fast, secure, and complexity-free applications in the shortest time possible.

Inspired by the spirit of the old KumbiaPHP guides, this document adapts classic modern web conventions to the Altiplano philosophy of simplicity and absolute control.

---

## 🌄 The Altiplano Philosophy in Code

Parina is neither a monolithic nor a magical framework. Its fundamental principle is that **code should fit in your head**. Therefore:
*   **No Magic:** There is no hidden query generation, invisible lifecycles, or complex auto-abstractions.
*   **Total Control:** You decide how to access the database and how to model your domain.
*   **Responsibility:** The framework provides the dependency container, the router, and the HTTP response; the rest of the architecture is up to your intent.

---

## 1. Application Design Recommendations

### The Explicit Lifecycle
Unlike traditional MVC frameworks where dozens of intermediate events occur, the flow in Parina is linear:

```
[ Request ] ───> [ Middleware Pipeline ] ───> [ Handler ]
                                                │
                                                ▼ (Returns)
                                          [ Response ]
```

### Skinny Handlers and Robust Services (CQS Separation)
We recommend keeping **Handlers** (Parina's controllers) with the minimum code necessary:
1.  Receive the [Request](../src/Core/Request.php).
2.  Extract and validate input parameters.
3.  Delegate business logic to an injected **Service** or **Repository**.
4.  Return a class that implements `Response` (`HtmlResponse`, `JsonResponse`, or `ErrorResponse`).

---

## 2. Database and CQS Conventions

### Table and Attribute Names
Following classic and clean conventions that facilitate manual mapping:
*   **Singular Tables:** Tables must be named in lowercase and singular (e.g., `producto`, `usuario`, `compra`).
*   **Many-to-Many Relationships:** Pivot tables combining both names in alphabetical order, separated by an underscore (e.g., `equipo_jugador`).
*   **Key Attribute:** The primary key must always be a unique numerical identifier named `id`.
*   **Foreign Keys:** Must be named with the source table name followed by the `_id` suffix (e.g., `proveedor_id`).
*   **Date Fields:**
    *   `creado_at` for the record's creation date/time.
    *   `actualizado_at` for the date/time of the last update.

### Data Abstraction without Magical ORM (CQS)
Instead of relying on a heavy ActiveRecord with lazy loading, in Parina we recommend separating reads from writes (**Command Query Separation**):
*   **Queries (Reads):** Dedicated repositories for querying data that directly return PHP associative arrays (fast and direct for views).
*   **Commands (Writes/Logic):** Dedicated classes for mutating database state explicitly and securely.

---

## 3. Handler (Controller) Conventions

Handlers are the classes responsible for handling a specific route. 

### Location and Naming
*   They reside within their corresponding feature in `src/Features/` (e.g., [src/Features/ProductManagement/Handlers/](../src/Features/ProductManagement/Handlers/)).
*   They must carry the `Handler` suffix and use singular names describing their exact action (e.g., `HomeHandler`, `ProductListHandler`).

### Example of a Clean Structure with Dependency Injection:
Parina's container automatically resolves and passes dependencies through the constructor:

```php
namespace Parina\Features\ProductManagement\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\View;
use Parina\Shared\Services\ProductQueryRepositoryInterface;

class ProductListHandler implements Handler
{
    // The DI container automatically resolves this interface using Reflection
    public function __construct(
        private ProductQueryRepositoryInterface $productRepo
    ) {}

    public function handle(RequestInterface $request): Response
    {
        $products = $this->productRepo->getActiveProducts();
        
        $content = View::renderWithLayout(
            "ProductManagement/Views/list", 
            "default", 
            ['products' => $products]
        );
        
        return new HtmlResponse($content, 200);
    }
}
```

---

## 4. View and Layout Conventions

Views in Parina are pure, fast, and direct PHP files, avoiding heavy template engines.

### File Location
*   **Feature Views (FDA):** They reside in the `Views/` subfolder of the corresponding feature (e.g., `src/Features/ProductManagement/Views/list.php`).
*   **Shared Layouts:** Global base HTML structures wrapping the views. They reside in [src/Shared/Layouts/](../src/Shared/Layouts/).

### Mandatory Data Escaping (XSS Protection)
To keep the application secure, it is mandatory to escape any dynamic variables coming from the database or user input using the global helper `h()`:

```php
<!-- src/Features/ProductManagement/Views/list.php -->
<h1>Product List</h1>
<ul>
    <?php foreach ($products as $product): ?>
        <li>
            <strong><?= h($product['nombre']) ?></strong> - 
            $<?= h($product['precio']) ?>
        </li>
    <?php endforeach; ?>
</ul>
```

---

## 5. Unit and Integration Testing (`tests/`)

Untested code is untrustworthy. In Parina, tests must be fast and follow a mirrored structure:
*   For every Handler in `src/Features/Marketing/Handlers/HomeHandler.php`, there must be a corresponding test in `tests/Features/Marketing/HomeHandlerTest.php`.
*   Tests must validate that the handler returns the expected HTTP status code and that the response body contains the correct elements.

---

## 🚀 The Golden Shortcut: CSV Scaffolding

Instead of creating these files by hand, Parina provides a CLI tool to explicitly speed up daily development.

1.  Declare your routes and their handlers in the centralized `routes.csv` file:
    ```csv
    Method,Path,HandlerClass,Middlewares,Description
    GET,/productos,Parina\Features\ProductManagement\Handlers\ProductListHandler,,Product list
    ```
2.  Run the scaffolding generator:
    ```bash
    php bin/scaffold.php routes.csv
    ```
3.  **Result:** The script will automatically create the Handler file with its base structure under `src/Features/ProductManagement/`, register the route in `config/routes.php`, and generate the unit test template in `tests/Features/ProductManagement/ProductListHandlerTest.php`.

---

*“I do not give you a framework to program faster; I give you one to program better.”*  
**All hands are welcome to the Altiplano revolution!**

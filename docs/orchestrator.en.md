# Industrial Automation of Clean Architectures: The Parina Orchestrator

Professional software development is often hindered by the creation of repetitive code (boilerplate) and the manual configuration of architectural layers. In Parina, we believe that the structure of an application should be predictable, clean, and, above all, **automatizable**.

The **[Parina orchestrator](../bin/orchestrator.php)** represents the concept of **Industrial Automation** applied to software design: an automated assembly line that translates your business definitions (routes, features, and databases) into a physical architecture that is perfectly structured and ready to code in a matter of seconds.

---

## The Philosophical Foundation

In traditional development methodologies, when a programmer wants to create a new feature (for example, billing an invoice), they have to perform multiple mechanical steps:
1. Create directories for controllers, views, and interfaces.
2. Write empty files respecting complex namespaces.
3. Register the new route in a centralized configuration file.
4. Create the tables in the development database.
5. Create the interfaces and repositories for commands and queries (CQS) and bind them in the DI container.

This manual process is time-consuming and prone to human errors (typos in namespaces, forgetting dependency registration, etc.).

**Parina's approach inverts this:**

> *"Declare your intent in flat files (CSV) and let the machine assemble the infrastructure."*

The programmer becomes a designer defining the application's topology on two levels (`routes.csv` and `cqs.csv`), and the orchestrator handles building the physical factory. The programmer only has to step in to write the pure business logic inside the generated Handlers and Repositories.

---

## Concrete Example: An Online Store (E-commerce)

Let's imagine we are building an online store with three key requirements:
1.  **Product Catalog:** Query and list products.
2.  **Shopping Cart:** Add, edit, and remove products from the cart in real time.
3.  **Checkout Process:** Confirm the purchase and generate an order.

### Step 1: Define Routes in `routes.csv`

First, we define how the user will communicate with our store via the API:

```csv
Method,Path,Feature,HandlerName,Middlewares,Description
GET,/products,ProductCatalog,ProductList,,List product catalog
POST,/cart/add,ShoppingCart,AddProduct,Auth,Add product to cart
POST,/checkout,Checkout,ProcessOrder,Auth,Process and confirm order
```

### Step 2: Define the Persistence Model in `cqs.csv`

Next, we define how the application will interact with the database using the CQS pattern:

```csv
Feature,Name,Table,Type
ProductCatalog,Product,product,query
ShoppingCart,Cart,cart_item,both
Checkout,Order,order,command
```

> [!NOTE]
> *   `ProductCatalog` only requires read operations (`query`), since customers do not modify products from the storefront.
> *   `ShoppingCart` requires both read and write (`both`) to add items to the cart and list what is inside.
> *   `Checkout` only requires write operations (`command`) to record the order and reduce inventory.

### Step 3: Run the Assembly Line

With both plans ready, we run the orchestrator:

```bash
php bin/orchestrator.php routes.csv cqs.csv
```

### Step 4: The Resulting Architecture (Strict FDA)

The orchestrator will execute the 4 phases and immediately build the following physical structure:

```text
src/Features/
├── ProductCatalog/
│   ├── Handlers/
│   │   └── ProductListHandler.php
│   ├── Queries/
│   │   ├── ProductQueryRepositoryInterface.php
│   │   └── DbProductQueryRepository.php
│   └── Views/
│
├── ShoppingCart/
│   ├── Handlers/
│   │   └── AddProductHandler.php
│   ├── Commands/
│   │   ├── CartCommandRepositoryInterface.php
│   │   └── DbCartCommandRepository.php
│   ├── Queries/
│   │   ├── CartQueryRepositoryInterface.php
│   │   └── DbCartQueryRepository.php
│   └── Views/
│
└── Checkout/
    ├── Handlers/
    │   └── ProcessOrderHandler.php
    ├── Commands/
    │   ├── OrderCommandRepositoryInterface.php
    │   └── DbOrderCommandRepository.php
    └── Views/
```

Additionally, the orchestrator will have completed the following internal hookups:
1.  Registered the HTTP routes in the active route file `config/routes.php`.
2.  Initialized database tables (created `product`, `cart_item`, and `order` tables in SQLite/MySQL/PostgreSQL based on the DDL schemas).
3.  Automatically bound all the new repository interfaces to their concrete classes within `config/dependencies.php` so that the Dependency Injection Container resolves them automatically.

---

## Clean Business Workflow

Thanks to this industrial automation, the code written by the programmer is highly straightforward and free of tight coupling with the database.

For example, the **Checkout** Handler (`ProcessOrderHandler.php`) looks like this:

```php
namespace Parina\Features\Checkout\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Responses\JsonResponse;
use Parina\Features\Checkout\Commands\OrderCommandRepositoryInterface;

class ProcessOrderHandler implements Handler
{
    // The Dependency Container automatically injects the command repository
    public function __construct(
        private OrderCommandRepositoryInterface $orderRepository
    ) {}

    public function handle(RequestInterface $request): Response
    {
        $data = $request->getParsedBody();
        
        // We explicitly save the order
        $success = $this->orderRepository->save([
            'user_id' => $request->getAttribute('user_id'),
            'total'   => $data['total'],
            'status'  => 'pending'
        ]);

        if (!$success) {
            return new JsonResponse(['error' => 'Could not process the order'], 500);
        }

        return new JsonResponse(['message' => 'Order processed successfully'], 201);
    }
}
```

### Why is this paradise for developers?
*   **Junior:** No need to worry about configuring the dependency container, wiring interfaces, creating manual PDO connections, or structuring complex folders. The environment guides them into the "pit of success" transparently.
*   **Seasoned Senior:** Can bypass 90% of the tedious mechanical tasks at the start of a project. Can spin up a robust, clean, and testable prototype of a full e-commerce system in under 10 minutes.
*   **QA / DevOps:** The architectural linter ensures that no programmer bypasses CQS rules or directly injects the database into views or controllers, keeping the project's codebase orderly over time.

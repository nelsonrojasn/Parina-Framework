# Dependency Configuration Guide (Explained Simply)

The [config/dependencies.php](../config/dependencies.php) file is the centralized place where we tell our application how to build and connect the pieces of its code. It works hand in hand with the **Dependency Container** defined in [Container.php](../src/Core/Container.php).

If you are not an expert programmer, this document will help you understand this concept using everyday analogies and practical examples.

---

## 1. Basic Concepts: What is "Dependency Injection"?

### The Restaurant Analogy 🍳
Imagine you are a **cook** in a restaurant. To do your job, you need tools: a stove, knives, and a blender. 

*   **Old Approach (Bad):** Every time you have to cook, you have to leave the kitchen yourself, find metal, forge your own knife, and install your own stove with gas pipes (`$stove = new Stove()`). This takes up your time, is prone to errors, and if the stove breaks, you have to fix it.
*   **Dependency Injection (Good):** You just walk into the kitchen and say: *"I need a knife and a stove to work"*. The **restaurant owner** (the **Container**) already has them ready for you and hands them directly to you. You don't know and don't care what brand the stove is or where it was bought; you just use it to cook.

The [config/dependencies.php](../config/dependencies.php) file is the **restaurant owner's shopping list and instructions**. It defines exactly what tools will be handed to the cooks (the system's classes).

---

## 2. File Structure: `bindings` vs `singletons`

The file is divided into two major lists:

### A. `bindings` (Disposable or Single-Use Tools)
*   **Analogy:** Like a paper cup for water or a pen at the front desk.
*   **How it works:** Every time a process in the application requests this object, the Container manufactures a **brand new instance**. When the process finishes, the object is discarded.
*   **Useful for:** Tasks that handle data that changes constantly and should not be mixed with others (for example, calculating the price of a specific shopping cart).

### B. `singletons` (Shared or Unique Tools)
*   **Analogy:** Like the office coffee machine or the refrigerator.
*   **How it works:** The Container manufactures the object **only once** at the beginning. If other people or other processes request that tool later, they are lent **the exact same** machine that is already turned on.
*   **Useful for:** Tools that are expensive to create or that must maintain the same information throughout the application (for example, the database connection or the system that logs errors to a file).

---

## 3. 5 Practical Examples Explained

Below, we see how these concepts are applied in the real life of a project:

### Example 1: Connecting a Data Archive (Repository)
In programming, we often define a **contract** (interface) that says: *"we need a place to save and search for products"* (`ProductRepositoryInterface`). The container decides which actual archive to use.

```php
'bindings' => [
    \App\Domain\Repositories\ProductRepositoryInterface::class => \App\Infrastructure\Repositories\SqlProductRepository::class,
],
```
*   **Simple Explanation:** We are telling the system: *"When someone asks for a place to search for products, give them the archive that connects to the SQL Database"*.
*   **Why is it useful? (Business Benefit):** If tomorrow you decide to stop using an SQL database and prefer to save products in Excel files or in the cloud, you only change the right side of this line. The rest of the application will continue to work without ever knowing the "archive" was changed.

---

### Example 2: The Application's Mailman (Mail Service - Singleton)
To send welcome emails or invoices, we need a messaging service (`MailerInterface`).

```php
'singletons' => [
    \App\Services\MailerInterface::class => \App\Services\SmtpMailer::class,
],
```
*   **Simple Explanation:** We indicate that the official mail courier will be `SmtpMailer`. Being in `singletons`, the system will not hire a new mailman for each letter; the same mailman will handle all the correspondence for the day.
*   **Why is it useful? (Business Benefit):** It saves memory and speed. Establishing a connection to a mail server takes time. Doing it once and reusing it makes the application respond much faster to users.

---

### Example 3: Construction Recipes with Specific Parameters (Factory Closures)
Sometimes, to create a tool, knowing its name is not enough; you also need to configure it with a password or a secret access key (API Key). For this, custom functions (called closures) are used.

```php
'singletons' => [
    \App\Services\SmsGatewayInterface::class => function (\Parina\Core\Container $c) {
        $config = $c->get(ConfigInterface::class)->get('sms');
        
        return new \App\Services\TwilioSmsGateway(
            $config['account_sid'],
            $config['auth_token']
        );
    },
],
```
*   **Simple Explanation:** We give a **step-by-step recipe** to the container: *"To build the SMS sender, first look for the secret passwords in the configuration file, then create the Twilio service with those keys and return it ready to use"*.
*   **Why is it useful? (Business Benefit):** It prevents you from exposing sensitive passwords directly in your everyday function code. Everything is configured securely in a single step.

---

### Example 4: The Flight Simulator (Development and Testing Environments)
When developers are testing the application, they do not want to spend real money charging credit cards, nor do they want to send real test emails to real customers.

```php
'singletons' => [
    \App\Services\PaymentGatewayInterface::class => function (\Parina\Core\Container $c) {
        $config = $c->get(ConfigInterface::class);
        $environment = $config->get('app.env') ?? 'production';

        if ($environment === 'local' || $environment === 'testing') {
            return new \App\Services\Mocks\FakePaymentGateway(); // Simulator
        }

        return new \App\Services\StripePaymentGateway($config->get('stripe.secret_key')); // Real
    },
],
```
*   **Simple Explanation:** If the system detects it is on the programmer's computer (`local`), it hands them a "payment simulator" that always says "approved" without spending money. If it is on the production server with real customers, it hands over the "real Stripe machine".
*   **Why is it useful? (Business Benefit):** It prevents catastrophic mistakes, such as accidentally charging thousands of dollars during testing or sending test SPAM emails to your real customers.

---

### Example 5: Universal Adapters (Driver Architecture)
Imagine you travel the world and bring a hair dryer. In each country, the plug is different. You need a **universal adapter**. The framework uses this technique for databases in [dependencies.php](../config/dependencies.php).

```php
'singletons' => [
    // We register the adapters for each type of plug
    'db.driver.mysql'  => fn($c) => new MySqlAdapter($c->get(ConfigInterface::class)->getDbConfig()),
    'db.driver.sqlite' => fn($c) => new SqliteAdapter($c->get(ConfigInterface::class)->getDbConfig()),

    // The universal adapter decides which one to use based on the country (configuration)
    DatabaseAdapter::class => function (\Parina\Core\Container $container) {
        $driver = $container->get(ConfigInterface::class)->getDbConfig()['driver'];
        
        return $container->get("db.driver.{$driver}");
    }
],
```
*   **Simple Explanation:** The application only asks for a "Database Adapter" (`DatabaseAdapter`). The container checks the configuration and dynamically connects the MySQL or SQLite cable as appropriate.
*   **Why is it useful? (Business Benefit):** It makes your software extremely flexible. You can package your application for clients using expensive databases (like enterprise-grade MySQL) or lightweight databases with no installation cost (like SQLite) simply by changing a text in the configuration.

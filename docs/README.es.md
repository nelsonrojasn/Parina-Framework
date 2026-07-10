# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

🇺🇸 [English](../README.md) | 🇪🇸 **Español**

### *Altiplano Edition: Menos es más. El framework web para pensar claro.*

---

## 💡 ¿Qué es Parina?

Parina es un micro-framework minimalista para aplicaciones PHP modernas. Proporciona la estructura justa y necesaria para construir aplicaciones con claridad, control y el máximo rendimiento, adhiriéndose a una Arquitectura Basada en Características (Feature-Driven) y patrones limpios de diseño.

---

## 🛠️ Características Clave

* **Contenedor DI con Reflection**: Resolución automática e inyección de dependencias por constructor en Controladores y Middlewares de manera recursiva.
* **Arquitectura Feature-Driven**: Controladores (handlers), vistas y pruebas unitarias organizadas por características cohesivas de negocio (ej. `Authentication`, `UserManagement`, `Marketing`) en lugar de carpetas basadas en roles o capas técnicas dispersas.
* **Petición HTTP sin Estado (`Request`)**: Entrada de datos unificada (`input()`), consulta simple de cabeceras (`header()`) y bolsa de contexto local (`setAttribute()`) para comunicación limpia entre middlewares y controladores.
* **Patrones CQS y Adapter**: Separación de consultas de lectura y comandos de escritura en Repositorios (validados automáticamente por el [Linter](file:///home/nelson/repos/Parina-Framework/bin/linter.php) del sistema), junto con adaptadores dinámicos de base de datos (SQLite, MySQL, PostgreSQL) conformes al Principio Abierto/Cerrado. Consulta las [Recomendaciones de CQS](file:///home/nelson/repos/Parina-Framework/recomendaciones_parinas.md#abstraccion-de-datos-sin-orm-magico-cqs) para ver las reglas detalladas.
* **Protección contra XSS**: Escape seguro de variables en vistas mediante la función de ayuda global `h()`.

---

## 🌄 Filosofía

**Claridad sobre abstracción. Control sobre conveniencia.**

Parina se enfoca en:
* **Diseño explícito:** Sin magia, sin ciclos de vida ocultos.
* **Sobrecarga mínima:** Cada byte y milisegundo cuenta.
* **Flujo predecible:** Lo que ves es exactamente lo que se ejecuta.

---

## 🧱 Arquitectura en 10 Líneas

1. Una petición entra a través de un controlador frontal (Front Controller).
2. Pasa a través del pipeline de middlewares.
3. Los middlewares pueden bloquear la petición o permitirle continuar.
4. Llega al controlador (Handler) registrado.
5. El Handler ejecuta la lógica de negocio.
6. Retorna una respuesta estándar (Response).
7. Sin magia pesada.
8. Sin ciclos de vida ocultos del framework.
9. Sin abstracciones innecesarias.
10. Solo una ejecución clara y lineal.

---

## 🔄 Ciclo de Vida de la Petición

```
[ Request ] ───> [ Pipeline de Middlewares ] ───> [ Handler ]
                            │                          │
                            │ (Retorna Response)       │ (Retorna Response)
                            ▼                          ▼
                      [ Response ] <───────────────────┘
```

### Modelo de Middleware
Cada capa de middleware sigue una regla binaria simple:
* **Retorna `Response`** → Detiene la ejecución y emite la respuesta.
* **Retorna `null`** → Continúa a la siguiente capa.

#### Ejemplo de Middleware
```php
namespace Parina\Shared\Middlewares;

use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\ErrorResponse;

class SimpleAuth implements Middleware
{
    public function handle(RequestInterface $request): ?Response
    {
        if (!isset($_SESSION['user'])) {
            return new ErrorResponse("No autorizado", 401);
        }
        return null; // Continúa a la siguiente capa
    }
}
```

---

## 🔒 Seguridad

La seguridad es primordial y vive exactamente donde debe: en el pipeline de middlewares.

* Limitación de tasa de peticiones (Rate limiting)
* Validación del tamaño de la petición
* Protección CSRF
* Política del mismo origen (CORS)
* Autenticación (Basic / JWT)
* Autorización (ACL)

---

## ⚡ Rendimiento

Diseñado para una sobrecarga mínima y precisión de microsegundos:

* **~0.0007 segundos** por ejecución de petición.
* **~0.05 MB** de huella de memoria RAM.
* Totalmente amigable con Opcache.

---

## 🚀 Ejemplo (Punto de Entrada / Bootstrapping)

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

$request = \Parina\Core\Request::capture();
$kernel = new Kernel($router, $container);
$response = $kernel->handle($request);

$emitter = new \Parina\Core\ResponseEmitter();
$emitter->emit($response);
```

## 🏠 Ejemplo de Handler Mínimo
```php
namespace Parina\Features\UserManagement\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\View;
use Parina\Shared\Services\UserQueryRepositoryInterface;

class UsersListHandler implements Handler
{
    // Resolved and injected automatically by the DI Container via Reflection
    public function __construct(private UserQueryRepositoryInterface $userRepo) {}

    public function handle(RequestInterface $request): Response
    {
        $users = $this->userRepo->all();
        // Secure HTML output using the global h() helper to prevent XSS
        $content = View::renderWithLayout("UserManagement/Views/list", "default", ['users' => $users]);
        return new HtmlResponse($content, 200);
    }
}
```

## 🖼 Ejemplo de Vista Mínima
```php
<!-- Features/UserManagement/Views/list.php -->
<h1>Users List</h1>
<ul>
  <?php foreach ($users as $user): ?>
    <li><?= h($user['username']) ?></li>
  <?php endforeach; ?>
</ul>
```

---

## 🛠️ CLI Scaffolding (Generación de Código)

Parina incluye una herramienta de línea de comandos para generar rutas, controladores (handlers) y pruebas unitarias a partir de un archivo CSV.

1. Define tus rutas en un archivo CSV (por ejemplo, `routes.csv`):
   ```csv
   Method,Path,HandlerClass,Middlewares,Description
   GET,/,Parina\Features\Marketing\Handlers\HomeHandler,,Página de inicio
   GET,/about,Parina\Features\Marketing\Handlers\AboutHandler,,Sobre nosotros
   ```

2. Ejecuta la herramienta de scaffolding:
   ```bash
   php bin/scaffold.php routes.csv
   ```

Esto creará automáticamente:
* La configuración de rutas en `config/routes.php`.
* Las clases de los Handlers faltantes en `src/Features/`.
* Pruebas unitarias básicas en `tests/Features/` para verificar tus controladores de manera simétrica.

---

## 🧪 Pruebas Incluidas

Parina está desarrollado con PHPUnit, enfocado en una cobertura completa.

```
tests/
 ├── KernelTest.php
 ├── RouterTest.php
 ├── ContainerTest.php
 └── Features/
```

---

## 🧘 Por qué existe Parina

La mayor parte de la complejidad en el software es accidental. Parina se pregunta:

¿Cuál es la estructura más pequeña que sigue funcionando de manera correcta, segura y rápida?

Parina no es minimalista por limitación. Es minimalista por intención. Elimina todo lo que realmente no necesitas.

Para obtener una explicación detallada de la filosofía principal y cómo todo el framework cabe en el diagrama de una servilleta de papel, consulta [THE-NAPKIN-REVOLUTION.es.md](THE-NAPKIN-REVOLUTION.es.md).

---

## 📦 Despliegue e Instalación

### Despliegue en Producción
Para conocer la estructura de directorios, permisos y consejos de producción, consulta [DEPLOY.es.md](DEPLOY.es.md).

### Limpieza y Reinicio
Para eliminar todos los archivos de demostración y restablecer el framework, consulta [CLEANUP.es.md](CLEANUP.es.md).

### Inicio Rápido / Instalación Local

Para ejecutar el framework localmente usando el servidor de desarrollo integrado de PHP:

```bash
git clone https://github.com/nelsonrojasn/Parina-Framework.git
cd Parina-Framework
php -S localhost:8000 -t public
```

---

## 🪶 Licencia

Licencia MIT.
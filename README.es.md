# Parina Framework
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/badges/build.png?b=main)](https://scrutinizer-ci.com/g/nelsonrojasn/Parina-Framework/build-status/main)

### *Altiplano Edition: Menos es más. El framework web para pensar claro.*

---

## 💡 ¿Qué es Parina?

Parina es un micro-framework minimalista para aplicaciones PHP modernas. Proporciona la estructura justa y necesaria para construir aplicaciones con claridad, control y el máximo rendimiento.

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

use Parina\Core\Request;
use Parina\Core\Interfaces\Middleware;
use Parina\Core\Interfaces\Response;
use Parina\Core\Responses\ErrorResponse;

class SimpleAuth implements Middleware
{
    public function handle(Request $request): ?Response
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
use Parina\Modules\Public\HomeHandler;

require_once '../vendor/autoload.php';

$router = new Router();
$router->add('GET', '/', HomeHandler::class);

$kernel = new Kernel($router);
$kernel->run();
```

## 🏠 Ejemplo de Handler Mínimo
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

## 🖼 Ejemplo de Vista Mínima
```php
<!-- Modules/Public/Views/home.php -->
<h1><?= $title ?></h1>
<p>Bienvenido a Parina Framework.</p>
```

---

## 🧪 Pruebas Incluidas

Parina está desarrollado con PHPUnit, enfocado en una cobertura completa.

```
tests/
 ├── KernelTest.php
 ├── RouterTest.php
 ├── HandlerTest.php
 └── Handlers/FakeHandler.php
```

---

## 🧘 Por qué existe Parina

La mayor parte de la complejidad en el software es accidental. Parina se pregunta:

¿Cuál es la estructura más pequeña que sigue funcionando de manera correcta, segura y rápida?

Parina no es minimalista por limitación. Es minimalista por intención. Elimina todo lo que realmente no necesitas.

---

## 📦 Despliegue e Instalación

### Despliegue en Producción
Para conocer la estructura de directorios, permisos y consejos de producción, consulta [DEPLOY.md](DEPLOY.md).

### Inicio Rápido / Instalación Local

Para ejecutar el framework localmente usando el servidor de desarrollo integrado de PHP:

```bash
git clone https://github.com/nelsonrojasn/Parina-Framework.git
cd Parina-Framework
composer install
php -S localhost:8000 -t public
```

### Gestor de Dependencias
Pronto en Packagist.

---

## 🪶 Licencia

Licencia MIT.
# Recomendaciones Parinas  
## *Consejos y Atajos para el Desarrollo Intencional en Parina Framework*

Esta sección tiene como objetivo presentar consejos, convenciones y atajos al desarrollador que llega a **Parina Framework**, ayudándole a construir aplicaciones veloces, seguras y libres de complejidad innecesaria en el menor tiempo posible.

Inspirado en el espíritu de las antiguas guías de KumbiaPHP, este documento adapta las convenciones clásicas de la web moderna a la filosofía de simplicidad y control absoluto del Altiplano.

---

## 🌄 La Filosofía del Altiplano en el Código

Parina no es un framework monolítico ni mágico. Su principio fundamental es que **el código debe caber en tu cabeza**. Por lo mismo:
*   **Sin Magia:** No hay generación de consultas ocultas, ciclos de vida invisibles ni auto-abstracciones complejas.
*   **Control Total:** Tú decides cómo accedes a la base de datos y cómo modelas tu dominio.
*   **Responsabilidad:** El framework te provee el contenedor de dependencias, el router y la respuesta HTTP; el resto de la arquitectura depende de tu intención.

---

## 1. Recomendaciones de Diseño de la Aplicación

### El ciclo de vida explícito
A diferencia de los MVC tradicionales donde ocurren decenas de eventos intermedios, en Parina el flujo es lineal:

```
[ Request ] ───> [ Middleware Pipeline ] ───> [ Handler ]
                                                │
                                                ▼ (Retorna)
                                          [ Response ]
```

### Handlers Flacos y Servicios Robustos (Separación CQS)
Recomendamos mantener los **Handlers** (los controladores de Parina) con el mínimo código necesario:
1.  Recibir el [Request](./src/Core/Request.php).
2.  Extraer y validar los parámetros de entrada.
3.  Delegar la lógica de negocio a un **Servicio** o **Repository** inyectado.
4.  Retornar una clase que implemente `Response` (`HtmlResponse`, `JsonResponse` o `ErrorResponse`).

---

## 2. Convenciones para Base de Datos y CQS

### Nombres de Tablas y Atributos
Siguiendo las convenciones clásicas y limpias que facilitan el mapeo manual:
*   **Tablas en Singular:** Las tablas deben nombrarse en minúscula y singular (ej: `producto`, `usuario`, `compra`).
*   **Relaciones Muchos a Muchos:** Tablas pivot combinando ambos nombres en orden alfabético y separadas por guion bajo (ej: `equipo_jugador`).
*   **Atributo Clave:** La clave primaria siempre debe ser un identificador único numérico llamado `id`.
*   **Claves Foráneas:** Deben nombrarse con el nombre de la tabla de origen seguido del sufijo `_id` (ej: `proveedor_id`).
*   **Campos de Fecha:**
    *   `creado_at` para la fecha/hora de creación del registro.
    *   `actualizado_at` para la fecha/hora del último cambio.

### Abstracción de Datos sin ORM Mágico (CQS)
En lugar de depender de un ActiveRecord pesado con carga perezosa (*lazy loading*), en Parina recomendamos separar las lecturas de las escrituras (**Command Query Separation**). Esto se audita de forma automatizada mediante nuestro linter (`php bin/linter.php`), el cual impone las siguientes reglas de aislamiento:

*   **Queries (Lecturas):** Repositorios dedicados a consultar datos que devuelven directamente arreglos asociativos de PHP (rápidos y directos para las vistas).
    *   **Regla de Nomenclatura:** La interfaz y clase concreta deben incluir la palabra `Query` en su nombre (ej: `UserQueryRepositoryInterface`, `DbUserQueryRepository`).
    *   **Regla de Retorno:** Sus métodos **nunca** deben declarar un tipo de retorno `void`.
    *   **Invariancia de Estado:** Tienen estrictamente prohibido realizar efectos secundarios. Su código no debe contener palabras clave SQL de escritura (`INSERT`, `UPDATE`, `DELETE`) ni invocar métodos mutadores de `SqlGenerator` (`insert`, `update`, `delete`).
*   **Commands (Escrituras/Lógica):** Clases dedicadas a mutar el estado de la base de datos de manera explícita y segura.
    *   **Regla de Nomenclatura:** La interfaz y clase concreta deben incluir la palabra `Command` en su nombre (ej: `UserCommandRepositoryInterface`, `DbUserCommandRepository`).
    *   **Regla de Retorno:** Sus métodos solo deben retornar tipos seguros de control: `void`, `bool`, `int` (para IDs o filas afectadas), o `null`. Tienen prohibido retornar colecciones o arreglos asociativos de datos persistentes.
    *   **Responsabilidad Única:** No deben incluir métodos de lectura típicos de entidades completas (como métodos que empiecen con `find`, `get`, `select` o `read`).
*   **Aislamiento en Features:**
    *   Para garantizar que las características de negocio sean independientes del motor SQL y sigan un acoplamiento indirecto, los Handlers en `src/Features/` tienen **prohibido** inyectar directamente la clase `DatabaseAdapter`. Deben depender exclusivamente de interfaces de repositorios Query o Command.

---

## 3. Convenciones para Handlers (Controladores)

Los Handlers son las clases encargadas de atender una ruta específica. 

### Ubicación y Nomenclatura
*   Viven dentro de su característica correspondiente en `src/Features/` (ej: [src/Features/ProductManagement/Handlers/](./src/Features/ProductManagement/Handlers/)).
*   Deben llevar el sufijo `Handler` y usar nombres en singular que describan su acción exacta (ej: `HomeHandler`, `ProductListHandler`).

### Ejemplo de Estructura Limpia con Inyección de Dependencias:
El contenedor de Parina resuelve y pasa las dependencias automáticamente a través del constructor:

```php
namespace Parina\Features\ProductManagement\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\View;
use Parina\Features\ProductManagement\Queries\ProductQueryRepositoryInterface;

class ProductListHandler implements Handler
{
    // El Contenedor DI resuelve esta interfaz automáticamente mediante Reflection
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

## 4. Convenciones para Vistas y Layouts

Las vistas en Parina son archivos PHP puros, rápidos y directos, evitando motores de plantillas pesados.

### Ubicación de archivos
*   **Vistas de Características (FDA):** Viven en la subcarpeta `Views/` de la característica correspondiente (ej: `src/Features/ProductManagement/Views/list.php`).
*   **Layouts Compartidos:** Estructuras HTML base globales que envuelven las vistas. Viven en [src/Shared/Layouts/](./src/Shared/Layouts/).

### Escape de Datos Obligatorio (Protección XSS)
Para mantener la aplicación segura, es mandatorio escapar cualquier variable dinámica proveniente de la base de datos o de la entrada del usuario usando el helper global `h()`:

```php
<!-- src/Features/ProductManagement/Views/list.php -->
<h1>Listado de Productos</h1>
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

## 5. Pruebas Unitarias e Integración (`tests/`)

Un código sin pruebas no es confiable. En Parina, las pruebas deben ser rápidas y tener una estructura espejo:
*   Para cada Handler en `src/Features/Marketing/Handlers/HomeHandler.php`, debe existir un test en `tests/Features/Marketing/HomeHandlerTest.php`.
*   Las pruebas deben validar que el handler retorne el código de estado HTTP esperado y que el cuerpo de la respuesta contenga los elementos correctos.

## 🎓 Guía de Supervivencia y Buenas Prácticas para Entusiastas

Si estás dando tus primeros pasos en la programación orientada a objetos (POO), patrones como CQS o arquitecturas desacopladas, aquí tienes reglas de oro para mantener tu código impecable en Parina Framework:

### 1. Usa Tipado Estricto Obligatorio (`strict_types`)
PHP es flexible, pero esa flexibilidad puede ocultar errores silenciosos. Inicia **siempre** cada archivo PHP con `declare(strict_types=1);` justo debajo de la etiqueta `<?php`.
*   **Por qué:** Evita conversiones automáticas invisibles (ej: que un string `"10"` se sume silenciosamente como número). Hace que el compilador y los linters detecten errores de tipado de inmediato.

### 2. No uses `new` para Instanciar Dependencias en tus Handlers
Cuando un Handler necesita otro servicio (como un repositorio, un servicio de seguridad o un logger), **nunca** uses el operador `new` dentro de sus métodos.
*   **Por qué:** Esto se conoce como *acoplamiento duro*. Si creas la instancia dentro del Handler, no podrás simularla (mockearla) en tus pruebas.
*   **Correcto:** Declara la dependencia en el constructor del Handler y deja que el contenedor de dependencias de Parina la inyecte de manera automática.

### 3. Respeta el Ciclo de Vida HTTP (No uses `echo`, `die()` o `exit()`)
Dentro de un Handler, tu única misión es retornar un objeto que implemente `Response` (como `HtmlResponse` o `JsonResponse`).
*   **Por qué:** Usar `die()`, `exit()` o `echo` interrumpe abruptamente la ejecución del framework, rompe la pipeline de middlewares y hace imposible probar ese Handler unitariamente.

### 4. No accedas a Superglobales (`$_GET`, `$_POST`, `$_SERVER`)
Para leer parámetros de consulta, entradas de formularios o datos del servidor, utiliza los métodos del objeto `$request` (como `$request->query()`, `$request->post()`, `$request->method()`).
*   **Por qué:** El objeto `$request` encapsula el estado HTTP de forma inmutable y testeable. Si usas superglobales directamente, tu código queda acoplado al estado global de PHP de ese instante, dificultando las pruebas y violando el principio de encapsulamiento.

### 5. CQS Simple: Las Consultas no deben Mutar Datos
Recuerda que separar lecturas de escrituras no es por capricho. Un método de repositorio de tipo "Query" **nunca** debe cambiar nada en la base de datos.
*   **Por qué:** Si al consultar datos (`getUserById`) modificas un registro en segundo plano, tu aplicación se volverá impredecible. Las consultas deben ser "puras" y seguras de ejecutar tantas veces como sea necesario sin alterar el estado del sistema.

### 6. No instancies Clases ni Accedas a Servicios en las Vistas
Las vistas (los archivos `.php` bajo `Views/`) deben ser puramente de presentación. **Nunca** instancies un repositorio o invoques un servicio global directamente dentro de una vista (ej: `<?php $db = new DatabaseAdapter(); ?>`).
*   **Por qué:** Rompe el patrón MVC. La vista no debe saber de dónde vienen los datos ni cómo se procesan; solo debe estructurar e imprimir (escapando con `h()`) las variables que el Handler le pasó a través del arreglo `$data` o usar los helpers provistos por el motor.

---

## 🚀 El Atajo de Oro: Scaffolding por CSV

En lugar de crear estos archivos a mano, Parina provee una herramienta CLI para acelerar el desarrollo diario de forma explícita.

1.  Declara tus rutas y sus handlers en el archivo centralizado `routes.csv`:
    ```csv
    Method,Path,Feature,HandlerName,Middlewares,Description
    GET,/productos,ProductManagement,ProductList,,Lista de productos
    ```
2.  Ejecuta el generador de andamiaje:
    ```bash
    php bin/scaffold.php routes.csv
    ```
3.  **Resultado:** El script creará automáticamente el archivo del Handler con su estructura base bajo `src/Features/ProductManagement/`, registrará la ruta en `config/routes.php` y generará la plantilla de prueba unitaria en `tests/Features/ProductManagement/ProductListHandlerTest.php`.

Para conocer en detalle todas las herramientas de consola disponibles para generar comandos, consultas y realizar análisis automáticos del código, consulta la guía de [Herramientas de Consola (docs/tools.md)](docs/tools.md).

---

*“No te doy un framework para programar más rápido; te doy uno para programar mejor.”*  
**¡Todas las manos son bienvenidas a la revolución del Altiplano!**

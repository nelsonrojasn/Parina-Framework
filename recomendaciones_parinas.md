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
En lugar de depender de un ActiveRecord pesado con carga perezosa (*lazy loading*), en Parina recomendamos separar las lecturas de las escrituras (**Command Query Separation**):
*   **Queries (Lecturas):** Repositorios dedicados a consultar datos que devuelven directamente arreglos asociativos de PHP (rápidos y directos para las vistas).
*   **Commands (Escrituras/Lógica):** Clases dedicadas a mutar el estado de la base de datos de manera explícita y segura.

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
use Parina\Core\Request;
use Parina\Core\Responses\HtmlResponse;
use Parina\Core\View;
use Parina\Shared\Services\ProductQueryRepositoryInterface;

class ProductListHandler implements Handler
{
    // El Contenedor DI resuelve esta interfaz automáticamente mediante Reflection
    public function __construct(
        private ProductQueryRepositoryInterface $productRepo
    ) {}

    public function handle(Request $request): Response
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

---

## 🚀 El Atajo de Oro: Scaffolding por CSV

En lugar de crear estos archivos a mano, Parina provee una herramienta CLI para acelerar el desarrollo diario de forma explícita.

1.  Declara tus rutas y sus handlers en el archivo centralizado `routes.csv`:
    ```csv
    Method,Path,HandlerClass,Middlewares,Description
    GET,/productos,Parina\Features\ProductManagement\Handlers\ProductListHandler,,Lista de productos
    ```
2.  Ejecuta el generador de andamiaje:
    ```bash
    php bin/scaffold.php routes.csv
    ```
3.  **Resultado:** El script creará automáticamente el archivo del Handler con su estructura base bajo `src/Features/ProductManagement/`, registrará la ruta en `config/routes.php` y generará la plantilla de prueba unitaria en `tests/Features/ProductManagement/ProductListHandlerTest.php`.

---

*“No te doy un framework para programar más rápido; te doy uno para programar mejor.”*  
**¡Todas las manos son bienvenidas a la revolución del Altiplano!**

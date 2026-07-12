# Automatización Industrial de Arquitecturas Limpias: El Orquestador de Parina

El desarrollo de software profesional a menudo se ve obstaculizado por la creación de código repetitivo (boilerplate) y la configuración manual de capas arquitectónicas. En Parina, creemos que la estructura de una aplicación debe ser predecible, limpia y, sobre todo, **automatizable**.

El **[orquestador de Parina](../bin/orchestrator.php)** representa el concepto de **Automatización Industrial** aplicado al diseño de software: una línea de ensamblaje automatizada que traduce tus definiciones de negocio (rutas, características y bases de datos) en una arquitectura física perfectamente estructurada y lista para codificar en cuestión de segundos.

---

## El Fundamento Filosófico

En las metodologías de desarrollo tradicionales, cuando un programador quiere crear una nueva funcionalidad (por ejemplo, el cobro de una factura), tiene que realizar múltiples pasos mecánicos:
1.  Crear directorios para controladores, vistas e interfaces.
2.  Escribir archivos vacíos respetando namespaces complejos.
3.  Registrar la nueva ruta en un archivo de configuración centralizado.
4.  Crear las tablas en la base de datos de desarrollo.
5.  Crear las interfaces y repositorios de comandos y consultas (CQS) y enlazarlos en el contenedor DI.

Este proceso manual consume tiempo y es propenso a errores humanos (errores de escritura en namespaces, olvidos en el registro de dependencias, etc.).

**El enfoque de Parina invierte esto:**
> *"Declara tu intención en archivos planos (CSV) y deja que la máquina ensamble la infraestructura."*

El programador se convierte en un diseñador que define la topología de la aplicación en dos planos (`routes.csv` y `cqs.csv`), y el orquestador se encarga de construir la fábrica física. El programador solo entra a escribir la lógica de negocio pura dentro de los Handlers y los Repositorios generados.

---

## Ejemplo Concreto: Una Tienda Online (E-commerce)

Imaginemos que vamos a construir una tienda en línea con tres requisitos clave:
1.  **Catálogo de Productos:** Consultar y listar productos.
2.  **Carro de Compras:** Añadir, editar y remover productos del carro en tiempo real.
3.  **Proceso de Checkout:** Confirmar la compra y generar un pedido.

### Paso 1: Definir las Rutas en `routes.csv`

Primero, definimos cómo se comunicará el usuario con nuestra tienda a través de la API:

```csv
Method,Path,Feature,HandlerName,Middlewares,Description
GET,/productos,ProductCatalog,ProductList,,Listar catálogo de productos
POST,/carro/agregar,ShoppingCart,AddProduct,Auth,Agregar producto al carro
POST,/checkout,Checkout,ProcessOrder,Auth,Procesar y confirmar el pedido
```

### Paso 2: Definir el Modelo de Persistencia en `cqs.csv`

Luego, definimos cómo interactuará la aplicación con la base de datos utilizando el patrón CQS:

```csv
Feature,Name,Table,Type
ProductCatalog,Product,producto,query
ShoppingCart,Cart,carro_item,both
Checkout,Order,pedido,command
```

> [!NOTE]
> *   `ProductCatalog` solo requiere operaciones de lectura (`query`), ya que los clientes no modifican los productos desde la tienda.
> *   `ShoppingCart` requiere lectura y escritura (`both`) para añadir items al carro y listar lo que hay dentro.
> *   `Checkout` solo requiere operaciones de escritura (`command`) para registrar el pedido y reducir el inventario.

### Paso 3: Ejecutar la Línea de Ensamblaje

Con los dos planos listos, ejecutamos el orquestador:

```bash
php bin/orchestrator.php routes.csv cqs.csv
```

### Paso 4: La Arquitectura Resultante (FDA Estricta)

El orquestador ejecutará las 4 fases y construirá de inmediato la siguiente estructura física:

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

Además, el orquestador habrá realizado las siguientes conexiones internas:
1.  Registró las rutas HTTP en el archivo de rutas activo `config/routes.php`.
2.  Inicializó las tablas de la base de datos (creó las tablas `producto`, `carro_item` y `pedido` en SQLite/MySQL/PostgreSQL basándose en los esquemas DDL).
3.  Enlazó de manera automática todas las interfaces de los nuevos repositorios con sus clases concretas dentro de `config/dependencies.php` para que el Contenedor de Inyección de Dependencias las resuelva automáticamente.

---

## Flujo de Trabajo Limpio de Negocio

Gracias a esta automatización industrial, el código que escribe el programador es sumamente directo y libre de acoplamiento rígido con la base de datos. 

Por ejemplo, el Handler de **Checkout** (`ProcessOrderHandler.php`) queda así:

```php
namespace Parina\Features\Checkout\Handlers;

use Parina\Core\Interfaces\Handler;
use Parina\Core\Interfaces\Response;
use Parina\Core\Interfaces\RequestInterface;
use Parina\Core\Responses\JsonResponse;
use Parina\Features\Checkout\Commands\OrderCommandRepositoryInterface;

class ProcessOrderHandler implements Handler
{
    // El Contenedor de Dependencias inyecta el repositorio de comandos de forma automática
    public function __construct(
        private OrderCommandRepositoryInterface $orderRepository
    ) {}

    public function handle(RequestInterface $request): Response
    {
        $data = $request->getParsedBody();
        
        // Guardamos el pedido de manera explícita
        $exito = $this->orderRepository->save([
            'usuario_id' => $request->getAttribute('user_id'),
            'total'      => $data['total'],
            'estado'     => 'pendiente'
        ]);

        if (!$exito) {
            return new JsonResponse(['error' => 'No se pudo procesar el pedido'], 500);
        }

        return new JsonResponse(['mensaje' => 'Pedido procesado con éxito'], 201);
    }
}
```

### ¿Por qué esto es la gloria para el desarrollador?
*   **Junior:** No necesita preocuparse por configurar el contenedor de dependencias, cablear interfaces, crear conexiones PDO manuales ni estructurar carpetas complejas. El entorno lo guía al "pozo del éxito" de forma transparente.
*   **Senior con años de circo:** Puede omitir el 90% de las tareas mecánicas aburridas de inicio de proyecto. Puede levantar un prototipo robusto, limpio y testeable de un e-commerce completo en menos de 10 minutos.
*   **QA / DevOps:** El linter arquitectónico garantiza que ningún programador se salte las reglas de CQS o inyecte directamente la base de datos en las vistas o controladores, manteniendo el código del proyecto ordenado a lo largo del tiempo.

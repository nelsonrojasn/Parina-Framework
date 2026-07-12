# Guía de Configuración de Dependencias (Explicada de Forma Sencilla)

El archivo [config/dependencies.php](../config/dependencies.php) es el lugar centralizado donde le indicamos a nuestra aplicación cómo construir y conectar las piezas de su código. Funciona de la mano con el **Contenedor de Dependencias** definido en [Container.php](../src/Core/Container.php).

Si no eres un programador experto, este documento te ayudará a entender este concepto usando analogías cotidianas y ejemplos prácticos.

---

## 1. Conceptos Básicos: ¿Qué es la "Inyección de Dependencias"?

### La Analogía del Restaurante 🍳
Imagina que eres un **cocinero** en un restaurante. Para hacer tu trabajo, necesitas herramientas: una cocina, cuchillos y una licuadora. 

*   **Enfoque antiguo (Malo):** Cada vez que tienes que cocinar, tú mismo tienes que salir de la cocina, buscar metal, forjar tu propio cuchillo e instalar tu propia cocina con tuberías de gas (`$cocina = new Cocina()`). Esto te quita tiempo, es propenso a errores y si la cocina se rompe, tú tienes que repararla.
*   **Inyección de Dependencias (Bueno):** Tú solo entras a la cocina y dices: *"Necesito un cuchillo y una cocina para trabajar"*. El **dueño del restaurante** (el **Contenedor**) ya los tiene listos para ti y te los entrega directamente en tus manos. Tú no sabes ni te importa de qué marca es la cocina o dónde se compró; solo la usas para cocinar.

El archivo [config/dependencies.php](../config/dependencies.php) es la **lista de compras e instrucciones del dueño del restaurante**. Ahí se define exactamente qué herramientas se van a entregar a los cocineros (las clases del sistema).

---

## 2. La Estructura del Archivo: `bindings` vs `singletons`

El archivo se divide en dos grandes listas:

### A. `bindings` (Herramientas Desechables o de Un Solo Uso)
*   **Analogía:** Como un vaso de papel para el agua o un bolígrafo de la recepción.
*   **Cómo funciona:** Cada vez que un proceso en la aplicación pide este objeto, el Contenedor fabrica una **instancia completamente nueva**. Cuando el proceso termina, el objeto se desecha.
*   **Útil para:** Tareas que manejan datos que cambian constantemente y no se deben mezclar con otras (por ejemplo, el cálculo del precio de un carrito de compras específico).

### B. `singletons` (Herramientas Compartidas o Únicas)
*   **Analogía:** Como la máquina de café de la oficina o el refrigerador.
*   **Cómo funciona:** El Contenedor fabrica el objeto **una sola vez** al inicio. Si otras personas u otros procesos piden esa herramienta más adelante, se les presta **la misma** máquina que ya está encendida.
*   **Útil para:** Herramientas caras de crear o que deben mantener la misma información en toda la aplicación (por ejemplo, la conexión a la base de datos o el sistema que registra los errores en un archivo).

---

## 3. 5 Ejemplos Prácticos Explicados

A continuación, vemos cómo se aplican estos conceptos en la vida real de un proyecto:

### Ejemplo 1: Conectar un Archivador de Datos (Repositorio)
En programación, a menudo definimos un **contrato** (interfaz) que dice: *"necesitamos un lugar para guardar y buscar productos"* (`ProductRepositoryInterface`). El contenedor decide qué archivador real usar.

```php
'bindings' => [
    \App\Domain\Repositories\ProductRepositoryInterface::class => \App\Infrastructure\Repositories\SqlProductRepository::class,
],
```
*   **Explicación Sencilla:** Le estamos diciendo al sistema: *"Cuando alguien pida un lugar para buscar productos, dale el archivador que se conecta a la Base de Datos SQL"*.
*   **¿Por qué es útil? (Beneficio de Negocio):** Si mañana decides dejar de usar una base de datos SQL y prefieres guardar los productos en archivos de Excel o en la nube, solo cambias el lado derecho de esta línea. El resto de la aplicación seguirá funcionando sin enterarse del cambio de "archivador".

---

### Ejemplo 2: El Cartero de la Aplicación (Servicio de Correo - Singleton)
Para enviar correos electrónicos de bienvenida o facturas, necesitamos un servicio de mensajería (`MailerInterface`).

```php
'singletons' => [
    \App\Services\MailerInterface::class => \App\Services\SmtpMailer::class,
],
```
*   **Explicación Sencilla:** Indicamos que el mensajero oficial de correos será `SmtpMailer`. Al estar en `singletons`, el sistema no contratará a un cartero nuevo para cada carta; el mismo cartero se encargará de toda la correspondencia del día.
*   **¿Por qué es útil? (Beneficio de Negocio):** Ahorra memoria y velocidad. Establecer una conexión con un servidor de correos toma tiempo. Hacerlo una sola vez y reutilizarla hace que la aplicación responda mucho más rápido a los usuarios.

---

### Ejemplo 3: Recetas de Construcción con Parámetros Específicos (Factory Closures)
A veces, para crear una herramienta no basta con saber su nombre, sino que necesitas configurarla con una contraseña o llave de acceso secreta (API Key). Para esto se usan funciones personalizadas (llamadas clausuras o *closures*).

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
*   **Explicación Sencilla:** Le damos una **receta paso a paso** al contenedor: *"Para construir el enviador de SMS, primero busca las contraseñas secretas en el archivo de configuración, luego crea el servicio de Twilio con esas claves y devuélvelo listo para usar"*.
*   **¿Por qué es útil? (Beneficio de Negocio):** Evita que expongas contraseñas delicadas directamente en el código de tus funciones cotidianas. Todo se configura de forma segura en un solo paso.

---

### Ejemplo 4: El Simulador de Vuelo (Entornos de Desarrollo y Pruebas)
Cuando los desarrolladores están probando la aplicación, no quieren gastar dinero real cobrando tarjetas de crédito ni quieren enviar correos reales de prueba a clientes reales.

```php
'singletons' => [
    \App\Services\PaymentGatewayInterface::class => function (\Parina\Core\Container $c) {
        $config = $c->get(ConfigInterface::class);
        $environment = $config->get('app.env') ?? 'production';

        if ($environment === 'local' || $environment === 'testing') {
            return new \App\Services\Mocks\FakePaymentGateway(); // Simulador
        }

        return new \App\Services\StripePaymentGateway($config->get('stripe.secret_key')); // Real
    },
],
```
*   **Explicación Sencilla:** Si el sistema detecta que está en la computadora del programador (`local`), le entrega un "simulador de pagos" que siempre dice "aprobado" sin gastar dinero. Si está en el servidor de producción con clientes reales, entrega la "máquina real de Stripe".
*   **¿Por qué es útil? (Beneficio de Negocio):** Previene errores catastróficos, como cobrar miles de dólares por accidente durante pruebas o enviar correos SPAM de prueba a tus clientes reales.

---

### Ejemplo 5: Adaptadores Universales (Arquitectura de Drivers)
Imagina que viajas por el mundo y llevas un secador de pelo. En cada país el enchufe es diferente. Necesitas un **adaptador universal**. El framework usa esta técnica para las bases de datos en [dependencies.php](../config/dependencies.php).

```php
'singletons' => [
    // Registramos los adaptadores para cada tipo de enchufe
    'db.driver.mysql'  => fn($c) => new MySqlAdapter($c->get(ConfigInterface::class)->getDbConfig()),
    'db.driver.sqlite' => fn($c) => new SqliteAdapter($c->get(ConfigInterface::class)->getDbConfig()),

    // El adaptador universal decide cuál usar según el país (configuración)
    DatabaseAdapter::class => function (\Parina\Core\Container $container) {
        $driver = $container->get(ConfigInterface::class)->getDbConfig()['driver'];
        
        return $container->get("db.driver.{$driver}");
    }
],
```
*   **Explicación Sencilla:** La aplicación solo pide un "Adaptador de Base de Datos" (`DatabaseAdapter`). El contenedor revisa la configuración y, de forma dinámica, le conecta el cable de MySQL o de SQLite según corresponda.
*   **¿Por qué es útil? (Beneficio de Negocio):** Permite que tu software sea extremadamente flexible. Puedes empaquetar tu aplicación para clientes que usen bases de datos costosas (como MySQL de nivel empresarial) o bases de datos ligeras sin costo de instalación (como SQLite) simplemente cambiando un texto en la configuración.

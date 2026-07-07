---

# 1. El Flujo de Ejecución: De la Petición a la Respuesta

El flujo de Parina es un **ciclo de vida lineal, síncrono y altamente predecible**. Sigue el patrón **Front Controller** en una tubería secuencial:

```
Petición HTTP
   │
   ▼
[public/index.php] ──(1. Bootstrap)──> Carga Autoload, Helpers globals (h())
   │
   ▼
[Container] ─────────(2. Configuración)──> Carga config/dependencies.php (DI)
   │
   ▼
[Db::init()] ────────(3. Capa de Datos)──> Inyecta DatabaseAdapter resuelto
   │
   ▼
[Router] ────────────(4. Enrutado)──> Busca match de método y URI (regex params)
   │
   ▼
[Kernel] ────────────(5. Despacho)──> Convierte a Request (Value Object)
   │
   ├─> [Middlewares] ──(6. Pipeline de Filtros)──> (Corta flujo si retorna Response)
   │
   ▼
[Container::get()] ──(7. Resolución DI)──> Instancia el Handler inyectando dependencias
   │
   ▼
[Handler::handle()] ─(8. Lógica del Controlador)──> Retorna objeto Response
   │
   ▼
[Kernel::send()] ────(9. Renderizado y Envío)──> Emite headers HTTP, status y body echo
```

### Hallazgo Arqueológico en el Flujo:
* En el estrato inicial, el Kernel instanciaba los middlewares y handlers directamente haciendo `new $className()`. 
* En el estrato moderno, el Kernel delega esto al `Container`. Esto permite que cualquier controlador declare qué interfaces necesita en su constructor (inyección de dependencias) y el framework las resuelva por reflexión recursiva antes de ejecutar la petición.

---

# 2. Seguridad y Acceso: Las Murallas del Sistema

La seguridad de Parina ha evolucionado desde la "seguridad por acoplamiento" (donde las capas se mezclaban) hasta una arquitectura defensiva basada en **interfaces y segregación**:

### A. Autenticación y Control de Sesión
* **El fósil antiguo**: El modelo de base de datos `User` manipulaba directamente la sesión global `$_SESSION['user_id'] = ...`. Esto atenta contra los principios de arquitectura limpia ya que la base de datos no debe saber que existe una cookie HTTP o una sesión web.
* **La estructura moderna**: Introdujimos `AuthInterface` y `SessionAuth`. Ahora, el inicio de sesión es un servicio inyectable. El middleware de Auth y el `LoginCheckHandler` simplemente preguntan al servicio `isLoggedIn()` o llaman a `login()`. En los tests, podemos simular que un usuario está autenticado sin crear sesiones de PHP reales en disco.

### B. Control de Accesos (ACL)
* **El fósil antiguo**: La clase `Acl` contenía un método `setMockHasPermissions` para alterar su estado desde las pruebas unitarias. Esto es un "olor a código de prueba" (*test smell*).
* **La estructura moderna**: El middleware `Acl` recibe un `AclInterface` por constructor. Toda la lógica estática y los atajos para tests fueron erradicados del código de producción de `Acl`. Los tests usan mocks nativos de PHPUnit.

### C. Defensas de Entrada y Salida (CSRF y XSS)
* **CSRF (Cross-Site Request Forgery)**: Se gestiona mediante el token `Csrf::token()`, inyectado en formularios y validado por el middleware de CSRF.
* **XSS (Cross-Site Scripting)**: La incorporación del helper global `h()` en el cargador de clases permite a las plantillas PHP escapar caracteres HTML peligrosos (`htmlspecialchars($value, ENT_QUOTES)`) de forma sencilla, asegurando que la salida visual no ejecute código JavaScript inyectado por terceros.

---

# 3. Acceso y Modificación de Datos: El Doble Estrato de Persistencia

En la capa de datos es donde se hace más evidente la transición arqueológica del framework:

```
                  ┌──────────────────────────────────────────┐
                  │                 CLIENTE                  │
                  └────────────────────┬─────────────────────┘
                                       │
                    ┌──────────────────┴──────────────────┐
                    ▼                                     ▼
        [Active Record (Legacy)]                [CQS (Moderno)]
        Utiliza BaseModel estático              Usa interfaces segregadas
        e instanciación directa.                para Lectura y Escritura.
                    │                                     │
                    │                                     ▼
                    │                       [UserQueryRepositoryInterface]
                    │                       [UserCommandRepositoryInterface]
                    │                                     │
                    ▼                                     ▼
             [Db::query()]                       [DbUserRepository]
                    │                                     │
                    └──────────────────┬──────────────────┘
                                       ▼
                              [DatabaseAdapter] (Interface)
                                       │
                        ┌──────────────┼──────────────┐
                        ▼              ▼              ▼
                 [SqliteAdapter] [MySqlAdapter] [PostgreSqlAdapter]
```

### A. El Estrato Active Record (`BaseModel`)
* Los modelos heredan de `BaseModel` y mapean de forma 1-a-1 a tablas SQLite/MySQL.
* Es una aproximación ideal para desarrollo hiperrápido (KISS), pero mezcla la representación de los datos con los métodos de almacenamiento (violando SRP).

### B. El Estrato CQS (Command Query Segregation)
* Para romper el acoplamiento de Active Record, introdujimos la segregación de interfaces de lectura y escritura:
  - `UserQueryRepositoryInterface`: Provee métodos optimizados para leer información (ej. `checkCredentials`, `findByUsername`).
  - `UserCommandRepositoryInterface`: Provee métodos para escribir, actualizar o borrar información.
* Ambos son implementados por `DbUserRepository` que se comunica con la base de datos.
* Esto permite cambiar por completo el motor de almacenamiento de una entidad (ej: a MongoDB o una API externa) modificando únicamente el repositorio, sin alterar las entidades ni la lógica del controlador.

### C. El Patrón Adaptador en la Conexión
* La base de datos no es instanciada de forma dura. El contenedor DI resuelve la interfaz `DatabaseAdapter` usando un factory en `dependencies.php` que lee la configuración de base de datos activa.
* Cumple estrictamente con el **Principio Abierto/Cerrado (OCP)**: el framework está cerrado a modificaciones internas pero abierto a que los desarrolladores añadan nuevos adaptadores SQL simplemente registrándolos en la configuración externa.

---

### Diagnóstico Final del Arqueólogo:
Parina Framework es un excelente ejemplo de cómo un framework "pragmático y estático" puede ser refinado hacia un diseño de "nivel empresarial" (SOLID completo) sin sacrificar la velocidad de ejecución y manteniendo total retrocompatibilidad con el código legado mediante fachadas dinámicas (`__callStatic`).
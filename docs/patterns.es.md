---

# 1. Implementación de los Principios SOLID en Parina

SOLID es el pilar que transformó a Parina de un script acoplado y monolítico en un framework flexible, mantenible y modular:

### **S – Single Responsibility Principle (SRP - Principio de Responsabilidad Única)**
Cada clase en Parina tiene **exactamente una única razón para cambiar**.
* **Separación de la Inicialización de DB**: Eliminamos por completo `SetupHandler`, delegando la inicialización del esquema y semillado de datos al usuario final/consumidor del framework.
* **Seguridad y Roles**: El control de acceso y validación de permisos se gestiona limpiamente en la configuración de rutas y el pipeline de middlewares, en lugar de acoplarse físicamente a las carpetas del proyecto.
* **Segregación de Capas**: Separamos la persistencia en repositorios Query/Command y la gestión de sesión en el servicio `SessionAuth`.

### **O – Open/Closed Principle (OCP - Principio de Abierto/Cerrado)**
El código está **abierto a la extensión, pero cerrado a la modificación**.
* **Adaptadores de Motores de Base de Datos**: La interfaz `DatabaseAdapter` abstrae los diferentes motores SQL. Los desarrolladores pueden añadir nuevos adaptadores implementando la interfaz e incluyéndola dinámicamente en `config/dependencies.php` sin modificar el código base.
* **Generación de Consultas SQL**: La interfaz `SqlGeneratorInterface` permite modificar o sustituir el motor de generación de SQL en tiempo de ejecución.

### **L – Liskov Substitution Principle (LSP - Principio de Sustitución de Liskov)**
Cualquier subclase debe poder reemplazar a su clase base sin alterar el correcto funcionamiento del programa.
* **Refactorización de `Response`**: La interfaz `Response` original contenía una firma rígida en su constructor. Removimos el constructor de la interfaz, permitiendo que clases como `RedirectResponse` o `JsonResponse` definan sus parámetros libremente y sean manejadas por el Kernel de forma uniforme.

### **I – Interface Segregation Principle (ISP - Principio de Segregación de Interfaces)**
Los clientes no deben ser forzados a depender de interfaces que no utilizan.
* **Segregación de Repositorios**: Dividimos el acceso a datos en dos contratos independientes: `UserQueryRepositoryInterface` y `UserCommandRepositoryInterface`.
* **Middlewares y Servicios**: El middleware de autenticación solo realiza consultas (Lectura) y por ende solo depende de `UserQueryRepositoryInterface`, previniendo el acceso no autorizado a operaciones de comando (Escritura).

### **D – Dependency Inversion Principle (DIP - Principio de Inversión de Dependencias)**
Los módulos de alto nivel no deben depender de módulos de bajo nivel; ambos deben depender de abstracciones.
* **Contenedor DI basado en Reflection**: Los controladores y middlewares ya no instancian sus dependencias mediante la palabra clave `new`. En su lugar, declaran parámetros de tipo interfaz en su constructor (ej. `ConfigInterface`, `Logger`, `TokenServiceInterface`, `CipherInterface`, `SqlGeneratorInterface`).
* **Constructores Requeridos**: Los middlewares y servicios declaran parámetros de constructor requeridos (no opcionales), obligando al contenedor de dependencias a resolverlos explícitamente y evitando la instanciación de fallbacks concretos dentro del código.

---

# 2. Implementación del Patrón CQS (Command Query Segregation)

El patrón CQS establece que **un método debe ser un comando** (ejecuta una acción que muta el estado del sistema) **o una consulta** (devuelve datos al cliente sin efectos secundarios), pero nunca ambos.

En Parina Framework, CQS se implementa en la capa de persistencia y servicios:

```
                            [Controller / Handler]
                           /                       \
        Inyecta Query     /                         \    Inyecta Command
                         ▼                           ▼
      [UserQueryRepositoryInterface]      [UserCommandRepositoryInterface]
      * findById()                        * save()
      * findByUsername()                  * delete()
      * all()
                         \                           /
                          ▼                         ▼
              ┌────────────────────────┐      ┌────────────────────────┐
              │ DbUserQueryRepository  │      │DbUserCommandRepository │
              └────────────────────────┘      └────────────────────────┘
```

### A. Capa de Consultas (Queries)
Representada por la interfaz `UserQueryRepositoryInterface`.
* **Métodos**: `findById()`, `findByUsername()`, `all()`.
* **Comportamiento**: Consultas puras de lectura que devuelven arreglos asociativos o nulos. Tienen estrictamente prohibido escribir en tablas o inyectar datos en la sesión.
* **Verificación de Credenciales:** Excluida de las consultas del repositorio. La validación criptográfica se realiza localmente en los controladores mediante `password_verify` sobre los datos devueltos por el repositorio para mantener las consultas SQL puras.

### B. Capa de Comandos (Commands)
Representada por la interfaz `UserCommandRepositoryInterface`.
* **Métodos**: `save()`, `delete()`.
* **Comportamiento**: Operaciones de escritura/mutación de registros físicos en la base de datos, retornando éxito o fracaso (`bool`).

---

# 3. Implementación de Feature-Driven Architecture (FDA)

Feature-Driven Architecture es un patrón de diseño que agrupa el código en base a dominios de negocio cohesivos (slices verticales) en lugar de capas técnicas horizontales (todas las vistas en una carpeta, todos los controladores en otra) o directorios basados en permisos.

### Organización de FDA:
* Cada funcionalidad de negocio (ej. `Authentication`, `UserManagement`, `Marketing`) es autónoma.
* Incluye sus propios Handlers y Views bajo un único subdirectorio en `src/Features/`.
* Los archivos de pruebas unitarias se estructuran de manera simétrica en `tests/Features/`.
* Las herramientas de consola CLI (`scaffold.php` y `cleanup.php`) soportan dinámicamente este diseño modular por características.
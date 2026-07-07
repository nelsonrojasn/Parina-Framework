---

# 1. Implementación de los Principios SOLID en Parina

SOLID es el pilar que transformó a Parina de un script monolítico acoplado a un framework flexible y modular:

### **S – Single Responsibility Principle (Principio de Responsabilidad Única)**
Cada clase en Parina tiene **una sola razón para cambiar**. 
* **Antes**: El modelo `User` mapeaba la base de datos y controlaba el estado de la sesión HTTP (`$_SESSION`).
* **Ahora**: Separamos la persistencia en el repositorio (`DbUserRepository`) y la gestión de sesión en el servicio `SessionAuth`.
* **Middlewares**: Cada middleware (`RateLimit`, `RequestSize`, `Csrf`) encapsula una regla de seguridad específica, manteniendo a la clase `Kernel` enfocada únicamente en el despacho del ciclo de vida HTTP.

### **O – Open/Closed Principle (Principio de Abierto/Cerrado)**
El código está **abierto a la extensión, pero cerrado a la modificación**.
* **Ejemplo (Base de Datos)**: La interfaz `DatabaseAdapter` abstrae los distintos drivers SQL. Si un desarrollador desea dar soporte a Oracle o SQL Server, no necesita modificar el núcleo de Parina. Simplemente crea una clase que implemente `DatabaseAdapter` y la vincula dinámicamente en el archivo externo `config/dependencies.php`.

### **L – Liskov Substitution Principle (Principio de Sustitución de Liskov)**
Cualquier subclase debe poder sustituir a su clase base sin alterar el comportamiento correcto del programa.
* **Refactorización de `Response`**: La interfaz original [Response.php](file:///home/nelson/repos/Parina-Framework/src/Core/Interfaces/Response.php) contenía una firma de constructor fija. Esto obligaba a clases como `RedirectResponse` o `JsonResponse` a recibir parámetros que no necesitaban, violando LSP. Quitamos el constructor de la interfaz, permitiendo que el Kernel trate a cualquier respuesta (Html, Json, Redirect) de forma uniforme.

### **I – Interface Segregation Principle (Principio de Segregación de Interfaces)**
Los clientes no deben verse obligados a depender de interfaces que no utilizan.
* **Segregación de Repositorios**: Dividimos el acceso a los datos de usuario en dos interfaces: `UserQueryRepositoryInterface` y `UserCommandRepositoryInterface`.
* **Uso**: El middleware de autenticación `BasicAuth` solo necesita verificar credenciales (Lectura). En lugar de recibir un repositorio con métodos como `save()` o `delete()`, solo inyecta `UserQueryRepositoryInterface`, limitando su superficie de acción al mínimo necesario.

### **D – Dependency Inversion Principle (Principio de Inversión de Dependencias)**
Los módulos de alto nivel no deben depender de módulos de bajo nivel; ambos deben depender de abstracciones.
* **El Contenedor DI por Reflection**: Los controladores y middlewares de Parina ya no instancian sus dependencias con la palabra clave `new`. En su lugar, declaran interfaces en sus constructores (ej: `ConfigInterface`, `Logger`, `TokenServiceInterface`, `CipherInterface`). El componente [Container](file:///home/nelson/repos/Parina-Framework/src/Core/Container.php) analiza estas firmas por reflexión en tiempo de ejecución e inyecta las dependencias resueltas.

---

# 2. Implementación del Patrón CQS (Command Query Segregation)

El patrón CQS establece que **un método debe ser un comando** (realizar una acción que mute el estado del sistema) **o una consulta** (retornar información al cliente sin efectos secundarios), pero nunca ambos.

En Parina Framework, CQS se implementa a nivel de la capa de datos y servicios:

```
                            [Controlador / Handler]
                           /                       \
        Inyecta Query     /                         \    Inyecta Command
                         ▼                           ▼
      [UserQueryRepositoryInterface]      [UserCommandRepositoryInterface]
      * findById()                        * save()
      * findByUsername()                  * delete()
      * checkCredentials()
                         \                           /
                          ▼                         ▼
                      ┌─────────────────────────────────┐
                      │        DbUserRepository         │
                      │ (Implementa ambas interfaces)   │
                      └─────────────────────────────────┘
```

### A. Capa de Consultas (Queries)
Representada por la interfaz `UserQueryRepositoryInterface`.
* **Métodos**: `findById()`, `findByUsername()`, `checkCredentials()`.
* **Comportamiento**: Métodos puros de solo lectura. Consultan la base de datos SQL y retornan arreglos de datos asociativos planos o nulos. **Tienen estrictamente prohibido alterar el estado del sistema** (no modifican tablas ni inyectan datos en la sesión global `$_SESSION`).

### B. Capa de Comandos (Commands)
Representada por la interfaz `UserCommandRepositoryInterface`.
* **Métodos**: `save()`, `delete()`.
* **Comportamiento**: Operaciones de escritura/mutación. Modifican los registros físicos en el motor de base de datos e informan el éxito o fallo de la acción (`bool`).

### C. Consecuencia en el Diseño de Pruebas
Gracias a CQS, en [LoginCheckHandlerTest.php](file:///home/nelson/repos/Parina-Framework/tests/Handlers/LoginCheckHandlerTest.php), el test solo simula la consulta (`checkCredentials()`) inyectando un mock ligero de la interfaz de Query. Esto hace que las pruebas unitarias se ejecuten de manera instantánea en memoria, aisladas completamente de la base de datos física SQLite.
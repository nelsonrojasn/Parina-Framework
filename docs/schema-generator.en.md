# Database Schema Generator from CSV

This CLI tool allows you to automatically generate SQL schemas for **SQLite**, **MySQL**, and **PostgreSQL** from your table definitions in a simply structured CSV file.

The tool is designed so that anyone, regardless of their technical level, can design complex relational models without worrying about DDL syntax for each database engine.

---

## CSV File Columns

The input CSV file must contain exactly the following columns in its header:

1. **`table`**: Table name (e.g., `product`).
2. **`attribute`**: Column / attribute name (e.g., `price`).
3. **`type`**: Generic or specific data type (e.g., `INTEGER`, `VARCHAR(100)`, `TEXT`).
4. **`pk`**: Primary Key. Use `Y` or `1` to enable, `N` or `0` to disable.
5. **`null`**: Allow null values (`NULL`). Use `Y` or `1` to allow, `N` or `0` to make it mandatory (`NOT NULL`).
6. **`unique`**: Require the value to be unique. Use `Y` or `1` to enable, `N` or `0` to disable.
7. **`default`** (Optional): Default value for the column (e.g., `1`, `'pending'`, `CURRENT_TIMESTAMP`).
8. **`references`** (Optional): Establish relationships / Foreign Keys by indicating the target table and column (e.g., `company(id)` or simply `company`).

---

## Expert-Level Features Included

* **Topological Ordering (DAG):** If the `order` table references the `customer` table, the generator will automatically place the creation of the `customer` table before `order` to avoid relational errors.
* **Safe Deletion (DROP TABLES):** Generated SQL files include `DROP TABLE IF EXISTS` statements at the beginning, sorted in reverse order (deleting child tables first, then parent tables) to avoid violating referential integrity constraints.
* **Reserved Word Validation:** The tool analyzes table and column names and will throw an error if you attempt to use invalid names such as `select`, `table`, `group`, etc.
* **Framework Integrity:** If the CSV file does not define the `user` table, the tool will automatically add the default `user` table definition and its seed (`INSERT`) to ensure that Parina's authentication system and tests continue to work correctly.

---

## Console Usage

To generate the SQL schemas, run the tool passing the path to your CSV file:

```bash
php bin/generate-schema.php path/to/your/file.csv
```

### Interactivity
By default, after successfully compiling the statements and saving them in the `database/` folder (as `schema.sqlite.sql`, `schema.mysql.sql`, and `schema.pgsql.sql`), the tool will ask if you want to immediately initialize and import the new schema into your local SQLite database.

If you are automating processes and do not want interactive prompts, you can use the `--no-interaction` flag:
```bash
php bin/generate-schema.php path/to/your/file.csv --no-interaction
```

---

## 3 Practical Examples

### Example 1: Simple Basic Structure (Inventory)

Defines a simple product catalog and inventory.

**`inventory.csv` file:**
```csv
table,attribute,type,pk,null,unique,default,references
product,id,INTEGER,Y,N,N,,
product,name,VARCHAR(100),N,N,Y,,
product,price,DECIMAL(10,2),N,N,N,0.00,
product,description,TEXT,N,Y,N,,
product,created_at,timestamp,N,N,N,CURRENT_TIMESTAMP,
```

**Generated SQLite Schema:**
```sql
PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS product;
DROP TABLE IF EXISTS user;

CREATE TABLE IF NOT EXISTS product (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    description TEXT DEFAULT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user ( ... ); -- Auto-injected by the framework
```

---

### Example 2: One-to-Many and Many-to-Many Relationships (e-Commerce)

Defines customers, orders linked to customers, and order items linking products and orders. Demonstrates foreign keys and automatic topological sorting (`customer` and `product` are created before `order`, and `order` is created before `order_item`).

**`ecommerce.csv` file:**
```csv
table,attribute,type,pk,null,unique,default,references
customer,id,INTEGER,Y,N,N,,
customer,name,VARCHAR(100),N,N,N,,
customer,email,VARCHAR(150),N,N,Y,,
order,id,INTEGER,Y,N,N,,
order,customer_id,INTEGER,N,N,N,,customer(id)
order,total_amount,DECIMAL(10,2),N,N,N,0.00,
order,created_at,timestamp,N,N,N,CURRENT_TIMESTAMP,
order_item,id,INTEGER,Y,N,N,,
order_item,order_id,INTEGER,N,N,N,,order
order_item,product_id,INTEGER,N,N,N,,product(id)
order_item,quantity,INTEGER,N,N,N,1,
product,id,INTEGER,Y,N,N,,
product,name,VARCHAR(100),N,N,N,,
product,price,DECIMAL(10,2),N,N,N,0.00,
```

**Generated PostgreSQL Schema:**
```sql
DROP TABLE IF EXISTS order_item CASCADE;
DROP TABLE IF EXISTS order CASCADE;
DROP TABLE IF EXISTS product CASCADE;
DROP TABLE IF EXISTS customer CASCADE;
DROP TABLE IF EXISTS user CASCADE;

CREATE TABLE IF NOT EXISTS customer (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS product (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00
);

CREATE TABLE IF NOT EXISTS "order" (
    id SERIAL PRIMARY KEY,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customer(id)
);

CREATE TABLE IF NOT EXISTS order_item (
    id SERIAL PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES "order"(id),
    FOREIGN KEY (product_id) REFERENCES product(id)
);
```

---

### Example 3: Customizing the Users Table

If you decide to customize or expand the `user` table required by the framework, simply define it in your CSV and the generator will use your structure instead of the basic default.

**`custom_users.csv` file:**
```csv
table,attribute,type,pk,null,unique,default,references
user,id,INTEGER,Y,N,N,,
user,username,VARCHAR(100),N,N,Y,,
user,password,VARCHAR(255),N,N,N,,
user,email,VARCHAR(255),N,N,N,,
user,role,VARCHAR(50),N,N,N,'operator',
user,last_login,TIMESTAMP,N,Y,N,,
user,is_active,TINYINT,N,N,N,1,
```

**Generated MySQL Schema:**
```sql
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS user;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'operator',
    last_login TIMESTAMP DEFAULT NULL,
    is_active TINYINT NOT NULL DEFAULT 1
);
```

CREATE TABLE IF NOT EXISTS usuario (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER DEFAULT NULL,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    email TEXT NOT NULL,
    is_active INTEGER DEFAULT 1,
    deleted INTEGER DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);

INSERT OR IGNORE INTO usuario (username, password, email, is_active) VALUES ('admin', '$2y$10$QCHG.PX4JEiR1E1VN/2Freu8QiphVmHFWK8G89SifuNrmvML2F5mu', 'admin@example.com', 1);

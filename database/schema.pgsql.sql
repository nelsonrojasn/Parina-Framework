CREATE TABLE IF NOT EXISTS usuario (
    id SERIAL PRIMARY KEY,
    company_id INT DEFAULT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    is_active SMALLINT DEFAULT 1,
    deleted SMALLINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO usuario (username, password, email, is_active) VALUES ('admin', '$2y$10$QCHG.PX4JEiR1E1VN/2Freu8QiphVmHFWK8G89SifuNrmvML2F5mu', 'admin@example.com', 1) ON CONFLICT (username) DO NOTHING;

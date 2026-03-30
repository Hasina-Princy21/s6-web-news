BEGIN;

CREATE TABLE IF NOT EXISTS backoffice_users (
    id BIGSERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT backoffice_users_username_unique UNIQUE (username)
);

INSERT INTO backoffice_users (username, password_hash, is_active)
VALUES (
    'admin',
    '$2y$10$X6nZEjV/dFl9LtS959CFhexssgtoobeEk5B.62TTl8VoCkEJsrQEu',
    TRUE
)
ON CONFLICT (username) DO NOTHING;

COMMIT;

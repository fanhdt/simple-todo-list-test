
CREATE TABLE IF NOT EXISTS todos (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    completed BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Data contoh (opsional, boleh dihapus)
INSERT INTO todos (title, completed) VALUES
    ('Belajar REST API', FALSE),
    ('Belajar Docker', FALSE),
    ('Belajar PHP', TRUE)
ON CONFLICT DO NOTHING;

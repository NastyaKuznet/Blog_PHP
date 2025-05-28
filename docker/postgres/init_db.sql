-- Создаем таблицу roles
CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    parent_id INTEGER REFERENCES roles(id) ON DELETE CASCADE
);

-- Заполняем roles
--INSERT INTO roles (name) VALUES ('reader'), ('writer'), ('moderator'), ('admin')
--ON CONFLICT DO NOTHING;

INSERT INTO roles (name, parent_id) VALUES 
('reader', NULL),
('writer', (SELECT id FROM roles WHERE name = 'reader')),
('moderator', (SELECT id FROM roles WHERE name = 'writer')),
('admin', (SELECT id FROM roles WHERE name = 'moderator'))
ON CONFLICT DO NOTHING;

-- Создаем таблицу users
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INTEGER NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    register_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_banned BOOLEAN DEFAULT FALSE
);

-- Создаем таблицу posts
CREATE TABLE IF NOT EXISTS posts (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    preview TEXT NOT NULL,
    author_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    publish_date TIMESTAMP,
    edit_date TIMESTAMP,
    delete_date TIMESTAMP,
    is_publish BOOLEAN NOT NULL DEFAULT FALSE,
    is_delete BOOLEAN NOT NULL DEFAULT FALSE,
    last_editor_id INTEGER NOT NULL REFERENCES users(id),
    content TEXT NOT NULL
);

-- Создаем таблицу likes
CREATE TABLE IF NOT EXISTS likes (
    id SERIAL PRIMARY KEY,
    create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    post_id INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE
);

-- Создаем таблицу comments
CREATE TABLE IF NOT EXISTS comments (
    id SERIAL PRIMARY KEY,
    content TEXT NOT NULL,
    post_id INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    edit_date TIMESTAMP,
    delete_date TIMESTAMP,
    is_edit BOOLEAN NOT NULL DEFAULT FALSE,
    is_delete BOOLEAN NOT NULL DEFAULT FALSE
);


-- Таблица categories
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_delete BOOLEAN NOT NULL DEFAULT FALSE
);

-- Таблица category_posts
CREATE TABLE IF NOT EXISTS category_posts (
    category_id INTEGER REFERENCES categories(id) ON DELETE CASCADE,
    post_id INTEGER REFERENCES posts(id) ON DELETE CASCADE,
    PRIMARY KEY (category_id, post_id)
);

-- Создаем таблицу tags
CREATE TABLE IF NOT EXISTS tags (
    id SERIAL PRIMARY KEY,
    post_id INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL
);

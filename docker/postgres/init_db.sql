-- Создаем таблицу roles
CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Заполняем roles
INSERT INTO roles (name) VALUES ('reader'), ('writer'), ('moderator'), ('admin')
ON CONFLICT DO NOTHING;

-- Создаем таблицу users
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    nickname VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INTEGER NOT NULL REFERENCES roles(id) ON DELETE CASCADE
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
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица category_posts
CREATE TABLE IF NOT EXISTS category_posts (
    category_id INTEGER REFERENCES categories(id) ON DELETE CASCADE,
    post_id INTEGER REFERENCES posts(id) ON DELETE CASCADE,
    PRIMARY KEY (category_id, post_id)
);

-- Добавляем тестовых пользователей, если их нет
INSERT INTO users (nickname, password, role_id) VALUES ('reader', 'reader', 1)
ON CONFLICT DO NOTHING;
INSERT INTO users (nickname, password, role_id) VALUES ('writer', 'writer', 2)
ON CONFLICT DO NOTHING;
INSERT INTO users (nickname, password, role_id) VALUES ('moderator', 'moderator', 3)
ON CONFLICT DO NOTHING;
INSERT INTO users (nickname, password, role_id) VALUES ('admin', 'admin', 4)
ON CONFLICT DO NOTHING;

-- Добавляем тестовые посты
INSERT INTO posts (title, content, user_id) VALUES ('Пост читателя', 'Читатель не должен иметь постов', 1);
INSERT INTO posts (title, content, user_id) VALUES ('Пост писателя', 'Привет, это мой первый пост!', 2);
INSERT INTO posts (title, content, user_id) VALUES ('Пост модера', 'Я могу редактировать и удалять посты', 3);
INSERT INTO posts (title, content, user_id) VALUES ('Пост админа', 'Я администратор и могу всё!', 4);
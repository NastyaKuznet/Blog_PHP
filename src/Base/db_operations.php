<?php //УДАЛИТЬ ВСЕ //Метод для добавления нового пользователя в таблицу users
function addUser($nickname, $password, $role_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("INSERT INTO users (nickname, password, role_id) VALUES (:nickname, :password, :role_id)");
        $stmt->execute([
            'nickname' => $nickname,
            'password' => password_hash($password, PASSWORD_DEFAULT), // Хэшируем пароль
            'role_id' => $role_id
        ]);
        return true;
    } catch (PDOException $e) {
        echo "Ошибка при добавлении пользователя: " . $e->getMessage();
        return false;
    }
}

//Метод для проверки существования пользователя по никнейму и паролю
function checkUser($nickname, $password) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE nickname = :nickname");
        $stmt->execute(['nickname' => $nickname]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        echo "Ошибка при проверке пользователя: " . $e->getMessage();
        return false;
    }
}

//Метод для получения всех постов
function getAllPosts() {
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Ошибка при получении постов: " . $e->getMessage();
        return [];
    }
}

//Метод для получения постов по автору, отсортированных по нику автора
function getPostsByAuthorAlphabetical($author_nickname) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT p.* 
                              FROM posts p 
                              JOIN users u ON p.user_id = u.id 
                              WHERE u.nickname = :author_nickname 
                              ORDER BY u.nickname ASC");
        $stmt->execute(['author_nickname' => $author_nickname]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Ошибка при получении постов по автору: " . $e->getMessage();
        return [];
    }
}

//Метод для получения постов по автору, отсортированных по нику автора в обратном порядке
function getPostsByAuthorReverseAlphabetical($author_nickname) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT p.* 
                              FROM posts p 
                              JOIN users u ON p.user_id = u.id 
                              WHERE u.nickname = :author_nickname 
                              ORDER BY u.nickname DESC");
        $stmt->execute(['author_nickname' => $author_nickname]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Ошибка при получении постов по автору: " . $e->getMessage();
        return [];
    }
}

//Метод для получения всех постов по конкретному нику автора
function getPostsByAuthor($author_nickname) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT p.* 
                              FROM posts p 
                              JOIN users u ON p.user_id = u.id 
                              WHERE u.nickname = :author_nickname");
        $stmt->execute(['author_nickname' => $author_nickname]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Ошибка при получении постов по автору: " . $e->getMessage();
        return [];
    }
}

//Метод для получения всех постов, отсортированных по количеству лайков в порядке возрастания
function getPostsByLikesAscending() {
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT * FROM posts ORDER BY likes ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Ошибка при получении постов по лайкам: " . $e->getMessage();
        return [];
    }
}

//Метод для получения всех постов, отсортированных по количеству лайков в порядке убывания
function getPostsByLikesDescending() {
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT * FROM posts ORDER BY likes DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Ошибка при получении постов по лайкам: " . $e->getMessage();
        return [];
    }
}

//Метод для получения всех постов, отсортированных по количеству комментариев в порядке возрастания
function getPostsByCommentsAscending() {
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT p.*, COUNT(c.id) as comment_count 
                            FROM posts p 
                            LEFT JOIN comments c ON p.id = c.post_id 
                            GROUP BY p.id 
                            ORDER BY comment_count ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Ошибка при получении постов по комментариям: " . $e->getMessage();
        return [];
    }
}

//Метод для получения всех постов, отсортированных по количеству комментариев в порядке убывания
function getPostsByCommentsDescending() {
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT p.*, COUNT(c.id) as comment_count 
                            FROM posts p 
                            LEFT JOIN comments c ON p.id = c.post_id 
                            GROUP BY p.id 
                            ORDER BY comment_count DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Ошибка при получении постов по комментариям: " . $e->getMessage();
        return [];
    }
}

//Метод для добавления нового поста
function addPost($title, $content, $user_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("INSERT INTO posts (title, content, user_id) VALUES (:title, :content, :user_id)");
        $stmt->execute([
            'title' => $title,
            'content' => $content,
            'user_id' => $user_id
        ]);
        return true;
    } catch (PDOException $e) {
        echo "Ошибка при добавлении поста: " . $e->getMessage();
        return false;
    }
}

//Метод для редактирования существующего поста
function editPost($post_id, $title, $content) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("UPDATE posts SET title = :title, content = :content WHERE id = :post_id");
        $stmt->execute([
            'title' => $title,
            'content' => $content,
            'post_id' => $post_id
        ]);
        return true;
    } catch (PDOException $e) {
        echo "Ошибка при редактировании поста: " . $e->getMessage();
        return false;
    }
}

//Метод для удаления поста
function deletePost($post_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :post_id");
        $stmt->execute(['post_id' => $post_id]);
        return true;
    } catch (PDOException $e) {
        echo "Ошибка при удалении поста: " . $e->getMessage();
        return false;
    }
}

//Метод для получения информации о пользователе по его id
function getUserInfo($user_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT nickname FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Ошибка при получении информации о пользователе: " . $e->getMessage();
        return [];
    }
}

//Метод для получения списка всех пользователей с их id, ником и ролью
function getAllUsers() {
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT u.id, u.nickname, r.name as role_name 
                            FROM users u 
                            JOIN roles r ON u.role_id = r.id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Ошибка при получении пользователей: " . $e->getMessage();
        return [];
    }
}

//Метод для изменения роли пользователя
function changeUserRole($user_id, $new_role_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("UPDATE users SET role_id = :new_role_id WHERE id = :user_id");
        $stmt->execute([
            'new_role_id' => $new_role_id,
            'user_id' => $user_id
        ]);
        return true;
    } catch (PDOException $e) {
        echo "Ошибка при изменении роли пользователя: " . $e->getMessage();
        return false;
    }
}

//Метод для удаления пользователя и всех его постов
function deleteUser($user_id) {
    global $pdo;

    try {
        // Удаляем все посты пользователя
        $stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);

        // Удаляем пользователя
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        return true;
    } catch (PDOException $e) {
        echo "Ошибка при удалении пользователя: " . $e->getMessage();
        return false;
    }
}
?>
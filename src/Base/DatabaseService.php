<?php
namespace Base;

use PDO;
use PDOException; 

class DatabaseService
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Метод для добавления нового пользователя
    public function addUser($nickname, $password, $role_id)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (nickname, password, role_id) VALUES (:nickname, :password, :role_id)");
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

    // Метод для проверки существования пользователя
    public function checkUser($nickname, $password)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE nickname = :nickname");
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

    // Метод для получения всех постов
    public function getAllPosts()
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении постов: " . $e->getMessage();
            return [];
        }
    }

    // Метод для получения постов по автору, отсортированных по нику автора
    public function getPostsByAuthorAlphabetical($author_nickname)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT p.* 
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

    // Метод для получения постов по автору, отсортированных по нику автора в обратном порядке
    public function getPostsByAuthorReverseAlphabetical($author_nickname)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT p.* 
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

    // Метод для получения всех постов по конкретному нику автора
    public function getPostsByAuthor($author_nickname)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT p.* 
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

    // Метод для получения всех постов, отсортированных по количеству лайков в порядке возрастания
    public function getPostsByLikesAscending()
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM posts ORDER BY likes ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении постов по лайкам: " . $e->getMessage();
            return [];
        }
    }

    // Метод для получения всех постов, отсортированных по количеству лайков в порядке убывания
    public function getPostsByLikesDescending()
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM posts ORDER BY likes DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении постов по лайкам: " . $e->getMessage();
            return [];
        }
    }

    // Метод для получения всех постов, отсортированных по количеству комментариев в порядке возрастания
    public function getPostsByCommentsAscending()
    {
        try {
            $stmt = $this->pdo->query("SELECT p.*, COUNT(c.id) as comment_count 
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

    // Метод для получения всех постов, отсортированных по количеству комментариев в порядке убывания
    public function getPostsByCommentsDescending()
    {
        try {
            $stmt = $this->pdo->query("SELECT p.*, COUNT(c.id) as comment_count 
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

    // Метод для добавления нового поста
    public function addPost($title, $content, $user_id)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO posts (title, content, user_id) VALUES (:title, :content, :user_id)");
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

    // Метод для редактирования существующего поста
    public function editPost($post_id, $title, $content)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE posts SET title = :title, content = :content WHERE id = :post_id");
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

    // Метод для удаления поста
    public function deletePost($post_id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :post_id");
            $stmt->execute(['post_id' => $post_id]);
            return true;
        } catch (PDOException $e) {
            echo "Ошибка при удалении поста: " . $e->getMessage();
            return false;
        }
    }

    // Метод для получения информации о пользователе по его ID
    public function getUserInfo($user_id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT nickname FROM users WHERE id = :user_id");
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении информации о пользователе: " . $e->getMessage();
            return [];
        }
    }

    // Метод для получения списка всех пользователей с их ID, ником и ролью
    public function getAllUsers()
    {
        try {
            $stmt = $this->pdo->query("SELECT u.id, u.nickname, r.name as role_name 
                                       FROM users u 
                                       JOIN roles r ON u.role_id = r.id");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении пользователей: " . $e->getMessage();
            return [];
        }
    }

    // Метод для изменения роли пользователя
    public function changeUserRole($user_id, $new_role_id)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET role_id = :new_role_id WHERE id = :user_id");
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

    // Метод для удаления пользователя и всех его постов
    public function deleteUser($user_id)
    {
        try {
            // Удаляем все посты пользователя
            $stmt = $this->pdo->prepare("DELETE FROM posts WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);

            // Удаляем пользователя
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :user_id");
            $stmt->execute(['user_id' => $user_id]);
            return true;
        } catch (PDOException $e) {
            echo "Ошибка при удалении пользователя: " . $e->getMessage();
            return false;
        }
    }
}
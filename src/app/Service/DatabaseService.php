<?php
namespace NastyaKuznet\Blog\Service;

use PDO;
use PDOException; 

class DatabaseService
{
    public $pdo;

    public function __construct(array $config)
    {
        $host = $config['db']['host'];
        $dbname = $config['db']['dbname'];
        $username = $config['db']['username'];
        $password = $config['db']['password'];

        try {
            $this->pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); //Установите режим выборки по умолчанию
        } catch (PDOException $e) {
            throw new \Exception("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }

    // Метод для получения всех постов
    public function getAllPosts()
    {
        try {
            $stmt = $this->pdo->query("SELECT p.*, u.nickname as user_nickname, COUNT(c.id) as comment_count
                                        FROM posts p
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        JOIN users u ON p.user_id = u.id
                                        GROUP BY p.id, u.nickname;");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении постов: " . $e->getMessage();
            return [];
        }
    }

    // Метод для получения постов, отсортированных по нику автора
    public function getPostsByAuthorAlphabetical()
    {
        try {
            $stmt = $this->pdo->query("SELECT p.* , u.nickname as user_nickname, COUNT(c.id) as comment_count
                                         FROM posts p 
                                         LEFT JOIN comments c ON p.id = c.post_id
                                         JOIN users u ON p.user_id = u.id  
                                         GROUP BY p.id, u.nickname
                                         ORDER BY u.nickname ASC;");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении постов по автору: " . $e->getMessage();
            return [];
        }
    }

    // Метод для получения постов по автору, отсортированных по нику автора в обратном порядке
    public function getPostsByAuthorReverseAlphabetical()
    {
        try {
            $stmt = $this->pdo->query("SELECT p.* , u.nickname as user_nickname, COUNT(c.id) as comment_count
                                         FROM posts p 
                                         LEFT JOIN comments c ON p.id = c.post_id
                                         JOIN users u ON p.user_id = u.id  
                                         GROUP BY p.id, u.nickname
                                         ORDER BY u.nickname DESC;");
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
            $stmt = $this->pdo->prepare("SELECT p.* , u.nickname as user_nickname, COUNT(c.id) as comment_count
                                         FROM posts p 
                                         LEFT JOIN comments c ON p.id = c.post_id
                                         JOIN users u ON p.user_id = u.id  
                                         WHERE u.nickname = :author_nickname
                                         GROUP BY p.id, u.nickname");
            $stmt->execute(['author_nickname' => $author_nickname]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении постов по автору: " . $e->getMessage();
            return [];
        }
    }

    public function getPostsByUserId(int $userId):array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT p.* , u.nickname as user_nickname, COUNT(c.id) as comment_count
                                         FROM posts p 
                                         LEFT JOIN comments c ON p.id = c.post_id
                                         JOIN users u ON p.user_id = u.id  
                                         WHERE u.id = :user_id
                                         GROUP BY p.id, u.nickname");
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении постов по юзер id: " . $e->getMessage();
            return [];
        }
    }

    // Метод для получения всех постов, отсортированных по количеству лайков в порядке возрастания
    public function getPostsByLikesAscending()
    {
        try {
            $stmt = $this->pdo->query("SELECT p.* , u.nickname as user_nickname, COUNT(c.id) as comment_count
                                        FROM posts p 
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        JOIN users u ON p.user_id = u.id  
                                        GROUP BY p.id, u.nickname
                                        ORDER BY likes ASC");
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
            $stmt = $this->pdo->query("SELECT p.* , u.nickname as user_nickname, COUNT(c.id) as comment_count
                                        FROM posts p 
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        JOIN users u ON p.user_id = u.id  
                                        GROUP BY p.id, u.nickname 
                                        ORDER BY likes DESC");
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
            $stmt = $this->pdo->query("SELECT p.* , u.nickname as user_nickname, COUNT(c.id) as comment_count
                                        FROM posts p 
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        JOIN users u ON p.user_id = u.id  
                                        GROUP BY p.id, u.nickname
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
            $stmt = $this->pdo->query("SELECT p.* , u.nickname as user_nickname, COUNT(c.id) as comment_count
                                        FROM posts p 
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        JOIN users u ON p.user_id = u.id  
                                        GROUP BY p.id, u.nickname
                                        ORDER BY comment_count DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении постов по комментариям: " . $e->getMessage();
            return [];
        }
    }

    // Метод для получения количества постов по юзерИд
    public function getCountPostsByUserId(int $userId):int
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) 
                                        FROM posts 
                                        WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            echo "Ошибка при получении количество постов: " . $e->getMessage();
            return 0;
        }
    }

    // Метод для получения поста по ид
    public function getPostById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT p.*, u.nickname as user_nickname, COUNT(c.id) as comment_count
                                        FROM posts p
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        JOIN users u ON p.user_id = u.id
                                        WHERE p.id = :post_id
                                        GROUP BY p.id, u.nickname;");
            $stmt->execute(['post_id' => $id]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                return $result[0];
            } else {
                return null;
            }
        } catch (PDOException $e) {
            echo "Ошибка при получении поста: " . $e->getMessage();
            return null;
        }
    }

    // Метод для добавления нового поста
    public function addPost(string $title, string $content, int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO posts (title, content, user_id) VALUES (:title, :content, :user_id)");
            $stmt->execute([
                'title' => $title,
                'content' => $content,
                'user_id' => $userId
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

    // Метод для удаления поста и связанных с ним комментариев
    public function deletePostAndComments(int $postId): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("DELETE FROM comments WHERE post_id = :post_id");
            $stmt->execute(['post_id' => $postId]);
            $commentsDeleted = $stmt->rowCount() > 0;

            $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :post_id");
            $stmt->execute(['post_id' => $postId]);
            $postDeleted = $stmt->rowCount() > 0;


            if ($commentsDeleted && $postDeleted) {
                $this->pdo->commit();
                return true;
            } elseif ($postDeleted) {
                $this->pdo->commit();
                return true;
            } else{
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                return false;
            }

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            } 
            echo("Ошибка при удалении поста и комментариев: " . $e->getMessage());
            return false;
        }
    }

    // Метод для получения коментариев по ид поста
    public function getCommentsById($postId)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT c.*, u.nickname as user_nickname
                                        FROM comments c
                                        JOIN users u ON c.user_id = u.id
                                        WHERE c.post_id = :post_id");
            $stmt->execute(['post_id' => $postId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении коментариев по ид поста: " . $e->getMessage();
            return [];
        }
    }

    // Метод для добавления коментария по ид поста
    public function addComment($content, $postId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO comments(content, post_id, user_id)
                                        VALUES (:content, :post_id, :user_id);");
            $stmt->execute([
                'content' => $content,
                'post_id' => $postId,
                'user_id' => $userId
            ]);
            return true;
        } catch (PDOException $e) {
            echo "Ошибка при добавлении коментария: " . $e->getMessage();
            return false;
        }
    }

    // Метод для добавления лайка по ид поста
    public function addLike(int $postId): bool
    {
        try {
            $stmt = $this->pdo->prepare("SELECT likes FROM posts WHERE id = :post_id");
            $stmt->execute(['post_id' => $postId]);
            $postLikes = $stmt->fetchColumn();
            $newLikes = $postLikes + 1;
            $stmt = $this->pdo->prepare("UPDATE posts SET likes = :newLikes
                                        WHERE id = :post_id");
            $stmt->execute([
                ':newLikes' => $newLikes,
                'post_id' => $postId
            ]);
            return $stmt->rowCount() !== 0;
        } catch (PDOException $e) {
            echo "Ошибка при добавлении лайка по ид поста: " . $e->getMessage();
            return false;
        }
    }

    // Метод для получения информации о пользователе по его ID
    public function getUserInfo($user_id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT u.*, r.name AS role_name 
                                        FROM users u 
                                        JOIN roles r ON u.role_id = r.id 
                                        WHERE u.id = :user_id");
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
            $stmt = $this->pdo->query("SELECT u.*, r.name as role_name 
                                       FROM users u 
                                       JOIN roles r ON u.role_id = r.id 
                                       ORDER BY u.id ASC");
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
    public function deleteUser(int $user_id): bool
    {
        try {
            $this->pdo->beginTransaction();

            // Проверяем, существует ли пользователь
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            if ($stmt->fetchColumn() == 0) {
                throw new \Exception("Пользователь с ID {$user_id} не найден.");
            }

            // Получаем все ID постов, написанных пользователем
            $stmt = $this->pdo->prepare("SELECT id FROM posts WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $postIds = $stmt->fetchAll(\PDO::FETCH_COLUMN); // Получаем массив ID постов

            // Если есть посты, удаляем комментарии к этим постам
            if (!empty($postIds)) {
                // Создаем строку с плейсхолдерами для IN ()
                $placeholders = str_repeat('?,', count($postIds) - 1) . '?';

                // Подготавливаем запрос на удаление комментариев
                $stmt = $this->pdo->prepare("DELETE FROM comments WHERE post_id IN ($placeholders)");

                // Выполняем запрос, передавая массив ID постов
                $stmt->execute($postIds);
            }

            // Удаляем все посты (автоматически удалятся комментарии благодаря ON DELETE CASCADE, если настроено)
            $stmt = $this->pdo->prepare("DELETE FROM posts WHERE user_id = ?");
            $stmt->execute([$user_id]);

            // Удаляем пользователя
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);

            $this->pdo->commit();
            return true;
        } catch (\PDOException | \Exception $e) {
            $this->pdo->rollBack();
            echo("Ошибка при удалении пользователя: " . $e->getMessage());
            return false;
        }
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

    // Метод для авторизации пользователя
    public function authorizationUser($nickname, $password)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT u.*, r.name AS role_name 
                                        FROM users u JOIN roles r ON u.role_id = r.id 
                                        WHERE u.nickname = :nickname");
            $stmt->execute(['nickname' => $nickname]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                return $user;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Ошибка при авторизации пользователя: " . $e->getMessage();
            return false;
        }
    }

    // Метод для проверки занятого ника
    public function checkUserNickname(string $nickname): bool
    {
        try {
            $stmt = $this->pdo->prepare("SELECT u.*, r.name AS role_name 
                                        FROM users u JOIN roles r ON u.role_id = r.id 
                                        WHERE u.nickname = :nickname");
            $stmt->execute(['nickname' => $nickname]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

           return !!$user;
        } catch (PDOException $e) {
            echo "Ошибка при проверке занятости ника: " . $e->getMessage();
            return false;
        }
    }

    // Метод для получения списка ролей
    public function getRoles(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT r.name 
                                        FROM roles r");

           return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (PDOException $e) {
            echo "Ошибка при получении списка ролей: " . $e->getMessage();
            return [];
        }
    }
}

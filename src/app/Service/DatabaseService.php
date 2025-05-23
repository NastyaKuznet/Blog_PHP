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

    // Метод для получения всех опубликованных постов
    public function getAllPosts()
    {
        try {
            $stmt = $this->pdo->query("SELECT p.*, 
                                            u.nickname as user_nickname, 
                                            u2.nickname as last_editor_nickname,
                                            ca.id as category_id,
                                            ca.name as category_name,
                                            COUNT(l.id) as like_count,
                                            COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
                                        FROM posts p
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        LEFT JOIN likes l ON p.id = l.post_id
                                        LEFT JOIN category_posts cp ON cp.post_id = p.id
                                        LEFT JOIN categories ca ON ca.id = cp.category_id
                                        JOIN users u ON p.author_id = u.id
                                        JOIN users u2 ON p.last_editor_id = u2.id
                                        WHERE p.is_publish = true and p.is_delete = false
                                        GROUP BY p.id, u.nickname, u2.nickname, ca.id;");
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
            $stmt = $this->pdo->query("SELECT p.* , 
                                                u.nickname as user_nickname, 
                                                u2.nickname as last_editor_nickname,
                                                ca.id as category_id,
                                                ca.name as category_name,
                                                COUNT(l.id) as like_count,
                                                COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
                                        FROM posts p 
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        LEFT JOIN likes l ON p.id = l.post_id
                                        LEFT JOIN category_posts cp ON cp.post_id = p.id
                                        LEFT JOIN categories ca ON ca.id = cp.category_id
                                        JOIN users u ON p.author_id = u.id  
                                        JOIN users u2 ON p.last_editor_id = u2.id
                                        WHERE p.is_publish = true and p.is_delete = false
                                        GROUP BY p.id, u.nickname, u2.nickname, ca.id
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
            $stmt = $this->pdo->query("SELECT p.*, 
                                            u.nickname as user_nickname, 
                                            u2.nickname as last_editor_nickname,
                                            ca.id as category_id,
                                            ca.name as category_name,
                                            COUNT(l.id) as like_count,
                                            COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
                                        FROM posts p 
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        LEFT JOIN likes l ON p.id = l.post_id
                                        LEFT JOIN category_posts cp ON cp.post_id = p.id
                                        LEFT JOIN categories ca ON ca.id = cp.category_id
                                        JOIN users u ON p.author_id = u.id
                                        JOIN users u2 ON p.last_editor_id = u2.id
                                        WHERE p.is_publish = true and p.is_delete = false
                                        GROUP BY p.id, u.nickname, u2.nickname, ca.id
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
            $stmt = $this->pdo->prepare("SELECT p.*, 
                                            u.nickname as user_nickname, 
                                            u2.nickname as last_editor_nickname,
                                            ca.id as category_id,
                                            ca.name as category_name,
                                            COUNT(l.id) as like_count,
                                            COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
                                        FROM posts p 
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        LEFT JOIN likes l ON p.id = l.post_id
                                        LEFT JOIN category_posts cp ON cp.post_id = p.id
                                        LEFT JOIN categories ca ON ca.id = cp.category_id
                                        JOIN users u ON p.author_id = u.id
                                        JOIN users u2 ON p.last_editor_id = u2.id 
                                        WHERE u.nickname = :author_nickname AND p.is_publish = true AND p.is_delete = false
                                        GROUP BY p.id, u.nickname, u2.nickname, ca.id;");
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
            $stmt = $this->pdo->query("SELECT p.*, 
                                            u.nickname as user_nickname,
                                            u2.nickname as last_editor_nickname,
                                            ca.id as category_id,
                                            ca.name as category_name,
                                            COUNT(l.id) as like_count,
                                            COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
                                        FROM posts p 
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        LEFT JOIN likes l ON p.id = l.post_id
                                        LEFT JOIN category_posts cp ON cp.post_id = p.id
                                        LEFT JOIN categories ca ON ca.id = cp.category_id
                                        JOIN users u ON p.author_id = u.id
                                        JOIN users u2 ON p.last_editor_id = u2.id 
                                        WHERE p.is_publish = true and p.is_delete = false
                                        GROUP BY p.id, u.nickname, u2.nickname, ca.id
                                        ORDER BY like_count ASC;");
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
            $stmt = $this->pdo->query("SELECT p.*, 
                                            u.nickname as user_nickname, 
                                            u2.nickname as last_editor_nickname,
                                            ca.id as category_id,
                                            ca.name as category_name,
                                            COUNT(l.id) as like_count,
                                            COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
                                        FROM posts p 
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        LEFT JOIN likes l ON p.id = l.post_id
                                        JOIN users u ON p.author_id = u.id  
                                        JOIN users u2 ON p.last_editor_id = u2.id
                                        LEFT JOIN category_posts cp ON cp.post_id = p.id
                                        LEFT JOIN categories ca ON ca.id = cp.category_id
                                        WHERE p.is_publish = true and p.is_delete = false
                                        GROUP BY p.id, u.nickname, u2.nickname, ca.id
                                        ORDER BY like_count DESC;");
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
            $stmt = $this->pdo->query("SELECT p.*, 
                                            u.nickname as user_nickname,
                                            u2.nickname as last_editor_nickname,
                                            ca.id as category_id,
                                            ca.name as category_name,
                                            COUNT(l.id) as like_count,
                                            COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
                                        FROM posts p 
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        LEFT JOIN likes l ON p.id = l.post_id
                                        LEFT JOIN category_posts cp ON cp.post_id = p.id
                                        LEFT JOIN categories ca ON ca.id = cp.category_id
                                        JOIN users u ON p.author_id = u.id
                                        JOIN users u2 ON p.last_editor_id = u2.id
                                        WHERE p.is_publish = true and p.is_delete = false
                                        GROUP BY p.id, u.nickname, u2.nickname, ca.id
                                        ORDER BY comment_count ASC;");
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
            $stmt = $this->pdo->query("SELECT p.*, 
                                            u.nickname as user_nickname, 
                                            u2.nickname as last_editor_nickname,
                                            ca.id as category_id,
                                            ca.name as category_name,
                                            COUNT(l.id) as like_count,
                                            COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
                                        FROM posts p 
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        LEFT JOIN likes l ON p.id = l.post_id
                                        LEFT JOIN category_posts cp ON cp.post_id = p.id
                                        LEFT JOIN categories ca ON ca.id = cp.category_id
                                        JOIN users u ON p.author_id = u.id 
                                        JOIN users u2 ON p.last_editor_id = u2.id 
                                        WHERE p.is_publish = true and p.is_delete = false
                                        GROUP BY p.id, u.nickname, u2.nickname, ca.id
                                        ORDER BY comment_count DESC;");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении постов по комментариям: " . $e->getMessage();
            return [];
        }
    }

    public function getPostsByTag(string $tagName): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT p.* , u.nickname as user_nickname, COUNT(c.id) as comment_count
                                        FROM tags t 
                                        JOIN posts p ON t.post_id = p.id
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        JOIN users u ON p.user_id = u.id  
                                        WHERE t.name = :tag_name
                                        GROUP BY p.id, u.nickname");
            $stmt->execute(['tag_name' => $tagName]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении постов по тегу: " . $e->getMessage();
            return [];
        }
    }

    // Получение всех постов по ид автора
    public function getPostsByUserId(int $userId):array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT p.*, 
                                            u.nickname as user_nickname,
                                            u2.nickname as last_editor_nickname, 
                                            ca.id as category_id,
                                            ca.name as category_name,
                                            COUNT(l.id) as like_count,
                                            COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
                                        FROM posts p 
                                        LEFT JOIN comments c ON p.id = c.post_id
                                        LEFT JOIN likes l ON p.id = l.post_id
                                        LEFT JOIN category_posts cp ON cp.post_id = p.id
                                        LEFT JOIN categories ca ON ca.id = cp.category_id
                                        JOIN users u ON p.author_id = u.id  
                                        JOIN users u2 ON p.last_editor_id = u2.id
                                        WHERE u.id = :user_id and p.is_publish = true
                                        GROUP BY p.id, u.nickname, u2.nickname, ca.id;");
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении постов по юзер id: " . $e->getMessage();
            return [];
        }
    }

    // Метод для получения всех не опубликованных постов
    public function getAllNonPublishPosts()
    {
        try {
            $stmt = $this->pdo->query("SELECT p.*, 
                                            u.nickname as user_nickname, 
                                            u2.nickname as last_editor_nickname
                                        FROM posts p
                                        JOIN users u ON p.author_id = u.id
                                        JOIN users u2 ON p.last_editor_id = u2.id
                                        WHERE p.is_publish = false and p.is_delete = false
                                        GROUP BY p.id, u.nickname, u2.nickname;");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении постов: " . $e->getMessage();
            return [];
        }
    }

    // Метод для получения поста по ид
    function getPostById(int $postId): ?array
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = :postId");
            $stmt->execute([':postId' => $postId]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$post) {
                echo "Пост с ID $postId не найден.\n";
                return null;
            }

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :postId");
            $stmt->execute([':postId' => $postId]);
            $likeCount = $stmt->fetchColumn();

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = :postId AND is_delete = false");
            $stmt->execute([':postId' => $postId]);
            $commentCount = $stmt->fetchColumn();

            $authorId = $post['author_id'];
            $stmt = $this->pdo->prepare("SELECT nickname FROM users WHERE id = :authorId");
            $stmt->execute([':authorId' => $authorId]);
            $authorNickname = $stmt->fetchColumn();

            $editorNickname = null;
            if (isset($post['last_editor_id']) && $post['last_editor_id'] !== null) 
            {
                $lastEditorId = $post['last_editor_id'];
                $stmt = $this->pdo->prepare("SELECT nickname FROM users WHERE id = :lastEditorId");
                $stmt->execute([':lastEditorId' => $lastEditorId]);
                $editorNickname = $stmt->fetchColumn();
            }
            $stmt = $this->pdo->prepare("SELECT category_id FROM category_posts WHERE post_id = :post_id");
            $stmt->execute([':post_id' => $postId]);
            $categoryId = $stmt->fetchColumn();

            $stmt = $this->pdo->prepare("SELECT name FROM categories WHERE id = :category_id");
            $stmt->execute([':category_id' => $categoryId]);
            $categoryName = $stmt->fetchColumn();

            $this->pdo->commit();

            $result = [
                'post' => $post,
                'like_count' => $likeCount,
                'comment_count' => $commentCount,
                'author_nickname' => $authorNickname,
                'last_editor_nickname' => $editorNickname,
                'category_id' => $categoryId,
                'category_name' => $categoryName,
            ];

            return $result;

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            echo "Ошибка в транзакции: " . $e->getMessage() . "\n";
            return null;
        }
    }

    // Метод для получения неопубликованного поста по ид
    function getNonPublishPostById(int $postId): ?array
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = :postId");
            $stmt->execute([':postId' => $postId]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$post) {
                echo "Пост с ID $postId не найден.\n";
                return null;
            }

            $authorId = $post['author_id'];
            $stmt = $this->pdo->prepare("SELECT nickname FROM users WHERE id = :authorId");
            $stmt->execute([':authorId' => $authorId]);
            $authorNickname = $stmt->fetchColumn();

            $editorNickname = null;
            if (isset($post['last_editor_id']) && $post['last_editor_id'] !== null) 
            {
                $lastEditorId = $post['last_editor_id'];
                $stmt = $this->pdo->prepare("SELECT nickname FROM users WHERE id = :lastEditorId");
                $stmt->execute([':lastEditorId' => $lastEditorId]);
                $editorNickname = $stmt->fetchColumn();
            }

            $stmt = $this->pdo->prepare("SELECT category_id FROM category_posts WHERE post_id = :post_id");
            $stmt->execute([':post_id' => $postId]);
            $categoryId = $stmt->fetchColumn();

            $categoryName = "";
            if($categoryId)
            {
                $stmt = $this->pdo->prepare("SELECT name FROM categories WHERE id = :category_id");
                $stmt->execute([':category_id' => $categoryId]);
                $categoryName = $stmt->fetchColumn();
            }

            $this->pdo->commit();

            $result = [
                'post' => $post,
                'author_nickname' => $authorNickname,
                'last_editor_nickname' => $editorNickname,
                'category_id' => $categoryId,
                'category_name' => $categoryName,
            ];

            return $result;

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            echo "Ошибка в транзакции: " . $e->getMessage() . "\n";
            return null;
        }
    }

    // Метод для получения количества опубликованных постов по юзерИд
    public function getCountPostsByUserId(int $userId):int
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) 
                                        FROM posts p
                                        WHERE p.author_id = :user_id and p.is_publish = true");
            $stmt->execute(['user_id' => $userId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            echo "Ошибка при получении количество постов: " . $e->getMessage();
            return 0;
        }
    }

    // Метод для добавления нового поста
    public function addPost(string $title, string $preview, string $content, int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO posts (title, preview, content, author_id, last_editor_id) VALUES (:title, :preview, :content, :author_id, :author_id)");
            $stmt->execute([
                'title' => $title,
                'preview' => $preview,
                'content' => $content,
                'author_id' => $userId
            ]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            echo "Ошибка при добавлении поста: " . $e->getMessage();
            return 0;
        }
    }

    // Метод для редактирования существующего поста
    public function editPost(int $postId, string $title, string $preview, string $content, int $editorId): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE posts 
                                        SET title = :title, 
                                            preview = :preview,
                                            content = :content,
                                            edit_date = CURRENT_TIMESTAMP,
                                            last_editor_id = :editor_id
                                        WHERE id = :post_id");
            $stmt->execute([
                'title' => $title,
                'preview' => $preview,
                'content' => $content,
                'editor_id' => $editorId,
                'post_id' => $postId
            ]);
            return true;
        } catch (PDOException $e) {
            echo "Ошибка при редактировании поста: " . $e->getMessage();
            return false;
        }
    }

    // Метод для удаления поста
    public function deletePost(int $postId): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE posts 
                                        SET delete_date = CURRENT_TIMESTAMP, 
                                            is_delete = true
                                        WHERE id = :post_id");
            $stmt->execute([
                'post_id' => $postId
            ]);
            return true;
        } catch (PDOException $e) {
            echo "Ошибка при установки даты удаления поста: " . $e->getMessage();
            return false;
        }
    }

    // Метод для публикации поста
    public function publishPost(int $postId): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE posts 
                                        SET publish_date = CURRENT_TIMESTAMP, 
                                            is_publish = true
                                        WHERE id = :post_id");
            $stmt->execute([
                'post_id' => $postId
            ]);
            return true;
        } catch (PDOException $e) {
            echo "Ошибка при публикации поста: " . $e->getMessage();
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
                                        WHERE c.post_id = :post_id AND c.is_delete = FALSE");
            $stmt->execute(['post_id' => $postId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении коментариев по ид поста: " . $e->getMessage();
            return [];
        }
    }

    public function getCommentById(int $commentId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM comments WHERE id = :id AND is_delete = FALSE");
            $stmt->execute(['id' => $commentId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Ошибка при получении комментария: " . $e->getMessage());
            return null;
        }
    }

    // Метод для добавления коментария по ид поста
    public function addComment($content, $postId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO comments (content, post_id, user_id, created_date, is_edit, is_delete) 
                                        VALUES (:content, :post_id, :user_id, CURRENT_TIMESTAMP, FALSE, FALSE)");
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

    public function updateComment(int $commentId, string $newContent): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE comments
                                        SET content = :content,
                                            edit_date = CURRENT_TIMESTAMP,
                                            is_edit = TRUE
                                        WHERE id = :comment_id");
            $stmt->execute([
                'content' => $newContent,
                'comment_id' => $commentId
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            echo "Ошибка при обновлении комментария: " . $e->getMessage();
            return false;
        }
    }

    public function deleteComment(int $commentId): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE comments
                                        SET is_delete = TRUE,
                                            delete_date = CURRENT_TIMESTAMP
                                        WHERE id = :comment_id");
            $stmt->execute(['comment_id' => $commentId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            echo "Ошибка при мягком удалении комментария: " . $e->getMessage();
            return false;
        } 
    }

    // Метод для проверки поставлен ли лайк пользателем по определенному посту
    public function checkLikeByPostIdAndUserId($postId, $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * 
                                        FROM likes l
                                        WHERE l.post_id = :post_id AND l.user_id = :user_id;
                                        ");
            $stmt->execute([
                'post_id' => $postId,
                'user_id' => $userId
            ]);
            $count = $stmt->fetchColumn();
            return $count > 0;
        } catch (PDOException $e) {
            echo "Ошибка при проверке лайка: " . $e->getMessage();
            return false;
        }
    }

    // Метод для добавления лайка по ид поста
    public function addLike(int $postId, int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO likes (post_id, user_id) 
                                        VALUES (:post_id, :user_id);");
            $stmt->execute([
                'post_id' => $postId,
                'user_id' => $userId
            ]);
            return true;
        } catch (PDOException $e) {
            echo "Ошибка при добавлении лайка: " . $e->getMessage();
            return false;
        }
    }

    public function deleteLike(int $postId, int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM likes l
                                        WHERE l.post_id = :post_id AND l.user_id = :user_id;
                                        ");
            $stmt->execute([
                'post_id' => $postId,
                'user_id' => $userId
            ]);
            return true;
        } catch (PDOException $e) {
            echo "Ошибка при удалении лайка: " . $e->getMessage();
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
    public function addUser($nickname, $password)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (nickname, password, role_id) VALUES (:nickname, :password, 2)");
            $stmt->execute([
                'nickname' => $nickname,
                'password' => password_hash($password, PASSWORD_DEFAULT) // Хэшируем пароль
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
  
      // Метод для изменения статуса "забанен"
    public function toggleUserBan(int $userId, bool $isBanned): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET is_banned = ? WHERE id = ?");
            $stmt->execute([$isBanned ? 't' : 'f', $userId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            echo "Ошибка при изменении статуса забаненности пользователя: " . $e->getMessage();
            return false;
        }
    }

    // Метод для получения списка ролей
    public function getRoles(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT r.id, r.name 
                                        FROM roles r");

           return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            echo "Ошибка при получении списка ролей: " . $e->getMessage();
            return [];
        }
    }

    // Метод для получения всех тегов
    public function getAllTags(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM tags");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении тегов: " . $e->getMessage();
            return [];
        }
    }

    // Метод для получения тегов поста
    public function getTagsByPostId(int $postId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*
                FROM tags t
                WHERE t.post_id = :post_id
            ");
            $stmt->execute(['post_id' => $postId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении тегов поста: " . $e->getMessage();
            return false;
        }
    }

    // Метод для получения всех категорий
    public function getAllCategories(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY id ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении категорий: " . $e->getMessage();
            return [];
        }
    }


    public function addTag(string $name, int $postId): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO tags (name, post_id) VALUES (:name, :post_id);");
            $stmt->execute(['name' => $name, 'post_id' => $postId]);
            return true;
        } catch (PDOException $e) {
            error_log("Ошибка при добавлении тега: " . $e->getMessage());
            return false;
        }
    }

    public function deleteTag(string $name, int $postId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM tags t
                                        WHERE t.name = :name AND t.post_id = :post_id;");
            $stmt->execute([
                'name' => $name, 
                'post_id' => $postId
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Ошибка при добавлении тега: " . $e->getMessage());
            return false;
        }
    }

    // Метод для получения категории по id
    public function getCategoryById(int $categoryId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = :category_id");
            $stmt->execute(['category_id' => $categoryId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении категории: " . $e->getMessage();
            return null;
        }
    }

    // Метод для добавления категории
    public function addCategory(string $name, ?int $parentId): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO categories (name, parent_id) VALUES (:name, :parent_id)");
            $stmt->execute([
                'name' => $name,
                'parent_id' => $parentId
            ]);
            return true;
        } catch (PDOException $e) {
            echo "Ошибка при добавлении категории: " . $e->getMessage();
            return false;
        }
    }

    // Получить ID тега по имени
    public function getTagIdByName(string $name): ?int
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM tags WHERE name = :name LIMIT 1");
            $stmt->execute(['name' => $name]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['id'] : null;
        } catch (PDOException $e) {
            error_log("Ошибка при получении ID тега: " . $e->getMessage());
            return null;
        }
    }

    // Добавить пост и вернуть его ID
    public function addPostAndGetId(string $title, string $content, int $userId): ?int
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO posts (title, content, user_id) VALUES (:title, :content, :user_id) RETURNING id");
            $stmt->execute([
                'title' => $title,
                'content' => $content,
                'user_id' => $userId
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['id'] : null;
        } catch (PDOException $e) {
            error_log("Ошибка при добавлении поста: " . $e->getMessage());
            return null;
        }
    }
=======
    // Метод для получения категории поста
    public function getCategoriesByPostId(int $postId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*
                FROM categories c
                JOIN category_posts cp ON c.id = cp.category_id
                WHERE cp.post_id = :post_id
            ");
            $stmt->execute(['post_id' => $postId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении категорий поста: " . $e->getMessage();
            return [];
        }
    }

    // Метод для получения постов по id категории
    public function getPostsByCategoryId(int $categoryId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                WITH RECURSIVE category_tree AS (
                SELECT id FROM categories WHERE id = :category_id
                UNION ALL
                SELECT c.id 
                FROM categories c
                INNER JOIN category_tree ct ON c.parent_id = ct.id
            )
            SELECT p.*, 
                u.nickname as user_nickname,
                u2.nickname as last_editor_nickname,
                ca.id as category_id,
                ca.name as category_name,  
                COUNT(l.id) as like_count,
                COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
            FROM posts p
            LEFT JOIN comments c ON p.id = c.post_id
            LEFT JOIN likes l ON p.id = l.post_id
            JOIN category_posts cp ON p.id = cp.post_id
             LEFT JOIN categories ca ON ca.id = cp.category_id
            JOIN users u ON p.author_id = u.id
            JOIN users u2 ON p.last_editor_id = u2.id
            WHERE cp.category_id IN (SELECT id FROM category_tree) AND p.is_publish = true
            GROUP BY p.id, u.nickname, u2.nickname, ca.id;
            ");

            $stmt->execute(['category_id' => $categoryId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Ошибка при получении постов по категории: " . $e->getMessage();
            return [];
        }
    }                         

    // Метод для удаления категории
    public function deleteCategory(int $categoryId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM category_posts WHERE category_id = :category_id");
            $stmt->execute(['category_id' => $categoryId]);

            $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = :category_id");
            $stmt->execute(['category_id' => $categoryId]);

            return true;
        } catch (PDOException $e) {
            error_log("Ошибка удаления категории: " . $e->getMessage());
            return false;
        }
    }

    public function connectPostAndCategory(int $postId, int $categoryId): bool
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("DELETE FROM category_posts 
                                        WHERE post_id = :post_id;");

            $stmt->execute(['post_id' => $postId]);

            $stmt = $this->pdo->prepare("INSERT INTO category_posts (category_id, post_id)
                                        VALUES (:category_id, :post_id);");

            $stmt->execute(['category_id' => $categoryId,
                            'post_id' => $postId]);
            $this->pdo->commit();

            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Ошибка добавления связи между постом и категорией: " . $e->getMessage());
            return false;
        }
    }
}

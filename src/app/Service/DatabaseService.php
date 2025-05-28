<?php
namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Service\interfaces\DatabaseServiceInterface;

use PDO;
use PDOException;
use Throwable;

class DatabaseService implements DatabaseServiceInterface
{
    public $pdo;
    private array $preparedStatements = [];

    public function __construct(array $config)
    {
        $host = $config['db']['host'];
        $dbname = $config['db']['dbname'];
        $username = $config['db']['username'];
        $password = $config['db']['password'];

        try {
            $this->pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            $this->prepareStatements();
        } catch (PDOException $e) {
            throw new \RuntimeException("Database connection failed", 0, $e);
        }
    }

    private function prepareStatements(): void
    {
        $sqlDir = __DIR__.'/sql';
        $queries = $this->loadSqlQueriesRecursively($sqlDir);
        
        foreach ($queries as $name => $sql) {
            try {
                $this->preparedStatements[$name] = $this->pdo->prepare($sql);
            } catch (PDOException $e) {
                throw new \RuntimeException("Failed to prepare database statements", 0, $e);
            }
        }
    }

    private function loadSqlQueriesRecursively(string $baseDir, string $subDir = ''): array
    {
        $queries = [];
        $currentDir = $baseDir . ($subDir ? DIRECTORY_SEPARATOR . $subDir : '');
        
        if (!is_dir($currentDir)) {
            return $queries;
        }
        
        $items = scandir($currentDir);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $path = $currentDir . DIRECTORY_SEPARATOR . $item;
            
            if (is_dir($path)) {
                $subQueries = $this->loadSqlQueriesRecursively($baseDir, $subDir . DIRECTORY_SEPARATOR . $item);
                $queries = array_merge($queries, $subQueries);
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'sql') {
                $queryName = pathinfo($item, PATHINFO_FILENAME);
                $queries[$queryName] = file_get_contents($path);
            }
        }
        
        return $queries;
    }

    // Метод для получения всех опубликованных постов
    public function getAllPosts(): array
    {
        try {
            $stmt = $this->preparedStatements['getAllPosts'];
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            throw new \RuntimeException("Error receiving posts", 0, $e);
        }
    }

    // Метод для получения постов, отсортированных по нику автора
    public function getPostsByAuthorAlphabetical(): array
    {
        try {
            $stmt = $this->preparedStatements['getPostsByAuthorAlphabetical'];
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving posts by author asc", 0, $e);
        }
    }

    // Метод для получения постов по автору, отсортированных по нику автора в обратном порядке
    public function getPostsByAuthorReverseAlphabetical(): array
    {
        try {
            $stmt = $this->preparedStatements['getPostsByAuthorReverseAlphabetical'];
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving posts by author desc", 0, $e);
        }
    }

    // Метод для получения всех постов по конкретному нику автора
    public function getPostsByAuthor($author_login): array
    {
        try {
            $stmt = $this->preparedStatements['getPostsByAuthor'];
            $stmt->execute(['author_login' => $author_login]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving posts by author", 0, $e);
        }
    }

    // Метод для получения всех постов, отсортированных по количеству лайков в порядке возрастания
    public function getPostsByLikesAscending(): array
    {
        try {
            $stmt = $this->preparedStatements['getPostsByLikesAscending'];
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving posts by likes asc", 0, $e);
        }
    }

    // Метод для получения всех постов, отсортированных по количеству лайков в порядке убывания
    public function getPostsByLikesDescending(): array
    {
        try {
            $stmt = $this->preparedStatements['getPostsByLikesDescending'];
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving posts by likes desc", 0, $e);
        }
    }

    // Метод для получения всех постов, отсортированных по количеству комментариев в порядке возрастания
    public function getPostsByCommentsAscending(): array
    {
        try {
            $stmt = $this->preparedStatements['getPostsByCommentsAscending'];
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving posts by count comments asc", 0, $e);
        }
    }

    // Метод для получения всех постов, отсортированных по количеству комментариев в порядке убывания
    public function getPostsByCommentsDescending(): array
    {
        try {
            $stmt = $this->preparedStatements['getPostsByCommentsDescending'];
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving posts by count comments desc", 0, $e);
        }
    }

    public function getPostsByTag(string $tagName): array
    {
        try {
            $stmt = $this->preparedStatements['getPostsByTag'];
            $stmt->execute(['tag_name' => $tagName]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving posts by tag", 0, $e);
        }
    }

    // Получение всех постов по ид автора
    public function getPostsByUserId(int $userId):array
    {
        try {
            $stmt = $this->preparedStatements['getPostsByUserId'];
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving posts by user id", 0, $e);
        }
    }

    // Метод для получения всех не опубликованных постов
    public function getAllNonPublishPosts(): array
    {
        try {
            $stmt = $this->preparedStatements['getAllNonPublishPosts'];
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving all non publish posts", 0, $e);
        }
    }

    // Метод для получения поста по ид
    public function getPostById(int $postId): ?array
    { 
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->preparedStatements['getPostById'];
            $stmt->execute([':postId' => $postId]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$post) {
                echo "Пост с ID $postId не найден.\n";
                return null;
            }

            $stmt = $this->preparedStatements['getLikeCountForPost'];
            $stmt->execute([':postId' => $postId]);
            $likeCount = $stmt->fetchColumn();

            $stmt = $this->preparedStatements['getCommentCountForPost'];
            $stmt->execute([':postId' => $postId]);
            $commentCount = $stmt->fetchColumn();

            $authorId = $post['author_id'];
            $stmt = $this->preparedStatements['getUserLoginById'];
            $stmt->execute([':user_id' => $authorId]);
            $authorLogin = $stmt->fetchColumn();

            $editorLogin = null;
            if (isset($post['last_editor_id']) && $post['last_editor_id'] !== null) 
            {
                $lastEditorId = $post['last_editor_id'];
                $stmt = $this->preparedStatements['getUserLoginById'];
                $stmt->execute([':user_id' => $lastEditorId]);
                $editorLogin = $stmt->fetchColumn();
            }
            $stmt = $this->preparedStatements['getPostCategoryId'];
            $stmt->execute([':post_id' => $postId]);
            $categoryId = $stmt->fetchColumn();

            $stmt = $this->preparedStatements['getCategoryNameById'];
            $stmt->execute([':category_id' => $categoryId]);
            $categoryName = $stmt->fetchColumn();

            $this->pdo->commit();

            $result = [
                'post' => $post,
                'like_count' => $likeCount,
                'comment_count' => $commentCount,
                'author_login' => $authorLogin,
                'last_editor_login' => $editorLogin,
                'category_id' => $categoryId,
                'category_name' => $categoryName,
            ];

            return $result;

        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new \RuntimeException("Transaction error", 0, $e);
        }
    }

    // Метод для получения неопубликованного поста по ид
    public function getNonPublishPostById(int $postId): ?array
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->preparedStatements['getNonPublishPostById'];
            $stmt->execute([':postId' => $postId]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$post) {
                $this->pdo->rollBack();
                echo "Пост с ID $postId не найден.\n";
                return null;
            }

            $authorId = $post['author_id'];
            $stmt = $this->preparedStatements['getUserLoginById'];
            $stmt->execute([':user_id' => $authorId]);
            $authorLogin = $stmt->fetchColumn();

            $editorLogin = null;
            if (isset($post['last_editor_id']) && $post['last_editor_id'] !== null) 
            {
                $lastEditorId = $post['last_editor_id'];
                $stmt = $this->preparedStatements['getUserLoginById'];
                $stmt->execute([':user_id' => $lastEditorId]);
                $editorLogin = $stmt->fetchColumn();
            }

            $stmt = $this->preparedStatements['getPostCategoryId'];
            $stmt->execute([':post_id' => $postId]);
            $categoryId = $stmt->fetchColumn();

            $categoryName = "";
            if($categoryId)
            {
                $stmt = $this->preparedStatements['getCategoryNameById'];
                $stmt->execute([':category_id' => $categoryId]);
                $categoryName = $stmt->fetchColumn();
            }

            $this->pdo->commit();

            $result = [
                'post' => $post,
                'author_login' => $authorLogin,
                'last_editor_login' => $editorLogin,
                'category_id' => $categoryId,
                'category_name' => $categoryName,
            ];

            return $result;

        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new \RuntimeException("Transaction error", 0, $e);
        }
    }

    // Метод для получения количества опубликованных постов по юзерИд
    public function getCountPostsByUserId(int $userId):int
    {
        try {
            $stmt = $this->preparedStatements['getCountPostsByUserId'];
            $stmt->execute(['user_id' => $userId]);
            return (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving the number of posts by user id", 0, $e);
        }
    }

    // Метод для добавления нового поста
    public function addPost(string $title, string $preview, string $content, int $userId): bool
    {
        try {
            $stmt = $this->preparedStatements['addPost'];
            $stmt->execute([
                'title' => $title,
                'preview' => $preview,
                'content' => $content,
                'author_id' => $userId
            ]);
            return $this->pdo->lastInsertId();
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when adding a post", 0, $e);
        }
    }

    // Метод для редактирования существующего поста
    public function editPost(int $postId, string $title, string $preview, string $content, int $editorId): bool
    {
        try {
            $stmt = $this->preparedStatements['editPost'];
            $stmt->execute([
                'title' => $title,
                'preview' => $preview,
                'content' => $content,
                'editor_id' => $editorId,
                'post_id' => $postId
            ]);
            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when editing a post", 0, $e);
        }
    }

    // Метод для удаления поста
    public function deletePost(int $postId): bool
    {
        try {
            $stmt = $this->preparedStatements['deletePost'];
            $stmt->execute([
                'post_id' => $postId
            ]);
            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when setting the date for deleting a post", 0, $e);
        }
    }

    // Метод для публикации поста
    public function publishPost(int $postId): bool
    {
        try {
            $stmt = $this->preparedStatements['publishPost'];
            $stmt->execute([':post_id' => $postId]);
            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when publishing a post", 0, $e);
        }
    }

    // Метод для получения коментариев по ид поста
    public function getCommentsByPostId(int $postId): array
    {
        try {
            $stmt = $this->preparedStatements['getCommentsByPostId'];
            $stmt->execute(['post_id' => $postId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving comments on the post id", 0, $e);
        }
    }

    public function getCommentById(int $commentId): ?array
    {
        try {
            $stmt = $this->preparedStatements['getCommentById'];
            $stmt->execute(['id' => $commentId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving a comment", 0, $e);
        }
    }

    // Метод для добавления коментария по ид поста
    public function addComment(string $content, int $postId, int $userId): bool
    {
        try {
            $stmt = $this->preparedStatements['addComment'];
            $stmt->execute([
                'content' => $content,
                'post_id' => $postId,
                'user_id' => $userId
            ]);
            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when adding a comment", 0, $e);
        }
    }

    public function updateComment(int $commentId, string $newContent): bool
    {
        try {
            $stmt = $this->preparedStatements['updateComment'];
            $stmt->execute([
                'content' => $newContent,
                'comment_id' => $commentId
            ]);
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error updating a comment", 0, $e);
        }
    }

    public function deleteComment(int $commentId): bool
    {
        try {
            $stmt = $this->preparedStatements['deleteComment'];
            $stmt->execute(['comment_id' => $commentId]);
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error deleting a comment", 0, $e);
        }
    }

    // Метод для проверки поставлен ли лайк пользателем по определенному посту
    public function checkLikeByPostIdAndUserId(int $postId, int $userId): bool
    {
        try {
            $stmt = $this->preparedStatements['checkLikeByPostIdAndUserId'];
            $stmt->execute([
                'post_id' => $postId,
                'user_id' => $userId
            ]);
            $count = $stmt->fetchColumn();
            return $count > 0;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error checking the like", 0, $e);
        }
    }

    // Метод для добавления лайка по ид поста
    public function addLike(int $postId, int $userId): bool
    {
        try {
            $stmt = $this->preparedStatements['addLike'];
            $stmt->execute([
                'post_id' => $postId,
                'user_id' => $userId
            ]);
            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error adding the like", 0, $e);
        }
    }

    public function deleteLike(int $postId, int $userId): bool
    {
        try {
            $stmt = $this->preparedStatements['deleteLike'];
            $stmt->execute([
                'post_id' => $postId,
                'user_id' => $userId
            ]);
            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error deleting the like", 0, $e);
        }
    }

    // Метод для получения информации о пользователе по его ID
    public function getUserInfo(int $user_id): array
    {
        try {
            $stmt = $this->preparedStatements['getUserInfo'];
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving user information", 0, $e);
        }
    }

    // Метод для получения списка всех пользователей с их ID, ником и ролью
    public function getAllUsers(): array
    {
        try {
            $stmt = $this->preparedStatements['getAllUsers'];
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving all users", 0, $e);
        }
    }

    // Метод для изменения роли пользователя
    public function changeUserRole(int $user_id, int $new_role_id): bool
    {
        try {
            $stmt = $this->preparedStatements['changeUserRole'];
            $stmt->execute([
                'new_role_id' => $new_role_id,
                'user_id' => $user_id
            ]);
            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when changing the user role", 0, $e);
        }
    }

    // Метод для добавления нового пользователя
    public function addUser(string $login, string $password): bool
    {
        try {
            $stmt = $this->preparedStatements['addUser'];
            $stmt->execute([
                'login' => $login,
                'password' => password_hash($password, PASSWORD_DEFAULT) // Хэшируем пароль
            ]);
            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when adding a user", 0, $e);
        }
    }

    // Метод для авторизации пользователя
    public function authorizationUser(string $login, string $password): mixed
    {
        try {
            $stmt = $this->preparedStatements['authorizationUser'];
            $stmt->execute(['login' => $login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                return $user;
            } else {
                return false;
            }
        } catch (Throwable $e) {
            throw new \RuntimeException("Error during user authorization", 0, $e);
        }
    }

    // Метод для проверки занятого ника
    public function checkUserLogin(string $login): bool
    {
        try {
            $stmt = $this->preparedStatements['checkUserLogin'];
            $stmt->execute(['login' => $login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

           return !!$user;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when checking the employment of a nickname", 0, $e);
        }
    }
  
      // Метод для изменения статуса "забанен"
    public function toggleUserBan(int $userId, bool $isBanned): bool
    {
        try {
            $stmt = $this->preparedStatements['toggleUserBan'];
            $stmt->execute([$isBanned ? 't' : 'f', $userId]);
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when changing the user's ban status", 0, $e);
        }
    }

    // Метод для получения списка ролей
    public function getRoles(): array
    {
        try {
            $stmt = $this->preparedStatements['getRoles'];
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when getting the list of roles", 0, $e);
        }
    }

    // Метод для получения всех тегов
    public function getAllTags(): array
    {
        try {
            $stmt = $this->preparedStatements['getAllTags'];
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error receiving tags", 0, $e);
        }
    }

    // Метод для получения тегов поста
    public function getTagsByPostId(int $postId): array
    {
        try {
            $stmt = $this->preparedStatements['getTagsByPostId'];
            $stmt->execute(['post_id' => $postId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving post tags", 0, $e);
        }
    }

    // Метод для получения всех категорий
    public function getAllCategories(): array
    {
        try {
            $stmt = $this->preparedStatements['getAllCategories'];
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when getting categories", 0, $e);
        }
    }


    public function addTag(string $name, int $postId): bool
    {
        try {
            $stmt = $this->preparedStatements['addTag'];
            $stmt->execute(['name' => $name, 'post_id' => $postId]);
            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when adding the tag", 0, $e);
        }
    }

    public function deleteTag(string $name, int $postId): bool
    {
        try {
            $stmt = $this->preparedStatements['deleteTag'];
            $stmt->execute([
                'name' => $name, 
                'post_id' => $postId
            ]);
            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when adding the tag", 0, $e);
        }
    }

    // Метод для получения категории по id
    public function getCategoryById(int $categoryId): ?array
    {
        try {
            $stmt = $this->preparedStatements['getCategoryById'];
            $stmt->execute(['category_id' => $categoryId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when getting the category", 0, $e);
        }
    }

    // Метод для добавления категории
    public function addCategory(string $name, ?int $parentId): bool
    {
        try {
            $stmt = $this->preparedStatements['addCategory'];
            $stmt->execute([
                'name' => $name,
                'parent_id' => $parentId
            ]);
            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when adding a category", 0, $e);
        }
    }

    // Получить ID тега по имени
    public function getTagIdByName(string $name): ?int
    {
        try {
            $stmt = $this->preparedStatements['getTagIdByName'];
            $stmt->execute(['name' => $name]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['id'] : null;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when getting the tag ID", 0, $e);
        }
    }

    // Добавить пост и вернуть его ID
    public function addPostAndGetId(string $title, string $preview, string $content, int $userId): ?int
    {
        try {
            $stmt = $this->preparedStatements['addPostAndGetId'];
            $stmt->execute([
                'title' => $title,
                'preview' => $preview,
                'content' => $content,
                'user_id' => $userId
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['id'] : null;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when adding a post", 0, $e);
        }
    }

    // Метод для получения категории поста
    public function getCategoriesByPostId(int $postId): array
    {
        try {
            $stmt = $this->preparedStatements['getCategoriesByPostId'];
            $stmt->execute(['post_id' => $postId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when getting post categories", 0, $e);
        }
    }

    // Метод для получения постов по id категории
    public function getPostsByCategoryId(int $categoryId): array
    {
        try {
            $stmt = $this->preparedStatements['getPostsByCategoryId'];
            $stmt->execute(['category_id' => $categoryId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when receiving posts by category", 0, $e);
        }
    }                         

    // Метод для удаления категории
    public function deleteCategory(int $categoryId): bool
    {
        try {
            $this->pdo->beginTransaction();
            $this->markAsDeletedRecursive($categoryId);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException("Error when marking a category as deleted", 0, $e);
        }
    }

    // Рекурсивная функция для пометки категории и всех подкатегорий как удалённых
    private function markAsDeletedRecursive(int $categoryId): void
    {
        $stmt = $this->preparedStatements['markCategoryAsDeleted'];
        $stmt->execute([':category_id' => $categoryId]);

        $stmt = $this->preparedStatements['getChildCategories'];
        $stmt->execute(['category_id' => $categoryId]);

        $children = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($children as $childId) {
            $this->markAsDeletedRecursive($childId);
        }
    }

    // Метод для добавления связи между постом и категорией
    public function connectPostAndCategory(int $postId, int $categoryId): bool
    {
        try {
            $this->pdo->beginTransaction();
            
            // Проверяем, что категория не удалена
            $stmt = $this->preparedStatements['checkCategoryActive'];
            $stmt->execute(['category_id' => $categoryId]);
            if (!$stmt->fetch()) {
                throw new PDOException("Категория не найдена или удалена");
            }

            $stmt = $this->preparedStatements['deletePostCategoryLinks'];
            $stmt->execute(['post_id' => $postId]);

            $stmt = $this->preparedStatements['insertPostCategoryLink'];
            $stmt->execute(['category_id' => $categoryId, 'post_id' => $postId]);
            
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new \RuntimeException("Error adding a link between a post and a category", 0, $e);
        }
    }
}

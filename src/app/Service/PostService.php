<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Service\interfaces\DatabaseServiceInterface;
use NastyaKuznet\Blog\Model\Post;
use NastyaKuznet\Blog\Model\Tag;
use NastyaKuznet\Blog\Service\interfaces\PostServiceInterface;

class PostService implements PostServiceInterface
{
    private DatabaseServiceInterface $databaseService;

    public function __construct(DatabaseServiceInterface $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    private function getPostsWithFilters(?string $sortBy, ?string $order, ?string $authorLogin, ?string $tag): array
    {
        if ($authorLogin) {
            $postsFromDb = $this->databaseService->getPostsByAuthor($authorLogin);
            return $postsFromDb;
        }

        if ($tag) {
            $postsFromDb = $this->databaseService->getPostsByTag($tag);
            return $postsFromDb;
        }

        switch ($sortBy) {
            case 'author':
                if($order === 'asc'){
                    $postsFromDb = $this->databaseService->getPostsByAuthorAlphabetical();
                    return $postsFromDb;
                } else {
                    $postsFromDb = $this->databaseService->getPostsByAuthorReverseAlphabetical();
                    return $postsFromDb;
                }
                break;
            case 'likes':
                if($order === 'asc'){
                    $postsFromDb = $this->databaseService->getPostsByLikesAscending();
                    return $postsFromDb;
                } else {
                    $postsFromDb = $this->databaseService->getPostsByLikesDescending();
                    return $postsFromDb;
                }
                break;
            case 'comments':
                if($order === 'asc'){
                    $postsFromDb = $this->databaseService->getPostsByCommentsAscending();
                    return $postsFromDb;
                } else {
                    $postsFromDb = $this->databaseService->getPostsByCommentsDescending();
                    return $postsFromDb;
                }
                break;
        }
        $postsFromDb = $this->databaseService->getAllPosts();
        return $postsFromDb;
    }

    public function getAllPosts(?string $sortBy, ?string $order, ?string $authorLogin, ?string $tag): array
    {
        $postsFromDb = $this->getPostsWithFilters($sortBy, $order, $authorLogin, $tag);
        $posts = [];
        foreach ($postsFromDb as $postData) {
            $tags = $this->getTagsByPostId($postData['id']);
            $posts[] = new Post(
                $postData['id'],
                $postData['title'],
                $postData['preview'],
                $postData['content'],
                $postData['author_id'],
                $postData['user_login'],
                $postData['last_editor_id'],
                $postData['last_editor_login'],
                $postData['create_date'],
                $postData['publish_date'],
                $postData['edit_date'],
                $postData['category_id'],
                $postData['category_name'],
                $tags,
                $postData['like_count'],
                $postData['comment_count']
            );
        }
        
        return $posts;
    }

    public function getAllNonPublishPosts(): array
    {
        $postsFromDb = $this->databaseService->getAllNonPublishPosts();
        $posts = [];
        foreach ($postsFromDb as $postData) {
            $tags = $this->getTagsByPostId($postData['id']);
            $posts[] = new Post(
                $postData['id'],
                $postData['title'],
                $postData['preview'],
                $postData['content'],
                $postData['author_id'],
                $postData['user_login'],
                $postData['last_editor_id'],
                $postData['last_editor_login'],
                $postData['create_date'],
                $postData['publish_date'],
                $postData['edit_date'],
                null,
                null,
                $tags
            );
        }
        return $posts;
    }

    public function getPostById(int $id): ?Post
    {
        $postFromDb = $this->databaseService->getPostById($id);

        if (!$postFromDb) {
            return null;
        }
        $tags = $this->getTagsByPostId($postFromDb['post']['id']);
        try {
            return new Post(
                $postFromDb['post']['id'],
                $postFromDb['post']['title'],
                $postFromDb['post']['preview'],
                $postFromDb['post']['content'],
                $postFromDb['post']['author_id'],
                $postFromDb['author_login'],
                $postFromDb['post']['last_editor_id'],
                $postFromDb['last_editor_login'],
                $postFromDb['post']['create_date'],
                $postFromDb['post']['publish_date'],
                $postFromDb['post']['edit_date'],
                $postFromDb['category_id'],
                $postFromDb['category_name'],
                $tags,
                $postFromDb['like_count'],
                $postFromDb['comment_count']
            );
        } catch (\Exception $e) {
            error_log("Ошибка при создании объекта Post: " . $e->getMessage());
            return null;
        }
    }


    public function addPost(string $title, string $preview, string $content, $userId): bool 
    {
        return $this->databaseService->addPost($title, $preview, $content, $userId); 
    }

    public function editPost(int $id, string $title, string $preview, string $content, int $userId, array $tags): bool 
    {
        $oldTagsFromDB = $this->getTagsByPostId($id);
        $oldTags = [];
        foreach($oldTagsFromDB as $oldTag)
        {
            $oldTags[] = $oldTag->name;
        }

        $tagsForDelete = array_diff($oldTags, $tags);
        foreach($tagsForDelete as $tagDel)
        {
            $isSuccess = $this->databaseService->deleteTag($tagDel, $id);
            if(!$isSuccess)
            {
                echo('Не удалось удалить тег: ');
                echo($tagDel);
            }
        }

        $tagsForInsert = array_diff($tags, $oldTags);
        foreach($tagsForInsert as $tagIns)
        {
            $isSuccess = $this->databaseService->addTag($tagIns, $id);
            if(!$isSuccess)
            {
                echo('Не удалось вставить тег: ');
                echo($tagDel);
            }
        }

        return $this->databaseService->editPost($id, $title,  $preview, $content, $userId); 
    }

    public function getNonPublishPostById(int $id): ?Post
    {
        $postFromDb = $this->databaseService->getNonPublishPostById($id);

        if (!$postFromDb) {
            return null;
        }
        $tags = $this->getTagsByPostId($postFromDb['post']['id']);
        try {
            return new Post(
                $postFromDb['post']['id'],
                $postFromDb['post']['title'],
                $postFromDb['post']['preview'],
                $postFromDb['post']['content'],
                $postFromDb['post']['author_id'],
                $postFromDb['author_login'],
                $postFromDb['post']['last_editor_id'],
                $postFromDb['last_editor_login'],
                $postFromDb['post']['create_date'],
                $postFromDb['post']['publish_date'],
                $postFromDb['post']['edit_date'],
                $postFromDb['category_id'],
                $postFromDb['category_name'],
                $tags
            );
        } catch (\Exception $e) {
            error_log("Ошибка при создании объекта Post: " . $e->getMessage());
            return null;
        }
    }

    public function publishPost(int $id): bool 
    {
        return $this->databaseService->publishPost($id); 
    }

    public function deletePost(int $id): bool 
    {
        return $this->databaseService->deletePost($id); 
    }

    public function checkLikeByPostIdAndUserId(int $postId, int $userId): bool 
    {
        return $this->databaseService->checkLikeByPostIdAndUserId($postId, $userId);
    }

    public function addLike(int $postId, int $userId): bool 
    {
        return $this->databaseService->addLike($postId, $userId);
    }

    public function deleteLike(int $postId, int $userId): bool
    {
        return $this->databaseService->deleteLike($postId, $userId);
    }

    public function getCountPosts (int $userId): int 
    {
        return $this->databaseService->getCountPostsByUserId($userId);
    }

    public function getPostsByUserId(int $userId): array 
    {
        $postsFromDb = $this->databaseService->getPostsByUserId($userId);
        $posts = [];
        foreach ($postsFromDb as $postData) {
            $tags = $this->getTagsByPostId($postData['id']);
            $posts[] = new Post(
                $postData['id'],
                $postData['title'],
                $postData['preview'],
                $postData['content'],
                $postData['author_id'],
                $postData['user_login'],
                $postData['last_editor_id'],
                $postData['last_editor_login'],
                $postData['create_date'],
                $postData['publish_date'],
                $postData['edit_date'],
                $postData['category_id'],
                $postData['category_name'],
                $tags,
                $postData['like_count'],
                $postData['comment_count']
            );
        }
        return $posts;
    }

    public function getPostsByCategoryId(int $categoryId): array
    {
        $postsFromDb = $this->databaseService->getPostsByCategoryId($categoryId);
        $posts = [];
        foreach ($postsFromDb as $postData) {
            $tags = $this->getTagsByPostId($postData['id']);
            $posts[] = new Post(
                $postData['id'],
                $postData['title'],
                $postData['preview'],
                $postData['content'],
                $postData['author_id'],
                $postData['user_login'],
                $postData['last_editor_id'],
                $postData['last_editor_login'],
                $postData['create_date'],
                $postData['publish_date'],
                $postData['edit_date'],
                $postData['category_id'],
                $postData['category_name'],
                $tags,
                $postData['like_count'],
                $postData['comment_count']
            );
        }
        return $posts;
    }

    // Метод для получения тегов поста
    public function getTagsByPostId(int $postId): array
    {
        $tagsFromDb = $this->databaseService->getTagsByPostId($postId);
        $tags = [];
        foreach ($tagsFromDb as $tagData) {
            $tags[] = new Tag(
                $tagData['id'],
                $tagData['name']
            );
        }
        return $tags;
    }

    // Метод для получения всех тегов
    public function getAllTags(): array
    {
        $tagsFromDb = $this->databaseService->getAllTags();
        $tags = [];
        foreach ($tagsFromDb as $tagData) {
            $tags[] = [
                'id' => $tagData['id'],
                'name' => $tagData['name']
            ];
        }
        return $tags;
    }

    public function addPostWithTags(string $title, string $preview, string $content, int $userId, array $tags): bool
    {
        $postId = $this->databaseService->addPostAndGetId($title, $preview, $content, $userId);
        if (!$postId) return false;

        foreach ($tags as $tagName) {
            $tagSaved = $this->databaseService->addTag($tagName, $postId);

            if (!$tagSaved)
            {
                echo ('тег не сохранен: ');
                echo ($tagName);
            }
        }
        return true;
    }
}

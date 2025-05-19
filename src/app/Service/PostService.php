<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Model\Comment;
use NastyaKuznet\Blog\Service\DatabaseService;
use NastyaKuznet\Blog\Model\Post;
use NastyaKuznet\Blog\Model\Tag;

class PostService
{
    private DatabaseService $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    private function getPostsWithFilters(?string $sortBy, ?string $order, ?string $authorNickname, ?string $tag): array
    {
        if ($authorNickname) {
            $postsFromDb = $this->databaseService->getPostsByAuthor($authorNickname);
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

    public function getAllPosts(?string $sortBy, ?string $order, ?string $authorNickname, ?string $tag): array
    {
        $postsFromDb = $this->getPostsWithFilters($sortBy, $order, $authorNickname, $tag);
        $posts = [];
        foreach ($postsFromDb as $postData) {
            $tags = $this->getTagsByPostId($postData['id']);
            $posts[] = new Post(
                $postData['id'],
                $postData['title'],
                $postData['content'],
                $postData['likes'],
                $postData['user_id'],
                $postData['user_nickname'],
                $postData['created_at'],
                $postData['comment_count'],
                $tags,
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
        $tags = $this->getTagsByPostId($postFromDb['id']);
        try {
            return new Post(
                (int)$postFromDb['id'],
                $postFromDb['title'],
                $postFromDb['content'],
                (int)$postFromDb['likes'],
                (int)$postFromDb['user_id'],
                $postFromDb['user_nickname'],
                $postFromDb['created_at'],
                (int)$postFromDb['comment_count'],
                $tags,
            );
        } catch (\Exception $e) {
            error_log("Ошибка при создании объекта Post: " . $e->getMessage());
            return null;
        }
    }

    public function addPost(string $title, string $content, int $userId): int
    {
        return $this->databaseService->addPost($title, $content, $userId);
    }

    public function editPost(int $id, string $title, string $content, array $tags): bool 
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

        return $this->databaseService->editPost($id, $title, $content); 
    }

    public function deletePostAndComments(int $id): bool 
    {
        return $this->databaseService->deletePostAndComments($id); 
    }

    public function getCommentsByPostId(int $postId): array
    {
        $commentsFromDb = $this->databaseService->getCommentsById($postId);
        $comments = [];
        foreach ($commentsFromDb as $commentData) {
            $comments[] = new Comment(
                $commentData['id'],
                $commentData['content'],
                $commentData['post_id'],
                $commentData['user_id'],
                $commentData['user_nickname'],
                $commentData['created_at']
            );
        }
        return $comments;
    }

    public function addComment(string $content, int $postId, int $userId): bool
    {
        return $this->databaseService->addComment($content, $postId, $userId);
    }

    public function addLike(int $postId): bool 
    {
        return $this->databaseService->addLike($postId);
    }

    public function getCountPosts (int $userId) : int 
    {
        return $this->databaseService->getCountPostsByUserId($userId);
    }

    public function getPostsByUserId (int $userId) : array 
    {
        $postsFromDb = $this->databaseService->getPostsByUserId($userId);
        $posts = [];
        foreach ($postsFromDb as $postData) {
            $posts[] = new Post(
                $postData['id'],
                $postData['title'],
                $postData['content'],
                $postData['likes'],
                $postData['user_id'],
                $postData['user_nickname'],
                $postData['created_at'],
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

    public function addPostWithTags(string $title, string $content, int $userId, array $tags): bool
    {
        $postId = $this->databaseService->addPostAndGetId($title, $content, $userId);
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

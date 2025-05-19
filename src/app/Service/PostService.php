<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Model\Comment;
use NastyaKuznet\Blog\Service\DatabaseService;
use NastyaKuznet\Blog\Model\Post;

class PostService
{
    private DatabaseService $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    private function getPostsWithFilters(mixed $sortBy, mixed $order, mixed $authorNickname): array
    {
        if ($authorNickname) {
            $postsFromDb = $this->databaseService->getPostsByAuthor($authorNickname);
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

    public function getAllPosts(mixed $sortBy, mixed $order, mixed $authorNickname): array
    {
        $postsFromDb = $this->getPostsWithFilters($sortBy, $order, $authorNickname);
        $posts = [];
        foreach ($postsFromDb as $postData) {
            $posts[] = new Post(
                $postData['id'],
                $postData['title'],
                $postData['preview'],
                $postData['content'],
                $postData['author_id'],
                $postData['user_nickname'],
                $postData['last_editor_id'],
                $postData['last_editor_nickname'],
                $postData['create_date'],
                $postData['publish_date'],
                $postData['edit_date'],
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
            $posts[] = new Post(
                $postData['id'],
                $postData['title'],
                $postData['preview'],
                $postData['content'],
                $postData['author_id'],
                $postData['user_nickname'],
                $postData['last_editor_id'],
                $postData['last_editor_nickname'],
                $postData['create_date'],
                $postData['publish_date'],
                $postData['edit_date'],
                $postData['comment_count']
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

        try {
            return new Post(
                $postFromDb['post']['id'],
                $postFromDb['post']['title'],
                $postFromDb['post']['preview'],
                $postFromDb['post']['content'],
                $postFromDb['post']['author_id'],
                $postFromDb['author_nickname'],
                $postFromDb['post']['last_editor_id'],
                $postFromDb['last_editor_nickname'],
                $postFromDb['post']['create_date'],
                $postFromDb['post']['publish_date'],
                $postFromDb['post']['edit_date'],
                $postFromDb['like_count'],
                $postFromDb['comment_count']
            );
        } catch (\Exception $e) {
            error_log("Ошибка при создании объекта Post: " . $e->getMessage());
            return null;
        }
    }

    public function getNonPublishPostById(int $id): ?Post
    {
        $postFromDb = $this->databaseService->getNonPublishPostById($id);

        if (!$postFromDb) {
            return null;
        }

        try {
            return new Post(
                $postFromDb['post']['id'],
                $postFromDb['post']['title'],
                $postFromDb['post']['preview'],
                $postFromDb['post']['content'],
                $postFromDb['post']['author_id'],
                $postFromDb['author_nickname'],
                $postFromDb['post']['last_editor_id'],
                $postFromDb['last_editor_nickname'],
                $postFromDb['post']['create_date'],
                $postFromDb['post']['publish_date'],
                $postFromDb['post']['edit_date']
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

    public function editPost(int $id, string $title, string $preview, string $content, int $editorId): bool 
    {
        return $this->databaseService->editPost($id, $title, $preview, $content, $editorId); 
    }

    public function publishPost(int $id): bool 
    {
        return $this->databaseService->publishPost($id); 
    }

    public function deletePost(int $id): bool 
    {
        return $this->databaseService->deletePost($id); 
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
                $commentData['created_date'],
                $commentData['edit_date'],
                $commentData['delete_date'],
                $commentData['is_edit'],
                $commentData['is_delete'] 
            );
        }
        return $comments;
    }

    public function addComment(string $content, int $postId, int $userId): bool
    {
        return $this->databaseService->addComment($content, $postId, $userId);
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
                $postData['preview'],
                $postData['content'],
                $postData['author_id'],
                $postData['user_nickname'],
                $postData['last_editor_id'],
                $postData['last_editor_nickname'],
                $postData['create_date'],
                $postData['publish_date'],
                $postData['edit_date'],
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
}

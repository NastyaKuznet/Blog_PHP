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
                    echo('lol2');
                    $postsFromDb = $this->databaseService->getPostsByAuthorAlphabetical();
                    echo(count($postsFromDb));
                    foreach($postsFromDb as $d){
                        echo($d['id']);
                    }
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
        echo('lol');
        $posts = [];
        foreach ($postsFromDb as $postData) {
            echo($postData['id']);
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

    public function getPostById(int $id): ?Post
    {
        $postFromDb = $this->databaseService->getPostById($id);

        if (!$postFromDb) {
            return null;
        }

        try {
            return new Post(
                (int)$postFromDb['id'],
                $postFromDb['title'],
                $postFromDb['content'],
                (int)$postFromDb['likes'],
                (int)$postFromDb['user_id'],
                $postFromDb['user_nickname'],
                $postFromDb['created_at'],
                (int)$postFromDb['comment_count']
            );
        } catch (\Exception $e) {
            error_log("Ошибка при создании объекта Post: " . $e->getMessage());
            return null;
        }
    }

    public function addPost(string $title, string $content, $userId): bool 
    {
        return $this->databaseService->addPost($title, $content, $userId); 
    }

    public function editPost(int $id, string $title, string $content): bool 
    {
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

    public function addComment(Comment $comment): bool
    {
        return $this->databaseService->addComment($comment->content, $comment->postId, $comment->userId);
    }

    public function addLike(int $postId): bool 
    {
        return $this->databaseService->addLike($postId);
    }
}

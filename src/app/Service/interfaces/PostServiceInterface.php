<?php
namespace NastyaKuznet\Blog\Service\interfaces;

use NastyaKuznet\Blog\Model\Post;

interface PostServiceInterface
{
    public function getAllPosts(?string $sortBy, ?string $order, ?string $authorLogin, ?string $tag): array;
    public function getAllNonPublishPosts(): array;
    public function getPostById(int $id): ?Post;
    public function addPost(string $title, string $preview, string $content, $userId): bool ;
    public function editPost(int $id, string $title, string $preview, string $content, int $userId, array $tags): bool;
    public function getNonPublishPostById(int $id): ?Post;
    public function publishPost(int $id): bool;
    public function deletePost(int $id): bool;
    public function checkLikeByPostIdAndUserId(int $postId, int $userId): bool;
    public function addLike(int $postId, int $userId): bool;
    public function deleteLike(int $postId, int $userId): bool;
    public function getCountPosts (int $userId): int;
    public function getPostsByUserId(int $userId): array;
    public function getPostsByCategoryId(int $categoryId): array;
    public function getTagsByPostId(int $postId): array;
    public function getAllTags(): array;
    public function addPostWithTags(string $title, string $preview, string $content, int $userId, array $tags): bool;   
}

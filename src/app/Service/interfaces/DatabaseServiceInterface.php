<?php
namespace NastyaKuznet\Blog\Service\interfaces;

interface DatabaseServiceInterface
{
    public function getAllPosts(): array;
    public function getPostsByAuthorAlphabetical(): array;
    public function getPostsByAuthorReverseAlphabetical(): array;
    public function getPostsByAuthor($author_nickname): array;
    public function getPostsByLikesAscending(): array;
    public function getPostsByLikesDescending(): array;
    public function getPostsByCommentsAscending(): array;
    public function getPostsByCommentsDescending(): array;
    public function getPostsByTag(string $tagName): array;
    public function getPostsByUserId(int $userId):array;
    public function getAllNonPublishPosts(): array;
    public function getPostById(int $postId): ?array;
    public function getNonPublishPostById(int $postId): ?array;
    public function getCountPostsByUserId(int $userId):int;
    public function addPost(string $title, string $preview, string $content, int $userId): bool;
    public function editPost(int $postId, string $title, string $preview, string $content, int $editorId): bool;
    public function deletePost(int $postId): bool;
    public function publishPost(int $postId): bool;
    public function getCommentsByPostId(int $postId): array;
    public function getCommentById(int $commentId): ?array;
    public function addComment(string $content, int $postId, int $userId): bool;
    public function updateComment(int $commentId, string $newContent): bool;
    public function deleteComment(int $commentId): bool;
    public function checkLikeByPostIdAndUserId(int $postId, int $userId): bool;
    public function addLike(int $postId, int $userId): bool;
    public function deleteLike(int $postId, int $userId): bool;
    public function getUserInfo(int $user_id): array;
    public function getAllUsers(): array;
    public function changeUserRole(int $user_id, int $new_role_id): bool;
    public function deleteUser(int $user_id): bool;
    public function addUser(string $nickname, string $password): bool;
    public function authorizationUser(string $nickname, string $password): mixed;
    public function checkUserNickname(string $nickname): bool;
    public function toggleUserBan(int $userId, bool $isBanned): bool;
    public function getRoles(): array;
    public function getAllTags(): array;
    public function getTagsByPostId(int $postId): array;
    public function getAllCategories(): array;
    public function addTag(string $name, int $postId): bool;
    public function deleteTag(string $name, int $postId): bool;
    public function getCategoryById(int $categoryId): ?array;
    public function addCategory(string $name, ?int $parentId): bool;
    public function getTagIdByName(string $name): ?int;
    public function addPostAndGetId(string $title, string $preview, string $content, int $userId): ?int;
    public function getCategoriesByPostId(int $postId): array;
    public function getPostsByCategoryId(int $categoryId): array;                     
    public function deleteCategory(int $categoryId): bool;
    public function connectPostAndCategory(int $postId, int $categoryId): bool;
}

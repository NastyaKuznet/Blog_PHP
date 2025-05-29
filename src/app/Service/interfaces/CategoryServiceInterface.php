<?php
namespace NastyaKuznet\Blog\Service\interfaces;

use NastyaKuznet\Blog\Model\Category;

interface CategoryServiceInterface
{
    public function getAll(): array;
    public function getTree(): array;
    public function getById(int $id): ?Category;
    public function add(string $name, ?int $parentId): bool;
    public function getByPostId(int $postId): array;
    public function getPostsByCategoryId(int $categoryId): array;
    public function delete(int $id): bool;
    public function connectPostAndCategory(int $postId, int $categoryId): bool;
}
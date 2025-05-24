<?php
namespace NastyaKuznet\Blog\Service\interfaces;

use NastyaKuznet\Blog\Model\Category;

interface CategoryServiceInterface
{
    public function getAllCategories(): array;
    public function getCategoriesTree(): array;
    public function getCategoryById(int $categoryId): ?Category;
    public function addCategory(string $name, ?int $parentId): bool;
    public function getCategoriesByPostId(int $postId): array;
    public function getPostsByCategoryId(int $categoryId): array;
    public function deleteCategory(int $categoryId): bool;
    public function connectPostAndCategory(int $postId, int $categoryId): bool;
}
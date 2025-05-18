<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Model\Category;
use NastyaKuznet\Blog\Service\DatabaseService;

class CategoryService
{
    private DatabaseService $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function getAllCategories(): array
    {
        return $this->databaseService->getAllCategories();
    }

    public function getCategoryById(int $categoryId): ?Category
    {
        return $this->databaseService->getCategoryById($categoryId);
    }

    public function addCategory(string $name, ?int $parentId): bool
    {
        return $this->databaseService->addCategory($name, $parentId);
    }

    public function getCategoriesByPostId(int $postId): array
    {
        return $this->databaseService->getCategoriesByPostId($postId);
    }

    public function getPostsByCategoryId(int $categoryId): array
    {
        return $this->databaseService->getPostsByCategoryId($categoryId);
    }

    public function deleteCategory(int $categoryId): bool
    {
        return $this->databaseService->deleteCategory($categoryId);
    }
}
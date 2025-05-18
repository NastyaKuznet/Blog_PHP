<?php

namespace NastyaKuznet\Blog\Controller;

use NastyaKuznet\Blog\Service\CategoryService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;
use Slim\Views\Twig;

class CategoryController
{
    private CategoryService $categoryService;
    private Twig $view;

    public function __construct(CategoryService $categoryService, Twig $view)
    {
        $this->categoryService = $categoryService;
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        $categories = $this->categoryService->getAllCategories();
        return $this->view->render($response, 'admin/categories.twig', [
            'categories' => $categories,
        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'GET') {
            return $this->view->render($response, 'admin/create_category.twig');
        }

        $data = $request->getParsedBody();
        $name = $data['name'] ?? '';
        $parentId = $data['parent_id'] ? (int)$data['parent_id'] : null;

        if (empty($name)) {
            return $this->view->render($response, 'admin/create_category.twig', [
                'error' => '<div class="error">Введите название категории</div>'
            ]);
        }

        $success = $this->categoryService->addCategory($name, $parentId);
        if ($success) {
            return $response->withHeader('Location', '/admin/categories')->withStatus(302);
        } else {
            return $this->view->render($response, 'admin/create_category.twig', [
                'error' => '<div class="error">Ошибка при создании категории</div>'
            ]);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $categoryId = (int)($args['id'] ?? 0);

        if (!$categoryId) {
            return $response->withHeader('Location', '/admin/categories')->withStatus(302);
        }

        $success = $this->categoryService->deleteCategory($categoryId);

        if ($success) {
            return $response->withHeader('Location', '/admin/categories')->withStatus(302);
        } else {
            $response->getBody()->write("Ошибка при удалении категории.");
            return $response->withStatus(500);
        }
    }
}
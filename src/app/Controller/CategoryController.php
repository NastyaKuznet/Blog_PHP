<?php

namespace NastyaKuznet\Blog\Controller;

use NastyaKuznet\Blog\Service\interfaces\CategoryServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Throwable;

class CategoryController
{
    private CategoryServiceInterface $categoryService;
    private Twig $view;

    public function __construct(CategoryServiceInterface $categoryService, Twig $view)
    {
        $this->categoryService = $categoryService;
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        try {
            $categories = $this->categoryService->getAll();
            return $this->view->render($response, 'categories/categories.twig', [
                'categories' => $categories,
            ]);
        } catch (Throwable){
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
    }

    public function create(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'GET') {
            try {
                $categories = $this->categoryService->getAll();
                return $this->view->render($response, 'categories/create_category.twig', [
                    'categories' => $categories,
                ]);
            } catch (Throwable){
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
            } 
        }

        $data = $request->getParsedBody();
        $name = $data['name'] ?? '';
        $parentId = $data['parent_id'] ? (int)$data['parent_id'] : null;

        if (empty($name)) {
            return $this->view->render($response, 'categories/create_category.twig', [
                'error' => '<div class="error">Введите название категории</div>'
            ]);
        }
      
        try {
            $this->categoryService->add($name, $parentId);
            return $response->withHeader('Location', '/categories')->withStatus(302);
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $categoryId = (int)($args['id'] ?? 0);

        if (!$categoryId) {
            return $response->withHeader('Location', '/categories')->withStatus(302);
        }
        try {
            $this->categoryService->delete($categoryId);
            return $response->withHeader('Location', '/categories')->withStatus(302);
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
    }
}
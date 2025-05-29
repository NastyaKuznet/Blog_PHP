<?php

namespace NastyaKuznet\Blog\Controller;

use NastyaKuznet\Blog\Service\interfaces\PostServiceInterface;
use NastyaKuznet\Blog\Service\interfaces\CategoryServiceInterface;
use NastyaKuznet\Blog\Service\interfaces\CommentServiceInterface;
use NastyaKuznet\Blog\Service\interfaces\LikeServiceInterface;
use NastyaKuznet\Blog\Service\interfaces\NonPublishPostServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;
use Slim\Views\Twig;
use Throwable;

class PostController
{
    private PostServiceInterface $postService;
    private CategoryServiceInterface $categoryService;
    private CommentServiceInterface $commentService;
    private NonPublishPostServiceInterface $nonPublishService;
    private LikeServiceInterface $likeService;
    private Twig $view;

    public function __construct(
        PostServiceInterface $postService, 
        CategoryServiceInterface $categoryService, 
        CommentServiceInterface $commentService,
        NonPublishPostServiceInterface $nonPublishService,
        LikeServiceInterface $likeService,
        Twig $view)
    {
        $this->postService = $postService;
        $this->categoryService = $categoryService;
        $this->commentService = $commentService;
        $this->nonPublishService = $nonPublishService;
        $this->likeService = $likeService;
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        $queryParams = $request->getQueryParams();
        $sortBy = $queryParams['sort_by'] ?? null;
        $order = $queryParams['order'] ?? 'asc';
        $authorLogin = $queryParams['author_login'] ?? null;
        $tag = $queryParams['tag_search'] ?? null;
        $categoryId = $queryParams['category_id'] ?? null;

        try {
            $categories = $this->categoryService->getTree();
            if ($categoryId) {
                $posts = $this->categoryService->getPostsByCategoryId($categoryId);
            } else {
                $posts = $this->postService->getAll($sortBy, $order, $authorLogin, $tag);
            }

            return $this->view->render($response, 'post/index.twig', [
                'posts' => $posts,
                'userRole' => is_array($user) ? $user['role'] : 'reader',
                'app' => [  
                    'request' => $request,
                ],
                'categories' => $categories,
            ]);
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
    }

    public function indexNonPublish(Request $request, Response $response): Response
    {
        try {
            $posts = $this->nonPublishService->getAll();

            return $this->view->render($response, 'post/nonPublish/index.twig', [
                'posts' => $posts,
                'app' => [  
                    'request' => $request,
                ],
            ]);
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        $postId = (int)$args['id'];
        try {
            $post = $this->postService->getById($postId);

            if (!$post) {
                $response->getBody()->write("Пост не найден.");
                return $response->withStatus(404)->withHeader('Content-Type', 'text/plain');
            }
            $isLikedByUser = null;
            if($user)
            {
                $isLikedByUser = $this->likeService->check($postId, $user['id']);
            }
            $comments = $this->commentService->getByPostId($postId);

            if ($request->getMethod() === 'GET') {
                $data = [
                    'post' => $post,
                    'comments' => $comments,
                    'app' => [
                        'user' => $user 
                    ],
                    'isLikedByUser' => $isLikedByUser
                ];

                try {
                    return $this->view->render($response, 'post/show.twig', $data);
                } catch (\Twig\Error\LoaderError $e) {
                    $response->getBody()->write("Ошибка загрузки шаблона: " . $e->getMessage());
                    return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
                } catch (\Twig\Error\RuntimeError $e) {
                    $response->getBody()->write("Ошибка времени выполнения шаблона: " . $e->getMessage());
                    return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
                } catch (\Twig\Error\SyntaxError $e) {
                    $response->getBody()->write("Синтаксическая ошибка в шаблоне: " . $e->getMessage());
                    return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
                }
            }

            $data = $request->getParsedBody();
            $commentText = trim($data['comment'] ?? '');

            if (!empty($commentText)) {
                $isSuccess = $this->commentService->add($commentText, $postId, $user['id']);
                if ($isSuccess) {
                    $response = new SlimResponse();
                    return $response->withHeader('Location', '/post/' . $postId)->withStatus(302);
                } else {
                    $response->getBody()->write("Произошла ошибка при добавлении комментария. Попробуйте позже.");
                    return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
                }
            }

            $response->getBody()->write("Ошибка при добавлении комментария.");
            return $response->withStatus(400)->withHeader('Content-Type', 'text/plain');
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
    }


    public function create(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if ($request->getMethod() === 'GET') {
            try {
                return $this->view->render($response, 'post/create.twig');
            } catch (\Twig\Error\LoaderError $e) {
                $response->getBody()->write("Ошибка загрузки шаблона: " . $e->getMessage());
                return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
            } catch (\Twig\Error\RuntimeError $e) {
                $response->getBody()->write("Ошибка времени выполнения шаблона: " . $e->getMessage());
                return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
            } catch (\Twig\Error\SyntaxError $e) {
                $response->getBody()->write("Синтаксическая ошибка в шаблоне: " . $e->getMessage());
                return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
            }
        }

        $data = $request->getParsedBody();
        $title = trim($data['title'] ?? '');
        $preview = trim($data['preview'] ?? '');
        $content = trim($data['content'] ?? '');
        $tags = $data['tags'] ?? [];

        try {
            if (!empty($title) && !empty($preview) && !empty($content)) {
                $success = $this->postService->add($title, $preview, $content, $user['id'], $tags);
                if ($success) {
                    return $response->withHeader('Location', '/')->withStatus(302);
                }
                else
                {
                    $response->getBody()->write("Не удалось сохранить пост.");
                    return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
                }
            }
            $response->getBody()->write("Ошибка при создании поста. Заполните все поля.");
            return $response->withStatus(400)->withHeader('Content-Type', 'text/plain');
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
    }


    public function edit(Request $request, Response $response, array $args): Response
    {
        $postId = (int)$args['id'];
        try {
            $post = $this->postService->getById($postId);
            if (!$post) {
                $response->getBody()->write("Пост не найден.");
                return $response->withStatus(404);
            }
            $categories = $this->categoryService->getAll();
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 

        try {
            return $this->view->render($response, 'post/edit.twig', [
                'post' => $post,
                'categories' => $categories,
            ]);
        } catch (\Twig\Error\LoaderError $e) {
            $response->getBody()->write("Ошибка загрузки шаблона: " . $e->getMessage());
            return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
        } catch (\Twig\Error\RuntimeError $e) {
            $response->getBody()->write("Ошибка времени выполнения шаблона: " . $e->getMessage());
            return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
        } catch (\Twig\Error\SyntaxError $e) {
            $response->getBody()->write("Синтаксическая ошибка в шаблоне: " . $e->getMessage());
            return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
        }
    }

    public function save(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        $postId = (int)$args['id'];
        $data = $request->getParsedBody();
        $title = $data['title'] ?? '';
        $preview = $data['preview'] ?? '';
        $content = $data['content'] ?? '';
        $tags = $data['tags'] ?? [];
        $categoryId = $data['category_id'] ? (int)$data['category_id'] : null;

        try {
            if (!empty($title) && !empty($preview) && !empty($content)) {
                $this->postService->edit($postId, $title, $preview, $content, $user['id'], $tags);
                $this->categoryService->connectPostAndCategory($postId, $categoryId);
                $response = new SlimResponse();
                return $response->withHeader('Location', '/')->withStatus(302);
            } else {
                $response->getBody()->write("Ошибка при сохранении изменений: Заголовок и содержание обязательны.");
                return $response->withStatus(400);
            }
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $postId = (int)$args['id'];
        try {
            $this->postService->delete($postId);
            $response = new SlimResponse();
            return $response->withHeader('Location', '/')->withStatus(302);
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
    }

    public function editNonPublish(Request $request, Response $response, array $args): Response
    {
        $postId = (int)$args['id'];
        try{
            $post = $this->nonPublishService->getById($postId);
            
            if (!$post) {
                $response->getBody()->write("Пост не найден.");
                return $response->withStatus(404);
            }
            $categories = $this->categoryService->getAll();  
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 

        try {
            return $this->view->render($response, 'post/nonPublish/edit.twig', [
                'post' => $post,
                'categories' => $categories,
            ]);
        } catch (\Twig\Error\LoaderError $e) {
            $response->getBody()->write("Ошибка загрузки шаблона: " . $e->getMessage());
            return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
        } catch (\Twig\Error\RuntimeError $e) {
            $response->getBody()->write("Ошибка времени выполнения шаблона: " . $e->getMessage());
            return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
        } catch (\Twig\Error\SyntaxError $e) {
            $response->getBody()->write("Синтаксическая ошибка в шаблоне: " . $e->getMessage());
            return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
        }
    }

    public function deleteNonPublish(Request $request, Response $response, array $args): Response
    {
        $postId = (int)$args['id'];
        try {
            $this->postService->delete($postId);
            $response = new SlimResponse();
            return $response->withHeader('Location', '/post-non-publish')->withStatus(302);
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        }
    }

    public function publish(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        $postId = (int)$args['id'];
        $data = $request->getParsedBody();
        $title = $data['title'] ?? '';
        $preview = $data['preview'] ?? '';
        $content = $data['content'] ?? '';
        $tags = $data['tags'] ?? [];
        $categoryId = $data['category_id'] ? (int)$data['category_id'] : null;

        try{
            if (!empty($title) && !empty($preview) && !empty($content)) {
                $this->postService->edit($postId, $title, $preview, $content, $user['id'], $tags);
                $this->postService->publish($postId);
                $this->categoryService->connectPostAndCategory($postId, $categoryId);
                $response = new SlimResponse();
                return $response->withHeader('Location', '/post-non-publish')->withStatus(302);
            } else {
                $response->getBody()->write("Ошибка при публицкации: Заголовок, превью и содержание обязательны.");
                return $response->withStatus(400);
            }
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        }
    }

    public function likePost(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        $postId = (int)$args['id'];
        try{
            $isLikedByUser = $this->likeService->check($postId, $user['id']);
            if($isLikedByUser)
            {
                $this->likeService->delete($postId, $user['id']);
            }
            else 
            {
                $this->likeService->add($postId, $user['id']);
            }     
            $response = new SlimResponse();
            return $response->withHeader('Location', '/post/' . $postId)->withStatus(302);
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        }
    }
}

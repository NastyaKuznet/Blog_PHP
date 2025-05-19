<?php

namespace NastyaKuznet\Blog\Controller;

use NastyaKuznet\Blog\Model\Comment;
use NastyaKuznet\Blog\Model\Post;
use NastyaKuznet\Blog\Service\PostService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;
use Slim\Views\Twig;

class PostController
{
    private PostService $postService;
    private Twig $view;

    public function __construct(PostService $postService, Twig $view)
    {
        $this->postService = $postService;
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        $queryParams = $request->getQueryParams();
        $sortBy = $queryParams['sort_by'] ?? null;
        $order = $queryParams['order'] ?? 'asc';
        $authorNickname = $queryParams['author_nickname'] ?? null;

        $posts = $this->postService->getAllPosts($sortBy, $order, $authorNickname);

        return $this->view->render($response, 'post/index.twig', [
            'posts' => $posts,
            'userRole' => $user['role'],
            'app' => [  
                'request' => $request,
            ],
        ]);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        $postId = (int)$args['id'];
        $post = $this->postService->getPostById($postId);

        if (!$post) {
            $response->getBody()->write("Пост не найден.");
            return $response->withStatus(404)->withHeader('Content-Type', 'text/plain');
        }

        $comments = $this->postService->getCommentsByPostId($postId);

        if ($request->getMethod() === 'GET') {
            $data = [
                'post' => $post,
                'comments' => $comments,
                'app' => [
                    'user' => $user 
                ]
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
            $isSuccess = $this->postService->addComment($commentText, $postId, $user['id']);

            if ($isSuccess) {
                $response = new SlimResponse();
                return $response->withHeader('Location', '/post/' . $postId)->withStatus(302);
            } else {
                error_log("Ошибка при добавлении комментария к посту ID: " . $postId);
                $response->getBody()->write("Произошла ошибка при добавлении комментария. Попробуйте позже.");
                return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
            }
        }

        $response->getBody()->write("Ошибка при добавлении комментария.");
        return $response->withStatus(400)->withHeader('Content-Type', 'text/plain');
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
        $content = trim($data['content'] ?? '');

        if (!empty($title) && !empty($content)) {
            $success = $this->postService->addPost($title, $content, $user['id']);
            if ($success) {
                $response = new SlimResponse();
                return $response->withHeader('Location', '/')->withStatus(302);
            } else {
                $response->getBody()->write("Ошибка при создании поста.");
                return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
            }
        }

        $response->getBody()->write("Ошибка при создании поста. Заполните все поля.");
        return $response->withStatus(400)->withHeader('Content-Type', 'text/plain');
    }


    public function edit(Request $request, Response $response, array $args): Response
    {
        $postId = (int)$args['id'];
        $data = $request->getParsedBody();

        $post = $this->postService->getPostById($postId);
        if (!$post) {
        $response->getBody()->write("Пост не найден.");
        return $response->withStatus(404);
        }

        if ($request->getMethod() === 'GET') {
            try {
                return $this->view->render($response, 'post/edit.twig', [
                    'post' => $post,
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

        $action = $data['action'] ?? null;

        if ($action === 'save') {
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';

            if (!empty($title) && !empty($content)) {
                $isSuccess = $this->postService->editPost($postId, $title, $content);
                if ($isSuccess)
                {
                    $response = new SlimResponse();
                    return $response->withHeader('Location', '/')->withStatus(302);
                }
                $response->getBody()->write("Неудалось сохранить пост.");
                return $response->withStatus(500);
            } else {
                $response->getBody()->write("Ошибка при сохранении изменений: Заголовок и содержание обязательны.");
                return $response->withStatus(400);
            }
        } elseif ($action === 'delete') {
            $isSuccess = $this->postService->deletePostAndComments($postId);
            if ($isSuccess)
            {
                $response = new SlimResponse();
                return $response->withHeader('Location', '/')->withStatus(302);
            }
            $response->getBody()->write("Неудалось удалить пост.");
            return $response->withStatus(500);
        } else {
            $response->getBody()->write("Недопустимое действие.");
            return $response->withStatus(400);
        }
    }

    public function likePost(Request $request, Response $response, array $args): Response
    {
        $postId = (int)$args['id'];

        $isSuccess = $this->postService->addLike($postId);

        if ($isSuccess) {
            $response = new SlimResponse();
            return $response->withHeader('Location', '/')->withStatus(302);
        } else {
            $response->getBody()->write("Не удалось поставить лайк посту.");
            return $response->withStatus(500);
        }
    }
}

<?php

namespace NastyaKuznet\Blog\Controller;

use NastyaKuznet\Blog\Service\interfaces\PostServiceInterface;
use NastyaKuznet\Blog\Service\interfaces\CategoryServiceInterface;
use NastyaKuznet\Blog\Service\interfaces\CommentServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;
use Slim\Views\Twig;

class PostController
{
    private PostServiceInterface $postService;
    private CategoryServiceInterface $categoryService;
    private CommentServiceInterface $commentService;
    private Twig $view;

    public function __construct(
        PostServiceInterface $postService, 
        CategoryServiceInterface $categoryService, 
        CommentServiceInterface $commentService, 
        Twig $view)
    {
        $this->postService = $postService;
        $this->categoryService = $categoryService;
        $this->commentService = $commentService;
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

        $categories = $this->categoryService->getCategoriesTree();
        if ($categoryId) {
            $posts = $this->postService->getPostsByCategoryId($categoryId);
        } else {
            $posts = $this->postService->getAllPosts($sortBy, $order, $authorLogin, $tag);
        }

        return $this->view->render($response, 'post/index.twig', [
            'posts' => $posts,
            'userRole' => is_array($user) ? $user['role'] : 'reader',
            'app' => [  
                'request' => $request,
            ],
            'categories' => $categories,
        ]);
    }

    public function indexNonPublish(Request $request, Response $response): Response
    {
        $posts = $this->postService->getAllNonPublishPosts();

        return $this->view->render($response, 'post/nonPublish/index.twig', [
            'posts' => $posts,
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
        $isLikedByUser = null;
        if($user)
        {
            $isLikedByUser = $this->postService->checkLikeByPostIdAndUserId($postId, $user['id']);
        }
        $comments = $this->commentService->getCommentsByPostId($postId);

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
            $isSuccess = $this->commentService->addComment($commentText, $postId, $user['id']);

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
        $preview = trim($data['preview'] ?? '');
        $content = trim($data['content'] ?? '');
        $tags = $data['tags'] ?? [];

        if (!empty($title) && !empty($preview) && !empty($content)) {
            $success = $this->postService->addPostWithTags($title, $preview, $content, $user['id'], $tags);
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
    }


    public function edit(Request $request, Response $response, array $args): Response
    {
        $postId = (int)$args['id'];
        $post = $this->postService->getPostById($postId);
        if (!$post) {
            $response->getBody()->write("Пост не найден.");
            return $response->withStatus(404);
        }
        $categories = $this->categoryService->getAllCategories();

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

        if (!empty($title) && !empty($content)) {
            $isSuccess = $this->postService->editPost($postId, $title, $preview, $content, $user['id'], $tags);
            $isSuccessCategory = $this->categoryService->connectPostAndCategory($postId, $categoryId);
            if ($isSuccess && $isSuccessCategory)
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
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $postId = (int)$args['id'];

        $isSuccess = $this->postService->deletePost($postId);
        if ($isSuccess)
        {
            $response = new SlimResponse();
            return $response->withHeader('Location', '/')->withStatus(302);
        }
        $response->getBody()->write("Неудалось удалить пост.");
        return $response->withStatus(500);
    }

    public function editNonPublish(Request $request, Response $response, array $args): Response
    {
        $postId = (int)$args['id'];
        $post = $this->postService->getNonPublishPostById($postId);
        
        if (!$post) {
            $response->getBody()->write("Пост не найден.");
            return $response->withStatus(404);
        }
        $categories = $this->categoryService->getAllCategories();

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
        $isSuccess = $this->postService->deletePost($postId);
        if ($isSuccess)
        {
            $response = new SlimResponse();
            return $response->withHeader('Location', '/post-non-publish')->withStatus(302);
        }
        $response->getBody()->write("Неудалось удалить пост.");
        return $response->withStatus(500);
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

        if (!empty($title) && !empty($preview) && !empty($content)) {
            $isSuccessEditPost = $this->postService->editPost($postId, $title, $preview, $content, $user['id'], $tags);
            $isSuccess = $this->postService->publishPost($postId);
            $isSuccessCategory = $this->categoryService->connectPostAndCategory($postId, $categoryId);
            if ($isSuccessEditPost && $isSuccess && $isSuccessCategory)
            {
                $response = new SlimResponse();
                return $response->withHeader('Location', '/post-non-publish')->withStatus(302);
            }
        }
        $response->getBody()->write("Неудалось опубликовать пост.");
        return $response->withStatus(500);
    }

    public function likePost(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        $postId = (int)$args['id'];

        $isLikedByUser = $this->postService->checkLikeByPostIdAndUserId($postId, $user['id']);
        if($isLikedByUser)
        {
            $isSuccess = $this->postService->deleteLike($postId, $user['id']);
        }
        else 
        {
            $isSuccess = $this->postService->addLike($postId, $user['id']);
        }      

        if ($isSuccess) {
            $response = new SlimResponse();
            return $response->withHeader('Location', '/post/' . $postId)->withStatus(302);
        } else {
            $response->getBody()->write("Не удалось поставить лайк посту / снять лайк с поста.");
            return $response->withStatus(500);
        }
    }
}

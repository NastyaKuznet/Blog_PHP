<?php

namespace NastyaKuznet\Blog\Controller;

use NastyaKuznet\Blog\Service\PostService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;
use Slim\Views\Twig;

class PostController
{
    private array $config;
    private static int $lastPostId = 3;

    private PostService $postService;
    private Twig $view;

    public function __construct(PostService $postService, Twig $view)
    {
        $this->postService = $postService;
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $sortBy = $queryParams['sort_by'] ?? null;
        $order = $queryParams['order'] ?? 'asc';
        $authorNickname = $queryParams['author_nickname'] ?? null;

        // изменить чтобы применение фильтров было в post service
        $posts = $this->postService->getAllPosts();

        if ($authorNickname) {
            $posts = $this->postService->filterByAuthorNickname($posts, $authorNickname);
        }

        switch ($sortBy) {
            case 'author':
                usort($posts, function ($a, $b) use ($order) {
                    $authorA = $this->postService->getAuthorName($a->userId);
                    $authorB = $this->postService->getAuthorName($b->userId);
                    return ($order === 'desc') ? strnatcasecmp($authorB, $authorA) : strnatcasecmp($authorA, $authorB);
                });
                break;
            case 'likes':
                usort($posts, function ($a, $b) use ($order) {
                    return ($order === 'desc') ? $b->likes - $a->likes : $a->likes - $b->likes;
                });
                break;
            case 'comments':
                usort($posts, function ($a, $b) use ($order) {
                    return ($order === 'desc') ? $b->commentCount - $a->commentCount : $a->commentCount - $b->commentCount;
                });
                break;
        }

        $authorName = [];
        foreach ($posts as $post) {
            $authorName[$post->id] = $this->postService->getAuthorName($post->userId);
        }
        return $this->view->render($response, 'post/index.twig', [
            'posts' => $posts,
            'authorName' => $authorName,
            'userRole' => "moder",
            'app' => [  //Чтобы получить доступ к request
                'request' => $request,
            ],
        ]);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $postId = (int)$args['id'];
        $post = null;

        // Найдем пост в конфиге
        foreach ($this->config['posts'] as $p) {
            if ($p['id'] === $postId) {
                $post = $p;
                break;
            }
        }

        if (!$post) {
            $response->getBody()->write("Пост не найден.");
            return $response->withStatus(404);
        }

        $authorName = $this->postService->getAuthorName($post['userId']);

        // Получаем комментарии для поста
        $comments = [];
        foreach ($this->config['comments'] as $comment) {
            if ($comment['postId'] === $postId) {
                //Находим автора комментария
                foreach ($this->config['users'] as $user) {
                    if ($user['id'] === $comment['userId']) {
                        $comment['author'] = $user['nickname'];
                    }
                }
                $comments[] = $comment;
            }
        }

        if ($request->getMethod() === 'GET') {
            // Отображаем страницу поста с комментариями
            ob_start();
            include __DIR__ . '/../View/post/show.php';
            $content = ob_get_clean();

            // Заменяем все переменные, которые хотим передать в шаблон
            $content = str_replace('<?php echo htmlspecialchars($post[\'id\']); ?>', htmlspecialchars($post['id']), $content);
            $content = str_replace('<?php echo htmlspecialchars($post[\'title\']); ?>', htmlspecialchars($post['title']), $content);
            $content = str_replace('<?php echo htmlspecialchars($post[\'content\']); ?>', htmlspecialchars($post['content']), $content);
            $content = str_replace('<?php echo htmlspecialchars($authorName); ?>', htmlspecialchars($authorName), $content);

            $response->getBody()->write($content);
            return $response->withStatus(200)->withHeader('Content-Type', 'text/html');
        }

        // Обрабатываем POST-запрос (добавление комментария)
        $data = $request->getParsedBody();
        $commentText = $data['comment'] ?? '';

        if (!empty($commentText)) {
            // Создаем новый комментарий (в заглушке)
            $newComment = [
                'id' => count($this->config['comments']) + 1,
                'postId' => $postId,
                'userId' => 1, // Предположим, что комментирует Reader1 (id=1)
                'content' => $commentText
            ];

            // Добавляем новый комментарий в конфиг
            $this->config['comments'][] = $newComment;
            file_put_contents(__DIR__ . '/../config.php', '<?php return ' . var_export($this->config, true) . ';');

            // **Перечитываем конфиг и создаем новый PostService**
            $this->config = include __DIR__ . '/../config.php';
            //$this->postService = new PostService($this->config);

            // Перенаправляем на страницу поста
            $response = new SlimResponse();
            return $response->withHeader('Location', '/post/' . $postId)->withStatus(302);
        }

        // Если что-то пошло не так, возвращаем на страницу поста
        $response->getBody()->write("Ошибка при добавлении комментария.");
        return $response->withStatus(400);
    }

    public function create(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'GET') {
            // Отображаем форму создания поста
            ob_start();
            include __DIR__ . '/../View/post/create.php';
            $content = ob_get_clean();
            $response->getBody()->write($content);
            return $response;
        }

        // Обрабатываем POST-запрос (создание поста)
        $data = $request->getParsedBody();
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';

        if (!empty($title) && !empty($content)) {
            // Создаем новый пост (в заглушке)
            self::$lastPostId++;
            $newPost = [
                'id' => self::$lastPostId,
                'title' => $title,
                'content' => $content,
                'likes' => 0,
                'userId' => 2, // Предположим, что автор - Writer1 (id=2)
            ];

            // Добавляем новый пост в конфиг
            $this->config['posts'][] = $newPost;
            //$this->postService = new PostService($this->config);
            file_put_contents(__DIR__ . '/../config.php', '<?php return ' . var_export($this->config, true) . ';');
            // Перенаправляем на главную страницу
            $response = new SlimResponse();
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        // Если что-то пошло не так, возвращаем на форму создания поста
        $response->getBody()->write("Ошибка при создании поста.");
        return $response->withStatus(400);
    }


    public function edit(Request $request, Response $response, array $args): Response
    {
        $postId = (int)$args['id'];

        $data = $request->getParsedBody();

        // Найдем пост в конфиге
        $postKey = null;
        foreach ($this->config['posts'] as $key => $p) {
            if ($p['id'] === $postId) {
                $postKey = $key;
                break;
            }
        }

        if ($postKey === null) {
            $response->getBody()->write("Пост не найден.");
            return $response->withStatus(404);
        }

        $post = $this->config['posts'][$postKey];


        if ($request->getMethod() === 'GET') {
            // Отображаем форму редактирования поста
            ob_start();
            include __DIR__ . '/../View/post/edit.php';
            $content = ob_get_clean();

            $content = str_replace('<?php echo htmlspecialchars($post[\'id\']); ?>', htmlspecialchars($post['id']), $content);
            $content = str_replace('<?php echo htmlspecialchars($post[\'title\']); ?>', htmlspecialchars($post['title']), $content);
            $content = str_replace('<?php echo htmlspecialchars($post[\'content\']); ?>', htmlspecialchars($post['content']), $content);

            $response->getBody()->write($content);
            return $response->withStatus(200)->withHeader('Content-Type', 'text/html');
        }

        // Обрабатываем POST-запрос (сохранение изменений или удаление)
        $action = $data['action'] ?? null;

        if ($action === 'save') {
            // Сохраняем изменения
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';

            if (!empty($title) && !empty($content)) {
                $this->config['posts'][$postKey]['title'] = $title;
                $this->config['posts'][$postKey]['content'] = $content;

                $result = file_put_contents(__DIR__ . '/../config.php', '<?php return ' . var_export($this->config, true) . ';');

                $this->config = include __DIR__ . '/../config.php';
                //$this->postService = new PostService($this->config);

                $response = new SlimResponse();
                return $response->withHeader('Location', '/')->withStatus(302);
            } else {
                $response->getBody()->write("Ошибка при сохранении изменений.");
                return $response->withStatus(400);
            }
        } elseif ($action === 'delete') {

            unset($this->config['posts'][$postKey]);

            $result = file_put_contents(__DIR__ . '/../config.php', '<?php return ' . var_export($this->config, true) . ';');

            $this->config = include __DIR__ . '/../config.php';
            //$this->postService = new PostService($this->config);

            $response = new SlimResponse();
            return $response->withHeader('Location', '/')->withStatus(302);
        } else {
            $response->getBody()->write("Недопустимое действие.");
            return $response->withStatus(400);
        }
    }

    public function likePost(Request $request, Response $response, array $args): Response
    {
        $postId = (int) $args['id'];
        error_log($postId);
        // Проверяем, существует ли пост
        if (!isset($this->config['posts'][$postId])) {
            // Можно сделать обработку ошибки, например, вернуть 404
            $response->getBody()->write('Post not found');
            return $response->withStatus(404);
        }

        // Увеличиваем количество лайков (либо через сервис, либо напрямую)
        $this->config['posts'][$postId]['likes']++;
        $result = file_put_contents(__DIR__ . '/../config.php', '<?php return ' . var_export($this->config, true) . ';');
        $this->config = include __DIR__ . '/../config.php';
        //$this->postService = new PostService($this->config);

        // Перенаправляем обратно на страницу поста (или куда нужно)
        return $response->withHeader('Location', '/')->withStatus(302); //  '/' -  главная страница, замените на нужный URL
    }

    public function users(Request $request, Response $response): Response
    {
        $response->getBody()->write("Страница управления пользователями (доступна только для admin)");
        return $response;
    }
}

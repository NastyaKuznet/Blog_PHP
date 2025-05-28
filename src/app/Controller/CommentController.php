<?php

namespace NastyaKuznet\Blog\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;
use Slim\Views\Twig;
use NastyaKuznet\Blog\Service\interfaces\CommentServiceInterface;
use Throwable;

class CommentController
{
    private CommentServiceInterface $commentService;
    private Twig $view;

    public function __construct(CommentServiceInterface $commentService, Twig $view)
    {
        $this->commentService = $commentService;
        $this->view = $view;
    }

    // Форма редактирования
    public function editForm(Request $request, Response $response, array $args): Response
    {
        $commentId = (int)$args['id'];
        try {
            $comment = $this->commentService->getById($commentId);

            if (!$comment) {
                $response->getBody()->write("Комментарий не найден или был удалён.");
                return $response->withStatus(404)->withHeader('Content-Type', 'text/plain');
            }

            $data = ['comment' => $comment];

            return $this->view->render($response, 'post/comment/edit.twig', $data);
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
    }

    // Обработка редактирования
    public function update(Request $request, Response $response, array $args): Response
    {
        $commentId = (int)$args['id'];
        $comment = $this->commentService->getById($commentId);

        if (!$comment) {
            $response->getBody()->write("Комментарий не найден.");
            return $response->withStatus(404)->withHeader('Content-Type', 'text/plain');
        }

        $data = $request->getParsedBody();
        $content = trim($data['comment'] ?? '');

        if (empty($content)) {
            $response->getBody()->write("Комментарий не может быть пустым.");
            return $response->withStatus(400)->withHeader('Content-Type', 'text/plain');
        }

        try {
            $this->commentService->update($commentId, $content);
            $postId = $comment['post_id'];
            $redirect = new SlimResponse();
            return $redirect->withHeader('Location', "/post/{$postId}")->withStatus(302);
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
    }

    // Удаление комментария
    public function delete(Request $request, Response $response, array $args): Response
    {
        $commentId = (int)$args['id'];
        $comment = $this->commentService->getById($commentId);

        if (!$comment) {
            $response->getBody()->write("Комментарий не найден.");
            return $response->withStatus(404)->withHeader('Content-Type', 'text/plain');
        }

        try {
            $this->commentService->delete($commentId);
            $postId = $comment['post_id'];
            $redirect = new SlimResponse();
            return $redirect->withHeader('Location', "/post/{$postId}")->withStatus(302);
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
    }
}
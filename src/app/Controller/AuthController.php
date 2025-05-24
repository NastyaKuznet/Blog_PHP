<?php

namespace NastyaKuznet\Blog\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use NastyaKuznet\Blog\Service\Interfaces\AuthServiceInterface;
use Slim\Views\Twig;

class AuthController
{
    private AuthServiceInterface $authService;
    private Twig $view;

    public function __construct(AuthServiceInterface $authService, Twig $view)
    {
        $this->authService = $authService;
        $this->view = $view;
    }

    public function home(Request $request, Response $response): Response
    {
        // Получаем токен из кук
        $cookies = $request->getCookieParams();
        $token = $cookies['token'] ?? null;

        if ($token) {
            // Проверяем токен
            $payload = $this->authService->decodeJwtToken($token, $_ENV['JWT_SECRET']);

            if (is_array($payload) && isset($payload['exp']) && $payload['exp'] > time()) {
                // Токен валиден → редирект на /post
                return $response->withHeader('Location', '/post')->withStatus(302);
            }
        }

        // Если нет токена → показываем форму регистрации
        return $this->view->render($response, 'auth/register.twig');
    }

    public function register(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'GET') {
            return $this->view->render($response, 'auth/register.twig');
        }

        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            $html = '<div class="error">Заполните имя и пароль</div>';
            $response->getBody()->write($html);
            return $response
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(400);
        }

        $checkUser = $this->authService->checkUserRegistration($username, $password);
        if ($checkUser){
            return $this->view->render($response, 'auth/register.twig', [
                'error' => '<div class="error">Такой никнейм уже существует</div>'
            ]);
        }

        $success = $this->authService->registerUser($username, $password);

        if ($success) {
            // После регистрации сразу логиним пользователя
            $user = $this->authService->authenticateUser($username, $password);

            // Вызываем наш отдельный метод для установки токена
            $response = $this->setTokenInCookie($response, $user);
            return $response->withHeader('Location', '/')->withStatus(302);
        } else {
            return $this->view->render($response, 'auth/register.twig', [
                'error' => '<div class="error">Ошибка при регистрации</div>'
            ]);
        }
    }

    public function login(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'GET') {
            return $this->view->render($response, 'auth/login.twig');
        }

        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            return $this->view->render($response, 'auth/login.twig', [
                'error' => '<div class="error">Введите имя и пароль</div>'
            ]);
        }

        $user = $this->authService->authenticateUser($username, $password);

        if (!$user) {
            return $this->view->render($response, 'auth/login.twig', [
                'error' => '<div class="error">Неверное имя или пароль</div>'
            ]);
        }

        if($user->isBanned)
        {
            return $this->view->render($response, 'auth/login.twig', [
                'error' => '<div class="error">Вы забанены!</div>'
            ]);
        }

        // Вызываем отдельный метод для установки токена
        $response = $this->setTokenInCookie($response, $user);

        return $response->withHeader('Location', '/')->withStatus(302);
    }

    /**
     * Генерирует токен и сохраняет его в куках
     *
     * @param Response $response
     * @param \NastyaKuznet\Blog\Model\User $user
     * @return Response
     */
    private function setTokenInCookie(Response $response, $user): Response
    {
        // Генерируем токен через AuthService
        $token = $this->authService->generateJwtToken($user, $_ENV['JWT_SECRET']);

        // Устанавливаем токен в куки
        $response = $response->withHeader(
            'Set-Cookie',
            sprintf('token=%s; Path=/; HttpOnly; Secure; SameSite=Strict', $token)
        );

        return $response;
    }

    public function logout(Request $request, Response $response): Response
    {
        // Устанавливаем куку с пустым значением и прошедшим сроком действия
        $response = $response->withHeader(
            'Set-Cookie',
            'token=; Path=/; Expires=' . gmdate('D, d M Y H:i:s', time() - 3600) . ' GMT'
        );

        // Перенаправляем на главную страницу
        return $response->withHeader('Location', '/register');
    }
}
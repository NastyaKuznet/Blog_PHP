<?php

namespace NastyaKuznet\Blog\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use NastyaKuznet\Blog\Service\Interfaces\AuthServiceInterface;
use Slim\Views\Twig;
use Throwable;

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

    public function registerForm(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'auth/register.twig');
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        try {
            $checkUser = $this->authService->checkUserRegistration($username, $password);
            if ($checkUser){
                return $this->view->render($response, 'auth/register.twig', [
                    'error' => '<div class="error">Такой никнейм уже существует</div>'
                ]);
            }
            $this->authService->registerUser($username, $password);
            // После регистрации сразу логиним пользователя
            $user = $this->authService->authenticateUser($username, $password);

            // Вызываем наш отдельный метод для установки токена
            $response = $this->setTokenInCookie($response, $user);
            return $response->withHeader('Location', '/')->withStatus(302);
        } catch (Throwable){
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        }   
    }

    public function loginForm(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'auth/login.twig');
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        try {
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
                ])->withStatus(403);
            }

            // Вызываем отдельный метод для установки токена
            $response = $this->setTokenInCookie($response, $user);

            return $response->withHeader('Location', '/')->withStatus(302);
        } catch (Throwable){
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
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
        setcookie(
            'token',
            $token,
            [
                'path' => '/',
                'httponly' => true,
                'secure' => true,
                'samesite' => 'Strict',
                'expires' => strtotime('+1 day')
            ]
        );

        return $response;
    }

    public function logout(Request $request, Response $response): Response
    {
        // Устанавливаем куку с пустым значением и прошедшим сроком действия
        setcookie(
            'token',
            '',
            [
                'path' => '/',
                'httponly' => true,
                'secure' => true,
                'samesite' => 'Strict',
                'expires' => time() - 3600
            ]
        );

        // Перенаправляем на главную страницу
        return $response->withHeader('Location', '/register');
    }
}
<?php

use FirebaseJWTJWT;
use PsrHttpMessageResponseInterface;
use PsrHttpMessageServerRequestInterface;
use PsrHttpServerRequestHandlerInterface;

class AuthMiddleware {
    private $jwtSecret;

    public function __construct() {
        $this->jwtSecret = getenv('JWT_SECRET');
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $headers = $request->getHeaderLine('Authorization');
        if (!$headers) {
            return (new Response())->withStatus(401);
        }

        list($jwt) = sscanf($headers, 'Bearer %s');
        if (!$jwt) {
            return (new Response())->withStatus(401);
        }

        try {
            $decoded = JWT::decode($jwt, $this->jwtSecret, ['HS256']);
            $request = $request->withAttribute('user', (array)$decoded);
        } catch (Exception $e) {
            return (new Response())->withStatus(401);
        }

        return $handler->handle($request);
    }
}


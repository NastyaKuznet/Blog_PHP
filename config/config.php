<?php
return [
    'jwt' => [
        'secret' => 'your-secret-key',
        'algorithm' => 'HS256'
    ],
    'roles' => [
        'reader' => 'Читатель',
        'writer' => 'Писатель',
        'moderator' => 'Модератор',
        'admin' => 'Администратор'
    ]
];
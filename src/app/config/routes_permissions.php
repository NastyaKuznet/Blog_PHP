<?php

return [
    'roles' => [
        'reader' => [
            'routes' => [
                '/', 
                '/login', 
                '/register', 
                '/logout',
                '/post', 
                '#^/post/\d+$#', 
                '#^/post/\d+/like$#'
            ],
        ],
        'writer' => [
            'extends' => 'reader',
            'routes' => [
                '#^/post/\d+/like$#',
                '/account',
                '/post/create',
                '#^/comment/edit/\d+$#',
                '#^/comment/delete/\d+$#',
            ],
        ],
        'moderator' => [
            'extends' => 'writer',
            'routes' => [
                '#^/post/edit/\d+$#',
                '/post-non-publish',
                '#^/post-non-publish/\d+$#',
                '/categories',
                '/category/create',
                '#^/category/delete/\d+$#',
            ],
        ],
        'admin' => [
            'extends' => 'moderator',
            'routes' => [
                '/admin/users',
                '/admin/change_role',
                '/admin/delete_user',
                '/admin/toggle_ban',
            ],
        ],
    ],
];
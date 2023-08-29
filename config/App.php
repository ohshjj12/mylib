<?php







/**
 * App 설정
 */
return [
    // 프로젝트 경로
    'dir' => dirname(__DIR__),

    // class alias
    'alias' => [
        'Route' => MVC\Route::class,
        'URL' => Network\URL::class,
    ],

    // 앱 맨처음 실행
    'before' => [
        'sys/referer->stack'
    ],

    // 앱 끝나고 실행
    'after' => [
        // 'sys/traffic->stack'
    ],

    /**
     * 공통 모듈 설정
     */
    'module' => [
        'class' => 'MVC\Module',
        // 프로젝트 경로에서
        'dir' => 'module',

        'cache' => [
            'class' => 'Cache\File',
            'dir' => dirname(__DIR__).'/storage/cache/module',
            // 단위는 초
            'expire' => 60,
        ],

        // 기본 모듈
        // 'name' => $index,
        'view' => [
            'class' => 'MVC\View',
            // 모듈 기본경로에서 폴더이름
            'dir' => 'view',
            'default' => [],
        ],
        'controller' => [
            'class' => 'MVC\Controller',
            'file' => 'controller.php',
        ],
        'middleware' => [
            'class' => 'Middleware',
            'dir' => 'middleware',
            'throttle' => [
                // 클래스 이름
                'handler' => 'Network\HTTP\Session',
                'key' => 'throttle',
            ]
        ],
        'model' => include realpath(__DIR__.'/Model.php'),
    ],

    // 템플릿
    'view' => [
        'class' => 'MVC\View',
        'dir' => 'template',
        'default' => [],
        // 서브리소스 설정
        'sub' => [
            'cache' => [
                'class' => 'Cache\File',
                'dir' => dirname(__DIR__).'/storage/cache/template',
                // 단위는 초
                'expire' => 1,
            ],
            // 보안요소 설정 || 다운로드 설정 @fix 다운로드는 나중에 테스트
            // integrity || download
            'external' => 'integrity',
        ],
    ],

    // 에러시 템플릿
    'error' => [
        'class' => 'MVC\View',
        'dir' => 'template/mes/error',
        'default' => [],
    ],

    // 기본
    'route' => include realpath(__DIR__.'/Route.php'),
];

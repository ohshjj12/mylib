<?php
    return [
        
        'jinju' => [
            'id' => '',
            'secret' => '',
            'token' => '',
            'channel_id' => '',
        ],
        'error' => [
            'id' => '',
            'secret' => '',
            'token' => '',
            'channel_id' => '',
        ],
        'template' => [
            "content" => "<@@>",
            "username" => "",
            "tts" => false,
            "embeds" => [
                [
                    // Embed Title
                    "title" => "",

                    // Embed Type
                    "type" => "rich",

                    // Embed Description
                    "description" => "테스트",

                    // URL of title link
                    "url" => "",

                    // Timestamp of embed must be formatted as ISO8601
                    "timestamp" => date("c", strtotime("now")),

                    // Embed left border color in HEX
                    "color" => hexdec( "3366ff" ),

                    // Footer
                    "footer" => [
                        "text" => "",
                        "icon_url" => "/img/dealrun_logo.png"
                    ],

                    // Image to send
                    "image" => [
                        "url" => "/img/dealrun_logo.png"
                    ],

                    // Thumbnail
                    "thumbnail" => [
                       "url" => "/img/dealrun.png"
                    ],

                    // Author
                    "author" => [
                        "name" => "",
                        "url" => "/"
                    ],

                    // // Additional Fields array
                    // "fields" => [
                    //     // Field 1
                    //     [
                    //         "name" => "Field #1 Name",
                    //         "value" => "Field #1 Value",
                    //         "inline" => false
                    //     ],
                    //     // Field 2
                    //     [
                    //         "name" => "Field #2 Name",
                    //         "value" => "Field #2 Value",
                    //         "inline" => true
                    //     ]
                    //     // Etc..
                    // ]
                ]
            ]
        ],
        'dev_test' => 'https://discord.com/api/webhooks/1143010280549404753/RMYwmrDJ1OPFV1t0gzrgfGEkoOtD1yl6JTjYvdTqQcFWjhL2FHqXcDJYY1Wdy9L7YHag'
    ];

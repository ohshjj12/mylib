<?php
    return [
        'geun' => [
            'id' => '1143008129945194586',
            'secret' => 'WySOD1OH2ZipegChvYAHWNDhDMjrZQ1z',
            'token' => 'MTE0MzAwODEyOTk0NTE5NDU4Ng.G35bE0.8y9PjGoO51DRtTsywQCBdtyKvLy9256xqcTyJo',
            'channel_id' => '1131138994483253328',
        ],
        'eun' => [
            'id' => '1144429146416615484',
            'secret' => 'QTNvNQ-OYKs8tCVmvEf89HbRlCmnVn1F',
            'token' => 'MTE0NDQyOTE0NjQxNjYxNTQ4NA.G8ZCRy.SV6voIsF-sK1ukBcqCGVXsmpE-e8kqm4rPP7EM',
            'channel_id' => '1131138994483253328',
        ],
        'jinju' => [
            'id' => '1144429773922250812',
            'secret' => 'stwbGaKOLBuK047UyB42-4B4v65ffjJ9',
            'token' => 'MTE0NDQyOTc3MzkyMjI1MDgxMg.GaYXA1.SXQwF_swxwXgPIUAffLKzHvBk1dTf-YeQGDE5Y',
            'channel_id' => '1144502673719173212',
        ],
        'error' => [
            'id' => '1144433512997458060',
            'secret' => 'QTNvNQ-OYKs8tCVmvEf89HbRlCmnVn1F',
            'token' => 'MTE0NDQzMzUxMjk5NzQ1ODA2MA.G_swwq.SHX_jvnmpP3F8BsjrrSGYOWuWo1LS7MSCeeeHc',
            'channel_id' => '1144502129491443784',
        ],
        'template' => [
            "content" => "<@&1144457350615404564>",
            "username" => "딜런",
            "tts" => false,
            "embeds" => [
                [
                    // Embed Title
                    "title" => "딜런",

                    // Embed Type
                    "type" => "rich",

                    // Embed Description
                    "description" => "딜런 테스트",

                    // URL of title link
                    "url" => "https://dealrun.co.kr",

                    // Timestamp of embed must be formatted as ISO8601
                    "timestamp" => date("c", strtotime("now")),

                    // Embed left border color in HEX
                    "color" => hexdec( "3366ff" ),

                    // Footer
                    "footer" => [
                        "text" => "딜런",
                        "icon_url" => "https://dealrun.co.kr/img/dealrun_logo.png"
                    ],

                    // Image to send
                    "image" => [
                        "url" => "https://dealrun.co.kr/img/dealrun_logo.png"
                    ],

                    // Thumbnail
                    "thumbnail" => [
                       "url" => "https://dealrun.co.kr/img/dealrun.png"
                    ],

                    // Author
                    "author" => [
                        "name" => "딜런",
                        "url" => "https://dealrun.co.kr/"
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

<?php
namespace API;
use Network\HTTP\Curl;


class Discord
{
    protected static $template;



    // permission integer 274877913088
    // bot token: MTEzMTEzOTE4MjQ1MDk4Mjk1Mg.GovaqU.dkCBImwb7EX7K79cYuGJYTRmoxEYvl0fGY0FUQ
    /**
     * @param config = [$id, $secret, $token]
     * $channel_id는 config 던지기 전에 설정 후
     */
    function __construct(array $config, array $template = [])
    {
        $this->host = 'https://discord.com';

        self::$template = 
        [
            "content" => "<@&1144457350615404564>",
            "username" => "딜런",
            "tts" => false,
            "embeds" => [
                [
                    // Embed Title
                    "title" => "테스트임",
        
                    // Embed Type
                    "type" => "rich",
        
                    // Embed Description
                    "description" => " 테스트",
        
                    // URL of title link
                    "url" => "내 url",
        
                    // Timestamp of embed must be formatted as ISO8601
                    "timestamp" => date("c", strtotime("now")),
        
                    // Embed left border color in HEX
                    "color" => hexdec( "3366ff" ),
        
                    // Footer
                    "footer" => [
                        "text" => "테스트",
                        "icon_url" => "내 url/img/dealrun_logo.png"
                    ],
        
                    // Image to send
                    "image" => [
                        "url" => "내 url/img/dealrun_logo.png"
                    ],
        
                    // Thumbnail
                    "thumbnail" => [
                       "url" => "내 url/img/dealrun.png"
                    ],
        
                    // Author
                    "author" => [
                        "name" => "테스트트",
                        "url" => "내 url/"
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
        ];
        self::$template = array_merge(self::$template, $template);
        foreach ($config as $k => $v) {
            $this->$k = $v;
        }
    }

    function setChannel($id)
    {
        $this->channel_id = $id;
    }

    /**
     * 디스코드 봇이 아닌 서버 웹 후크 기능을 이용한 채널 메시지 전송
     * @param string  URL
     * @param array  메시지 전송할 데이터
     * @param array  템플릿 config
     * @param bool  에러 확인용
     */
    static function webhook($url, $data, $config, $bool)
    {
        $config = array_merge(self::$template, $config);
        $config['embeds'][0] = array_merge($config['embeds'][0], $data);
        $data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $curl = new Curl($url);
        $response = $curl->post($data)->header(['Content-type' => 'application/json'])->send();
        //['response']
        if($bool)
        {
            var_dump($response);
        }
    }
    
    /**
     * 메세지 보내기
     * https://discord.com/developers/docs/resources/channel#create-message
     */
    function message($msg)
    {
        // POST:/channels/{channel.id}/messages
        $curl = new Curl($this->host."/api/channels/{$this->channel_id}/messages");
        $res = $curl->post([
            'content' => $msg
        ])->header([
            'Authorization' => "Bot {$this->token}"
        ])->send()['response'];
    }

}

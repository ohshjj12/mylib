<?php


namespace Client;

use \Exception;
use Session;

/**
 * 클라이언트 정보, 연결수 등 글로벌 변수 없는거도 추가
 */
class Request
{
    static protected array $item = ['ip','agent','scheme','uri','method','query','get','post','input','header','session','cookie'];
    protected $ip;
    protected $agent;
    protected $scheme;
    protected $uri;
    protected $method;
    protected $query;

    /**
     * 초기화, 프로퍼티 세팅
     * config은 필요할까
     */
    #function __construct(array $config = [])
    function __construct()
    {
        // 한번만입력
        foreach(self::$item as $v)
            if ( ! isset ( $this->$v ) )
                $this->$v = self::$v();
    }


    /**
     * 있는 프로퍼티만
     */
    function __get(string $k)
    {
        if ( property_exists($this, $k) )
            return $this->$k;
    }


    /**
     * public 에서 getter setter 안됨
     */
    function __set($k,$v)
    {
        if ( property_exists($this, $k) )
            throw new Exception("프로퍼티 수정 금지", 500);
    }


    /**
     * 클라이언트 아이피
     */
    static function ip()
    {
        $ip = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ip = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ip = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ip = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ip = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ip = getenv('REMOTE_ADDR');
        else
            throw new Exception("클라이언트 아이피를 찾지 못함", 500);
            
        return $ip;
    }


    /**
     * 클라이언트 에이전트
     */
    static function agent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * 클라이언트 요청 프로토콜
     */
    static function scheme()
    {
        return $_SERVER['REQUEST_SCHEME'];
    }


    /**
     * 클라이언트 요청 메서드
     */
    static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }


    /**
     * 클라이언트 요청 uri
     */
    static function uri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if ( is_int ( strpos($uri,'?') ) )
            $uri = strstr ( $uri , '?' , true ) ;
        return $uri ;
    }

    /**
     * 클라이언트 요청 http 쿼리
     */
    static function query()
    {
        $res = [];
        parse_str($_SERVER['QUERY_STRING'],$res);
        return $res;
    }

    /**
     * 요청 파라미터 GET
     */
    static function get()
    {
        return $_GET;
    }

    /**
     * 요청 파라미터 POST
     */
    static function post()
    {
        return $_POST;
    }


    /**
     * 요청 파라미터 INPUT
     */
    static function input()
    {
        return file_get_contents('php://input');
    }


    /**
     * 요청 헤더
     */
    static function header()
    {
        return apache_request_headers();
    }

    /**
     * 세션 확인
     */
    static function session()
    {
        return $_SESSION;
    }

    /**
     * 쿠키 확인
     */
    static function cookie()
    {
        return $_COOKIE;
    }
}

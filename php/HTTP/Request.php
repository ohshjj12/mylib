<?php


namespace HTTP;

use Exception, ArrayAccess, ReflectionClass, ReflectionMethod;

/**
 * 클라이언트 리퀘스트 정보를 모은다
 */
class Request implements ArrayAccess
{
    function __construct()
    {
        $m = get_class_methods($this);
        
        $r = new ReflectionClass(__CLASS__);
        $m = array_intersect(
            $r->getMethods(ReflectionMethod::IS_STATIC),
            $r->getMethods(ReflectionMethod::IS_PUBLIC)
        );
        foreach ($m as $v)
        {
            $n = $v->name;
            $this->$n = $n;
        }
    }


    function __set($k,$v)
    {
        if ( ! method_exists($this,$k ) || is_int(strpos($k,'__')) || in_array($k,['toArray']) )
            return;
        $this->$k = $this->$v();
    }


    public function offsetExists($offset):bool
    {
        return isset($this->$offset) ;
    }
    
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value):void
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset):void
    {
        unset($this->$offset);
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
        // else
        //     throw new Exception("클라이언트 아이피를 찾지 못함", 500);
            
        return $ip;
    }


    /**
     * 클라이언트 에이전트
     */
    static function agent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
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
     * 세션
     */
    static function session()
    {
        return new Session;
    }

    /**
     * 쿠키
     */
    static function cookie()
    {
        return $_COOKIE;
    }


    /**
     * 요청 데이터 타입, 확장자 우선
     * @return string 타입
     */
    static function type()
    {
        // 기본값
        $default = 'html';

        // 확장자 있다면
        $ext = pathinfo ( self::uri() )['extension'] ?? '' ;
        if ( ! empty ( $ext ) )
            return $ext;

        // 헤더 확인
        $accept = self::header()['Accept'] ?? '';
        $accept = explode(',',$accept)[0];
        $accept = explode('/',$accept)[1] ?? '';


        // * 처리는 안해놔서 기본값으로
        if ( $accept == '*' )
            $accept = $default;

        // accept 요청 헤더 있다면
        if ( ! empty ( $accept ) )
            return $accept;
        
        // 기본값 리턴
        return $default;
    }


    /**
     * 요청 파라미터 REQUEST
     */
    static function request()
    {
        return $_REQUEST;
    }

}

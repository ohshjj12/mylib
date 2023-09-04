<?php


namespace HTTP;

use Config as Config;

/**
 * 응답
 */
class Response
{
    /**
     * @var array 응답할 뷰 인스턴스
     */
    public $View;
    public $Template;

    /**
     * @var array 응답할 헤더
     */
    protected static $header = [];

    /**
     * @var array 응답할 메세지
     */
    protected static $message = [];

    /**
     * @var array 리다이렉트 컨트롤러
     */
    protected static $redirect;

    function __construct ( $config = [] )
    {
        foreach ($config as $p => $c)
        {
            $class = ucfirst($p);
            $this->$class = new $class ( $c ) ;
        }

        // if ( empty ($this->View ))
        //     $this->View = new View;
    }


    /**
     * 프로퍼티 초기화
     */
    function clear()
    {
        self::$header = [];
        self::$message = [];
        self::$redirect = '';
        $this->View->clear();
        $this->Template->clear();
    }


    function send()
    {
        if ( ! empty ( self::$redirect ) )
            return header( 'Location: '.Config::get('App','route','uri','prefix').'/'.self::$redirect );
        
        foreach ( self::$header as $v )
            header($v);
        
        echo implode("\n",self::$message);

        if ( ! empty ( $this->View ) )
            $this->View->send();
        if ( ! empty ( $this->Template ) )
            $this->Template->send();
    }


    /**
     * 응답헤더 세팅
     */
    function header ( $h )
    {
        if ( ! is_array ( $h ) )
            $h = [$h];

        self::$header = array_merge(self::$header, $h);
        return $this;
    }


    /**
     * HTTP 코드 응답 헤더 세팅
     * @param int HTTP 코드
     * @param string 메세지
     */
    function httpcode ( $code, $msg = '' )
    {
        self::$header[] = $_SERVER['SERVER_PROTOCOL'] . ' ' . $code . ' ' . $msg;
        return $this;
    }


    /**
     * 응답 결과 세팅
     * @param string 메세지
     * @return this
     */
    function message ( $msg )
    {
        self::$message[] = $msg;
        return $this;
    }


    /**
     * 뷰 쌓기
     */
    function view ( $name, $param = [] )
    {
        $this->View->queue($name, $param);
        return $this;
    }


    /**
     * 템플릿 쌓기
     */
    function template ( $name, $param = [] )
    {
        $this->Template->queue($name, $param);
        return $this;
    }


    /**
     * 리다이렉트 세팅
     * @param string uri
     * @return this
     */
    function redirect ( $c )
    {
        self::$redirect = $c;
        return $this;
    }


    /**
     * json 형식으로 응답
     * @param mixed 데이터
     */
    function json ( $data )
    {
        return $this->message(json_encode($data));
    }


    /**
     * 다운로드
     * @param string 파일 경로
     * @param string 다운로드될 이름
     */
    function download ( $file, $name = '' )
    {
        if ( empty ( $name ) )
            $name = basename($file);

        header("Content-type: ".mime_content_type($file));
        header("Content-Disposition: attachment; filename=".$name);
        header("Content-length: " . filesize($file));
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile($file);

    }
}

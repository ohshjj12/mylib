<?php


namespace Network\HTTP;


/**
 * 
 * @fix 다른 메서드일때 데이터 집어넣는거 빼먹음
 * 사용법
 $curl = new Curl('http://naver.com');
 $curl->get($data)->send();
 $curl->post($data)->send();


 $curl
 ->method('put')
 ->setOption( CURLOPT_COOKIEFILE, true )
 ->send();


 @see 필요시 추가
$result[1] = curl_errno($ch);
$result[2] = curl_error($ch);
$result[3] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 */



class Curl
{

    /**
     * 연결 리소스
     */
    protected $curl;

    /**
     * 옵션
     */
    protected $option = [
        
        // 세션 유지
        CURLOPT_COOKIEFILE => true,
        CURLOPT_COOKIESESSION => true,

        CURLOPT_FRESH_CONNECT => true,
        // 응답을 변수로 빼기
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FORBID_REUSE => true,

        CURLOPT_TIMEOUT => true,
        CURLOPT_CONNECTTIMEOUT => 1,

        // redirect 따라가기
        CURLOPT_FOLLOWLOCATION => true,
        // redirect 제한
        CURLOPT_MAXREDIRS => 5,
    ];

    /**
     * HTTP method
     * method 안겹치게 따로 세팅
     */
    protected $method;

    protected $url;

    protected $error;


    function __construct ( $host = '' )
    {
        $this->curl = curl_init();
        $this->url = $host;
    }


    /**
     * readonly
     */
    function __get($p)
    {
        if ( $p == 'error' )
            return $this->error();

        return property_exists($this,$p) ? $this->$p : false ;
    }


    /**
     * 연결 닫기
     * 리소스 타입들은 제때 끊어줘야 렉 안걸림
     */
    function __destruct()
    {
        $this->close();
    }


    /**
     * 에러 확인
     */
    function error()
    {
        $res = '';
        if ( gettype($this->curl) === 'resource' )
            $res = curl_error($this->curl) ;
        if ( ! empty ( $res ) )
            throw new Exception('curl 에러 : '.$res, 1);

        return $res;
    }


    function header( $arr )
    {   
        $h = [];
        foreach ($arr as $k => $v) {
            $h[] = $k.': '.$v;
        }

        $this->setOption(CURLOPT_HTTPHEADER, $h);

        return $this;
    }


    /**
     * HTTP method
     * @param string GET POST 등
     */
    function method ( $method )
    {
        $method = strtoupper($method);
        $option = [
            'GET' => [CURLOPT_HTTPGET => true],
            'POST' => [CURLOPT_POST => true],
            'PUT' => [CURLOPT_PUT => true],
            'HEAD' => [CURLOPT_NOBODY => true],
        ];
        $this->method = $option[$method] ?? [CURLOPT_CUSTOMREQUEST => $method];

        return $this;
    }


    /**
     * 파라미터 세팅
     */
    function get ( $data )
    {
        // $recursive = function($data,$recursive){
        //     curl_escape($this->curl, $str);
        // };
        // $data = $recursive($data,$recursive);
        
        $this->url .= '?'.http_build_query($data);
        // $this->setOption('???',$data);
        return $this;
    }

    function post ( $data )
    {
        if ( is_array($data) || is_object($data) )
            $data = http_build_query($data);
        $this->setOption(CURLOPT_POST,true);
        $this->setOption(CURLOPT_POSTFIELDS,$data);
        return $this;
    }

    /**
     * CURL 옵션 추가
     * https://www.php.net/manual/en/function.curl-setopt.php
     * @param int CURLOPT 상수
     * @param mixed 세팅값
     */
    function setOption ( $k, $v )
    {
        $this->option[$k] = $v;
        return $this;
    }


    /**
     * 연결 닫기
     */
    function close()
    {
        if ( gettype($this->curl) === 'resource' )
            curl_close ( $this->curl ) ;
    }
    

    function send()
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->url);
        curl_setopt_array($this->curl, $this->option);
        if ( ! empty ( $this->method ) )
            curl_setopt_array($this->curl, $this->method);

        return array_merge(
            curl_getinfo($this->curl),
            ['response' => curl_exec($this->curl)]
        );
    }
}

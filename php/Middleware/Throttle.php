<?php

namespace Middleware;

use HTTP\Request as Request;

/**
 * 요청 정보를 파일|세션 등에 담고 몇번했는지 확인, 제한
 */
class Throttle implements MiddlewareInterface
{
    /**
     * @var resource|object 핸들러 파일|세션 등
     */
    protected static $handler;

    /**
     * @var string 데이터키
     */
    protected static $key;

    protected $rule;
    protected $request;
    protected $response;

    /**
     * 세팅
     */
    function __construct ( $config = [] )
    {
        foreach ($config as $k => $v)
        {
            if ( $k == 'handler' && is_string($v) )
            {
                $class = '\\'. $v;
                $v = new $class;
            }
            if ( empty ( self::$$k ) )
                self::$$k = $v;
        }
    }

    /**
     * HTTP 요청 데이터를 세팅
     */
    function setRequest ( Request &$request )
    {
        $this->request = $request;
        return $this;
    }


    /**
     * 룰 세팅
     * @fix 이거 다른거랑 비슷하게 request 키 붙어있는데 없앨수도
     * @param array config
     * @return this
     */
    function setRule ( $config )
    {
        $this->rule = $config;
        return $this;
    }


    /**
     * 응답을 세팅한다
     * @param array config
     * @return this
     */
    function setResponse ( $config )
    {
        $this->response = $config;
        return $this;
    }


    /**
     * 검사 후 응답을 반환한다
     * @return array|null
     */
    function check()
    {
        // 요청 정보 기록
        // 검사하는 부분만 기록하기
		self::push($this->request->uri,$this->request->method);

        
        // request key => rule config
        foreach ($this->rule as $k => $v)
        {
            // 없으면 스킵
            if ( ! isset ( $this->request->$k[self::$key] ) )
                break;
            $q = 0;
            $stack = $this->request->$k[self::$key];
            // second 계산
            $time = time() - $v['second'];
            foreach ($stack as $vv)
            {
                if ( $vv['time'] < $time )
                    continue;
                if ( $this->request->uri != $vv['uri'] )
                    continue;
                if ( $this->request->method != $vv['method'] )
                    continue;
                $q ++;
            }
            // 시간내 요청 초과시 에러
            if ( $v['max'] < $q )
                return $this->response[$k];
        }

        $this->clear();
    }


    /**
     * limit 시간 넘는거 삭제
     */
    function clear()
    {
        $rule = array_pop($this->rule);
        $time = time() - $rule['second'];

        $s = self::$handler->get(self::$key);
        foreach ( $s as $k => $v )
        {
            if ( $v['uri'] != $this->request->uri )
                continue;
            if ( $v['method'] != $this->request->method )
                continue;

            // 설정시간 내 삭제 안함
            if ( $v['time'] > $time )
                continue;

            // 나중에
            // self::$handler->unset([self::$key,$k]);
            if ( session_status() != PHP_SESSION_ACTIVE )
			    session_start();
            unset($_SESSION[self::$key][$k]);
        }

        foreach ( ['data','rule','response'] as $v)
            unset($this->$v);
    }
    
    /**
     * 요청 정보 기록
     * @param string uri
     * @param string http method 
     */
    static function push($uri, $method)
    {
        // 싱글톤
        static $count;
        if ( ! empty ( $count ) )
            return;

        $request = [
            'uri' => $uri,
            'method' => $method,
            'time' => time(),
        ];
        self::$handler->push(self::$key, $request);

        $count = 1;
    }


}

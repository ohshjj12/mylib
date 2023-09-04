<?php

namespace Middleware;

use HTTP\Request as Request;

/**
 * 세션 보안
 */
class CSRF implements MiddlewareInterface
{
    protected $rule;
    protected $request;
    protected $response;

    /**
     * HTTP 요청 데이터를 세팅
     * @param Request 요청
     * @return this
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
        var_dump('체크CSRF');
        exit;
    }



}

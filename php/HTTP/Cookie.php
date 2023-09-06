<?php



namespace Network\HTTP;

use ArrayAccess;
use Iterator;


/**
 * https://www.php.net/manual/en/class.arrayaccess.php
 * implements ArrayAccess
 * 배열접근 가능하게하는 메서드 4개
 * 7,8 버전 인터페이스 다름
 */
class Cookie implements ArrayAccess , Iterator
{
    protected $position = [];
    function __construct ()
    {
    }


    /**
     * 주석 어케 ??
     * @implement Iterator
     */
    public function rewind()
    {
        $this->position = [];
    }


    /**
     * @implement Iterator
     */
    public function current()
    {
        return self::get($this->position);
    }


    /**
     * @implement Iterator
     */
    public function key()
    {
        return $this->position;
    }


    /**
     * @implement Iterator
     */
    public function next()
    {
        // 현재의 다음 키 얻어와서 세팅 하고 뱉어야함
        echo '<pre>';
        var_dump(__CLASS__,__FUNCTION__);
        var_dump($this->position);
        exit;
    }


    /**
     * @implement Iterator
     */
    public function valid()
    {
        return is_null($this->current());
    }


    /**
     * @implement ArrayAccess
     */
    public function offsetExists($offset)
    {
        return self::get($offset) !== false ;
    }


    /**
     * @implement ArrayAccess
     */
    public function &offsetGet($offset)
    {
        return $this->get($offset);
    }


    /**
     * @implement ArrayAccess
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value) ;
    }


    /**
     * @implement ArrayAccess
     */
    public function offsetUnset($offset)
    {
        $this->set($offset,'');
    }


    /**
     * 세션 값 얻기
     * @param string ... keys 키 > 하위키 ...
     * @return mixed 해당하는 키의 값
     */
    static public function &get ()
    {
        // 파라미터 없으면 전체 리턴
        $args = func_get_args();
        if ( empty ( $args ) )
            return $_COOKIE;
        
        // 맞는 키 찾아서 리턴
        $res = NULL;
        foreach ( $args as $v )
            $res = $res[$v] ?? $_COOKIE[$v] ?? NULL ;
        return $res;
    }


    /**
     * 세션 키에 값을 할당한다
     * @param mixed 키
     * @param mixed 값
     * @param int 만료시간 초
     */
    public function set($k, $v, $sec = 1000)
    {
        return setcookie($k,$v,time()+$sec);
    }
}

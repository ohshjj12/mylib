<?php



namespace Middleware;

use Exception;
use RuntimeException;
use ReflectionClass;
use ReflectionMethod;
use Arr;

/**
 * https://m.blog.naver.com/hj_kim97/222346215380
 * jQuery 플러그인 validate 보고 만듬
 * 
 * 사용법
 * $validate
 *  ->setData($data)
 *  ->setRule($rule)
 *  ->setResponse($response)
 *  ->check();
 * 
 * DB에서 확인하는부분 필요
 * 값 부분이 array일때 처리 확인필요
 */
class Validate implements MiddlewareInterface
{
    protected static $method;
    protected static $special;

    /**
     * 룰에 있는 키 이외에 있으면 에러표시 할 파라미터 타입 키
     * Response 객체의 메서드
     */
    protected static $strict = ['get','post','input','request'];

    protected $rule;
    protected $request;
    protected $response;

    /**
     * https://stackoverflow.com/questions/8299886/php-get-static-methods
     * public static method 만 추출해서 입력
     */
    function __construct()
    {
        if ( empty ( self::$method ) )
        {
            $r = new ReflectionClass(__CLASS__);
            $m = array_intersect(
                $r->getMethods(ReflectionMethod::IS_STATIC),
                $r->getMethods(ReflectionMethod::IS_PUBLIC)
            );
            foreach ( $m as $k =>$v )
                self::$method[] = $v->name;
            self::$special = ['default','optional'];
            rsort(self::$special);
        }
    }


    /**
     * 레퍼런스 사용으로 검사 후 입력한 데이터에 디폴트 값이 세팅된다
     * @param array 데이터
     * @return this
     */
    function setRequest( &$request )
    {
        $this->request =& $request;
        return $this;
    }


    /**
     * rule을 정렬하고 정리해서 세팅한다
     * 메서드만 있는것은 key => NULL
     * order 순서대로 맨앞에 오도록
     * 
     * @param array $config [ 데이터 키 => 데이터 값 확인할 메서드 ]
     * @return this
     */
    function setRule ( $config )
    {
        $this->rule = $this->orderRule($config);
        return $this;
    }


    /**
     * 검사 결과 세팅
     * @param array $config [ 데이터 키 => 실패시 메세지, 리다이렉트 등 ]
     * @return this
     */
    function setResponse ( $config )
    {
        $this->response = $config;
        return $this;
    }



    /**
     * 규칙 재배치
     * @param array 규칙
     * @return array 규칙
     */
    function orderRule ( $rule )
    {
        foreach ($rule as $k => $v)
        {
            // 하위 배열이면 재귀
            if ( is_array ( $v ) )
                $rule[$k] = $this->orderRule($v);
            else if ( is_int($k) && in_array ( $v , array_merge(self::$method,self::$special) ) )
            {
                // 메서드를 키로 옮기기
                $rule[$v] = '';
                unset($rule[$k]);
            }
        }

        // special 순으로 앞쪽에 나오도록
        if ( ! empty ( array_intersect ( array_keys($rule), self::$special ) ) )
        {
            foreach (self::$special as $k)
            {
                if ( isset ( $rule[$k] ) )
                    $rule = array_merge ( [ $k => $rule[$k] ] , $rule ) ;
            }
        }
        return $rule;
    }


    /**
     * 값 확인, default 적용
     * @fix 오브젝트일때 array 처럼 사용가능하게
     * @param mixed 데이터
     * @param array 규칙
     * @param array 응답
     * @param mixed 데이터와 비교해서 확인했는지
     * @return null|string 에러 있으면 응답
     */
    function recursive( &$data, $rule, $response)
    {
        $rule = $this->orderRule($rule);

        // 룰 기준으로 도는데
        foreach ($rule as $k => $v)
        {
            // 데이터키가 optional이 아닌거만
            if ( ! isset ( $data[$k] ) && $k == 'optional' )
            {
                if ( ! is_int ( $data ) && ( empty ( $data ) || ! isset ( $data ) ) )
                    break;
                continue;
            }

            // 루프 못돌면 값 확인
            if ( ! is_iterable ( $data ) && gettype($data) != 'object' )
            {
                if ( $k == 'default' )
                {
                    if ( empty ( $data ) || ! isset ( $data ) )
                        $data = $v;
                    continue;
                }

                // 숫자가 아니면서 빈값이면 데이터 없는취급
                if (
                    ( ! is_numeric ( $data ) && empty ( $data ) )
                    || ! call_user_func_array([__CLASS__,$k],[$data,$v])
                )
                    return $response[$k] ?? $response;
            }
            elseif ( is_array ( $data ) && empty ( $data ) )
            {
                // array인데 빈값이면 default 있을때 세팅해주기
                if ( isset ( $v['default'] ) )
                    $data[$k] = $v['default'];

                $res = $this->recursive($data[$k] , $v ,$response[$k]);
                if ( ! empty ( $res ) )
                    return $res;
            }
            else if ( ! empty ( $data ) && gettype($data) == 'array' && array_values ( $data ) === $data )
            {
                $depth = Arr::depth($data);
                // 데이터가 리스트인지 확인 후 재귀
                foreach ($data as $n => $row)
                {
                    $res = $depth == 1
                        ? $this->recursive($data[$n],$rule,$response)
                        : $this->recursive($data[$n][$k],$v,$response[$k]);
                    if ( ! empty ( $res ) )
                        return $res;
                }
            }
            else if ( isset ( $data[$k] ) && is_iterable ( $data[$k] ) )
            {
                // 루프 되면 재귀
                $res = $this->recursive($data[$k],$v,$response[$k]);
                if ( ! empty ( $res ) )
                    return $res;
            }
            else if ( is_iterable ( $data ) )
            {
                // 일반적인 1차
                $res = $this->recursive($data[$k],$v,$response[$k]);
                if ( ! empty ( $res ) )
                    return $res;
            }
            else if ( ! isset ( $data[$k] ) )
            {
                // 데이터가 없는데 다른거 하위 있으면 넘기기
                $res = $this->recursive($data[$k],$v,$response[$k]);
                if ( ! empty ( $res ) )
                return $res;
            }
            else
            {
                
                throw new RuntimeException('데이터 확인 중 에러');
            }
        }
    }


    /**
     * 세팅된 데이터들을 실행한다
     * rule 기준으로 루프 돌면서 확인한다
     * 키 부분에 [] 있으면 숫자루프
     * @return string 메세지 비어있으면 에러없음
     */
    function check()
    {
        $o = is_object($this->request);
        foreach ($this->rule as $k => $rule)
        {
            // 레퍼런스는 3항연산자 안됨
            if ( $o )
                $data =& $this->request->$k;
            else
                $data =& $this->request[$k];

            // 엄격한 파라미터 검사 적용 대상
            // if ( ! is_int ( $k ) && in_array ( strtolower ( $k ) , self::$strict ) ) 
            // {
            //     // 요청에 키가 있는데 룰에 없으면 에러 출력
            //     $diff = array_diff ( array_keys( $data ) , array_keys($rule) );
            //     if ( ! empty ( $diff ) )
            //         throw new Exception('룰에 정의하지 않은 데이터키가 있습니다 : `' . implode(',',$diff) . '`', 1);
            // }

            $res = $this->recursive($data,$rule,$this->response[$k]);

            // 중간에 에러 있으면 리턴
            if ( ! is_null ( $res ) )
                return $res;
        }


        $this->clear();
        return $res;
    }


    /**
     * 프로퍼티를 비운다
     */
    function clear()
    {
        foreach ( ['data','rule','response'] as $v)
            unset($this->$v);
    }


    /**
     * 날짜 형식이 맞는지 확인한다
     * @param string 데이터
     * @param string 포맷 date() 함수에 들어가는 형식 Y-m-d 등
     * @return boolean
     */
    public static function date($data, $format)
    {
        if ( $format == 'Y' )
            return self::int($data) && self::min($data,1) ;
        else if ( $format == 'm' )
            return self::int($data) && self::min($data,1) && self::max($data,12);

        if ( strtotime($data) === false )
        {
            var_dump($data,$format);
            throw new Exception("날짜 형식 변경실패", 1);
        }
        return date($format, strtotime($data)) == $data;
    }

    
    /**
     * 데이터 길이 최소 넘는지 확인
     * @param string|int|float 데이터
     * @param int|string 정수 숫자
     * @return boolean 결과
     */
    public static function minlength($data, $min)
    {
        if ( ! self::int($min) )
            throw new Exception('$min은 숫자(정수)만 입력 가능합니다', 1);
        return strlen(strval( $data )) >= $min;
    }


    /**
     * 데이터 길이 최대 넘는지 확인
     * @param string|int|float 데이터
     * @param int|string 정수 숫자
     * @return boolean 결과
     */
    public static function maxlength($data, $max)
    {
        if ( ! self::int($max) )
            throw new Exception('$max은 숫자(정수)만 입력 가능합니다', 1);

        return strlen(strval( $data )) <= $max;
    }


    /**
     * 숫자 최소인지 확인
     * @param int|float|string 데이터 숫자
     * @param int|float|string 숫자
     * @return boolean 결과
     */
    public static function min($data, $min)
    {
        if ( ! self::numeric($min) )
            throw new Exception('$min은 숫자(정수, 소수점)만 입력 가능합니다', 1);
        
        if ( ! self::numeric($data) )
            return false;
        return $data >= $min;
    }


    /**
     * 숫자 최대인지 확인
     * @param int|float|string 데이터 숫자
     * @param int|float|string 숫자
     * @return boolean 결과
     */
    public static function max($data, $max)
    {
        if ( ! self::numeric($max) )
            throw new Exception('$max는 숫자(정수,소수점)만 입력 가능합니다', 1);

        if ( ! self::numeric($data) )
            return false;
        return $data <= $max;
    }


    /**
     * 정규식 매치 결과를 반환
     * @param string 데이터
     * @param string 정규식
     * @return boolean 결과
     */
    public static function regex($data, $regex)
    {
        if ( strpos($regex,'/') !== 0)
            $regex = '/'.$regex;
        if ( strpos(strrev($regex),'/') !== 0)
            $regex .= '/';

        return preg_match($regex, $data) > 0;
    }


    /**
     * 정규식 매치 결과의 반대를 반환
     * @param string 데이터
     * @param string 정규식
     * @return boolean 결과
     */
    public static function not_regex($data, $regex)
    {
        return ! self::regex($data, $regex);
    }


    /**
     * 숫자만 있는지 확인 (정수, 소수점)
     * @param string 데이터
     * @return boolean 결과
     */
    public static function numeric( $data )
    {
        return is_numeric($data);
    }


    /**
     * 정수인지 확인
     * @param string|int 데이터
     * @return boolean 결과
     */
    public static function int($data)
    {
        return is_numeric($data) && ! is_int(strpos($data, '.')) ;
    }


    /**
     * IPv4 형식 확인
     * @param string 아이피
     * @return boolean 결과
     */
    public static function IPv4 ( string $ip )
    {
        $ip = explode('/', $ip);
        return is_int ( ip2long ( $ip[0] ) )
            && isset ( $ip[1] ) ? ( 0 <= $ip[1] && $ip[1] <= 32 ) : true ;
        // preg_match('/^(([0-9]|[0-9]{2}|1[0-9][0-9]?|2[0-9]|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[0-9]{2}|1[0-9][0-9]?|2[0-9]|2[0-4][0-9]|25[0-5])(\/(0|[1-2][0-9]|3[0-2]))?$/', $ip, $match);
        // return count($match) > 0 ;
    }


    /**
     * 배열에 있는지 확인
     * @param string 값
     * @return boolean
     */
    public static function in ( string $data, array $arr )
    {
        return in_array($data,$arr);
    }


    /**
     * 배열에 있는지 확인
     * @param string 값
     * @return boolean
     */
    public static function notin ( string $data, array $arr )
    {
        return ! in_array($data,$arr);
    }


    /**
     * json 형식인지 확인
     * @param string 값
     * @return boolean
     */
    public static function json ( string $data )
    {
        return ! empty ( json_encode($data) ) ;
    }


    /**
     * 호출 결과를 boolean으로 반환
     * 여기서만 제한적으로 call > param > 값에 $data문자 있으면 $data 변수로
     * @param 
     * @return boolean
     */
    public static function call ( $data , $call )
    {
        if ( isset($call['function']) )
            $func = $call['function'];
        else
            $func = [$call['class'], $call['method']];

        $param = $call['param'] ?? [];
        array_walk_recursive($param,function(&$v)use($data){
            if ( $v == '$data' )
                $v = $data;
        });

        return boolval(call_user_func_array($func,$param) );
    }
}

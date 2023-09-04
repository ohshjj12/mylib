<?php

namespace DB\Mysql;


class Statement
{

    /**
     * 커넥션 없이 이스케이프
     */
    static function escape ( $data )
    {
        // 아스키코드에서 ' " 포함 모든 공백 이스케이프
        static $char = [];
        if ( empty ( $char ) )
        {
            foreach (range(0,127) as $s)
                if (
                    $s < 33
                    || $s > 126
                    // ' "
                    || $s === 34 || $s === 39
                    // `
                    || $s === 96
                )
                    $char[] = chr($s);
            $char = implode('',$char);
        }

        $res = null;
        if ( is_array ( $data ) )
        {
            $res = [];
            foreach ($data as $k => $v)
            {
                $res[addcslashes($k,$char)] = is_array ( $v )
                    ? self::escape($v)
                    : addcslashes($v,$char);
            }
        }
        else
            $res[addcslashes($k,$char)] = $v;

        return $res;
    }

    /**
     * @param string join 종류
     * @param string 테이블 이름
     * @param array 연결조건
     */
    static function join ( $join, $table, $on )
    {
        $on = 'ON('.self::where($on).')';
        return implode(' ',[$join,$table,$on]);
    }

    /**
     * 키 = 값 만들기
     * @param array 데이터  [ k => v, k => v, ...]
     * @param string 키값합치고 연결
     * @return string       k = ? , k = ? , k = ?
     */
    static function kv(array $data, string $kvglue = '=', string $arrglue = ', ')
    {
        $data = self::escape($data);
        $tmp = [];
        foreach ( $data as $k => $v )
        {
            // 키부분 . 있으면 포함 안하게 감싸기
            // 함수 쓰이면 불가능
            // $k = implode('.',array_map(function($v){
            //     return '`'.$v.'`';
            // },explode('.',$k)));

            if ( is_null ( $v ) || strtoupper($v) == 'DEFAULT' || strtoupper($v) == 'NULL' )
                $v = 'DEFAULT';
            $tmp[] = ' ' . $k . ' ' . $kvglue . ' \'' . $v . '\' ';
        }

        $res = implode($arrglue, $tmp);
        return $res;
    }


    /**
     * IN() 만들기
     * @param array 데이터 [v,v,v, ...]
     * @return string
     */
    static function in(array $data)
    {
        $data = self::escape($data);
        $res = ' IN(';
        $tmp = [];
        foreach ($data as $k => $v)
            $tmp[] = is_numeric ( $v ) && strpos($v,'0') != 0 ? $v : '\''.$v.'\'' ;
        $res .= implode(',', $tmp) . ')';
        return $res;
    }


    /**
     * NOT IN() 만들기
     * @param array [v,v,v ...]
     * @return string
     */
    static function notin(array $data)
    {
        $data = self::escape($data);
        return ' NOT ' . self::in($data);
    }


    /**
     * SET절 만들기
     * @param array 데이터
     * @return string SET절
     */
    static function set(array $data)
    {
        return ' SET ' . self::kv($data);
    }


    /**
     * WHERE 만들기
     * @param array 데이터
     * @param string 논리연산자 키값 사이
     * @param string 논리연산자 별개 요소 사이
     * @return string WHERE절
     */
    static function where(array $data, $o1 = '=', $o2 = 'AND' )
    {
        $data = self::escape($data);
        return ' WHERE ' . self::kv ( $data, $o1, ' ' . $o2 . ' ');
    }


    /**
     * SQL문을 보기좋게 출력한다
     * @param string SQL
     * @return string SQL
     */
    public static function pprint($sql, $enter = "\n", $space = 4)
    {
        $sql = preg_replace('/\s+/',' ',$sql);

        // 앞에 엔터
        $prefix_enter = array_map('strtolower',[
            'LIMIT','ORDER','HAVING',
            'WHERE','FROM','SET',
            'LEFT','RIGHT','INNER','JOIN',
            ')',
        ]);

        // 뒤에 엔터
        $affix_enter = array_map('strtolower',[
            'INSERT','UPDATE','SELECT','DELETE',
            'FROM','WHERE','SET',
            '(',
        ]);

        // 대문자로 바꿀것
        $upper = array_unique(array_merge($prefix_enter,$affix_enter,array_map('strtolower',[
            'LEFT','RIGHT','INNER',
            'AS','ON','ON(','AND','OR',
            'LIMIT','ORDER BY','HAVING',
            'BETWEEN',
        ])));

        $res = [];
        foreach( explode(' ', $sql) as $v )
        {
            // 쉼표 엔터 따로추가
            if ( is_int ( strpos($v, ',') ) )
                $v = implode(',' . $enter . str_repeat(' ', $space), explode(',', $v));
            // 키워드 대문자로 변경
            if ( in_array( $v, $upper) )
                $v = strtoupper( $v ) ;

            if (
                in_array(strtolower($v), $affix_enter)
                && in_array(strtolower($v), $prefix_enter)
            ) // 앞뒤 엔터 추가
                $res[] = $enter . $v . $enter . str_repeat(' ', $space);
            elseif ( in_array(strtolower($v), $prefix_enter) ) // 앞에 엔터 추가
                $res[] = $enter . $v ;
            elseif ( in_array(strtolower($v), $affix_enter) ) // 뒤에 엔터 추가
                $res[] = $v . $enter . str_repeat(' ', $space);
            else
                $res[] = $v;
        }
        return implode(' ', $res);
    }
}

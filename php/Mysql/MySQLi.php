<?php

namespace DB\Mysql;

use Config;

class Mysql
{
    protected static $config = [];

    /**
     * @param array 설정값
     */
	function __construct ( $config = [] )
	{
        if ( empty ( $config ) )
        {
            // $c = explode('\\',__CLASS__);
            $c = ['DB','Mysql'];
            $config = call_user_func_array(['Config','get'],$c);
        }

        self::$config = $config;
	}

	public function connect()
	{
        foreach (self::$config as $k => $v)
            $$k = $v;
            
        $conn = mysqli_connect($host, $user, $password, $db, $port);
        $conn->set_charset($encoding);
        
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

		if ($conn->connect_error) {
			die('데이터베이스 연결에 문제가 있습니다.\n관리자에게 문의 바랍니다.');
		} else {
			return $conn;
		}
	}


	/**
     * DB 연결을 가져온다 싱글톤
     * @return object 연결객체
     */
    public static function getDB()
    {
        static $dbconn;
        if ( empty( $dbconn ))
        {
            $class = __CLASS__;
            $db = new $class ( self::$config ) ;
            $dbconn = $db->connect();
        }
        return $dbconn;
    }


    /**
     * 쿼리를 실행하고 결과를 가져온다
	 * @param string sql문
	 * @return array|int|false 실행결과 SELECT => array, INSERT|UPDATE => int, 쿼리 실패시 false
	 */
	public static function query($sql)
    {
		$sql = trim($sql);

		$dbconn = self::getDB();
		$qry = mysqli_query($dbconn, $sql);
		if(!$qry)echo mysqli_error($dbconn);


		/**
		 * SELECT array 이스케이프된 데이터
		 * INSERT int last_inserted_id 입력된id pk
		 * UPDATE int affected_rows 업데이트된 컬럼수
		 */
        $res = false;
        if( strpos( strtolower($sql), 'insert') === 0)
			$res = $dbconn->insert_id;
        elseif( strpos( strtolower($sql), 'update') === 0)
            $res = $dbconn->affected_rows;
        elseif( strpos( strtolower($sql), 'delete') === 0)
            $res = $dbconn->affected_rows;
        else
        {
            $res = [];
            while($row = mysqli_fetch_assoc($qry))
                $res[] = array_map(function($v){
                    if ( ! empty ( $v ) )
                        return htmlentities($v);
                    else
                        return $v;
                },$row);
        }
            
        return $res;
	}


    /**
     * 쿼리를 실행하고 결과를 한개만 가져온다
     */
    public static function queryOne($sql)
    {
        $res = self::query($sql);
        if ( is_array ( $res ) )
            $res = array_pop($res);
        return $res;
    }


    /**
     * 트랜잭션시작
     * @param closure 실행 ㄱ
     * @param int mysqli_begin_transaction 옵션 그대로
     * @param string 세이브 포인트 이름
     */
    public static function transaction ( $closure , $name = 'mysql transaction' , $flag = 0 )
    {
        $dbconn = self::getDB();
        mysqli_autocommit($dbconn, false);
        /**
         * MYSQLI_TRANS_START_READ_ONLY
         * MYSQLI_TRANS_START_READ_WRITE
         * MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT
         */
        mysqli_begin_transaction($dbconn, $flag, $name);

        try {
            $closure($dbconn);
        } catch (\Throwable $th) {

            mysqli_rollback($dbconn);
            throw $th;
        }
        /**
         * 트랜잭션 끝
         * MYSQLI_TRANS_COR_AND_CHAIN: Appends "AND CHAIN" to mysqli_commit or mysqli_rollback.
         * MYSQLI_TRANS_COR_AND_NO_CHAIN: Appends "AND NO CHAIN" to mysqli_commit or mysqli_rollback.
         * MYSQLI_TRANS_COR_RELEASE: Appends "RELEASE" to mysqli_commit or mysqli_rollback.
         * MYSQLI_TRANS_COR_NO_RELEASE: Appends "NO RELEASE" to mysqli_commit or mysqli_rollback.
         */
        mysqli_commit($dbconn);
        
    }


    /**
     * 있는지 확인
     * 컬럼, 데이터 수가 맞아야됨
     * @param string 테이블
     * @param mixed 컬럼
     * @param mixed 데이터
     * @return boolean
     */
    public static function is($table, $column, $data)
    {
        // 이스케이프
        foreach (['table','column','data'] as $v)
        {
            $$v = self::escape($$v);
            if ( $v == 'table' )
                continue;
            // 형변환
            if ( ! is_array ( $$v ) )
                $$v = [$$v];
        }

        $sql = 'SELECT COUNT(*) > 0 FROM `' . $table . '` '. self::where(array_combine($column,$data));
        $res = self::queryOne($sql);
        return boolval(array_pop($res));
    }


    /**
     * 전달받은 데이터들을 이스케이프 한다
     * @param mixed 데이터
     * @return array 이스케이프된 데이터
     */
    public static function escape( $data )
    {
        $dbconn = self::getDB();
        if ( is_array ( $data ) )
        {
            $res = $data;
            array_walk_recursive($res,function(&$v)use ( $dbconn ){
                if ( is_numeric($v) )
                    $v = strpos($v, '.') === false
                        ? intval($v)
                        : floatval($v) ;
                // 10.5.11-MariaDB 기준
                // default 값 있는데 NULL 에러 뜰때
                // elseif ( is_null ( $v ) )
                //     $v = 'DEFAULT';
                else
                    if ( ! empty ( $v ) )
                        $v = mysqli_real_escape_string($dbconn,$v);
            });
        }
        else
        {
            if ( is_numeric($data) )
                $res = strpos($data, '.') === false
                    ? intval($data)
                    : floatval($data) ;
            elseif ( is_null ( $data ) )
                $res = 'DEFAULT';
            else
                $res = addslashes($data);
        }
            
        return $res;
    }
}

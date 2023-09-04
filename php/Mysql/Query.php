<?php
namespace DB\Mysql;

use PDO;
use exception;

class Query
{
	static protected ?PDO $DB ;
	function __construct ( ?array $config = array() )
	{
		if ( empty ( $config ) )
			return;
		/*
		$options = [
			\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
			\PDO::ATTR_EMULATE_PREPARES   => false,
		];
		*/
		if ( ! isset ( $config['option'] ) )
			$config['option'] = array ( PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC ) ;
		$this->connect($config);
	}

	static function getInstance()
	{
		if ( empty ( self::$DB ) )
			throw new PDOException('인스턴스가 없습니다');
		return new (__CLASS__);
	}

	function __destruct()
	{
		$this->close();
	}

	/*
	function __sleep()
	{
		var_dump(__FUNCTION__);
		$this->close();
	}
	function __wakeup()
	{
		var_dump(__FUNCTION__);
		$this->connect();
	}
	*/

	protected function connect ( array $config )
	{
		if (empty($config))
			throw new exception('에러');

		$info = 'mysql:';
		foreach ( array ( 'dbname' , 'host' , 'port' , 'charset' ) as $k => $v )
			if( isset($config[$v]))
				$info .= $v . '=' . $config[$v].';';

		$option = $config['option'] ?? array () ;
		self::$DB = new PDO ( $info, $config['user'], $config['password'] , $option );
	}

	protected function close ()
	{
		if ( isset (self::$DB) && ! empty(self::$DB) )
			self::$DB = NULL;
	}

    function explain ( string $sql , array $bind = array () )
    {
        $add = false ;
        foreach ( array ( 'explain' , 'desc' , 'describe' ) as $v )
            if ( ! is_int ( stripos ( $sql , $v ) ) )
                $add = true ;
        if ( $add )
            $sql = 'EXPLAIN ' . $sql ;
        return $this->query ( $sql , $bind ) ;
    }

    function query ( string $sql , array $bind = array () )
	{
		if ( empty ( $bind ) )
			return self::$DB->query($sql);

		$stmt = self::$DB->prepare($sql);
		if ( key ( $bind ) === 0 )
			foreach ( $bind as $k => $v )
				$stmt->bindValue ( $k+1, $v, is_null ( $v ) ? PDO::PARAM_NULL : PDO::PARAM_STR ) ;
		else
			foreach ( $bind as $k => $v )
				$stmt->bindValue ( ':'.$k, $v, is_null ( $v ) ? PDO::PARAM_NULL : PDO::PARAM_STR ) ;

		if ( ! $stmt->execute() )
			throw new PDOException('쿼리 실패');

		return $stmt;
	}

	function toArray ( string $sql , array $bind = array () )
	{
		return $this->query($sql , $bind)->fetchAll();
	}

    function lastInsertId()
    {
        return self::$DB->lastInsertId();
    }

	function fetch ( string $sql , array $bind = array () )
	{
		return $this->query($sql , $bind)->fetch();
	}

	// 성가신듯
	function select ( string $table , array $column , int $offset = 0 , int $limit = 10 , array $where = array() )
	{
		$sql = 'SELECT ' . implode ( ' , ' , $column )
			. ' FROM ' . $table;
		$bind = array();
		if ( ! empty ( $where ) )
		{
			$sql .= ' WHERE ' . implode ( ' = ? AND ' , array_keys ( $where ) ) ;
			foreach ( array_values ( $where ) as $v )
				array_push ( $bind , $v ) ;
		}
		$sql .= ' LIMIT ' . $offset.' , ' . $limit ;
		return $this->query ( $sql , $bind ) ;
	}

	static function in ( array $arr )
	{
		return implode ( ',' , array_map ( fn ( $v ) => '?' , $arr ) ) ;
	}

	// 프로시저나 변수없이도 했었는데
	function pivot()
	{

	}
}

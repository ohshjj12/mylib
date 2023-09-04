<?php

namespace Client;

/**
 * 세션 제어
 */
class Session
{
	function __construct(array $config = array())
	{
		foreach ($config as $k => $v)
			ini_set('session.'.$k, $v);
		$this->regen();
	}

	function start()
	{
		if ( session_status() != PHP_SESSION_ACTIVE )
			session_start();
	}

	/*
		session_status();
		PHP_SESSION_DISABLED	: 0
		PHP_SESSION_NONE		: 1
		PHP_SESSION_ACTIVE		: 2
	*/


/*	function __destruct()
	{
		$this->close();
	}*/

	function id()
	{
		return session_id();
	}

	protected function modify($closure)
	{
		$this->start();
		$closure();
		$this->close();
	}

	function destroy()
	{
		$this->modify(function(){
			session_destroy();
		});
	}

	function set ( string $key , $value )
	{
		$this->modify(function()use($key, $value){
			$_SESSION[$key] = $value;
		});
	}

	function unset ( string $key )
	{
		$this->modify(function()use($key){
			unset ( $_SESSION[$key] ) ;
		});
	}

	function regen()
	{
		$this->modify(function(){
			session_regenerate_id();
		});
	}

	function close()
	{
		return session_write_close();
	}
}

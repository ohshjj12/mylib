<?php

namespace CLI;

/**
 * 참고한 원본
 * https://gist.github.com/nicksantamaria/21dce5ff2a6640cdff76ce7bc57d2981
 * 멀티프로세스 실행하기
 */
class Process
{
	/**
	 * $work array 실행할 목록들 closure 담기
	 * $wait bool 프로세스 기다리기 false 사용시 좀비프로세스 생길수 있음
	 * $interval int 작업 시작 인터벌 usleep
	 */
	public static function run ( $work , $wait = true , $interval = 10000  )
	{
		if ( php_sapi_name() != 'cli' )
			throw new Exception("CLI 환경에서만 사용 가능합니다.", 1);
		foreach($work as $v)
		{
			$pid = pcntl_fork();
			if($pid == -1)
				exit('FORK ERROR');
			elseif ( $pid == 0 )
			{
				// 실행한 파일의 자식프로세스
				// 여기에 pcntl_wait* 넣으면 ppid 1되면서 중지못함
				$v($pid);
				exit;
			}
			/*
			else
			{
				// 지금 메서드에서는 이부분 있으면 작동안됨
				// elseif의 프로세스의 자식프로세스
				exit;
			}
			*/
			usleep($interval);
		}

		// 자식 프로세스 끝나면 죽이기
		$waitpid = pcntl_waitpid(0, $status);

		// 모든 자식 프로세스 끝날때까지 대기
		if ( $wait )
			while (pcntl_waitpid(0, $status) != -1);
	}
}

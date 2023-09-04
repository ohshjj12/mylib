<?php

namespace DB\Mysql;

use DB\Mysql\Mysql;


/**
 * 의존성 얻기
 * https://marock.tokyo/en/2021/06/23/how-to-obtain-mysql-views-dependency-the-table-you-are-using/
 * 
 * 쿼리문 안에 테이블 이름 들어가는지 확인하는거로 출력함
 * 
 * 뷰 이외에는 각각의 의존성도 있음?????
 */


class Dependency extends Mysql
{
    /**
     * @param string DB 이름
     * @param string 뷰 이름
     */
    static function view ( $db = '' , $name = '' )
    {
        if ( empty ( $db ) )
            $db = 'DATABASE()';
        else
            $db = '\''.$db.'\'';
        if ( ! empty ( $name ) )
            $name = ' AND V.TABLE_NAME = \'' . $name.'\'';
        // view의 table,view 의존성
        $sql = 'WITH RECURSIVE VIEW_REC AS (
            SELECT V.TABLE_SCHEMA SCHEMA_NAME
                 , 1 AS HIERARCHY
                 , V.TABLE_NAME AS VIEW_NAME
                 , T.TABLE_NAME AS TABLE_NAME
                 , T.TABLE_TYPE AS TYPE
              FROM INFORMATION_SCHEMA.TABLES AS T
             INNER JOIN INFORMATION_SCHEMA.VIEWS AS V 
                ON V.TABLE_SCHEMA = T.TABLE_SCHEMA
               AND V.VIEW_DEFINITION LIKE CONCAT(\'%\',T.TABLE_NAME,\'%\')
             WHERE T.TABLE_SCHEMA = '.$db . ' ' . $name . '
            UNION ALL
            SELECT V.TABLE_SCHEMA SCHEMA_NAME
                 , HIERARCHY + 1 AS HIERARCHY
                 , V.TABLE_NAME AS VIEW_NAME
                 , T.TABLE_NAME AS TABLE_NAME
                 , T.TABLE_TYPE AS TYPE
              FROM INFORMATION_SCHEMA.TABLES AS T 
             INNER JOIN INFORMATION_SCHEMA.VIEWS AS V 
                ON V.TABLE_SCHEMA = T.TABLE_SCHEMA
               AND V.VIEW_DEFINITION LIKE CONCAT(\'%\',T.TABLE_NAME,\'%\')
             INNER JOIN VIEW_REC AS R
                ON V.TABLE_SCHEMA = R.SCHEMA_NAME
                AND V.TABLE_NAME = R.TABLE_NAME
            )
            SELECT * FROM VIEW_REC
            ORDER BY HIERARCHY
                   , VIEW_NAME
            ';
        return Mysql::query($sql);
    }


    static function event()
    {
        // event의 table, event 의존성 표시
        $sql = 'WITH RECURSIVE EVENT_REC AS (
            SELECT 
                E.EVENT_SCHEMA SCHEMA_NAME, 
                1 HIERARCHY, 
                E.EVENT_NAME EVENT_NAME, 
                T.TABLE_NAME TABLE_NAME, 
                T.TABLE_TYPE TYPE 
            FROM INFORMATION_SCHEMA.TABLES T 
            INNER JOIN INFORMATION_SCHEMA.EVENTS E 
                ON E.EVENT_SCHEMA = T.TABLE_SCHEMA 
                AND E.EVENT_DEFINITION LIKE CONCAT(\'%\', T.TABLE_NAME, \'%\') 
            WHERE 
                T.TABLE_SCHEMA = DATABASE() 
            UNION ALL 
            SELECT 
                E.EVENT_SCHEMA SCHEMA_NAME, 
                HIERARCHY + 1 AS HIERARCHY, 
                E.EVENT_NAME EVENT_NAME, 
                T.TABLE_NAME TABLE_NAME, 
                T.TABLE_TYPE TYPE 
            FROM INFORMATION_SCHEMA.TABLES T 
            INNER JOIN INFORMATION_SCHEMA.EVENTS E 
                ON E.EVENT_SCHEMA = T.TABLE_SCHEMA 
                AND E.EVENT_DEFINITION LIKE CONCAT(\'%\', T.TABLE_NAME, \'%\')
            INNER JOIN EVENT_REC R 
                ON E.EVENT_SCHEMA = R.SCHEMA_NAME 
                AND E.EVENT_NAME = R.TABLE_NAME
        )
        SELECT 
            * 
        FROM 
            EVENT_REC 
        ORDER BY 
            HIERARCHY, 
            EVENT_NAME
        ';
        return Mysql::query($sql);
    }


    /**
     * @param string DB 이름
     * @param string 이벤트 걸린 테이블 이름
     */
    static function trigger( $db = '' , $name = '' )
    {
        if ( empty ( $db ) )
            $db = 'DATABASE()';
        else
            $db = '\''.$db.'\'';
        if ( ! empty ( $name ) )
            $name = ' AND TR.EVENT_OBJECT_TABLE = \'' . $name.'\'';

        // 트리거
        $sql = 'WITH RECURSIVE TRIGGER_REC AS (
            SELECT 
                TR.TRIGGER_SCHEMA AS SCHEMA_NAME, 
                1 AS HIERARCHY, 
                TR.TRIGGER_NAME AS TRIGGER_NAME, 
                TR.EVENT_OBJECT_TABLE, 
                T.TABLE_NAME AS TABLE_NAME, 
                T.TABLE_TYPE AS TYPE 
            FROM INFORMATION_SCHEMA.TABLES AS T 
            INNER JOIN INFORMATION_SCHEMA.TRIGGERS AS TR
                ON TR.TRIGGER_SCHEMA = T.TABLE_SCHEMA 
                AND TR.ACTION_STATEMENT LIKE CONCAT(\'%\', T.TABLE_NAME, \'%\')
            WHERE T.TABLE_SCHEMA = '.$db . ' ' . $name . '
            UNION ALL 
            SELECT 
                TR.TRIGGER_SCHEMA AS SCHEMA_NAME, 
                HIERARCHY + 1 AS HIERARCHY, 
                TR.TRIGGER_NAME AS TRIGGER_NAME, 
                TR.EVENT_OBJECT_TABLE, 
                T.TABLE_NAME AS TABLE_NAME, 
                T.TABLE_TYPE AS TYPE 
            FROM INFORMATION_SCHEMA.TABLES AS T 
            INNER JOIN INFORMATION_SCHEMA.TRIGGERS AS TR 
                ON TR.TRIGGER_SCHEMA = T.TABLE_SCHEMA 
                AND TR.ACTION_STATEMENT LIKE CONCAT(\'%\', T.TABLE_NAME, \'%\') 
            INNER JOIN TRIGGER_REC AS R
                ON TR.TRIGGER_SCHEMA = R.SCHEMA_NAME
                AND TR.TRIGGER_NAME = R.TABLE_NAME
        ) 
        SELECT 
            * 
        FROM 
            TRIGGER_REC 
        ORDER BY 
            HIERARCHY, 
            TRIGGER_NAME
        ';
        return Mysql::query($sql);
    }


    static function procedure()
    {
        // 루틴|프로시저
        $sql = 'WITH RECURSIVE ROUTINE_REC AS (
            SELECT RT.ROUTINE_SCHEMA SCHEMA_NAME
                , 1 HIERARCHY
                , RT.ROUTINE_NAME 
                , T.TABLE_NAME TABLE_NAME
                , T.TABLE_TYPE TYPE
            FROM INFORMATION_SCHEMA.TABLES T 
            INNER JOIN INFORMATION_SCHEMA.ROUTINES RT
                ON RT.TABLE_SCHEMA = T.TABLE_SCHEMA
                AND RT.ROUTINE_DEFINITION LIKE CONCAT(\'%\',T.TABLE_NAME,\'%\')
            WHERE T.TABLE_SCHEMA = DATABASE()
            UNION ALL
            SELECT RT.ROUTINE_SCHEMA SCHEMA_NAME
                , HIERARCHY + 1 AS HIERARCHY
                , RT.ROUTINE_NAME 
                , T.TABLE_NAME TABLE_NAME
                , T.TABLE_TYPE TYPE
            FROM INFORMATION_SCHEMA.TABLES T 
            INNER JOIN INFORMATION_SCHEMA.ROUTINES RT
                ON RT.TABLE_SCHEMA = T.TABLE_SCHEMA
                AND RT.ROUTINE_DEFINITION LIKE CONCAT(\'%\',T.TABLE_NAME,\'%\')
            INNER JOIN ROUTINE_REC R
                ON RT.TABLE_SCHEMA = R.SCHEMA_NAME
                AND RT.ROUTINE_NAME = R.TABLE_NAME
            )
            SELECT * FROM ROUTINE_REC
            ORDER BY HIERARCHY
                , ROUTINE_NAME
        ';
        return Mysql::query($sql);
    }
}

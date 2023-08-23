'use strict';

// http://yoonbumtae.com/?p=3584


/**
 * 이벤트 반복 실행때 반복 안되면 콜백 실행
 * @param callback func 함수
 * @param int limit 밀리세컨드
 * @returns 
 */
function debounce ( func , limit = 100 )
{
    let timeout;
    return (... args)=>{
        clearTimeout(timeout);
        timeout = setTimeout(()=>{
            func.apply(this,args);
        },limit);
    };
}

/**
 * 이벤트 반복할때 일정 시간 간격으로 실행되게끔
 * @param {*} func 
 * @param {*} limit 
 */
function throttle ( func , limit = 100 )
{
    let w = false;
    return (... args)=>{
        if ( w )
            return;
        func.apply(this,args);
        w = true;
        setTimeout(()=>{
            w = false;
        },limit);
    };

}

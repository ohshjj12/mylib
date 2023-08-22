/**
 * 날짜 포맷 맞추기
 * php date 처럼 사용하게끔
 * @param string|int|Date 날짜
 * @param string 포맷
 */
function date_format(date = new Date(), format = 'Y-m-d H:i:s')
{
    // 숫자일때
    if ( ! isNaN ( date ) )
        date = new Date(Number(date));
    else if (typeof date == 'string')
        date = new Date(date);
    let formating = {
        Y : date.getFullYear(),
        m : date.getMonth() + 1,
        d : date.getDate(),
        H : date.getHours(),
        i : date.getMinutes(),
        s : date.getSeconds()
    }

    for (const key in formating)
    {
        let v = formating[key];
        if ( v < 10 )
            v = "0" + String(v)
        format = format.replace(key, v);
    }
    return format;

}

/**
 * @see 타임존, 로케일 안맞으면 오차 있음
 * @see 포맷 현재 d만 가능
 * 시작, 끝 시간 차이 구하고 f 단위만큼 배열에 반환
 * 파라미터에 속하는 날짜도 포함
 * start, end 둘다 있는데 작은쪽이 시작, 큰쪽이 끝
 * @param string|int|Date 날짜
 * @param string|int|Date 날짜
 * @param string 포맷
 */
function date_range(start,end, format = 'd')
{
    // 포맷타입 밀리초
    let t = {
        // 윤년 계산해야함
        // Y: '',
        // m: '',
        d: 86400000,
        h: 3600000,
        i: 60000,
        s: 1000,
    };
    if ( ! t[format] )
        throw '없는 포맷'

    format = t[format];

    if ( ! isNaN ( start ) )
        start = new Date(Number(start));
    else if (typeof start == 'string')
        start = new Date(start);

    if ( ! isNaN ( end ) )
        end = new Date(Number(end));
    else if (typeof end == 'string')
        end = new Date(end);

    start = Math.min(start.valueOf(),end.valueOf());
    end = Math.max(start.valueOf(),end.valueOf());

    let diff = end - start;
    diff = diff / format;

    let res = [];
    for ( let i = diff + 1 ; i > 0; i -- )
        res.push(new Date(start + (i * format)))

    return res;
}


/**
 * 두 날짜의 차이를 구한다
 * @param string|int|Date 날짜
 * @param string|int|Date 날짜
 * @param string 포맷
 */
function date_diff ( one, two , format = 'd')
{
    // 포맷타입 밀리초
    let t = {
        // 윤년 계산해야함
        // Y: '',
        // m: '',
        d: 86400000,
        h: 3600000,
        i: 60000,
        s: 1000,
    };
    if ( ! t[format] )
        throw '없는 포맷'

    if ( ! isNaN ( one ) )
        one = new Date(Number(one));
    else if (typeof one == 'string')
        one = new Date(one);

    if ( ! isNaN ( two ) )
        two = new Date(Number(two));
    else if (typeof two == 'string')
        two = new Date(two);

    format = t[format];

    let diff = one.getTime() - two.getTime();
    diff = diff / format;
    return diff;
}

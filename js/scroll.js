/**
 * 스크롤 이벤트
 * deltaX, deltaY 는 mousewheel 이벤트만 가능
 */
const scroll = {
    /**
     * http://yoonbumtae.com/?p=3584
     * @see throttle 과 다른점을 모르겠음
     * ms 마다 한번씩만 실행되게 해줌
     * @param {*} func 실행할 함수
     * @param {*} ms 실행제한 시간
     * @returns 이벤트에 걸 함수
     */
    _debounce: function(func, ms = 200){
        let timeout;
        return (... args)=>{
            clearTimeout(timeout);
            timeout = setTimeout(()=>{
                func.apply(this,args);
            },ms);
        };
    },

    /**
     * ms 마다 한번씩만 실행되게 해줌
     * @param {*} func 실행할 함수
     * @param {*} ms 실행제한 시간
     * @returns 이벤트에 걸 함수
     */
    _throttle: function(func, ms = 200){
        let w = false;
        return (... args)=>{
            if ( w )
                return;
            func.apply(this,args);
            w = true;
            setTimeout(()=>{
                w = false;
            },ms);
        };
    },

    /**
     * [Element] 로 가공
     * @param {string|element|NodeList|Jquery} selector
     * @returns {[Element]}
     */
    _check: function(selector){
        let target;

        // element 인지 확인
        if ( /^HTML[a-zA-Z]*(Element|Document)$/.test(selector.constructor.name) )
            target = [selector];
        // 문자인지 확인
        else if ( typeof selector == 'string' )
            target = document.querySelectorAll(selector);
        // iterator인지 확인
        else if ( typeof selector[Symbol.iterator] === 'function' )
            target = selector;
        else
            throw '처리할 수 없는 타입'
        // if ( target.length == 0 )
        //     return console.warn('scroll.row 타겟이 없습니다');
        return target;
    },

    /**
     * 이벤트나 함수 실행
     * @param {CustomEvent|Function|null} func
     */
    _dispatch: function(func, e){
        if ( ! func )
            return;
        if ( func.constructor.name == 'Function' )
            func(e);
        else if ( func.constructor.name == 'CustomEvent' )
            i.dispatchEvent(func);
    },
    /**
     * 가로 스크롤 마우스휠 활성화
     * @param {string|HTMLElement|NodeList|Jquery} selector
     * @param {CustomEvent|Function|null} func
     * @returns
     */
    row: function(selector, func){

        // 확인, 가공
        let target = this._check(selector);
        for (const i of target)
        {
            i.addEventListener('mousewheel',this._debounce(function(e){
                e.preventDefault();
                this.scrollLeft += e.deltaY > 0 ? 100 : -100 ;
                scroll._dispatch(func, e);
            }));
        }
    },

    /**
     * 스크롤 밑쪽에 있을때
     * @param {*} selector
     * @param {*} func
     * @param {*} per
     * @returns
     */
    down: function( selector, func, ms = 200, per = 0.5 ){
        // 확인, 가공
        let target = this._check(selector);

        for (const i of target)
        {
            i.addEventListener('scroll', this._debounce((e)=>{
                let len = (i.scrollHeight - i.offsetHeight) 
                    || document.documentElement.scrollHeight - window.innerHeight;
                let top = i.scrollTop || window.scrollY ;
                // 이거 값 안바뀜 || document.documentElement.scrollTop

                if ( top / len > per )
                    scroll._dispatch(func, e);
            }, ms));
        }
    },

    /**
     * 스크롤 위쪽에 있을때
     * @fix 스크롤 down이랑 같은 판별이라 확인필요
     * @param {*} selector
     * @param {*} func
     * @param {*} per
     * @returns
     */
    up: function( selector, func, ms = 200, per = 0.5 ){
        // 확인, 가공
        let target = this._check(selector);

        for (const i of target)
        {
            i.addEventListener('scroll',this._debounce((e)=>{
                let len = (i.scrollHeight - i.offsetHeight) 
                    || document.documentElement.scrollHeight - window.innerHeight;
                let top = i.scrollTop || window.scrollY;

                if ( top / len > per )
                    scroll._dispatch(func, e);
            }, ms));
        }

    },


    /**
     * 스크롤 왼쪽에 있을때
     * @param {*} selector
     * @param {*} func
     * @param {*} per
     * @returns
     */
    left: function( selector, func, ms = 200 , per = 0.5 ){
        // 확인, 가공
        let target = this._check(selector);

        for (const i of target)
        {
            i.addEventListener('scroll',this._debounce((e)=>{
                let len = (i.scrollWidth - i.offsetWidth)
                    || document.documentElement.scrollWidth - window.innerHeight;
                let left = i.scrollLeft || document.documentElement.scrollLeft;

                if ( left / len < per )
                    scroll._dispatch(func, e);
            }, ms));
        }

    },


    /**
     * 스크롤 오른쪽에 있을때
     * @param {*} selector
     * @param {*} func
     * @param {*} per
     * @returns
     */
    right: function( selector, func, ms = 200 , per = 0.5 ){
        // 확인, 가공
        let target = this._check(selector);

        for (const i of target)
        {
            i.addEventListener('scroll',this._debounce((e)=>{
                let len = (i.scrollWidth - i.offsetWidth)
                    || document.documentElement.scrollWidth - window.innerHeight;
                let left = i.scrollLeft || document.documentElement.scrollLeft;

                if ( left / len > per )
                    scroll._dispatch(func, e);
            }, ms));
        }

    },
};

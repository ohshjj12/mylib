'use strict';

let Validate = {
    /**
     * 입력받은 데이터를 입력받은 rule대로 체크한다.
     * @param mixed 입력받은 데이터
     * @param array 데이터 처리 조건
     * @param array 조건에 맞지않을 때 보낼 메시지
     * @return null|string 에러 있으면 응답
     */
    recursive:function(data, rule, resp, parent = '')
    {
        let res;
        for(const property in rule)
        {
            // 체크 조건
            // 데이터가 빈값이고 default가 있다면 데이터에 default값 설정
            if(data[property] == '' && Object.keys(rule[property]).includes('default')){
                data[property] = rule[property]['default'];
            }
            // 데이터가 빈값이고 optional가 있다면 continue 처리
            if(data[property] == '' && Object.keys(rule[property]).includes('optional')){
                continue;
            }

            if ( typeof data[property] == 'array' )
            {
                res = recursive(data[property], rule, resp);
                if(res && Object.keys(res).length > 0) return res;
            }
            else if(typeof rule[property] == 'object'){
                res = this['recursive'](data[parent], rule[property], resp[property], property)
                if(res && Object.keys(res).length > 0) return res;
            }
            // 자식 노드가 오브젝트일시 재귀
            else if(data[property] != undefined && data[property] != null && typeof data[property] == 'object' ){
                res = this['recursive'](data[property], rule[property], resp[property], property)
                if(res && Object.keys(res).length > 0) return res;
            }
            // 최하위 노드의 데이터일시 해당 체크(property)함수에 데이터(data), 조건(rule[property]) 던지기
            else if(property != 'default' && property != 'optional' && !this[property](data, rule[property])){
                return {
                    elem : parent,
                    msg : resp['msg']};
            }
        }
        return res;
    },


    /**
     * 날짜형식인지 확인한다
     * @param {String} data 검사할 데이터
     * @param {String} format php처럼
     * @returns {Boolean}
     */
    date: function(data, format){
        // data가 숫자일때(숫자는 날짜형식으로 바꿀수 있음)
        if(Number.isInteger(data)) return false;

        // data가 Date객체일 때
        if(data.constructor.name == "Date") return false;

        let date = new Date(data);

        let formating = {
            Y : date.getFullYear(),
            m : date.getMonth() + 1,
            d : date.getDate(),
            H : date.getHours(),
            i : date.getMinutes(),
            s : date.getSeconds()
        }

        for(const key in formating){
            let v = formating[key];
            if(v < 10)
                v = "0" + String(v)
            format = format.replace(key, v)
        }

        return format === data;
    },


    /**
     * 최소길이 넘는지 확인한다
     * @param {String} data 검사할 데이터
     * @param {Number} min 최소길이
     * @returns {Boolean}
     */
    minlength: function(data,min)
    {
        return String(data).length >= min;
    },

    /**
     * 최대길이 넘는지 확인한다
     * @param {String} data 검사할 데이터
     * @param {Number} max 최대길이
     * @returns
     */
    maxlength: function(data,max)
    {
        return String(data).length <= max;
    },

    /**
     * 최소값 넘는지 확인한다
     * @param {String} data 검사할 데이터
     * @param {Number} min 최소값
     * @returns {Boolean}
     */
    min: function(data,min)
    {
        if ( isNaN(min) )
            throw 'min 설정은 숫자만 가능';
        if ( isNaN(data) )
            return false;
        return data >= min;
    },

    /**
     * 최대값 넘는지 확인한다
     * @param {String} data 검사할 데이터
     * @param {Number} max 최대값
     * @returns {Boolean}
     */
    max: function(data,max)
    {
        if ( isNaN(max) )
            throw 'max 설정은 숫자만 가능';
        if ( isNaN(data) )
            return false;
        return data <= max;
    },

    /**
     * 정규식에 맞는지 확인
     * @param {String} data 검사할 데이터
     * @param {String|RegExp} regex 정규식
     * @returns {Boolean}
     */
    regex: function(data,regex)
    {
        let r = new RegExp(regex);
        return r.test(data);
    },

    /**
     * 정규식에 맞는지 확인
     * @param {String} data 검사할 데이터
     * @param {String|RegExp} regex 정규식
     * @returns {Boolean}
     */
    not_regex: function(data,regex)
    {
        return ! this.regex(data,regex);
    },

    /**
     * 정수인지 확인
     * @param {String} data 검사할 데이터
     * @returns {Boolean}
     */
    int: function(data){
        return ! isNaN(data) && Number.isInteger(Number(data));
    },

    /**
     * 숫자인지 확인 (정수, 소수점)
     * @param {String} data 검사할 데이터
     * @returns {Boolean}
     */
    numeric: function (data)
    {
        return ! isNaN(data);
    },

    /**
     * 항목에 있는 값인지 확인한다
     * @param {String} data 검사할 데이터
     * @param {Array} arr 항목
     * @returns {Boolean}
     */
    in: function(data,arr)
    {
        return ! ( arr.indexOf(data) < 0 );
    },

    /**
     * 항목에 있는 값인지 확인한다
     * @param {String} data 검사할 데이터
     * @param {Array} arr 항목
     * @returns {Boolean}
     */
    notin: function(data,arr)
    {
        return ( arr.indexOf(data) < 0 );
    },

    /**
     * 모든 정규식이 맞을때 true 이외 false
     * @param mixed 검사할 값
     * @param array<regex> 정규식 목록
     * @return boolean
     */
    regex_arr: function(data, arr)
    {
        for (const e of arr)
            if(!e.test(data)) return false;
        return true;
    },

    /**
     * 하나라도 true면 false
     * @param mixed 검사할 값
     * @param array<regex> 정규식목록
     * @return boolean
     */
    not_regex_arr: function(data, arr)
    {
        for (const e of arr)
            if(e.test(data)) return false;
        return true;
    },

    quotient_int: function(data, d){
        return this.numeric(data) && this.int(data / d);
    }
};



/**
 * @fix 아래 주석인 코드 정리하기 ㄱㄱㄱ
 * @fix 일단 1차원 가능하게 나중에 다차원 ㄱㄱ
 * @see 확인필요 name[] 일때 확인 안해봤음
 * @param {*} rule
 * @param {*} message
 * @param {*} act 에러 발생시 처리할 콜백 함수
 */
HTMLFormElement.prototype.validate = function(rule,response,act){
    return true;
    // 실제 입력 값 데이터
    let formdata = {};
    $(this).serializeArray().forEach(e=>{
        formdata[e.name] = e.value;
    });

    // validate 처리
    // res >> {에러난 element name : 에러 메시지}
    let res;
    for (const k in rule)
    {
        res = Validate.recursive(formdata, rule[k], response[k]);
        // 에러 발생 시 처리
        if ( typeof res != 'undefined' )
        {
            act($(`[name="${res['elem']}"`), res['msg']);
            return false;
        }
    }
    return true;
};


/**
 * @fix 나중에 수정
 * 실행만 제어할지 메세지까지 출력할지
 *
 * @param {*} rule
 * @param {*} message
 * @returns
 */
HTMLFormElement.prototype.throttle = function(rule,message){


    return true;

    let a = function(func, ms = 200){
        let timeout;
        return (... args)=>{
            clearTimeout(timeout);
            timeout = setTimeout(()=>{
                func.apply(this,args);
            },ms);
        };
    }
    a(function (){

    })
    let data = new FormData(this);

    Object.entries(rule).forEach(v=>{
        // console.log(v[0])
    });
};

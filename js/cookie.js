
/**
 * 쿠키
 */
function Cookie()
{
    Object.defineProperties(this, {
        data: {
            /**
             * 문자로된 값을 오브젝트로 만든다
             * @returns {} assoc array
             */
             "get": function() {
                let data = document.cookie.split(';');
                let res = {};
                for (let i of data)
                {
                    i = i.trim().split('=');
                    res[i[0]] = i[1];
                }
                return res;
             },
        }
    });
}

Cookie.prototype = {
    /**
     * 키에 값을 세팅한다
     * @param {string} k 키
     * @param {string} v 값
     * @param {int} sec 초
     */
    set: function(k,v, sec = 0){
        v = escape(v);
        let now = new Date();
        now.setSeconds(now.getSeconds() + sec);
        let expire = sec < 1 ? '' : 'expires=' + now.toGMTString();
        document.cookie = `${k}=${v}; ${expire}`
    },

    /**
     * 키에 대한 값을 얻는다
     * @param {string} k 키
     * @returns {?string} 값
     */
    get: function(k = null){
        return decodeURIComponent(this.data[k]) ?? null;
    },
};

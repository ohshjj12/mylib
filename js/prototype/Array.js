'use strict';

/**
 * 큰 수 부터 나열 sort 거꾸로
 * @returns {Array}
 */
Array.prototype.descend = function(){
    return this.sort(function(a,b){return b-a;});
};

/**
 * https://stackoverflow.com/questions/1187518/how-to-get-the-difference-between-two-arrays-in-javascript
 * 공통된 요소만 반환한다
 * @returns {Array}
 */
Array.prototype.intersection = function(){
    let res = this;
    Object.entries(arguments).forEach(arr => {
        res = arr[1].filter(x => res.includes(x));
    });
    return res;
};

/**
 * https://stackoverflow.com/questions/1187518/how-to-get-the-difference-between-two-arrays-in-javascript
 * 자신만 가지고있는 요소를 반환한다
 * @returns {Array}
 */
Array.prototype.diff = function(){
    let res = this;
    Object.entries(arguments).forEach(arr => {
        res = res.filter(x => !arr[1].includes(x));
    });
    return res;
};

/**
 * https://stackoverflow.com/questions/1187518/how-to-get-the-difference-between-two-arrays-in-javascript
 * 공통된것 이외 모든 요소를 합쳐서 반환한다
 * @returns {Array}
 */
Array.prototype.symmetric = function(){
    let res = this;
    Object.entries(arguments).forEach(arr => {
        res = res.filter(
            x => !arr[1].includes(x)
        ).concat(
            arr[1].filter(
                x => !res.includes(x)
            )
        );        
    });
    return res;
};

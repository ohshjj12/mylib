/**
 * 테이블의 숫자 타입이 있는 경우 합계를 구하고 tfoot에 추가
 * @author jinju
 * @email ohshjj12@naver.com
 * @version 1.0
 */
HTMLTableElement.prototype.sum = function(){

    // table의 tbody 선택   
    let tbody = this.querySelector('tbody')

    // tbody 안에 tr 전체
    let tr = tbody.querySelectorAll('tr');
 
    // 담아둘 값
    let arr = [];
    for (const row of tr) {
        // tr에 세로 td 전체 합계
        let r = row.querySelectorAll('td')
        for (let i = 0; i < r.length; i++) {
            const element = r[i];
            if(arr[i] == undefined) 
                arr[i] = Number(element.innerHTML)
            else {
                arr[i] += Number(element.innerHTML)
            }
        }
    }

    // arr의 루프돌릴 때
    arr = arr.map(function(v){
        // NaN 숫자타입이 아닐 때 빈칸 처리
        if(isNaN(v))
            return `<td></td>`
        // 부동소수점때문에 반올림 처리 
        return `<td>${Math.round(v*10)/10}</td>`
    })

    // tfoot에 합쳐서 넣기
    let tfoot =  `
    <tfoot>
        <tr>
            <td align="right" colspan="${arr.length}">합계</td>
        </tr>
        <tr>
            ${arr.join('')}
        </tr>
    </tfoot>`.toElement()

    for (let f of tfoot) {
         this.append(f);
    }

}


/**
 * 테이블의 숫자 타입이 있는 경우 평균을 구하고 tfoot에 추가
 * 
 */
HTMLTableElement.prototype.avg = function(){

    // table의 tbody 선택   
    let tbody = this.querySelector('tbody')

    // tbody 안에 tr 전체
    let tr = tbody.querySelectorAll('tr');
 
    // 담아둘 값
    let arr = [];
    for (const row of tr) {
        // tr에 세로 td 전체 합계
        let r = row.querySelectorAll('td')
        for (let i = 0; i < r.length; i++) {
            const element = r[i];
            if(arr[i] == undefined) 
                arr[i] = Number(element.innerHTML)
            else {
                arr[i] += Number(element.innerHTML)
                
            }
        }
    }

console.log(arr)

    // arr의 루프돌릴 때
    arr = arr.map(function(v){
        // NaN 숫자타입이 아닐 때 빈칸 처리
        if(isNaN(v))
            return `<td></td>`
        // 부동소수점때문에 반올림 처리 
        return `<td>${Math.round(v/tr.length*10)/10}</td>`
    })

    // tfoot에 합쳐서 넣기
    let tfoot =  `
    <tfoot>
        <tr>
            <td align="right" colspan="${arr.length}">평균</td>
        </tr>
        <tr>
            ${arr.join('')}
        </tr>
    </tfoot>`.toElement()

    for (let f of tfoot) {
         this.append(f);
    }

}

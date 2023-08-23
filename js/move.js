/**
 * 목록 내에서 순서바꾼다
 * this 한단계 밑에만 드래그 할 수 있음
 * @param {callback} func 추가적으로 실행되는 함수
 */

HTMLElement.prototype.moveorder = function (func = {}) {
    //드래그하는 엘리먼트
    let target;

    //droppable="true" draggable="true" 기본적으로 세팅하기
    this.setAttribute("droppable", true);

    for (const i of this.children) {
        i.setAttribute("draggable", true);

        //드래그 시작한 엘리먼트 담기
        i.addEventListener("dragstart", function (e) {
            target = this;
            if (func.start) func.start(e, this);
        });

        //드롭할 때 끼워넣기
        i.addEventListener("drop", function (e) {
            if (this.offsetHeight / 2 < e.offsetY) {
                this.after(target);
            } else {
                this.before(target);
            }

            if (func.drop) func.drop(e, this);
        });
    }

    //드롭이 실행되려면 이벤트 막아야함
    this.addEventListener("dragover", function (e) {
        e.preventDefault();
        if (func.over) func.over(e, this);
    });
};

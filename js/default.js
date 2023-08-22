/*
 * 업로드할 img 미리보기
 * @param {} input 업로드 input태그
 *           label 파일 이름 보여질 태그
 *           preview 미리보기 보여질 태그
 *           event 실행될 이벤트(change)
 */
function imgpreview(arr) {
    // 기본값 세팅
    if (arr.event.length == 0) arr.event = "change";

    // event
    $(arr.input)[arr.event](function (e) {
        let file = this.files[0];

        // 있을때만 파일이름 표시
        if (arr.label.length > 0) $(arr.label).text(file["name"]);

        // 미리보기
        var reader = new FileReader();
        reader.onload = function (e) {
            $(arr.preview).html("");

            let img = `
                <img src="${e.target.result}"
                    style="height: auto; width: 100px;">
            `;

            $(arr.preview).append(img);
        };
        reader.readAsDataURL(e.target.files[0]);
    });
}

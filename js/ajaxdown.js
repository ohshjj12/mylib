/**
 * ajax 다운로드
 */
(function (factory) {
    if (typeof define === "function" && define.amd) {
        // AMD. Register as anonymous module.
        define(["jquery"], factory);
    } else if (typeof exports === "object") {
        // Node / CommonJS
        factory(require("jquery"));
    } else {
        // Browser globals.
        factory(jQuery);
    }
})(function ($) {
    $.ajaxdownload = function (option) {
        let args = Object.assign(
            {},
            {
                method: "get",
                contentType: "application/x-www-form-urlencoded;charset=UTF-8",
                // CSRF
                beforeSubmit: function (a, b, c) {},
                xhr: function () {
                    let xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState > 2) return;
                        //response 데이터를 바이너리로 처리한다. 세팅하지 않으면 default가 text
                        xhr.responseType = "blob";
                    };
                    return xhr;
                },
                success: function (data, message, xhr) {
                    if (xhr.readyState != 4 || xhr.status != 200)
                        return toast.show(
                            "에러",
                            "다운로드에 실패했습니다 httpcode" + xhr.status
                        );

                    // 성공
                    let disposition = xhr.getResponseHeader(
                        "Content-Disposition"
                    );
                    let filename;

                    if (
                        !disposition ||
                        disposition.indexOf("attachment") === -1
                    )
                        return toast.show("에러", "다운로드에 실패했습니다");
                    let filenameRegex =
                        /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    let matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1])
                        filename = matches[1].replace(/['"]/g, "");
                    let blob = new Blob([data]);
                    let link = document.createElement("a");
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    link.click();
                },
            },
            option
        );

        $.ajax(args);
    };
});

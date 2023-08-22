 $(document).ready(() => {
        /**
         * 토스트, 툴팁 세팅
         */
        toast = new Toast();
        tt = new Tooltip();

        // 팝오버 세팅
        let popover = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="popover"]')
        );
        popover.map((v) => {
            return new bootstrap.Popover(v);
        });

        // 툴팁 세팅
        let tooltip = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );
        tooltip.map((v) => {
            if (v.tagName != "BUTTON") $(v).css("cursor", "help");
            return new bootstrap.Tooltip(v);
        });

        /**
         * js 에러 핸들링
         */
        window.addEventListener("error", function (e) {
            // 콘솔창 열어서 코드쓰는거 예외처리
            // if ( location.href == e.filename )
            // {
            //     return;
            // }

            let msg = [e.message, e.error, e.stack, e.filename, e.lineno];
            console.log(msg.join("\n"));
            // toast.show("에러", msg.join("<br>"));

            // 콘솔출력만 가능
            // console.trace();
        });

        /**
         * ajax 에러 핸들링
         */
        // $(document).ajaxError(function (e, xhr, ajaxOptions, thrownError) {
        //     let body = [ajaxOptions.type, ajaxOptions.url];
        //     console.log(xhr.responseText);
        //     // console.log(ajaxOptions, thrownError)
        //     toast.show(
        //         e.type,
        //         "code: " + xhr.status + " " + thrownError,
        //         body.join("<br>")
        //     );
        // });

        $.ajaxSetup({
            dataType: "json",
            beforeSend: function (xhr) {
                // console.log('beforesend')

                /* 페이지 로딩 */
                if($("#spinner-loading").length == 0)
                {

                    $('body').append(`
                    <div class=" justify-content-center wrap-loading " style="display:none;" id="spinner-loading">
                        <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    `)
                }
                
                // 로딩바 보여주기
                $('.wrap-loading').css("display","block");

                // let url = new URL(this.url);
                // let form = $(`form[method="${this.type}"][action="${url.pathname}"]`)[0];
                // console.log(this)
                // validate(form, xhr);
            },
            // complete
            complete : function ( xhr )
            {   
                // 로딩바 없애기
                $('.wrap-loading').css("display","none");
                // console.log("로딩없애기")
            },
            error: function (res) {
                let title = "";
                if (res.status == 400) title = "";
                else title = "에러";

                if (res.status > 400) {
                    return;
                    alert("에러가 발생했습니다");
                    location.href = "/";
                }

                let data = res.responseJSON;
                if ( ! data )
                    return toast.show(title, res.responseText) ;
                if (data.msg) toast.show(title, data.msg);
            },
        });

        /**
         * 폼 유효성 검사 실행
         */
        $("form").on("submit", function (e) {
            
            if ( document.cookie.indexOf('user=hong') >= 0 )
            {
                e.preventDefault();
                // 설정값 얻기
                let config = $(this).data("validate");
                if ( ! config )
                    return console.warn('validate 설정이 없습니다');
                config = JSON.parse(atob(config))

                // 값 검사
                let res = this.validate(config.rule, config.response,  function(el, res){
                    console.log(el,res)

                    // 툴팁보여주고 포커스 주기
                    tt.show(el,msg);
                    el.focus();
                });

                console.log(res);

            
                return false;
                
                
                return false;
            }

            // return true;
            // e.preventDefault();

            // let form = this;
            // // 에러나면 return false;
            // // 이외에는 no return
            // // throttle 아직
            // console.log("form submit 이벤트 전체");
            // let res = validate(form);

            // console.log("이벤트 마지막", res);

            // return false;
        });

        // 최상단 스크롤 이동 전역 설정
        $('.totop').on('click', (e)=>{
            e.preventDefault();
            window.scrollTo({top:0, behavior: 'smooth'});
        })
    });

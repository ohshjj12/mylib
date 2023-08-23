/**
let u = new upload({
    selector:{
        form: '#upload',
        // 폼 안에 있어야하는거로
        submit: '.btn-upload',
        // 노드 안에 있어야 하는거로
        remove: '.btn-close',
    },
    node: $('#file').html(),
}).drop().submit();
 */
'use strict';
class upload
{
    constructor(o)
    {
        // 없으면 기본값 세팅
        let def = {
            // 업로드할 목록 보여줄 템플릿
            // list: `<ul></ul>`,
            // input file 노드 html
            selector: 'required',
            node: `<div>
                <span>{{ name }}</span>
                <button type="button" class="btn-close"></button>
            </div>`,
            removeNodeSelector:'.btn-close',
        };
        this.element = document.querySelectorAll(o.selector.form);

        for (const k in def)
            this[k] = o[k];
        for (const i of this.element)
        {
            i.init_content = i.childNodes;
            i.file = [];
            console.log(i.init_content)
        }
    }

    string2html(s)
    {
        let d = document.createElement('div');
        d.innerHTML = s.trim();
        return d.firstElementChild;

    }

    recursive( i , file , directory )
    {
        let self = this;
        for ( let ii of i )
        {
            let entry = ii.webkitGetAsEntry
                ? ii.webkitGetAsEntry()
                : ii ;

            if ( entry.isFile && file )
                file(entry);
            else if ( entry.isDirectory )
            {
                if ( directory )
                    directory(entry);
                entry.createReader().readEntries(function(e){
                    self.recursive(e , file , directory);
                },function(e){
                    console.log('err',e)
                });
            }
        }
    }


    /**
     * 파일 드랍
     */
    drop()
    {
        for (const l of this.element)
        {
            // 드롭 적용하려면 막어야함
            l.addEventListener('dragover',e=>{
                e.preventDefault();
            });

            l.addEventListener('drop',e=>{
                e.preventDefault();
                if ( e.target.closest(this.selector) == null )
                    return;
                // 재귀, 파일만
                this.recursive(e.dataTransfer.items,e => {
                    e.file(f => {
                        // 파일 추가
                        // 이름을 풀경로로
                        let n = new File([f],e.fullPath,f);
                        let node = this.string2html(this.node.replace(/{{\s*name\s*}}/,e.fullPath));
                        l.append(node);
                        l.file.push(n);

                        // 지우기
                        for (const i of node.querySelectorAll(this.selector.remove))
                            i.addEventListener('click',e=>{
                                node.remove();
                                l.file.pop(l.file.indexOf(n));
                            });
                    });
                });
            });
        }
        

        return this;
    }

    submit()
    {
        // 파일 따로 담아둬야해서 담아둔거 뺀다음 submit
        for (const i of this.element)
        {
            i.addEventListener('submit',function(e){
                e.preventDefault();
            });
            
            for (const s of i.querySelectorAll(this.selector.submit))
            {
                s.addEventListener('click',function(){
                    console.log(i)
                    console.log(i.file)

                    //  i.submit();

                });
            }
        }
        // $('.btn-upload').click(function(e){

        //     e.preventDefault();

        //     console.log()
        //     return;
        //     $('#upload').submit();
        // });
        return this;
    }
}

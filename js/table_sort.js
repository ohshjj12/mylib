/**
 * 사용법
 * table_sort('.table-bordered > thead th', "dddd");
 * jquery 사용 필수
 */

function table_sort(selector, attr = 'order' )
{
    // '.table-bordered > thead th'
    $(document).on('click',selector,function(){

        let type = [
            'asc', 'desc'
        ];
        //table-bordered
        var th = $(this);
        let o = th.attr(attr);
        var tr = th.closest('tr').find('th');
        let i = 0;

        if ( !o ) 
        o = type[0]
        else 
        o = type[type.indexOf(o) + 1] ?? type[0]

        th.attr(attr , o)
        
        for (const r of tr) {
           
            
            let rr = this.isSameNode(r);
            if( rr )
            break;
            i ++;
          
        }
      
        let tbody = th.closest('table').find('tbody');
        console.log(tbody);
        var tr = tbody.find('tr');
        console.log(tr);
        
        let arr = {};
        let n = 0;
        for (const r of tr) {
            
            
            let td = $(r).find('td');
            
            
            let tt = td[i];
            console.log(tt);
            arr[n] = tt.innerHTML;
            
            n ++;
        
        }
    /* 	arr.sort();
        console.log(arr); */
    
        let res = [];
        for (const r  in arr) {
            res.push([r, arr[r]]);
        }
    
        if ( o == 'asc'){

            res.sort(function(k,v){
                let x = k[1].toLowerCase();
                let y = v[1].toLowerCase();
                if (x < y) {
                    
                    return -1;
                }
                if (x > y) {
                    return 1;
                }
                return 0;
            });
        }else if( o == 'desc'){
            res.sort(function(k,v){
                let x = k[1].toLowerCase();
                let y = v[1].toLowerCase();
                if (x > y) {
                    return -1;
                }
                if (x < y) {
                    return 1;
                }
                return 0;
            });
        }else {
            console.log(arr);
            throw 'exit';
        }
    
    
        for (const i of res) {
    
            tbody.append(tr[i[0]]);
    
        }
        
    
    
    
        
    });
}

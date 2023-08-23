<?
namespace API;

use Exception;
use HTTP\Curl;

class Holiday
{   
    protected $apikey;
    protected $url;
    protected $cache;

    function __construct(string $apikey)
    {   
        $this -> url = 'http://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService/getAnniversaryInfo'; /*URL*/
        $this -> apikey = $apikey;

        $dir = empty($_SERVER['DOCUMENT_ROOT'] ) ?  $_SERVER['OLDPWD'] : $_SERVER['DOCUMENT_ROOT'] ;
        $this -> cache = $dir.'/storage/cache/holiday.json';
    }

    function get(array $data)
    {   
        $get = $this->getCache();
        if($get){
            return $get;
        }
        $arr = array(
            'pageNo' => array (
                'length' => 4,
                'div' => 'require',
                'type' => 'integer'
            ),
            'numOfRows' => array (
                'length' => 4,
                'div' => 'require',
                'type' => 'integer'
            ),
            'solYear' => array (
                'length' => 4,
                'div' => 'require',
                'type' => 'integer'
            ),
            'solMonth' => array (
                'length' => 4,
                'div' => 'option',
                'type' => 'integer'
            )

        );
        
        foreach ($arr as $k => $v) {
            if($v['div'] == 'require' &&  ! isset($data[$k])) {
                throw new Exception($k.'는 필수값 입니다.' );
            }
            if(isset($v['length']) && strlen($data[$k]) > $v['length'] ){
                throw new Exception($k.'는'.$v['length'].'보다깁니다.' );
            }
            if(gettype($data[$k]) != $v['type']){
                throw new Exception($k.'는'.$v['type'].'이어야합니다.' );
            }

        }

        $data = http_build_query($data);
        $data .= '&'.$this-> apikey;
        $curl = new Curl($this-> url.'?'.$data);
        $res = $curl->send();
        $res = ($res['response']);
        
        $xml = simplexml_load_string($res, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);
        
        if( isset( $array['cmmMsgHeader']['errMsg'] ) && $array['cmmMsgHeader']['errMsg'] == 'SERVICE ERROR'){
            return false;
        }
        $this -> setCache(($json));
        return $data;
    }

    protected function getCache()
    {
        // 저장경로
        $file = $this ->cache;

        if( !file_exists($file))
            return false;

        // 파일내용 리턴
        return json_decode(file_get_contents($file), true);
        
    }
    
    function setCache($data)
    {
         // 저장경로
        $file = $this ->cache;
    
        $file_time =  date("Y-m-d", filemtime($file));


        $f = date("Y-m-d", strtotime ("+12 month", filemtime($file)));

        if($file_time <= $f){

             // 파일내용 리턴
            return file_put_contents($file, $data);
        }
    }

}

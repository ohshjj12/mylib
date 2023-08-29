<?php
 /**
     * @var array 카카오 API 에 필요한 정보
     */
    protected $kakao = [];

    /**
     * @var array 네이버 API 에 필요한 정보
     */
    protected $naver = [];


    /*
     * 카카오 간편회원가입
     */
    function kakaosignup ( $req ){

         // 아이디 중복 확인
        $is = $this->Model->auth->idcheck([
            'id' => $req->post['id'],
            'type' => 'kakao'
        ])[0]['is'];
        if ( $is == true )
            return [
                'msg' => '이미 있는 아이디 입니다'
            ];
         $arr = [
            'id' => $req->post ['data']['email'],
            'company_idx' => 0,
            'type' => 'kakao',
            'pwd' => '',
            'phone' => '',
         ];

         $this->Model->auth->signup($arr);

         $jinju = Config::get('Discord', 'jinju');
         $jinju = new Discord($jinju);
         $jinju->message('카카오 회원가입 요청이 도착했습니다.');

         return [
             'msg' => '카카오 간편회원가입이 완료되었습니다.',
         ];
    }

  /**
     * 카카오 로그인 처리
     */
    function kakaoauth( $req )
    {
        $restAPIKey = $this->kakao['api_key'];
        $callbacURI = $this->kakao['redirectURI']; // 본인의 Call Back URL을 입력해주세요
        $admin_key = $this->kakao['admin_key'];

        // 콜백받은 인가코드로 토큰받기
        $curl = new Curl($this->kakao['token']);
        $token = $curl->post([
            'grant_type' => 'authorization_code',
            'client_id' => $restAPIKey,
            'redirect_uri' => $callbacURI,
            'code' => $req -> get['code'],
        ])->send()['response'];

        $token = json_decode($token,true);
        $req -> post['token'] = $token;
        // return ['token' => $token];

        // 사용자 정보 가져오기
        $curl = new Curl($this->kakao['user']);
        $info = $curl->header([
            'Authorization' => "Bearer {$token['access_token']}"
        ])->send()['response'];

        $info = json_decode($info,true);


        $req->post['type'] = 'kakao';
        $req->post['data'] = $info['kakao_account'];
        // var_dump($req->post['data']);
        // var_dump($info['kakao_account'])

        return $this->login($req);


    }


   /**
     * 회원 탈퇴 토근 요청
     */
    function kakaodelete( $req ){
        $token = $req->auth['access_token'];

        // 사용자 정보 가져오기
        $curl = new Curl($this->kakao['delete']);
        $res = $curl->header([
            'Authorization' => "Bearer {$token}"
        ])->post()->send()['response'];

        $res = json_decode($res,true);

        return $res;

    }

     /**
     * 로그인 처리
     */
    function login($req)
    {
        if ( $req->post['type'] == 'simple')
        {
            $res = $this->Model->auth->loginspw([
                'idx' => $req->post['idx'],
                'spw' => $req->post['spw'],
            ]);
        }
        // 구글
        else if( $req->post['type'] == 'google')
        {
            $data = [
                'id' => $req->post['id'],
                'email' => $req->post['email'],
                'pwd' => '',
                'type' => 'google'
            ];
            $res = $this->Model->auth->loginGoogle($data);

            if(empty($res))
            {
                $this->googlesignup($req);
                $res = $this->Model->auth->loginGoogle($data);
            }

            $user = $req->post['email'];
            $method = 'google';
        }
        // 네이버 로그인 처리
        else if ( $req->post['type'] == 'naver')
        {
            // 가입처리
            $this->signupNaver($req);

            $req->post['id'] = $req->post['info']['response']['id'];
            $res = $this->Model->auth->loginNaver([
                'id' => $req->post['id'],
            ]);
        }
        // 카카오 로그인 처리
        else if($req->post['type'] == 'kakao')
        {
            $req->post['id'] = $req->post ['data']['email'];
            $data = [
                'id' => $req->post['id'],
                'pwd' => '',
                'type' => 'kakao',
                'name' => $req->post['data']['profile']['nickname']
            ];
            $res = $this->Model->auth->loginKakao($data);

            if ( empty( $res ))
            {
                $this->kakaosignup($req);
                $res = $this->Model->auth->loginKakao($data);
            }

            $res[0]['token'] = $req->post['token'];

            $user = $req->post['data']['email'];
            $method = 'kakao';

        }
        else
        {
            $pwd = $this->hash($req->post['pw'], $this->salt['public']);
            $res = $this->Model->auth->login([
                'id' => $req->post['id'],
                'pwd' => $pwd,
            ]);
        }
        // 에러
        if ( empty ( $res ) )
        {
            $this->Model->auth->insertLog([
                'type' => 'fail',
                'id' => $req->post['id'],
                'ip' => $req->ip,
            ]);
            return false;
        }

        $this->Model->auth->insertLog([
            'type' => 'success',
            'id' => $req->post['id'],
            'ip' => $req->ip,
        ]);
        return $res;
    }

     /**
     * 회원가입
     */
    function signup($req)
    {
        // 아이디 중복 확인
        $is = $this->Model->auth->idcheck([
            'id' => $req->post['id'],
            'type' => 'common'
        ])[0]['is'];
        if ( $is == true )
            return [
                'msg' => '이미 있는 아이디 입니다'
            ];


        // 기호 빼고 입력
        $num = preg_replace ( '/[^0-9]/' , '' , $req->post['regist_num'] ) ;

        // 회사 조회
        $company_idx = $this->Model->auth->selectCompany([
            'regist_num' => $num
        ])[0]['idx'] ?? 0;

        // 없으면 입력
        if ( empty ( $company_idx ) )
        {
            $company_idx = $this->Model->auth->insertCompany([
                'name' => $req->post['company_name'],
                'type' => $req->post['type'],
                'ceo_name' => $req->post['ceo_name'],
                'regist_num' => $num,
                'address' => $req->post['company_address'],
                'address_detail' => $req->post['company_address_detail'],
                'email' => $req->post['company_email'],
                'phone' => $req->post['company_phone'],
            ]);
        }
        // 있으면 업데이트
        else
        {
            $this->Model->auth->IFNULLUpdateCompany([
                'company_idx' => $company_idx,
                'name' => $req->post['company_name'],
                'type' => $req->post['type'],
                'ceo_name' => $req->post['ceo_name'],
                'regist_num' => $num,
                'address' => $req->post['company_address'],
                'address_detail' => $req->post['company_address_detail'],
                'email' => $req->post['company_email'],
                'phone' => $req->post['company_phone'],
            ]);
        }

        // 사업자등록증 업로드 할 파일
        $cert = $this->upload.'/'.$this->uniqfile($this->upload) ?? NULL;

        // 비번 암호화 후 입력
        $pwd = $this->hash($req->post['pw'], $this->salt['public']);
        $auth_idx = $this->Model->auth->signup([
            'company_idx' => $company_idx,
            'type' => 'common',
            'id' => $req->post['id'],
            'pwd' => $pwd,
            'cert' => $cert
        ]);

        $this->Model->auth->insertMember([
            'name' => $req->post['name'],
            'phone' => $req->post['phone'],
            'company_idx' => $company_idx,
            'auth_idx' => $auth_idx,
        ]);

        if ( $req->post['type'] == 'merchant' )
        {
            if ( ! is_dir ( $this->upload ) )
                Folder::mkdirp($this->upload);
            move_uploaded_file($req->file['cert']['tmp_name'] , $cert );
        }
        
        $jinju = Config::get('Discord', 'jinju');
        $jinju = new Discord($jinju);
        $jinju->message('일반회원가입 요청이 도착했습니다.');

        return [
            'msg' => '회원가입 요청되었습니다'
        ];
    }


  /**
     * 네이버 회원가입
     */
    function signupNaver($req)
    {
        $data = $req->post['info']['response'];
        $data['mobile'] = preg_replace('/\D+/','',$data['mobile']);

        // 아이디 중복 확인
        $is = $this->Model->auth->idcheck([
            'id' => $data['id'],
            'type' => 'naver'
        ])[0]['is'];
        if ( $is == true )
            return [
                'msg' => '이미 있는 아이디 입니다'
            ];

        $auth_idx = $this->Model->auth->signupNaver([
            'email' => $data['email'] ?? NULL,
            'id' => $data['id'],
        ]);

        // member 입력 일단 0으로 놓고 추가 입력받게
        $this->Model->auth->insertMember([
            'auth_idx' => $auth_idx,
            'company_idx' => 0,
            'name' => $data['name'],
            'phone' => $data['mobile'],
        ]);

        $jinju = Config::get('Discord', 'jinju');
        $jinju = new Discord($jinju);
        $jinju->message('네이버 회원가입 요청이 도착했습니다.');

        return [
            'msg' => '회원가입 요청되었습니다'
        ];
    }

    /**
     * 네이버 로그인 값 받아오기
     */
    function naverlogin ( $req )
    {

        // /**
        //  * @fix 컨셉과 다르게 때움
        //  * 했었는지 확인해서 했으면 스킵
        //  */
        // if ( isset ( $_SESSION['naver_token_count'] ) )
        //     return $this->login($req);

        $client_id = $this->naver['client_id'];
        $client_secret = $this->naver['client_secret'];
        $code = $_GET["code"];
        $state = $_GET["state"];
        $redirectURI = urlencode($this->naver['redirectURI']);

        // if ($_SESSION['naver_state'] != $_GET['state']) {
        //     // 오류가 발생하였습니다. 잘못된 경로로 접근 하신것 같습니다.
        // }

        //토큰값 가져오기
        $curl = new Curl ($this->naver['token']);
        $token = $curl->get([
            'grant_type' => 'authorization_code',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirectURI,
            'code' => $code,
            'state' => $state,
        ])->send();

        $token = json_decode($token['response'], true);

        //토큰값으로 네이버 회원정보 가져오기
        $curl = new Curl ($this->naver['user']);
        $info = $curl->header([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$token['access_token']
            ])->send();
        $info = json_decode($info['response'], true);


        if( empty ( $token['access_token'] ) )
         {
            //토큰값 가져오지 못했을 때
            return [
                'msg' => '네이버 토큰값 에러',
            ];
        }

        if ( empty ( $info ['response']['id'] ) )
        {
            //네이버 회원정보가 없을때
            return [
                'msg' => '네이버 회원정보 없음',
            ];
        }

        $req->post['token'] = $token['access_token'];
        $req->post['type'] = 'naver';
        $req->post['info'] = $info;

        // $_SESSION['naver_token_count'] = true;

        return $this->login($req);
    }

     /**
     * 네이버 로그인 탈퇴
     */
    function naverdelete ( $req )
    {

        $client_id = $this->naver['client_id'];
        $client_secret = $this->naver['client_secret'];
        $grant_type = "delete";
        $token = $req->auth['token'];

        //토큰값으로 네이버 탈퇴
        $curl = new Curl ($this->naver['token']);
        $info = $curl->get([
            'service_provider' => 'NAVER',
            'grant_type' => $grant_type,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'access_token' => $token,
        ])->send();

        $info = json_decode($info['response'], true);

        $msg = $info ['result'] != 'success'  ? '네이버 탈퇴처리 하였습니다.' : '네이버 탈퇴 처리 못함';

        return [
            'res' => $info ['result'],
            'msg' => $msg
        ];
    }

  /**
     * 구글회원가입
     */
    function googlesignup( $req )
    {

        // 아이디 중복 확인
        $is = $this->Model->auth->idcheck([
            'id' => $req->post['id'],
            'type' => 'google'
        ])[0]['is'];
        if ( $is == true )
            return [
                'msg' => '이미 있는 아이디 입니다'
            ];


        $arr = [
            'id' => $req->post['id'],
            'email' => $req->post ['email'],
            'type' => 'google',
            'company_idx' => 0,
        ];
        

        $this->Model->auth->signupGoogle($arr);

        return [
            'msg' => '간편회원가입이 완료되었습니다.',
        ];
    }



  /**
     * 아이디/비번 찾기
     */
    function find($req)
    {
        // 아이디 찾기
        if ( $req->post['type'] == 'id' )
            $res = $this->Model->auth->find([
                'name' => $req->post['name'],
                'phone' => $req->post['phone'],
            ])[0] ?? [];
        // 비번 재설정
        else if ( $req->post['type'] == 'pwd' )
            $res = $this->Model->auth->find([
                'id' => $req->post['id'],
                'name' => $req->post['name'],
                'phone' => $req->post['phone'],
            ])[0] ?? [];

        return array_merge($res,['type' => $req->post['type']]);
    }

    /**
     * 비밀번호 변경/재설정
     * @fix member 테이블의 조건도 추가하게
     */
    function changepw($req)
    {
        $pwd = $this->hash($req->post['pw'], $this->salt['public']);

        $res = $this->Model->auth->changepw([
            'id' => $req->post['id'],
            'pwd' => $pwd,
        ]);
        return [
            'msg' => '적용 되었습니다'
        ];
    }

   /**
     * 로그아웃
     */
    function logout($req)
    {
        $req->session->destroy();
    }

    /**
     * 단방향 암호화
     * @param string 받은 비번
     * @param string 섞을 문자
     * @param int 반복수
     * @return string 암호화된 데이터
     */
    protected function hash ( string $str , string $salt , int $repeat = 5 )
    {
        $res = hash('sha256', Str::mix($str,$salt));
        if ( ! empty ( $repeat ) )
            return $this->hash($res,$salt,$repeat - 1);
        else
            return $res;
    }
};

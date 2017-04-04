<?php
namespace Modules\Users\Classes;


class FBApi extends OAuth{

    /**
     * @var string
     */
    protected $accessTokenUri = 'https://graph.facebook.com/oauth/access_token';

    /**
     * @var string
     */
    protected $authUri = 'https://www.facebook.com/dialog/oauth';

    protected $apiUri = 'https://graph.facebook.com/me';

    /**
     * @param string $redirectUri
     * @param string $responseType
     * @param string $scope
     * @return string
     */
    public function generateAuthUrl($redirectUri, $responseType='code', $scope='email'){
        $urlParams = ['client_id'=>$this->getClientId(),'redirect_uri'=>$redirectUri,'response_type'=>$responseType, 'scope'=>$scope];
        return $this->getAuthUri().'?'.urldecode(http_build_query($urlParams));
    }

    protected function parseAccessToken($token){
        $tokenInfo = null;
        parse_str($token, $tokenInfo);
        return $tokenInfo;
    }


    /**
     * @return bool|mixed|string
     */
    public function getUserInfo(){
        if(count($this->accessToken) && isset($this->accessToken['access_token'])){
            $params = ['access_token'=>$this->accessToken['access_token']];
            $result = file_get_contents($this->apiUri . '?' . urldecode(http_build_query($params)));
            $result = \json_decode($result, true);
            if (isset($result['id'])){
                return $result;
            }
        }
        return false;
    }
}
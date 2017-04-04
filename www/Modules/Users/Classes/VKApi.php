<?php
namespace Modules\Users\Classes;


class VKApi extends OAuth{
    /**
     * @var string
     */
    protected $apiUri = 'https://api.vk.com/method/';

    /**
     * @var string
     */
    protected $authUri = 'http://oauth.vk.com/authorize';

    /**
     * @var string
     */
    protected $accessTokenUri = 'https://oauth.vk.com/access_token';

    /**
     * @param string $redirectUri
     * @param string $responseType
     * @return string
     */
    public function generateAuthUrl($redirectUri, $responseType='code'){
        $urlParams = ['client_id'=>$this->getClientId(),'redirect_uri'=>$redirectUri,'response_type'=>$responseType];
        return $this->getAuthUri().'?'.urldecode(http_build_query($urlParams));
    }

    /**
     * @param $redirectUri
     * @param null $code
     * @return array
     */
    /*
    public function loadAccessToken($redirectUri, $code=null){
        if (is_null($code)){
            $code = $this->getCode();
        }
        $params = array_merge($this->getRequestParams(), ['code'=>$code, 'redirect_uri'=>$redirectUri]);
        $requestUri = $this->getAccessTokenUri() . '?' . urldecode(http_build_query($params));
        $this->setAccessToken(json_decode(file_get_contents($requestUri), true));
        return $this->getAccessToken();
    }
    */

    /**
     * @param string $method
     * @param array $params
     * @return array
     */
    public function request($method, array $params=[]){
        $params = array_merge(['access_token'=>$this->getAccessToken('access_token')], $params);
        $response = json_decode(file_get_contents($this->getApiUri().$method . '?' . urldecode(http_build_query($params))), true);
        return $response;
    }




    /**
     * @return string
     */
    public function getApiUri(){
        return $this->apiUri;
    }

    /**
     * @param string $apiUri
     */
    public function setApiUri($apiUri){
        $this->apiUri = $apiUri;
    }

} 
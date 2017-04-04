<?php
namespace Modules\Users\Classes;


class OAuth{
    /**
     * @var int
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret = '';

    /**
     * @var string
     */
    protected $authUri = '';

    /**
     * @var string
     */
    protected $accessTokenUri = '';

    /**
     * @var string
     */
    protected $code = '';

    /**
     * @var array
     */
    protected $accessToken = [];

    public function __construct($clientId, $clientSecret){
        $this->setClientId($clientId);
        $this->setClientSecret($clientSecret);
    }

    /**
     * @param string $redirectUri
     * @param string|null $code
     * @return mixed
     */
    public function loadAccessToken($redirectUri, $code=null){
        $code = is_null($code) ? $this->getCode() : $code;
        $params = array_merge($this->getRequestParams(), ['code'=>$code, 'redirect_uri'=>$redirectUri]);
        $requestUri = $this->getAccessTokenUri() . '?' . urldecode(http_build_query($params));
        $result = file_get_contents($requestUri);
        $this->setAccessToken($this->parseAccessToken($result));
        return $this->getAccessToken();
    }

    /**
     * @param string $token
     * @return mixed
     */
    protected function parseAccessToken($token){
        return \json_decode($token, true);
    }

    /**
     * @return int
     */
    public function getClientId(){
        return $this->clientId;
    }

    /**
     * @param int $clientId
     */
    public function setClientId($clientId){
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret(){
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret($clientSecret){
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getAuthUri(){
        return $this->authUri;
    }

    /**
     * @param string $authUri
     */
    public function setAuthUri($authUri){
        $this->authUri = $authUri;
    }

    /**
     * @return string
     */
    public function getAccessTokenUri(){
        return $this->accessTokenUri;
    }

    /**
     * @param string $accessTokenUri
     */
    public function setAccessTokenUri($accessTokenUri){
        $this->accessTokenUri = $accessTokenUri;
    }

    /**
     * @return string
     */
    public function getCode(){
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code){
        $this->code = $code;
    }

    /**
     * @param string|null $field
     * @return mixed
     */
    public function getAccessToken($field=null){
        return is_null($field) ? $this->accessToken : $this->accessToken[$field];
    }

    /**
     * @param array $accessToken
     */
    public function setAccessToken($accessToken){
        $this->accessToken = $accessToken;
    }

    /**
     * @return array
     */
    protected function getRequestParams(){
        return ['client_id'=>$this->getClientId(), 'client_secret'=>$this->getClientSecret()];
    }
}
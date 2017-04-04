<?php
namespace Modules\GeoLocation\Map;


class DirectionsQuery{
    /**
     * Output format
     * @var string
     */
    protected $outputFormat = 'json';

    /**
     * Query protocol
     * @var string
     */
    protected $queryProtocol = 'https';

    /**
     * HTTPS query URI
     * @var string
     */
    protected $httpsApiUri = 'https://maps.googleapis.com/maps/api/directions/';

    /**
     * HTTP query URI
     * @var string
     */
    protected $httpApiUri = 'http://maps.googleapis.com/maps/api/directions/';

    /**
     * @var int
     */
    protected $maxQueryLength = 2000;

    /**
     * Адрес, текстовое значение широты/долготы или идентификатор места,
     * от которого требуется провести расчет маршрутов.
     * @var string
     * @see https://developers.google.com/maps/documentation/directions/intro
     */
    protected $origin;

    /**
     * Адрес, текстовое значение широты/долготы или идентификатор места, до которого требуется провести расчет маршрутов.
     * Параметры для поля destination аналогичны описанным выше параметрам поля origin.
     * @var string
     */
    protected $destination;

    /**
     * API key
     * @var string
     */
    protected $apiKey;

    /**
     * Указывает, какой способ передвижения использовать при расчете маршрута.
     * Допустимые значения и другие сведения о запросах указаны в разделе "Способы передвижения".
     * driving - на автомобиле
     * walking - пеший маршрут
     * bicycling - запрос маршрута для велосипедистов по велосипедным дорожкам и предпочитаемым улицам (где это возможно)
     * transit - запрос маршрута на общественном транспорте (где это возможно).
     * @var string
     * @see https://developers.google.com/maps/documentation/directions/intro#TravelModes
     */
    protected $mode;

    /**
     * Определяет массив промежуточных точек.
     * @var array
     */
    protected $wayPoints = [];

    /**
     * Если это поле имеет значение true, служба маршрутов может предоставлять несколько альтернативных маршрутов в ответе.
     * Время ответа сервера может быть больше.
     * @var bool
     */
    protected $alternatives = false;

    /**
     * Указывает, что в рассчитанном маршруте следует избегать указанных особенностей.
     * tolls - указывает, что в рассчитанном маршруте следует избегать платных дорог/мостов
     * highways - указывает, что в рассчитанном маршруте следует избегать шоссе.
     * ferries - указывает, что в рассчитанном маршруте следует избегать паромов.
     * indoor - указывает, что в рассчитанном маршруте следует избегать внутренних переходов по лестницам и маршрутов на общественном транспорте.
     * @see https://developers.google.com/maps/documentation/directions/intro#Restrictions
     * @var array
     */
    protected $avoid=[];

    /**
     * Время отправления.
     * Указывает желаемое время прибытия для маршрутов на общественном транспорте, измеренное в секундах с полуночи 1 января 1970 г.
     * или now которое устанавливает для времени отправления текущий момент времени (с точностью до секунды).
     * @see https://developers.google.com/maps/documentation/directions/intro#RequestParameters
     * @var int|string
     */
    protected $departureTime;

    /**
     * Время прибытия.
     * Указывает желаемое время прибытия для маршрутов на общественном транспорте, измеренное в секундах с полуночи 1 января 1970 г.
     * @var int
     */
    protected $arrivalTime;

    /**
     * Указывает один или несколько предпочитаемых способов передвижения. Данный параметр может быть указан для маршрутов на общественном транспорте.
     * bus, subway, train, tram (трамвай и легкое метро), rail(поезде, трамвае, метрополитене и легком метро. Аналогично train+tram+subway)
     * @var array
     */
    protected $transitMode=[];

    /**
     * Указывает предпочтения для маршрутов на общественном транспорте.
     * less_walking - указывает, что в рассчитанном маршруте следует отдать приоритет уменьшению расстояния, которое нужно пройти пешком
     * fewer_transfers - указывает, что в рассчитанном маршруте следует отдать приоритет уменьшению количества пересадок
     * @var string
     */
    protected $transitRoutingPreference = '';

    /**
     * В этом параметре для указания региона используется аргумент ccTLD (домен верхнего уровня кода страны).
     * @var string
     */
    protected $region = 'ru';

    /**
     * Язык, на котором выводятся результаты.
     * @see https://developers.google.com/maps/faq#languagesupport
     * @var string
     */
    protected $language = 'ru';

    /**
     * Указывает предположения, используемые при расчете времени в пути.
     * best_guess - (по умолчанию) – означает, что возвращаемое в поле duration_in_traffic значение должно содержать наилучшую оценку ожидаемого времени пути.
     * pessimistic - возвращаемое значение duration_in_traffic должно быть больше, чем фактическое время поездки в большинство дней
     * optimistic - возвращаемое значение duration_in_traffic должно быть меньше, чем фактическое время поездки в большинство дней
     * @var string
     */
    protected $trafficModel;

    /**
     * Обязательные поля
     * @var array
     */
    protected $requiredFields = ['origin', 'destination', 'apiKey'];

    /**
     * Возможные значения некоторых переменных
     * @var array
     */
    protected $availableValues = [
        'outputFormat'=>['xml','json'],
        'queryProtocol'=>['http', 'https'],
        'mode'=>['driving', 'walking', 'bicycling', 'transit'],
        'avoid'=>['tolls', 'highways', 'ferries', 'indoor'],
        'transitMode'=>['bus', 'subway', 'train', 'tram', 'rail'],
        'transitRoutingPreference'=>['less_walking', 'fewer_transfers'],
        'trafficModel'=>['best_guess', 'pessimistic', 'optimistic']
    ];


    public function __construct($apiKey=null){
        if (!is_null($apiKey)){
            $this->setApiKey($apiKey);
        }
    }

    public function execute(){
        foreach ($this->requiredFields as $field){
            if (empty($this->$field)){
                return false;
            }
        }
        $parameters = [
            'origin'=>$this->getOrigin(),
            'destination'=>$this->getDestination(),
            'key'=>$this->getApiKey(),
        ];
        if ($this->isAlternatives()){
            $parameters['alternatives'] = 'true';
        }
        if (count($this->getWayPoints()) > 0){
            $parameters['waypoints'] = implode('|', $this->getWayPoints());
        }
        if (count($this->getAvoid()) > 0){
            $parameters['avoid'] = implode('|', $this->getAvoid());
        }
        if (!empty($this->getMode())){
            $parameters['mode'] = $this->getMode();
        }
        if (!empty($this->getLanguage())){
            $parameters['language'] = $this->getLanguage();
        }
        if (!empty($this->getRegion())){
            $parameters['region'] = $this->getRegion();
        }
        if (!empty($this->getArrivalTime())){
            $parameters['arrival_time'] = $this->getArrivalTime();
        }
        if (!empty($this->getDepartureTime())){
            $parameters['departure_time'] = $this->getDepartureTime();
        }
        if (!empty($this->getTrafficModel())){
            $parameters['traffic_model'] = $this->getTrafficModel();
        }
        if (count($this->getTransitMode()) > 0){
            $parameters['transit_mode'] = implode('|', $this->getTransitMode());
        }
        if (!empty($this->getTransitRoutingPreference())){
            $parameters['transit_routing_preference'] = $this->getTransitRoutingPreference();
        }
        $uri = ($this->getQueryProtocol() == 'http' ? $this->httpApiUri : $this->httpsApiUri).$this->getOutputFormat().'?'.urldecode(http_build_query($parameters));
        //var_dump($uri);
        $result = file_get_contents($uri);
        //var_dump($result);
        return $result;

    }

    /**
     * Проверяет, является ли $val допустимым значением для $field
     * @param string $field
     * @param mixed $val
     * @return bool
     */
    public function isAvailable($field, $val){
        return isset($this->availableValues[$field]) ? in_array($val, $this->availableValues[$field]): true;
    }

    /**
     * @return string
     */
    public function getOutputFormat(){
        return $this->outputFormat;
    }

    /**
     * @param string $outputFormat
     * @return $this
     */
    public function setOutputFormat($outputFormat){
        if ($this->isAvailable('outputFormat', $outputFormat)) $this->outputFormat = $outputFormat;
        return $this;
    }

    /**
     * @return string
     */
    public function getQueryProtocol(){
        return $this->queryProtocol;
    }

    /**
     * @param string $queryProtocol
     * @return DirectionsQuery
     */
    public function setQueryProtocol($queryProtocol){
        if ($this->isAvailable('queryProtocol', $queryProtocol)) $this->queryProtocol = $queryProtocol;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxQueryLength(){
        return $this->maxQueryLength;
    }

    /**
     * @param int $maxQueryLength
     * @return DirectionsQuery
     */
    public function setMaxQueryLength($maxQueryLength){
        $this->maxQueryLength = $maxQueryLength;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrigin(){
        return $this->origin;
    }

    /**
     * @param string $origin
     * @return $this
     */
    public function setOrigin($origin){
        $this->origin = $origin;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestination(){
        return $this->destination;
    }

    /**
     * @param string $destination
     * @return $this
     */
    public function setDestination($destination){
        $this->destination = $destination;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey(){
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     * @return DirectionsQuery
     */
    public function setApiKey($apiKey){
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getMode(){
        return $this->mode;
    }

    /**
     * @param string $mode
     * @return $this
     */
    public function setMode($mode){
        if ($this->isAvailable('mode', $mode)) $this->mode = $mode;
        return $this;
    }

    /**
     * @return array
     */
    public function getWayPoints(){
        return $this->wayPoints;
    }

    /**
     * @param array $wayPoints
     * @return $this
     */
    public function setWayPoints(array $wayPoints){
        $this->wayPoints = $wayPoints;
        return $this;
    }

    /**
     * @param string $point
     * @param bool $isVIA
     * @return $this
     */
    public function addWayPoint($point, $isVIA=false){
        $this->wayPoints[] = ($isVIA ? 'via:' : '').$point;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAlternatives(){
        return $this->alternatives;
    }

    /**
     * @param boolean $alternatives
     * @return $this
     */
    public function setAlternatives($alternatives){
        $this->alternatives = $alternatives;
        return $this;
    }

    /**
     * @return array
     */
    public function getAvoid(){
        return $this->avoid;
    }

    /**
     * @param array $avoid
     * @return $this
     */
    public function setAvoid(array $avoid){
        $this->avoid = $avoid;
        return $this;
    }

    /**
     * @param string $avoid
     * @return $this
     */
    public function addAvoid($avoid){
        if ($this->isAvailable('avoid', $avoid)){
            $this->avoid[] = $avoid;
        }
        return $this;
    }

    /**
     * @return int|string
     */
    public function getDepartureTime(){
        return $this->departureTime;
    }

    /**
     * @param int|string $departureTime
     * @return $this
     */
    public function setDepartureTime($departureTime){
        if (ctype_digit(strval($departureTime)) || $departureTime == 'now'){
            $this->departureTime = $departureTime;
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getArrivalTime(){
        return $this->arrivalTime;
    }

    /**
     * @param int $arrivalTime
     * @return $this
     */
    public function setArrivalTime($arrivalTime){
        if (ctype_digit(strval($arrivalTime))){
            $this->arrivalTime = $arrivalTime;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getTransitMode(){
        return $this->transitMode;
    }

    /**
     * @param array $transitMode
     * @return $this
     */
    public function setTransitMode(array $transitMode){
        $this->transitMode = $transitMode;
        return $this;
    }

    /**
     * @param $mode
     * @return $this
     */
    public function addTransitMode($mode){
        if ($this->isAvailable('transitMode', $mode)){
            $this->transitMode[] = $mode;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getTransitRoutingPreference(){
        return $this->transitRoutingPreference;
    }

    /**
     * @param string $transitRoutingPreference
     * @return $this
     */
    public function setTransitRoutingPreference($transitRoutingPreference){
        if ($this->isAvailable('transitRoutingPreference', $transitRoutingPreference)){
            $this->transitRoutingPreference = $transitRoutingPreference;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getRegion(){
        return $this->region;
    }

    /**
     * @param string $region
     * @return $this
     */
    public function setRegion($region){
        $this->region = $region;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage(){
        return $this->language;
    }

    /**
     * @param string $language
     * @return $this
     */
    public function setLanguage($language){
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getTrafficModel(){
        return $this->trafficModel;
    }

    /**
     * @param string $trafficModel
     * @return $this
     */
    public function setTrafficModel($trafficModel){
        if ($this->isAvailable('trafficMode', $trafficModel)){
            $this->trafficModel = $trafficModel;
        }
        return $this;
    }


}
<?php
namespace Core\DataBase;


class Connection extends \PDO {

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $database;

    /**
     * @var string
     */
    protected $charset;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $socket;

    /**
     * @var bool
     */
    protected $logQueries;

    /**
     * @var \PDO
     */
    protected $connection = null;

    /**
     * Конструктор класса.
     * @param string $host      - Хост БД
     * @param string $user      - Пользователь БД
     * @param string $password  - Пароль пользователя
     * @param string $database  - Название БД
     * @param string $charset   - Кодировка соединения (utf8)
     * @param int $port         - Порт подключения (3306)
     * @param bool $logQueries  - Отслеживать запросы?
     */
    public function __construct($host, $user, $password, $database, $charset = 'utf8', $port = 3306, $logQueries = false){
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->charset = $charset;
        $this->port = $port;
        $this->socket = $port;
        $this->logQueries = $logQueries;


        //Парсим host вдиа localhost:3306
        $tmp = substr(strstr($this->host, ':'), 1);
        if (!empty($tmp)){
            // Номер порта или имя сокета
            if (is_numeric($tmp)){
                $this->port = $tmp;
            }
            else{
                $this->socket = $tmp;
            }

            //Получаем только имя хоста
            $this->host = substr($this->host, 0, strlen($this->host) - (strlen($tmp) + 1));

            // Проверка для подобного вида хостов: ":3306"
            if ($this->host == ''){
                $this->host = 'localhost';
            }
        }
        //$this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, '\\Core\\DataBase\\Statement');
        parent::__construct("mysql:host={$this->host};dbname={$this->database};port={$this->port};charset={$this->charset}", $this->user, $this->password,
            array(\PDO::ATTR_STATEMENT_CLASS => array('\\Core\\DataBase\\Statement'))
        );
    }

    public static function parseXML(){
        $path = QS_path(array('Tables', 'schema.xml'), false);
    }

}
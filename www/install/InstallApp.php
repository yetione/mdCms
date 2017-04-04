<?php
namespace install;

use Core\Config;
use Core\Core;
use Core\DataBase\Exception\StatementExecuteError;
use Core\Debugger;
use Core\Module\ModuleManager;
use Core\Response\HTMLResponse;
use Core\Response\JSONResponse;
use Core\Response\Response;

class InstallApp  {

    /**
     * @var Response
     */
    protected $response;

    public function __construct($name, \Autoloader $autoloader){
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        $this->name = $name;
        $this->autoLoader = $autoloader;
        $this->config = new Config(QS_path(array('Configs','config.ini'), false));

        Debugger::init(Debugger::ALL, QS_path(array('logs'), false, true));


        $modulesFilePath = QS_path(array('Modules', 'modules.xml'), false);
        $modulesAutoloadPath = QS_path(array('Modules', 'autoload.xml'), false);
        $modulesDir = QS_path(array('Modules'), false);
        $this->modulesManager = new ModuleManager($modulesFilePath, $modulesAutoloadPath, $modulesDir, $this->config);

        $this->core = new Core($this->config, $this);
        $this->modulesManager->addModule('Core', $this->core);


        $this->buildResponse();

    }

    public function install($data){
        $this->clearData();
        //$this->buildEntities();
        $this->modulesManager->loadData();

        $this->modulesManager->installModule('Kernel', true);
        $kernel = $this->modulesManager->getModule('Kernel');

        $this->modulesManager->installModule('Users', true);
        $users = $this->modulesManager->getModule('Users');

        $this->modulesManager->installModule('AdminPanel', true);

        $this->core->getEntityManager()->getDatabaseBuilder()->build();
        $this->core->getEntityManager()->resetDatabaseBuilder();
        $this->modulesManager->installModules(true);
        $this->core->getEntityManager()->getDatabaseBuilder()->build();
        $this->core->getEntityManager()->load();

        $user = $users->createUser($data['userLogin'], $data['userEmail'], $data['userPassword'], $data['userIsAdmin']);
        if ($user !== false){
            if (!$user->addFlag('z')){
                throw new \RuntimeException('InstallApp::install: Cant add root flag to user');
            }
        }else{
            $query = $this->core->getEntityManager()->getEntityQuery('User');
            try{
                $user = $query->findByLogin($data['userLogin'])->loadOne(false, true);
                if (!$user){
                    throw new StatementExecuteError();
                }
            }catch (StatementExecuteError $e){
                var_dump('ERR');

            }

            $user->setRegistrDate(time());
            $user->setPassword($data['userPassword'], false);
            $user = $query->save($user, true);


            //throw new \RuntimeException('InstallApp::install: Cant create user');
        }
    }

    protected function clearData(){
        $this->core->getEntityManager()->cleanEntitiesData();
        $this->core->getRouter()->clear();
        $this->core->getCache()->clean();
        $this->core->getPhpDumper()->clear();
        $this->modulesManager->clear();
    }

    protected function buildEntities(){
        $builder = $this->core->getEntityManager()->getDatabaseBuilder();

        $ent = $builder->createEntity('route');
        $ent->addField('id', null, null)->addField('url')->addField('data',null, null, 'serialize', 'unserialize');
    }

    protected function buildResponse(){
        $format = strtoupper($this->core->getInput()->request('_format', 'HTML'));
        $supportedFormats = array('HTML', 'JSON');
        if (!in_array($format, $supportedFormats)){
            throw new \Exception(__CLASS__.': Response format: '.$format.' doesn\'t support');
        }
        $className = '\\Core\\Response\\'.$format.'Response';
        if (!class_exists($className)){
            throw new \Exception(__CLASS__.': Response class: '.$className. ' doesn\'t exists');
        }
        switch ($format){
            case 'HTML':
                $this->response = new HTMLResponse('site', 'install');
                break;
            case 'JSON':
                $this->response = new JSONResponse('site', 'index');
                break;
            default:
                http_response_code(415);
                throw new \Exception(__CLASS__.': Response format: '.$format.' doesn\'t support');
                break;
        }

        $this->response->setCore($this->core);
        $this->modulesManager->addModule('Core/Response', $this->response);
    }
} 
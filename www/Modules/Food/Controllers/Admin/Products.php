<?php
namespace Modules\Food\Controllers\Admin;


use Core\DataBase\Model\EntityQuery;
use Core\Debugger;
use Core\Module\Base\Controller;

class Products extends Controller {

    /**
     * @param $data
     * @param EntityQuery $query
     * @return \Core\DataBase\Model\EntityQuery
     */
    protected function buildQuery($data, $query){

        foreach ($data as $key=>$value){

            if (substr($key,0,1) == '_'){
                $m = substr($key,1);

                switch ($m){
                    case 'orderBy':
                        if (!is_array($value)){
                            Debugger::log(__CLASS__.'::buildQuery: orderBy value must be an array. Giving: '.gettype($value));
                            break;
                        }
                        foreach ($value as $stm){
                            if (!is_array($stm) || count($stm) != 2){
                                Debugger::log(__CLASS__.'::buildQuery: orderBy value item must be an array and have 2 elements.');
                                continue;
                            }
                            $methodName = 'orderBy'.$stm[0];
                            $query->$methodName($stm[1]);
                        }
                        break;
                    case 'groupBy':
                        if (!is_array($value)){
                            Debugger::log(__CLASS__.'::buildQuery: groupBy value must be an array. Giving: '.gettype($value));
                            break;
                        }
                        foreach ($value as $stm){
                            $methodName = 'groupBy'.$stm;
                            $query->$methodName();
                        }
                        break;
                    default:
                        Debugger::log(__CLASS__.'::buildQuery: unsupported special method: '.$m);
                        break;
                }
                continue;
            }
            $methodName = 'findBy'.$key;
            if (!is_null($query->getMetadata()->getRelationship($key)) && is_object($value)){
                //$relationshipQuery = $query->$methodName();
                $this->buildQuery($value, $query->$methodName());
                continue;
            }else{
                if (is_array($value)){
                    //Если 2 эл-та в массиве то 1-ый значение, а второй оператор.
                    //Если нет, то предполагаем, что там только один элемент и оператор - это равно
                    if (count($value) == 2){
                        $query->$methodName($value[0], $value[1]);
                    }else{
                        $query->$methodName($value[0]);
                    }
                    //call_user_func_array([$query, $methodName], [count($value) == 2 ? $value : [$value[0], '=']]);

                }elseif (is_scalar($value)){
                    $query->$methodName($value);
                }
                continue;
            }
            //throw new \RuntimeException(__CLASS__.'::buildQuery: can not build query condition for key: '.$key);
        }
        return $query;
    }

    public function getList(array $data){
        $input = $this->module->getCore()->getInput();
        $params = $input->get('params', null, TYPE_RAW);

        $params = is_null($params) ? new \stdClass() : json_decode($params);
        if (is_null($params) && json_last_error() !== JSON_ERROR_NONE){
            $view = $this->module->view('Admin\\Error');
            $view->render([
                'message'=>json_last_error_msg(),
                'code'=>500
            ]);
            return;
        }else if (is_null($params)){
            $params = new \stdClass();
        }

        $em = $this->module->getCore()->getEntityManager();
        $query = $em->getEntityQuery('Product');
        try{
            $query = $this->buildQuery($params, $query);
        }catch (\Exception $e){
            $view = $this->module->view('Admin\\Error');
            $view->render([
                'message'=>$e->getMessage(),
                'code'=>404
            ]);
            return;
        }

        $limit = $input->get('limit', new \stdClass(), TYPE_JSON);
        if (is_object($limit) && isset($limit->count) && (int) $limit->count > -1){
            $query->limit($limit->count, isset($limit->offset) && (int) $limit->offset > -1 ? $limit->offset : null);
        }
        $count = $input->get('count', false, TYPE_BOOL);
        if ($count){
            $view = $this->module->view('Admin\\Simple');
            $view->render($query->count());
        }else{
            $view = $this->module->view('Admin\\EntitiesList');
            $view->render($query->load());
        }
    }

    public function getInfo(array $data){
        $pid = $this->module->getCore()->getInput()->get('pId', 0, TYPE_INT);

        $em = $this->module->getCore()->getEntityManager();


        $query = $em->getEntityQuery('Product');
        $query->findById($pid);
        $product = $query->loadOne();


        $query = $em->getEntityQuery('ProductType');
        $productType = $query->load();

        $view = $this->module->view('Admin\\ProductItem');
        $view->render($product, $productType);
    }

    public function getTypes(array $data){
        $em = $this->module->getCore()->getEntityManager();
        $query = $em->getEntityQuery('ProductType');
        $productType = $query->load();
        $view = $this->module->view('Admin\\ProductTypes');
        $view->render($productType);
    }

    public function addItem(array $data){
        $em = $this->module->getCore()->getEntityManager();
        $ent = $em->getEntity('Product');

        $ent->setCategoryId($this->module->getCore()->getInput()->get('category', 0, TYPE_INT));

        $query = $em->getEntityQuery('ProductType');
        $productType = $query->load();
        $view = $this->module->view('Admin\\ProductItem');
        $view->render($ent, $productType);
    }

    public function saveProduct(array $data){
        /*
        $input = $this->module->getCore()->getInput();
        $product = $input->post('product', null, TYPE_STRING);
        */
        $params = json_decode(trim(file_get_contents('php://input')), true);
        $em = $this->module->getCore()->getEntityManager();
        $ent = $em->getEntity('Product');
        $ent->fromArray($params['product']);

        if (!$ent->getId()){
            $ent->setIsNew(true);
            $ent->setImage('/uploads/empty_img.jpg');
        }

        $result = $em->getEntityQuery('Product')->save($ent);

        $view = $this->module->view('Admin\\ProductSave');
        $view->render($params, $result);
    }

    public function deleteItem(array $data){
        $input = $this->module->getCore()->getInput();
        $pId = $input->get('productId', 0, TYPE_STRING);
        $em = $this->module->getCore()->getEntityManager();
        $query = $em->getEntityQuery('Product');
        $query->findById($pId);
        $result = $query->delete();
        $view = $this->module->view('Admin\\ProductDelete');
        $view->render($result);
    }

    public function changeImage(array $data){
        $input = $this->module->getCore()->getInput();
        $pId = $input->get('productId', 0, TYPE_STRING);

        $uploadFile = $this->uploadFile('file');
        if ($uploadFile['success']){
//            var_dump($_GET);
            $em = $this->module->getCore()->getEntityManager();
            $query = $em->getEntityQuery('Product');
            $query->findById($pId);
            $product = $query->loadOne();



            $product->setImage($uploadFile['filePath']);
            $query->save($product);
        }
/*
        $em = $this->module->getCore()->getEntityManager();
        $query = $em->getEntityQuery('Product');
        $query->findById($pId);
        $product = $query->loadOne();



        $product->setImage($imageUrl);
        $query->save($product);
        */

        $view = $this->module->view('Admin\\ProductUpdateImage');
        $view->render($uploadFile);
    }

    protected function uploadFile($key, $index = null){
        $return = array('success' => false, 'error' => '', 'id' => 0);
        $input = $this->module->getCore()->getInput();
        $data = $input->files($key);
        $return['data'] = $data;
        if (!$data){
            //QSLog('FileManager: Ошибка при получение данных о файле с ключом '.$key);
            $return['error'] = 'Ошибка при получение данных о файле с ключом '.$key;
            return $return;
        }
        $error = (!is_null($index) && $index >= 0) ? $data['error'][$index] : $data['error'];
        $name = (!is_null($index) && $index >= 0) ? $data['name'][$index] : $data['name'];
        if (!$name){
            $return['success'] = true;
            return $return;
        }
        //var_dump($error);
        //var_dump(UPLOAD_ERR_OK);
        if ($error == UPLOAD_ERR_OK){
            $tmp_name = (!is_null($index) && $index >= 0) ? $data['tmp_name'][$index] : $data['tmp_name'];
            $type = (!is_null($index) && $index >= 0) ? $data['type'][$index] : $data['type'];
            $size = (!is_null($index) && $index >= 0) ? $data['size'][$index] : $data['size'];
            $image_info = getimagesize($tmp_name);
            $return['imgInf'] = $image_info;
            if($image_info["mime"] != "image/gif" && $image_info["mime"] != "image/jpeg" && $image_info["mime"] !="image/png") {
                //QSLog("FileManager: Не удалось загрузить файл формата: {$image_info['mime']}",5);
                $return['error'] = "Не удалось загрузить файл формата: {$image_info['mime']}";
                return $return;
            }
            $mime = explode("/",$image_info["mime"]);
            $uploadsDir = $this->module->getCore()->getConfig()->get('paths.upload', 'uploads');
            //$upl_path = QS_UPLOAD_PATH.$mime[0].'/';
            $upl_path = QS_path([$uploadsDir, $mime[0]]);
            //$upl_dir = QS_UPLOAD_DIR.'/'.$mime[0].'/';
            $upl_dir = QS_path([$uploadsDir, $mime[0]], true, false, false);
            //Проверяем, есть ли папка для загрузки, если нет - создаем
            if (!file_exists($upl_path)){
                if (!mkdir($upl_path, 0644, true)){
                    //QSLog('FileManager: Не удалось создать папку: '.$upl_dir);
                    $return['error'] = 'Не удалось создать папку: '.$upl_dir;
                    return $return;
                }
            }
            $file_name = md5(time().$name.rand(0,1000)).".{$mime[1]}";
            if (move_uploaded_file($tmp_name, $upl_path.$file_name)) {

                $fullPath = $upl_dir.$file_name;

                $return['success']=true;
                $return['filePath'] = $fullPath;
                return $return;
            }else{
                //QSLog('FileManager: Не удалось переместить файл: '.$name, 5);
                $return['error'] = 'Не удалось переместить файл: '.$name;
                return $return;
            }
        }
        //QSLog('FileManager: Не удалось загрузить файл: '.$name );
        $return['error'] = self::getError($error);
        return $return;
    }

    public static function getError($code){
        switch ($code){
            case UPLOAD_ERR_OK:
                return 'Нет ошибок.';
            case UPLOAD_ERR_INI_SIZE:
                return 'Размер принятого файла превысил максимально допустимый размер, который задан директивой upload_max_filesize конфигурационного файла php.ini.';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме.';
            case UPLOAD_ERR_PARTIAL:
                return 'Загружаемый файл был получен только частично.';
            case UPLOAD_ERR_NO_FILE:
                return 'Файл не был загружен.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Отсутствует временная папка.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Не удалось записать файл на диск.';
            case UPLOAD_ERR_EXTENSION:
                return 'PHP-расширение остановило загрузку файла.';
            default:
                return 'Неизвестная ошибка.';
        }
    }
}

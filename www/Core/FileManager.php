<?php
namespace Core;


class FileManager{

    public static function uploadFile($key, $index = null){
        $return = array('success' => false, 'error' => '', 'id' => 0);
        //$input = QSCore::getApplication()->input;
        $data = $_FILES[$key];
        if (!$data){
            Debugger::log('FileManager: Ошибка при получение данных о файле с ключом '.$key);
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
            if($image_info["mime"] != "image/gif" && $image_info["mime"] != "image/jpeg" && $image_info["mime"] !="image/png") {
                Debugger::log("FileManager: Не удалось загрузить файл формата: {$image_info['mime']}",5);
                $return['error'] = "Не удалось загрузить файл формата: {$image_info['mime']}";
                return $return;
            }
            $mime = explode("/",$image_info["mime"]);
            $upl_path = QS_UPLOAD_PATH.$mime[0].DIRECTORY_SEPARATOR;
            $upl_dir = QS_UPLOAD_DIR.DIRECTORY_SEPARATOR.$mime[0].DIRECTORY_SEPARATOR;
            //Проверяем, есть ли папка для загрузки, если нет - создаем
            if (!file_exists($upl_path)){
                if (!mkdir($upl_path, 0770, true)){
                    Debugger::log('FileManager: Не удалось создать папку: '.$upl_dir);
                    $return['error'] = 'Не удалось создать папку: '.$upl_dir;
                    return $return;
                }
            }
            $file_name = md5(microtime(true).$name).".{$mime[1]}";
            //$file_name = QSCore::getApplication()->getHash(time().$name.rand(0,1000)).".{$mime[1]}";
            if (move_uploaded_file($tmp_name, $upl_path.$file_name)) {
                /*$db = QSCore::getDB();
                $query = $db->getQuery();
                $query->insertIgnore('files')->columns('type, path, upload_by');
                $query->values(implode(', ', array($db->quote($mime[0]), $db->quote($upl_dir.$file_name), $db->quote(QSCore::getUser()->get('id')))));
                try{
                    $db->execute($query);
                    $id = $db->insertId();
                }catch (Exception $e){
                    QSLog('FileManager: Не удалось загрузить файл: '.$e->getMessage());
                    $return['error'] = 'Не удалось загрузить файл: '.$e->getMessage();
                    return $return;
                }
                $return['id'] = $id;*/
                $return['path'] = $upl_dir.$file_name;
                $return['success']=true;
                return $return;
            }else{
                Debugger::log('FileManager: Не удалось переместить файл: '.$name, 5);
                $return['error'] = 'Не удалось переместить файл: '.$name;
                return $return;
            }
        }
        Debugger::log('FileManager: Не удалось загрузить файл: '.$name );
        $return['error'] = self::getError($error);
        return $return;
    }

    /*
    public static function deleteFile($id){
        if ($file_info = self::getFile($id)){
            if (unlink(QS_BASE_PATH.'/'.$file_info['path'])){
                $db = QSCore::getDB();
                $query = $db->getQuery();
                $query->delete('files')->where('id = '.$db->quote($id));
                try{
                    $db->execute($query);
                }catch (Exception $e){
                    QSLog('FileManager: Ошибка при удаление файла из БД: '.$e->getMessage());
                    return false;
                }
                return true;
            }
            QSLog('FileManager: Ошибка при удаление файла с диска.');
        }
        return false;
    }

    public static function getFile($id){
        $db = QSCore::getDB();
        $query = $db->getQuery();
        $query->select('id, type, upload_time, path, upload_by')->from('files')->where('id = '.$db->quote($id))->limit(0,1);
        try{
            $result = $db->execute($query);
            $data = $result->fetch_assoc();
        }catch (Exception $e){
            QSDebugger::log('FileManager: Не удалось получить информацию о файле: '.$e->getMessage());
            return false;
        }
        if (!file_exists(QS_BASE_PATH.$data['path'])){
            $query->clear();
            $query->delete('files')->where('id = '.$db->quote($data['id']));
            $db->execute($query);
            unlink(QS_BASE_PATH.$data['path']);
            return false;
        }
        return $data;
    }
    */

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
<?php
namespace Modules\Users\Entities;



use Core\DataBase\Exception\StatementExecuteError;
use \Core\DataBase\Model\Entity;
use Core\Debugger;

class User extends Entity
{
	protected function init(){}


    /**
     * @param string $input
     * @return bool
     */
    public function hasFlag($input){
        $flags = $this->getFlags();
        foreach ($flags as $flag){
            $key =$flag->getFlag()->getFlag();
            if ($key == $input || $key == 'z'){
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $flag
     * @return bool
     */
    public function addFlag($flag){
        if ($this->isNew() || !$this->getId()){
            Debugger::log('User::addFlag: Cant add flag to new or empty user.');
            return false;
        }
        $accessFlagQuery = $this->entityManager->getEntityQuery('AccessFlag');
        try{
            $flagEnt = $accessFlagQuery->findByFlag($flag)->loadOne(false, true);
            if (!is_null($flag)){
                $userFlag = $this->entityManager->getEntity('UserFlags');
                $userFlag->setUserId($this->getId());
                $userFlag->setFlagId($flagEnt->getId());
                try{
                    $this->entityManager->getEntityQuery('UserFlags')->save($userFlag, true);
                    return true;
                }catch (StatementExecuteError $e){
                    Debugger::log('User::addFlag: Cant add flag: '.$flag.' to user id:'.$this->getId().'. Data:'.implode(', ', $e->getErrorData()));
                    return false;
                }
            }
        }catch (StatementExecuteError $e){
            Debugger::log('User::addFlag: Cant load flag: '.$flag.'. Data:'.implode(', ', $e->getErrorData()));
        }
        return true;
    }

    public function setPassword($password, $hashed=true, $salt=null){
        if (!$hashed){
            $password = $this->entityManager->getCore()->getCrypt()->blowfish($password, 11, is_null($salt) ? 'UNIQUE' : $salt, true);
        }
        $this->properties['Password'] = $password;
    }
}

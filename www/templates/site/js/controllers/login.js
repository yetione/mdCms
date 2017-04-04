var LoginPageController = function($scope, BackendService, $idialog, UsersService){
    var self = this;

    self.userData = {};
    self.currentUser = {};
    self.loginInProgress = false;

    self.doLogin = doLogin;

    activate();
    function activate(){
        self.userData = {
            Email:'',
            Password:'',
            RememberMe:false
        };
        UsersService.getCurrentUser().then(function(user){
            self.currentUser = user;
        });
    }

    function doLogin(){
        if (!self.userData.Email || !self.userData.Password){
            $idialog('common/message', {dialogId:'formNotValid', options:{header:'Ошибка', message:'Не заполны поля'}});
            return false;
        }
        self.loginInProgress = true;
        BackendService.send(self.userData,{module:'Users', controller:'Auth', action:'login'}).then(function(response){
            var responseData = response.data;
            if (responseData.status != 'OK'){
                console.error('LoginPageController::doLogin: Login error.', response);
                $idialog('common/message', {dialogId:'errorDialog', options:{header:'Ошибка', message:'Возникла ошибка при регистрации нового пользователя.'}});
                return response;
            }
            console.log(responseData);
            self.loginInProgress = false;
            var result = responseData.data;
            switch  (result.code){
                case 0:
                    self.currentUser = result.user;
                    UsersService.setCurrentUser(self.currentUser);
                    break;
                default :
                    $idialog('common/message', {dialogId:'errorDialog', options:{header:'Ошибка', message:result.message == '' ? 'Серверная ошибка.' : result.message}});
                    console.error('LoginPageController::doLogin: Error in registration of new user.', response);
                    break;
            }
            return response;
        });
    }

    /**
     * 0 - Все в порядке
     * 1 - Не заполнены обязателные поля
     * 2 - Неправильная длина пароля
     * 3 - Пароль и подтверждение не совпадают
     * @returns {number}
     */
    function checkUserData(){
        if (!self.userData.Name || !self.userData.Email || !self.userData.Password || !self.userData.PasswordConfirm || !self.userData.ReCaptcha){
            return 1;
        }else if (self.userData.Password.length < self.minPasswordLength || self.userData.Password.length > self.maxPasswordLength){
            return 2;
        }else if (self.userData.Password !== self.userData.PasswordConfirm){
            return 3;
        }else{
            return 0;
        }
    }

    function getErrorMessage(code){
        switch (code){
            case 0:
                return 'Ошибок нет.';
            case 1:
                return 'Не заполнены обязательные поля.';
            case 2:
                return 'Длина пароля должна быть от '+self.minPasswordLength+' до '+self.maxPasswordLength+' символов.';
            case 3:
                return 'Пароль и подтверждение не совпадают.';
            default:
                return 'Неизвестная ошибка.';
        }
    }
};

siteApp.controller('LoginPageController', ['$scope', 'BackendService', '$idialog', 'UsersService', LoginPageController]);
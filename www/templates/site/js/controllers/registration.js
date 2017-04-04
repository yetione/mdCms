var RegistrationPageController = function($scope, BackendService, $idialog, UsersService, $window){
    var self = this;

    self.userData = {};
    self.phoneMask = '+7 (999) 999-99-99';
    self.minPasswordLength = 4;
    self.maxPasswordLength = 100;
    self.currentUser = {};
    self.successRegistration = false;
    self.registrationInProgress = false;
    self.reCaptchaWidgetId = '';

    self.doRegistration = doRegistration;
    self.onReCaptchaCreated = onReCaptchaCreated;

    activate();
    function activate(){
        self.userData = {
            Name:'',
            Surname:'',
            Patronymic:'',
            Phone:'',
            Email:'',
            Password:'',
            PasswordConfirm:'',
            ReCaptcha:''
        };
        UsersService.getCurrentUser().then(function(user){
            self.currentUser = user;
        });
    }

    function doRegistration(){
        if (checkUserData()){
            $idialog('common/message', {dialogId:'formNotValid', options:{header:'Ошибка', message:getErrorMessage(checkUserData())}});
            return;
        }
        self.registrationInProgress = true;
        BackendService.send(self.userData, {module:'Users', controller:'Auth', action:'doRegistration'}).then(function(response){
            var responseData = response.data;
            if (responseData.status != 'OK'){
                console.error('RegistrationPageController::doRegistration: Error in registration of new user.', response);
                $idialog('common/message', {dialogId:'errorDialog', options:{header:'Ошибка', message:'Возникла ошибка при регистрации нового пользователя.'}});
                return response;
            }
            self.registrationInProgress = false;
            var result = responseData.data;
            switch (result.code){
                case 0:
                    self.currentUser = result.user;
                    UsersService.setCurrentUser(self.currentUser);
                    self.successRegistration = true;
                    $idialog('common/message', {dialogId:'errorDialog', options:{title:'Сообщение', message:'Регистрация завершена.', okCb:function (scope) {
                        scope.hide();
                        //$window.location = result.redirect_url;
                        console.log(result.redirect_url);
                    }}});
                    break;
                default :
                    $idialog('common/message', {dialogId:'errorDialog', options:{header:'Ошибка', message:result.message == '' ? 'Серверная ошибка.' : result.message}});
                    self.userData.ReCaptcha = '';
                    grecaptcha.reset(self.reCaptchaWidgetId);
                    console.error('RegistrationPageController::doRegistration: Error in registration of new user.', response);
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

    function onReCaptchaCreated(widgetId){
        self.onReCaptchaCreated = widgetId;
    }


};

siteApp.controller('RegistrationPageController', ['$scope', 'BackendService', '$idialog', 'UsersService', '$window', RegistrationPageController]);
var UserbarController = function($scope, UsersService, $idialog, BackendService, $timeout, $window){
    var self = this;
    self.currentUser = {};
    self.vkLoginLink = '';
    self.fbLoginLink = '';
    self.showLogin = showLogin;




    activate();
    function activate(){
        UsersService.getCurrentUser().then(function(user){
            self.currentUser = user;
        });

        UsersService.getVkLoginLink().then(function (link) {
            self.vkLoginLink = link['vk'];
            self.fbLoginLink = link['fb'];
        });
    }

    function showLogin() {
        $idialog('login-dialog',{dialogId:'LoginDialog', options:{
            User:{Email:'',Password:''},
            VkLoginLink:self.vkLoginLink,
            FbLoginLink:self.fbLoginLink,
            formSubmit:function(scope){
                if (!this.User.Email || !this.User.Password){
                    $idialog('common/message', {dialogId:'formNotValid', options:{header:'Ошибка', message:'Не заполны поля'}});
                    return false;
                }
                self.loginInProgress = true;
                BackendService.send(this.User,{module:'Users', controller:'Auth', action:'login'}).then(function(response){
                    var responseData = response.data;
                    if (responseData.status != 'OK'){
                        console.error('LoginPageController::doLogin: Login error.', response);
                        $idialog('common/message', {dialogId:'errorDialog', options:{header:'Ошибка', message:'Возникла ошибка при регистрации нового пользователя.'}});
                        return response;
                    }
                    self.loginInProgress = false;
                    var result = responseData.data;
                    switch  (result.code){
                        case 0:
                            self.currentUser = result.user;
                            UsersService.setCurrentUser(self.currentUser);
                            $idialog('common/message', {dialogId:'errorDialog', options:{header:'Сообщение', message:result.message == '' ? 'Вход выполнен.' : result.message}});
                            $timeout(function(){
                                window.location.reload();
                            }, 2000);
                            scope.hide();
                            break;
                        default :
                            $idialog('common/message', {dialogId:'errorDialog', options:{header:'Ошибка', message:result.message == '' ? 'Серверная ошибка.' : result.message}});
                            console.error('LoginPageController::doLogin: Error in registration of new user.', response);
                            break;
                    }
                    return response;
                });
            }
        }})
    }

};

siteApp.controller('UserbarController', ['$scope', 'UsersService', '$idialog', 'BackendService', '$timeout', '$window', UserbarController]);
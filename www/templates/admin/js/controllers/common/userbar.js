var UserbarController = function ($scope, UsersService){
    var self = this;
    self.currentUser = {};

    activate();
    function activate(){
        UsersService.getCurrentUser().then(function(user){
            self.currentUser = user;
        });
    }
};


adminApp.controller('common.userBarController', ['$scope', 'UsersService', UserbarController]);
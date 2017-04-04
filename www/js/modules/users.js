(function (windows, angular) {
    var UsersService = function($rootScope, BackendService, $q, $timeout){
        var self = this;
        self.currentUser = false;
        self.config = {};

        self.run = run;
        self.setCurrentUser = setCurrentUser;
        self.getCurrentUser = getCurrentUser;
        self.getVkLoginLink = getVkLoginLink;
        self.getOrders = getOrders;

        var currentUserExtend = {
            Addresses:[],
            getAddresses:function(update){
                var deferred = $q.defer();
                var currentUser = this;
                var f = function(){
                    if (!currentUser.Addresses && !update){
                        deferred.resolve(currentUser.Addresses);
                    }else{
                        BackendService.get({module:'Users', controller:'UserService', action:'getCurrentUserAddresses'}).then(function(response){
                            var responseData = response.data;
                            if (responseData.status == 'OK'){
                                currentUser.setAddresses(responseData.data);
                                deferred.resolve(currentUser.Addresses);
                            }else{
                                deferred.reject(response);
                            }
                        }, function(response){deferred.reject(response);});
                    }
                };
                $timeout(f);
                return deferred.promise;
            },
            setAddresses:function(addresses){
                this.Addresses = addresses;
            },
            saveAddress:function(address){
                var deferred = $q.defer();
                var f = function () {
                    if (address._original){
                        delete  address._original;
                    }
                    address.UserId = self.currentUser.Id;
                    BackendService.get({data:address, module:'Users', controller:'UserService', action:'saveCurrentUserAddress'}).then(function(response){
                    //BackendService.send({data:address}, {module:'Users', controller:'UserService', action:'saveCurrentUserAddress'}).then(function(currentUser){
                        var responseData = response.data;
                        if (responseData.status == 'OK'){
                            this.Addresses = responseData.data;
                            deferred.resolve(this.Addresses);
                        }else{
                            deferred.reject(response);
                        }
                    }, function (response){deferred.reject(response)});
                };
                $timeout(f);
                return deferred.promise;
            },
            deleteAddress:function(address){
                var deferred = $q.defer();
                var f = function () {
                    BackendService.get({data:address, module:'Users', controller:'UserService', action:'deleteCurrentUserAddress'}).then(function(response){
                        var responseData = response.data;
                        if (responseData.status == 'OK'){
                            console.log('delete',response);
                            this.Addresses = responseData.data;
                            deferred.resolve(this.Addresses);
                        }else{
                            deferred.reject(response);
                        }
                    }, function (response){deferred.reject(response)});
                };
                $timeout(f);
                return deferred.promise;
            }

        };

        function run(configs){
            angular.extend(self.config, configs);
            $rootScope.$broadcast('UsersService:run', this);
        }

        function getCurrentUser(update){
            var deferred = $q.defer();
            var f = function(){
                if (self.currentUser && !update){
                    deferred.resolve(self.currentUser);
                }else{
                    BackendService.get({module:'Users', controller:'UserService', action:'getCurrentUser'}).then(function(response){
                        var responseData = response.data;
                        if (responseData.status == 'OK'){
                            self.setCurrentUser(responseData.data);
                            deferred.resolve(self.currentUser);
                        }else{
                            deferred.reject(response);
                        }
                    }, function(response){deferred.reject(response);});
                }
            };
            $timeout(f);
            return deferred.promise;
        }

        function setCurrentUser(user){
            user = angular.extend(user, currentUserExtend);
            self.currentUser = user;
            $rootScope.$broadcast('UsersService:currentUserChanged', {User:self.currentUser});
        }

        function getVkLoginLink() {
            var deferred = $q.defer();
            var f = function(){
                BackendService.get({module:'Users', controller:'UserService', action:'getVkLoginLink'}).then(function(response){
                    var responseData = response.data;
                    if (responseData.status == 'OK'){
                        deferred.resolve(responseData.data);
                    }else{
                        deferred.reject(response);
                    }
                }, function(response){deferred.reject(response);});
            };
            $timeout(f);
            return deferred.promise;
        }

        function getOrders() {
            var deferred = $q.defer();
            var f = function(){
                BackendService.get({module:'Users', controller:'UserService', action:'getUserOrders'}).then(function(response){
                    var responseData = response.data;
                    if (responseData.status == 'OK'){
                        deferred.resolve(responseData.data);
                    }else{
                        deferred.reject(response);
                    }
                }, function(response){deferred.reject(response);});
            };
            $timeout(f);
            return deferred.promise;
        }
    };



    var CurrentUser = function ($rootScope, UserService, BackedService, $q, $timeout) {
        var self = this;
        self.user = {};

        self.getUser = getUser;
        $rootScope.$on('UsersService:run', onUserServiceRun);
        function onUserServiceRun(event, data) {
            UserService.getCurrentUser().then(function(user){
                self.user = user;
            });
        }

        function getUser() {return self.user;}

        function getAddreses() {

        }
    };

    angular.module('mdUsers', ['mdBackend'])
        .service('UsersService', ['$rootScope', 'BackendService', '$q', '$timeout', UsersService])
        .service('CurrentUser', ['UserService', 'BackendService', '$q', '$timeout', CurrentUser]);
})(window, window.angular);
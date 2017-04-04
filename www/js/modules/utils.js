(function (windows, angular) {
    var Utils = function($rootScope, BackendService, $q, $timeout){
        var self = this;
        self.config = {};
        self.generateUrl = generateUrl;

        function generateUrl(str) {
            var deferred = $q.defer();
            var f = function () {
                BackendService.get({module:'Kernel', controller:'Utils', action:'generateUrl', data:str}).then(function(response){
                    var responseData = response.data;
                    if (responseData.status == 'OK'){
                        deferred.resolve(responseData.data);
                    }else{
                        deferred.reject(responseData);
                    }
                }, function(response){deferred.reject(response);});
            };
            $timeout(f);
            return deferred.promise;
        }

    };

    angular.module('mdUtils', ['mdBackend'])
        .service('Utils', ['$rootScope', 'BackendService', '$q', '$timeout', Utils]);
})(window, window.angular);
(function (windows, angular) {
    var FileManagerService = function ($rootScope, $q, $timeout, BackendService) {
        var self = this;

        self.getDirectory = getDirectory;

        activate();
        function activate() {

        }

        function getDirectory() {
            var deferred = $q.defer();
            var f = function(){
                BackendService.get({module:'FileManager', controller:'Directory', action:'getDirectory', Directory:''}).then(function(response){
                    var responseData = response.data;
                    console.log(responseData);
                }, function(response){
                    deferred.reject(response);
                });
            };
            $timeout(f);
            return deferred.promise;
        }
    };

    var FileManagerSelectFileDialogController = function ($scope, $rootScope, FileManagerService) {
        var self = this;

        activate();
        function activate() {

        }
    };

    angular.module('mdFileManager', ['mdBackend'])
        .service('FileManagerService', ['$rootScope', '$q', '$timeout', 'BackendService', FileManagerService])
        .controller('mdFileManager.SelectFile', ['$scope', '$rootScope', 'FileManagerService', FileManagerSelectFileDialogController]);
})(window, window.angular);
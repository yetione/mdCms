(function (windows, angular) {
    var BackendService = function($rootScope, $http, $httpParamSerializer){
        var self = this;
        self.config = {
            jsonPath:'json.php'
        };
        self.run = function(configs){
            angular.extend(self.config, configs);
            $rootScope.$broadcast('DataService:run', this);
        };
        self.get = function(params){
            return $http.get(self.config.jsonPath, {params:params});
        };

        self.send = function(data, params){
            return $http.post(self.config.jsonPath, data, {params:params});
        };

        self.load = function(url, params){
            return $http.get(url, {params:params});
        };

        self.buildUrl = function(url, params){
            var serializedParams = $httpParamSerializer(params);

            if (serializedParams.length > 0) {
                url += ((url.indexOf('?') === -1) ? '?' : '&') + serializedParams;
            }
            return url;
        }
    };

    var UrlGenerator = function($httpParamSerializer){
        function build(){

        }
    };

    angular.module('mdBackend', [])
        .service('BackendService', ['$rootScope', '$http', '$httpParamSerializer', BackendService]);
})(window, window.angular);
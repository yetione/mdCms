adminApp.service('adminDataService', ['$http', '$location', 'UrlConfigs', function($http, $location, urlConfigs){
    var separator = '/';
    var jsonPath = urlConfigs.buildUrl('admin/json.php');
    this.get = function(params){
        return $http.get(jsonPath, {params:params});
    };

    this.http = function(){
        return $http;
    };

    this.send = function(data, params){
        return $http.post(jsonPath, data, {params:params});
    };

    this.buildRequestString = function(module,controller, action){
        return module+separator+controller+separator+action;
    };

    this.menu = function(module, controller, action, params){
        return this._go('menu', module, controller, action, params);
    };

    this.center = function(module, controller, action, params){
        console.log('12333');

        return this._go('center', module, controller, action, params);
    };

    this._go = function(column, module, controller, action, params){
        var request = module+separator+controller+separator+action;

        //$location.search(column, request);


        params = params || {};
        params.request = request;
        params.column = column;

        return $http.get(jsonPath, {params:params/*,
            transformRequest:function(data, headersGetter){
                console.log('resp',data, headersGetter);
                return angular.toJson(data);
            }, transformResponse:function(data, headersGetter){
                console.log(data, headersGetter);
                return angular.fromJson(data);
            }*/
        });
    }

}]);
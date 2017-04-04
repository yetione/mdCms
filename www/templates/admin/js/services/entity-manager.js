adminApp.service('entityManager', ['$http', '$location', 'UrlConfigs', function($http, $location, urlConfigs){
    var separator = '/';
    var self = this;
    var jsonPath = urlConfigs.buildUrl('admin/json.php');

    this.getItem = function(entity, params){
        var requestParams = {
            module:'Restful',
            controller:'Entity',
            action:'getItem',
            entity:entity,
            params:angular.toJson(params)
        };
        return self.get(requestParams);
    };

    this.getList = function(entity, params){
        var requestParams = {
            module:'Restful',
            controller:'Entity',
            action:'getList',
            entity:entity,
            params:angular.toJson(params)
        };
        return self.get(requestParams);
    };

    this.appendTransformRequest = function(transform){
        var defaults = $http.defaults.transformRequest;
        // We can't guarantee that the default transformation is an array
        defaults = angular.isArray(defaults) ? defaults : [defaults];
        // Append the new transformation to the defaults
        return defaults.concat(transform);
    };

    this.appendTransformResponse = function(transform){
        var defaults = $http.defaults.transformResponse;
        // We can't guarantee that the default transformation is an array
        defaults = angular.isArray(defaults) ? defaults : [defaults];
        // Append the new transformation to the defaults
        return defaults.concat(transform);

    };

    this.get = function(params){
        return $http.get(jsonPath, {
            params:params,
            transformResponse:self.appendTransformResponse(function(value){
                if (value.status === 'OK'){
                    return value.result;
                }
                return null;
            })
        });
    };

    this.send = function(data, params){
        return $http.post(jsonPath, data, {params:params});
    };

    this.getEmptyEntity = function(name){
        
    }

}]);
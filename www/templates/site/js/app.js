'use strict';

/* App Module */

var config = function(){
    var baseUrl = '/';
    function _getUrl(url, parameters){
        if (!angular.isString(url)){
            console.error('UrlConfig::buildUrl: URL must be a string.');
            return '';
            //throw new Error('UrlConfig::buildUrl: URL must be a string.');
        }
        parameters = parameters || {};
        if (!angular.isObject(parameters)){
            console.error('UrlConfig::buildUrl: parameters must be a object.');
        }
        var ret = [];
        for (var d in parameters)
            ret.push(encodeURIComponent(d) + "=" + encodeURIComponent(parameters[d]));
        return (url.substr(0,1) == '/'? '' :baseUrl)+url+(ret.length > 0 ? '?'+ret.join('&') : '');
    }
    return {
        buildUrl:_getUrl
    };
};


var siteApp = angular.module('SektaFoodApp', ['ngAnimate', 'ngCookies', 'ngSanitize', 'idialog', 'mdBackend', 'mdEntity', 'mdGeoLocation', 'mdCart', 'mdDialogs', 'mdUsers', 'ui.mask', 'ui.select', 'vcRecaptcha', 'ngKladr'])
    .value('UrlConfigs', config())
    .run(['BackendService', 'UsersService', 'UrlConfigs', 'KladrService', 'GeoLocationService',  function(BackendService, UsersService, UrlConfigs, KladrService, GeoLocationService){
        BackendService.run({jsonPath:UrlConfigs.buildUrl('json.php')});
        UsersService.run();
        KladrService.run({token:'57daa1ab0a69de16308b4576'});
        GeoLocationService.run();
        moment().locale('ru');

}]).config(['$locationProvider', function($locationProvider){
        $locationProvider.html5Mode(true);
    }]);
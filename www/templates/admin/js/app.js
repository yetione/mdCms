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


var adminApp = angular.module('adminApp', ['angularFileUpload', 'idialog', 'datePicker', 'ngAnimate', 'mdBackend', 'mdEntity', 'mdGeoLocation', 'mdCart', 'mdDialogs', 'mdUsers', 'mdUtils', 'ckeditor', 'mdFileManager', 'dndLists', 'ui-notification', 'mdShop', 'ngKladr','ui.mask', 'mdRepeatParser']).value('UrlConfigs', config())
    .run(['BackendService', 'UrlConfigs', 'UsersService', 'MenuEntityService', 'KladrService', 'GeoLocationService',  function(BackendService, UrlConfigs, UsersService, MenuEntityService, KladrService, GeoLocationService){
        BackendService.run({jsonPath:UrlConfigs.buildUrl('json.php')});
        UsersService.run();
        KladrService.run({token:'57daa1ab0a69de16308b4576'});
        GeoLocationService.run();
        MenuEntityService.run();
        moment().locale('ru');
    }]);
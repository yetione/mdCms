var PopupConfigs = {
    wrapperTemplate:'templates/admin/templates/popup/index.html',
    cacheKey: 'Popup.cache'
};
adminApp.value('PopupConfigs', PopupConfigs);
var PopupFactory = function(popupConfigs, urlConfigs, $cacheFactory, dataService, $q, $timeout){
    var cache = $cacheFactory(popupConfigs.cacheKey);
    var popupWrapperTemplate = urlConfigs.buildUrl(popupConfigs.wrapperTemplate);
    var popups = [];

    var Popup = function(template, controller){
        var self = this;
        this.setTemplate(template);


    };

    Popup.prototype.setTemplate = function(template, isAbsolutePath){
        if (typeof  template === 'string'){
            if (typeof isAbsolutePath !== 'boolean') isAbsolutePath = false;
            this.template = isAbsolutePath ? template : urlConfigs.buildUrl(template);
            return true;
        }
        console.error('Popup::setTemplate: template must be a string.');
        return false;
    };

    Popup.prototype.render = function(){

    };

    Popup.prototype.loadTemplateContent = function(){
        var self = this;
        var deferred = $q.defer();
        $timeout(function(){
            var templateContent = cache.get(self.template);
            if (typeof templateContent !== 'string'){
                dataService.http().get(self.template).success(function(response){
                    templateContent = response;
                    deferred.resolve(templateContent);
                }).error(function(response, code){
                    console.error('Popup::loadTemplateContent: Error while loading template: ' + self.template);
                    deferred.reject('Error when try to load from server');
                });
            }else{
                deferred.resolve(templateContent);
            }
        });
        return deferred.promise;
    }



};
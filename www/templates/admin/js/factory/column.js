var ColumnFactory = function (backendService, urlConfigs, $cacheFactory, $rootScope, $compile, $q, $timeout){
    var applyTemplate = function(templateContent, columnElement, parentScope){
        columnElement.html(templateContent);
        var newScope = typeof parentScope !== 'undefined' ? parentScope : $rootScope.$new(true);
        $compile(columnElement)(newScope);
    };

    var _columns = {};

    var ColumnObject = function(elementId, cacheId, parentScope){
        var self = this;
        var docElem = document.getElementById(elementId);
        if (!docElem){
            console.error('ColumnObject::constructor: can not find element with id: '+elementId);
        }else {
            this.$element = angular.element(docElem);
            this.elementId = elementId;
        }
        this.parentScope = parentScope;
        this.template = undefined;
        this.cacheId = typeof cacheId === 'string' && cacheId.length > 0 ? cacheId : elementId;
        this.cache = $cacheFactory(this.cacheId);
        this.cache.removeAll();
    };

    /**
     *
     * @param {string} template
     * @param {boolean} isAbsolutePath
     * @returns {boolean}
     */
    ColumnObject.prototype.setTemplate = function(template, isAbsolutePath){
        if (typeof  template === 'string'){
            if (typeof isAbsolutePath !== 'boolean') isAbsolutePath = false;
            this.template = isAbsolutePath ? template : urlConfigs.buildUrl(template);
            return true;
        }
        console.error('ColumnObject::setTemplate: template must be a string.');
        return false;
    };

    /**
     *
     * @param {string} template Relative template path
     * @returns {IPromise<T>}
     */
    ColumnObject.prototype.show = function(template){
        var self = this;
        var deferred = $q.defer();


        var f = function(){
            if (typeof template !== 'string' || template.length == 0){
                console.error('ColumnObject::show: template must be nit empty string.');
                deferred.reject('Template is not a string or is empty string');
            } else if (urlConfigs.buildUrl(template) === self.template) {
                deferred.resolve(self.parentScope);
            } else if (!self.setTemplate(template)){
                deferred.reject('Can not set template');
                //return deferred.promise;
            } else {
                var templateContent = self.getTemplateFromCache(self.template);
                if (typeof templateContent !== 'string'){
                    backendService.http().get(self.template,{cache:false}).success(function(response){
                        templateContent = response;
                        self.cache.put(self.template, templateContent);
                        applyTemplate(templateContent, self.$element, self.parentScope);
                        deferred.resolve(self.parentScope);
                    }).error(function(response, code){
                        console.error('ColumnObject::show: Error while loading template: ' + self.template);
                        deferred.reject('Error when try to load from server');
                    });
                }else{
                    applyTemplate(templateContent, self.$element, self.parentScope);
                    deferred.resolve(self.parentScope);
                }
            }
        };
        $timeout(f);

        return deferred.promise;
    };

    /**
     * @param {string} template
     * @returns {undefined|string}
     */
    ColumnObject.prototype.getTemplateFromCache = function(template){
        return this.cache.get(template);
    };

    return function(elementId, cacheId, parentScope){
        if (!(elementId in _columns)){
            _columns[elementId] = new ColumnObject(elementId, cacheId, parentScope);
        }
        return _columns[elementId];
    };
};

adminApp.factory('ColumnFactory', ['adminDataService', 'UrlConfigs', '$cacheFactory', '$rootScope', '$compile', '$q', '$timeout', ColumnFactory]);
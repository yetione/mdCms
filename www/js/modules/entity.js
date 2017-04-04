(function (windows, angular) {


    var EntityFactory = function(backend, $q, $timeout){

        var self = this;
        self.instances = {};

        var Entity = function (name){
            this.name = name;
            this._idPool = {};
            this._entities = [];
            this._empty = false;
            this._allLoaded = false;
        };

        Entity.prototype.getById = function(id){
            if (isNaN(parseInt(id))){
                throw  new Error(this.name+' Entity.getById::id must be integer. '+id+' is giving');
            }
            return id in this._idPool ? this._idPool[id] : false;
        };

        Entity.prototype.loadById = function(id){
            var deferred = $q.defer(), self = this;
            //нужно для того, что бы вернуть промис, а потом уже выполнить действия
            var f = function(){
                if (isNaN(parseInt(id))){
                    deferred.reject('Id must be integer');
                }
                backend.get({module:'Restful', controller:self.name, action:'getItem', params:{Id:id}}).then(function(response){
                    var responseData = response.data;
                    if (responseData.status == 'OK'){
                        self.addEntity(responseData.data);
                        //self._idPool[id] = responseData.data;
                        deferred.resolve(self._idPool[id]);
                    }else{
                        deferred.reject(response);
                    }
                }, function(response){
                    deferred.reject(response);
                });
            };
            $timeout(f);
            return deferred.promise;
        };

        Entity.prototype.getList = function(params){
            var deferred = $q.defer(), self = this;
            backend.get({module:'Restful', controller:self.name, action:'getList', params:params}).then(function(response){
                var responseData = response.data;
                if (responseData.status == 'OK'){
                    var list = responseData.data;
                    for (var i=0;i<list.length;i++){
                        self.addEntity(list[i]);
                        //self._idPool[list[i].Id] = list[i];
                    }
                    deferred.resolve(list);
                }else{
                    deferred.reject(response);
                }
            }, function(response){
                deferred.reject(response);
            });
            return deferred.promise;
        };

        Entity.prototype.getItem = function(params){
            var deferred = $q.defer(), self = this;
            backend.get({module:'Restful', controller:self.name, action:'getItem', params:params}).then(function(response){
                    var responseData = response.data;
                    if (responseData.status == 'OK'){
                        self._idPool[responseData.data.Id] = responseData.data;
                        deferred.resolve(self._idPool[responseData.data.Id]);
                    }else{
                        deferred.reject(response);
                    }
                },
                function(response){
                    deferred.reject(response);
                });
            return deferred.promise;
        };

        Entity.prototype.getByIds = function(ids){
            var deferred = $q.defer(), self = this, result = [], idsToLoad=[];
            var f = function(){
                for (var i=0;i<ids.length;i++){
                    if (isNaN(parseInt(ids[i]))){
                        deferred.reject('Id must be integer');
                    }
                    if (ids[i] in self._idPool){
                        result.push(self._idPool[ids[i]]);
                    }else{
                        idsToLoad.push(ids[i]);
                    }
                }

                if (idsToLoad.length == 0){
                    deferred.resolve(result);
                }else{
                    backend.get({module:'Restful', controller:self.name, action:'getList', params:{Id:[idsToLoad, 'IN']}}).then(function(response){
                            var responseData = response.data;
                            if (responseData.status == 'OK'){
                                var list = responseData.data;
                                for (var i=0;i<list.length;i++){
                                    self.addEntity(list[i]);
                                    //self._idPool[list[i].Id] = list[i];
                                    result.push(self._idPool[list[i].Id]);
                                }
                                deferred.resolve(result);
                            }else{
                                deferred.reject(response);
                            }
                        },
                        function(response){deferred.reject(response)})
                }
            };
            $timeout(f);
            return deferred.promise;
        };

        Entity.prototype.getLoaded = function(){
            return this._entities;
        };

        Entity.prototype.getEmpty = function(){
            var deferred = $q.defer(), self = this;
            var f = function(){
                if (this._empty){
                    deferred.resolve(angular.extend({}, this._empty));
                }else{
                    backend.get({module:'Restful', controller:self.name, action:'getEmpty'}).then(function(response){
                            var responseData = response.data;
                            if (responseData.status == 'OK'){
                                self._empty = responseData.data;
                                deferred.resolve(angular.extend({}, self._empty));
                            }else{
                                deferred.reject(response);
                            }
                        },
                        function(response){deferred.reject(response)});
                }
            };
            $timeout(f);
            return deferred.promise;
        };

        Entity.prototype.addEntity = function(entity){
            var index = -1;
            if ('_index' in entity){
                index = entity['_index'];
            }else if(parseInt(entity.Id) in this._idPool){
                index = this._idPool[entity.Id]['_index'];
            }
            this._idPool[parseInt(entity.Id)] = entity;
            entity._index = index;
            if (index > -1){
                this._entities[index] = entity;
                return index;
            }else{
                this._entities.push(entity);
                index = this._entities.length - 1;
                entity['_index'] = index;
                return index;
            }
        };

        Entity.prototype.removeEntity = function (entity) {
            var index = -1;
            if (!(parseInt(entity.Id) in this._idPool)){
                return false;
            } else if ('_index' in entity){
                index = entity['_index'];
            }else{
                index = this._idPool[entity.Id]['_index'];
            }
            if (index >= 0){
                this._entities.splice(index, 1);
                delete this._idPool[parseInt(entity.Id)];
                this._updateIndexes();
                return true;
            }
            return false;
        };

        Entity.prototype._updateIndexes = function () {
            for (var i=0;i<this._entities.length;++i){
                this._entities[i]._index = i;
            }
        };

        Entity.prototype.getAll = function(orderBy){
            var deferred = $q.defer(), self = this;
            var f = function(){
                if (self._allLoaded){
                    deferred.resolve(self.getLoaded());
                }else{
                    orderBy = orderBy || [['Id', 'ASC']];
                    self.getList({_orderBy:orderBy}).then(function(list){
                        self._allLoaded = true;
                        deferred.resolve(self.getLoaded());
                    }, function(response){
                        deferred.reject(response);
                    });
                }
            };
            $timeout(f);
            return deferred.promise;
        };

        Entity.prototype.update = function(){
            var deferred = $q.defer(), self = this;
            var f = function(){
                var ids = self._entities.map(function(item){
                    return item.Id;
                });
                backend.get({module:'Restful', controller:self.name, action:'getList', params:{Id:[ids, 'IN']}}).then(function(response){
                    var responseData = response.data;
                    if (responseData.status == 'OK'){
                        responseData.data.forEach(function(item, i, arr){
                            self.addEntity(item);
                        });
                        deferred.resolve(self.getLoaded());
                    }else{
                        deferred.reject(response);
                    }
                }, function(response){deferred.reject(response)});
            };
            $timeout(f);
            return deferred.promise;
        };

        Entity.prototype.saveItem = function(item){
            var deferred = $q.defer(), self = this;
            var f = function(){
                backend.send({entity:item},{module:'Restful', controller:self.name, action:'saveItem'}).then(function(response){
                    var responseData = response.data;
                    if (responseData.status == 'OK'){
                        self.addEntity(responseData.data);
                        deferred.resolve(responseData.data);
                    }else{
                        deferred.reject(responseData);
                    }
                }, function(response){deferred.reject(response)});
            };
            $timeout(f);
            return deferred.promise;
        };

        Entity.prototype.deleteItem = function (item) {
            var deferred = $q.defer(), self = this;
            var f = function(){
                backend.get({module:'Restful', controller:self.name, action:'deleteItem', id:item.Id}).then(function(response){
                    var responseData = response.data;
                    if (responseData.status == 'OK'){
                        self.removeEntity(item);
                        deferred.resolve(responseData.data);
                    }else{
                        deferred.reject(responseData);
                    }
                }, function(response){deferred.reject(response)});
            };
            $timeout(f);
            return deferred.promise;
        };

        return function(name){
            if (!(name in self.instances)){
                self.instances[name] = new Entity(name);
            }
            return self.instances[name];
        };

    };

    angular.module('mdEntity', ['mdBackend'])
        .factory('EntityFactory', ['BackendService', '$q', '$timeout', EntityFactory]);
})(window, window.angular);
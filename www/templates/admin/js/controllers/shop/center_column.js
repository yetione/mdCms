var ShopCenterColumnController = function($scope, dataService){
    var self = this;
    self.categories = [];
    self.activeItem = -1;
    
    self.click = click;
    self.setActiveItem = setActiveItem;
    self.editCategory = editCategory;
    self.showPromocode = showPromocode;

    activate();

    function activate(){
        dataService.get({module:'Food', controller:'Categories', action:'getList'}).success(function(response){
            self.categories = response.data;
            //console.log('RSP', response);
            if (self.categories.length > 0){
                self.click(0);
            }
            //self.setActiveItem(0);
            return response;
        });
    }

    function click(index){
        if (self.setActiveItem(index)){
            self.editCategory(index);
        }
    }

    function setActiveItem(index){
        index = parseInt(index);
        if (!isNaN(index)){
            self.activeItem = index < self.categories.length && index > -1 ? index : -1;
            return true;
        }
        return false;
    }

    function editCategory(index){
        if (typeof self.categories[index] === 'undefined'){
            console.error('ShopCenterColumnController::editCategory: can not find category with index '+index);
            return false;
        }
        $scope.rightColumn.show('templates/admin/templates/shop/right_column.html').then(
            function(parentScope){
                //console.log('resolve', self.categories[index]);
                parentScope.$broadcast('shop.categorySelected', self.categories[index]);
            },
            null
        );
    }

    //test();
    function test(){
        dataService.get({module:'Food', controller:'Products', action:'getList', params:{Category:{Name:['%е%', 'LIKE']}}, count:0, limit:{count:1, offset:2}}).success(function(response){
            console.log('TEST', response);
        });
    }

    function showPromocode() {
        $scope.rightColumn.show('templates/admin/templates/shop/promocode.html').then(
            function(parentScope){
                //console.log('resolve', self.categories[index]);
            },
            null
        );
    }




};

adminApp.controller('shop.centerColumn', ['$scope', 'adminDataService', ShopCenterColumnController]);
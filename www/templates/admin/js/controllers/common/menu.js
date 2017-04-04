var LeftMenuController = function($scope, dataService){
    var self = this;
    self.items = [];
    self.activeItem = -1;

    self.click = click;
    self.setActiveItem = setActiveItem;
    self.loadItems = loadItems;

    activate();
    function activate(){
        self.loadItems().success(function(items){
            //console.log(items);
            if (self.items.length > 0){
                self.click(0);
            }
            return self.items;
        });


    }

    function loadItems(){
        return dataService.get({module:'AdminPanel', controller:'Menu', action:'getItems'}).success(function(response){
            self.items = response.data;
            return self.items;
        });
    }

    function click(index){
        if (self.setActiveItem(index)){
            $scope.$eval(self.items[index].Action);
        }
    }

    function setActiveItem(index){
        index = parseInt(index);
        if (!isNaN(index)){
            self.activeItem = index < self.items.length && index > -1 ? index : -1;
            return true;
        }
        return false;
    }
};

adminApp.controller('common.leftMenu', ['$scope', 'adminDataService', LeftMenuController]);
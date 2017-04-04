var MenuCenterColumnController = function ($scope, dataService) {
    var self = this;

    activate();
    function activate(){
        $scope.rightColumn.show('templates/admin/templates/menu/page.html');
    }
};

adminApp.controller('menu.centerColumn', ['$scope', 'adminDataService', MenuCenterColumnController]);
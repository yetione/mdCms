var OrderDocumentsSelectCourierController = function($scope, $rootScope, BackendService, EntityFactory){
    var self = this;
    self.list = [];
    self.selectedId = null;
    self.onSelect = function (item, scope) {
        $scope.hide();
    };
    self.selectItem = selectItem;
    self.setNull = setNull;

    activate();
    function activate() {
        self.list = $scope.dialogOptions.List;
        if ($scope.dialogOptions.onSelect){
            self.onSelect = $scope.dialogOptions.onSelect;
        }
    }

    function selectItem(item) {
        self.onSelect(item, $scope);
    }

    function setNull() {
        self.onSelect(null, $scope);
    }
};


adminApp.controller('order.documents.selectCourier', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', OrderDocumentsSelectCourierController]);
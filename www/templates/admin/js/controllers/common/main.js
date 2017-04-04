var MainApplicationController = function ($scope,columnFactory){
    $scope.centerColumn = columnFactory('column-center', 'columns.center', $scope);
    $scope.rightColumn = columnFactory('column-right', 'columns.right', $scope);
};


adminApp.controller('common.applicationController', ['$scope', 'ColumnFactory', MainApplicationController]);
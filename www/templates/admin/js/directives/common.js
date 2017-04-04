adminApp.controller('directiveWindow', ['$scope',
    function($scope){
        $scope.title = 'Window Title';
}]);
adminApp.directive('window',['$compile', 'UrlConfigs', function($compile, urlConfigs){
    return {
        controller:'directiveWindow',
        templateUrl:urlConfigs.buildUrl('templates/admin/templates/directives/window.html'),
        link: function ($scope, element, attrs) {
            console.log('ee',element, attrs);
        }
    };
}]);

var SelectInlineDirective = function($compile){
    var link = function ($scope, $element, $attrs){
        $scope.header = angular.element($element.children()[0]);
        $scope.list = angular.element($element.children()[1]);
        $scope.id = $attrs.selectInline;
        $scope.toggle(false);
    };

    var controller = function($scope){
        var id = $scope.id;
        var self = this;
        self.openning = false;
        angular.element(document.body).on('click', function(event){
            var target = angular.element(event.target);
            var parent = angular.element(target.parent());
            if (target[0] == $scope.header[0] || $scope.header[0] == parent[0]){
                $scope.toggle();
            }else if (self.openning){
                $scope.toggle(false);
            }
            event.stopPropagation();
        });

        $scope.toggle = function(state){
            self.openning = typeof state === 'boolean'? state :!self.openning;
            $scope.list.css('display', self.openning ? 'block': 'none');
        }
    };

    return {
        restrict: 'A',
        scope:true,
        link: link,
        controller:['$scope', controller]
    };
};
adminApp.directive('selectInline', ['$compile', SelectInlineDirective]);

var MultiSelectInline = function($compile){
    var link = function ($scope, $element, $attrs){
        $scope.header = angular.element($element.children()[0]);
        $scope.list = angular.element($element.children()[1]);
        $scope.id = $attrs.selectInline;
        $scope.toggle(false);
    };

    var controller = function($scope){
        var id = $scope.id;
        var self = this;
        self.openning = false;
        angular.element(document.body).on('click', function(event){
            var target = angular.element(event.target);
            var parent = angular.element(target.parent());
            if (target[0] == $scope.header[0] || $scope.header[0] == parent[0]) {
                $scope.toggle();
            }
            event.stopPropagation();
        });

        $scope.toggle = function(state){
            self.openning = typeof state === 'boolean'? state :!self.openning;
            $scope.list.css('display', self.openning ? 'block': 'none');
        }
    };

    return {
        restrict: 'A',
        scope:true,
        link: link,
        controller:['$scope', controller]
    };
};
adminApp.directive('multiSelectInline', ['$compile', MultiSelectInline]);
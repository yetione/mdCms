var AutoCompleteDirective = function($compile){
    var link = function ($scope, $element, $attrs){
        $scope.input = angular.element($element.children()[0]);
        $scope.id = $attrs.autoComplete;
    };


    var controller = function($scope){
        var id = $scope.id;
        var self = this;
        self.items = [];
        self.openning = false;
        angular.element(document.body).on('click', function(event){
            var target = angular.element(event.target);
            var parent = angular.element(target.parent());
            if (target[0] == $scope.input[0] || $scope.input[0] == parent[0]){
                $scope.toggle();
            }else if ($scope.options.Opening){
                $scope.toggle(false);
            }
            event.stopPropagation();
        });

        $scope.toggle = function(state){
            $scope.options.Opening = typeof state === 'boolean'? state :!$scope.options.Opening;
        };


        activate();
        function activate() {
            $scope.options = angular.extend({
                Items:[],
                Opening:false,
                onTextChange:function(text){return [];}
            }, $scope.options);
            $scope.$watch('text', textChange);
            $scope.$watch('options.Opening', function (newVal, oldVal) {
                $scope.options.Opening = newVal;
            });
            $scope.$watch('options.Items', function (newVal, oldVal) {
                setItems(newVal);
            });
        }

        function textChange(newVal, oldVal) {
            var items = $scope.options.onTextChange(newVal);
            setItems(items);
        }

        function setItems(items) {
            $scope.options.Items = items;
            $scope.options.Opening = items.length > 0;
        }
    };

    return {
        restrict: 'A',
        scope:{
            text:'=acString',
            options:'=acOptions'
        },
        link: link,
        controller:['$scope', controller],
        //controllerAs:'AutoComplete'
    };
};


adminApp.directive('autoComplete', ['$compile', AutoCompleteDirective]);
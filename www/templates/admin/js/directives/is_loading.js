var IsLoadingDirective = function($compile, $timeout){
    var link = function(scope, element, attrs, $isLoading){
        //element.append();
        $isLoading.id = 'isLoading_'+moment().valueOf();
        $isLoading.$container = element;
        element.css({position:'relative'});
        var w = $isLoading.$container[0].clientWidth, h = $isLoading.$container[0].clientHeight;
        var delayStep = 0.15, startDelay=0, curDelay = startDelay, height = Math.floor(h/3);
        height = height > 18 ? 18 : height;
        var count = Math.floor(w/18), items=[], t;
        count = count > 8 ? 8 : count;
        for (var i=0;i<count;++i){
            t = {style:{
                '-webkit-animation-delay':''+curDelay+'s',
                '-moz-animation-delay':''+curDelay+'s',
                '-o-animation-delay':''+curDelay+'s',
                'animation-delay':''+curDelay+'s',
                'width':height+'px',
                'height':height+'px',
                'border-radius':Math.ceil(height/2)+'px',
                'flex-basis':height+'px'
            }};
            curDelay+=delayStep;
            items.push(t);
        }
        $isLoading.items = items;
        scope.$watch(attrs.isLoading, function (newValue) {
            if (newValue === $isLoading.state || !$isLoading.$container) return false;
            $isLoading.state = newValue;
            if ($isLoading.state){
                $isLoading.$loadingBlock = $isLoading.createLoader();
                $isLoading.$container.append($isLoading.$loadingBlock);
                $compile($isLoading.$loadingBlock)(scope);
            }else{
                $timeout(function() {
                    $isLoading.$loadingBlock.remove();
                }, $isLoading.disappearDelay);

            }
        })
    };
    return {
        restrict: 'A',
        required:'isLoading',
        link: link,
        controller:'isLoading.controller',
        controllerAs:'$isLoading'
    };
};

adminApp.directive('isLoading', ['$compile', '$timeout', IsLoadingDirective]);
var IsLoadingDirectiveController = function($scope, $templateCache){
    var self = this;
    self.template = '';
    self.$container = null;
    self.state = false;
    self.visible = false;
    self.appearDelay = 500;
    self.disappearDelay = 500;
    self.items = [];
    self.createLoader = createLoader;
    activate();
    function activate() {
        self.template = $templateCache.get('isLoading/loading-block');
        self.$loadingBlock = angular.element(self.template);
    }

    function setState(state){
        if (state === self.state || !self.$container) return false;
        else if (state){
            self.$container.append(self.$loadingBlock);

        }
    }

    function createLoader() {
        return angular.element(self.template);
    }
};
adminApp.controller('isLoading.controller', ['$scope', '$templateCache', IsLoadingDirectiveController]);
adminApp.run(['$templateCache',function($templateCache) {
    $templateCache.put('isLoading/loading-block', '<div class="is-loading full ng-hide" ng-show="$isLoading.state">' +
        '<div id="fountainG" class="animated">' +
            '<div class="fountainG" ng-repeat="item in $isLoading.items" ng-style="item.style"></div>' +
            /*'<div class="fountainG"></div>' +
            '<div class="fountainG"></div>' +
            '<div class="fountainG"></div>' +
            '<div class="fountainG"></div>' +
            '<div class="fountainG"></div>' +
            '<div class="fountainG"></div>' +
            '<div class="fountainG"></div>' +*/
            '</div>' +
        '</div>' +
        '</div>');
    //$templateCache.put('isLoading/loading-block', '<div class="is-loading full ng-hide" ng-show="$isLoading.visible"><div class="cssload-jumping"><span></span><span></span><span></span><span></span><span></span></div></div>');
}]);
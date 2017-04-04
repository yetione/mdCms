var SelectBoxDirective = function($document, $compile, $parse, $timeout, urlConfigs){
    var compileFn = function (tElement, tAttrs){
        return linkFn;
    };
    var linkFn = function(scope, element, attrs, ctrls, transcludeFn) {
        var $select = ctrls[0];
        var ngModel = ctrls[1];

        $select.ngModel = ngModel;
        scope.$watch('items', function(newVal){
            $select.setItems(newVal);
        });
        scope.$watch('nullItem', function(newVal){
            $select.setNullItem(newVal);
        });
        if (scope.onSelectCallback){
            $select.onSelectCallback = $parse(scope.onSelectCallback);
        }

        function onDocumentClick(e) {
            if (!$select.opened) return; //Skip it if dropdown is close
            var contains = false;
            if (window.jQuery) {
                // Firefox 3.6 does not support element.contains()
                // See Node.contains https://developer.mozilla.org/en-US/docs/Web/API/Node.contains
                contains = window.jQuery.contains(element[0], e.target);
            } else {
                contains = element[0].contains(e.target);
            }
            if (!contains ) {
                $select.close();
                scope.$digest();
            }
            $select.clickTriggeredSelect = false;
        }
        // See Click everywhere but here event http://stackoverflow.com/questions/12931369
        angular.element(document.body).on('click', onDocumentClick);
        scope.$on('$destroy', function() {
            angular.element(document.body).off('click', onDocumentClick);
        });

        //From view --> model
        ngModel.$parsers.unshift(function (inputValue) {
            var locals = {},
                result;
            locals[$select.parserResult.itemName] = inputValue;
            result = $select.parserResult.modelMapper(scope, locals);
            return result;
        });

        //From model --> view
        ngModel.$formatters.unshift(function (inputValue) {
            var data = $select.parserResult && $select.parserResult.source (scope, {}), //Overwrite $search
                locals = {},
                result;

            //console.log('From model ', inputValue, $select.parserResult, data, angular.equals($select.nullItem, inputValue));
            if (data){
                var checkFnSingle = function(d){
                    locals[$select.parserResult.itemName] = d;
                    result = $select.parserResult.modelMapper(scope, locals);
                    return result === inputValue;
                };
                //If possible pass same object stored in $select.selected
                if ($select.selected && checkFnSingle($select.selected)) {
                    return $select.selected;
                }
                for (var i = data.length - 1; i >= 0; i--) {
                    if (checkFnSingle(data[i])) return data[i];
                }
            }
            return inputValue;
        });

        //Update viewValue if model change
        scope.$watch('$select.selected', function(newValue) {
            if (ngModel.$viewValue !== newValue) {
                ngModel.$setViewValue(newValue);
            }
        });

        ngModel.$render = function() {
            $select.selected = ngModel.$viewValue;
        };

        scope.$on('sb:select', function (event, item) {
            $select.selected = item;
        });

        scope.$on('sb:close', function (event, skipFocusser) {
            return;
            $timeout(function(){
                $select.focusser.prop('disabled', false);
                if (!skipFocusser) $select.focusser[0].focus();
            },0,false);
        });

        scope.$on('sb:activate', function () {
            return;
            focusser.prop('disabled', true); //Will reactivate it on .close()
        });

        transcludeFn(scope, function(clone){
            element.append(clone);
        });
    };

    return {
        restrict: 'E',
        replace: true,
        transclude:true,
        require: ['selectBox', '^ngModel'],
        scope:{
            onSelectCallback:'&onSelect',
            nullItem:'=nullItem',
            items:'=source',
            id:'@sbId'
        },
        controller:'selectBoxController',
        controllerAs:'$select',
        templateUrl:'select-box/template.html',
        compile: compileFn
    };
};
adminApp.directive('selectBox', ['$document', '$compile', '$parse', '$timeout', 'UrlConfigs', SelectBoxDirective]);

var SelectBoxController = function($scope, $element, $timeout, $filter, $parse, $injector, $window, RepeatParser) {
    var self = this;

    self.opened = false;
    self.closeOnSelect = true;

    self.selected = undefined;
    self.nullItem = undefined;
    self.items = [];
    self.activeIndex = 0;
    self.onSelectCallback = undefined;
    self.clickTriggeredSelect = false;
    self.multiple = false;
    self.ngModel = undefined;
    //sb-header
    construct();
    function construct(){}

    self.activate = function(){
        if (!self.opened){
            $scope.$broadcast('sb:activate');
            self.opened = true;
            self.activeIndex = (self.activeIndex >= self.items.length || self.activeIndex < 0) ? 0 : self.activeIndex;
        }else{
            self.close();
        }
    };

    self.setItems = function(items) {
        if (items === undefined || items === null){
            self.items = [];
        }else{
            if (!angular.isArray(items)){
                throw Error('SelectBoxDirective::setItems : items must be an array');
            }
            self.items = items;
        }
        $scope.$broadcast('sb:refresh');
        if(angular.isDefined(self.ngModel.$modelValue)) {
            self.ngModel.$modelValue = null; //Force scope model value and ngModel value to be out of sync to re-run formatters
        }
    };

    self.setNullItem = function(item){
        self.nullItem = item;
    };

    self.close = function() {
        if (!self.opened) return;
        if (self.ngModel && self.ngModel.$setTouched) self.ngModel.$setTouched();
        self.opened = false;
        $scope.$broadcast('sb:close');
    };

    self.selectNullItem = function($event){
        self.select(self.nullItem, $event);
    };

    self.select = function(item, $event){
        if (!self.items) return;
        self.clickTriggeredSelect = $event && $event.type === 'click' && item;
        if (angular.equals(self.selected, item)){
            self.close();
            return;
        }
        $scope.$broadcast('sb:select', item);
        if (self.onSelectCallback !== undefined){
            $timeout(function(){
                self.onSelectCallback({
                    $item: item,
                    $event: $event,
                    $isNull: angular.equals(self.nullItem, item)
                });
            });
        }
        if (self.closeOnSelect) {
            self.close();
        }
    };

    self.toggle = function(e) {
        if (self.opened) {
            self.close();
            e.preventDefault();
            e.stopPropagation();
        } else {
            self.activate();
        }
    };

    self.isEmpty = function() {
        return angular.isUndefined(self.selected) || self.selected === null || self.selected === '' || (self.multiple && self.selected.length === 0);
    };

    self.parseRepeatAttr = function(repeatAttr) {
        self.parserResult = RepeatParser.parse(repeatAttr);
        self.itemProperty = self.parserResult.itemName;

    };

};
adminApp.controller('selectBoxController', ['$scope', '$element', '$timeout', '$filter', '$parse', '$injector', '$window', 'RepeatParser', SelectBoxController]);

var SelectBoxHeaderDirective = function ($compile, UrlConfigs) {
    var compileFn = function (tElement, tAttrs) {
        return linkFn;
    };
    var linkFn = function (scope, element, attrs, $select, transcludeFn) {
        $select.headerBlock = element;
        transcludeFn(scope, function (clone) {
            angular.element(element.children()[0]).append(clone);
        });
    };
    return {
        restrict: 'EA',
        require:'^selectBox',
        replace:true,
        controller:['$scope', function ($scope) {
        }],
        transclude:true,
        compile:compileFn,
        templateUrl:'sb-header/template.html'
    };
};
adminApp.directive('sbHeader', ['$compile', 'UrlConfigs', SelectBoxHeaderDirective]);

var SelectBoxListDirective = function ($compile, UrlConfigs) {
    var compileFn = function(tElement, tAttrs){
        tAttrs.$set('ngShow', '$select.opened');
        return linkFn;
    };
    var linkFn = function (scope, element, attrs, $select, transcludeFn) {
        transcludeFn(scope, function (clone) {
            angular.element(element.children()[0]).append(clone);
        });
    };
    return {
        restrict: 'E',
        require:'^selectBox',
        replace:true,
        transclude:true,
        compile:compileFn,
        templateUrl:'sb-list/template.html'
    };
};
adminApp.directive('sbList', ['$compile', 'UrlConfigs', SelectBoxListDirective]);

var SelectBoxItemDirective = function($compile, UrlConfigs, $window, RepeatParser, $timeout){
    var compileFn = function (tElement, tAttrs) {
        if (!tAttrs.ngRepeat) throw Error("Expected 'ng-repeat' expression.");
        var parserResult = RepeatParser.parse(tAttrs.ngRepeat);
        var inner = tElement.querySelectorAll('.list-row-inner');
        if (inner.length !== 1) {
            throw Error("Expected 1 .list-row-inner but got '"+inner.length+"'.");
        }
        inner.attr('ng-click', '$select.select('+parserResult.itemName+', $event)');
        //inner.attr('ng-if', '$select.open'); //Prevent unnecessary watches when dropdown is closed

        return function link(scope, element, attrs, $select,transcludeFn ) {
            $select.parseRepeatAttr(attrs.ngRepeat); //Result ready at $select.parserResult
            transcludeFn(scope, function (clone) {
                angular.element(element.children()[0]).append(clone);
            });
        };
    };
    return {
        restrict: 'EA',
        require:'^selectBox',
        replace:true,
        transclude:true,
        compile:compileFn,
        templateUrl:'sb-list-item/template.html'
    };
};
adminApp.directive('sbListItem', ['$compile', 'UrlConfigs', '$window', 'RepeatParser', '$timeout', SelectBoxItemDirective]);

var SelectBoxNullItemDirective = function($compile, UrlConfigs, $window, RepeatParser, $timeout){
    var compileFn = function (tElement, tAttrs) {
        var inner = tElement.querySelectorAll('.list-row-inner');
        inner.attr('ng-click', '$select.selectNullItem($event)');

        return function link(scope, element, attrs, $select,transcludeFn ) {
            transcludeFn(scope, function (clone) {
                angular.element(element.children()[0]).append(clone);
            });
        };
    };
    return {
        restrict: 'EA',
        require:'^selectBox',
        replace:true,
        transclude:true,
        compile:compileFn,
        templateUrl:'sb-list-null-item/template.html'
    };
};
adminApp.directive('sbListNullItem', ['$compile', 'UrlConfigs', '$window', 'RepeatParser', '$timeout', SelectBoxNullItemDirective]);

/**
 * Add querySelectorAll() to jqLite.
 *
 * jqLite find() is limited to lookups by tag name.
 * TODO This will change with future versions of AngularJS, to be removed when this happens
 *
 * See jqLite.find - why not use querySelectorAll? https://github.com/angular/angular.js/issues/3586
 * See feat(jqLite): use querySelectorAll instead of getElementsByTagName in jqLite.find https://github.com/angular/angular.js/pull/3598
 */
if (angular.element.prototype.querySelectorAll === undefined) {
    angular.element.prototype.querySelectorAll = function(selector) {
        return angular.element(this[0].querySelectorAll(selector));
    };
}

adminApp.run(["$templateCache", function($templateCache) {
    $templateCache.put("select-box/template.html",'<div class="select-box"></div>');
    $templateCache.put("sb-header/template.html",'<div class="header sb-header" ng-click="$select.activate()"><div class="label"></div><img class="arrow" ng-class="{opened:$select.opened}" ng-src="templates/admin/images/select_arrow_closing.png"></div>');
    $templateCache.put("sb-list/template.html",'<div class="list-wrapper ng-hide" ng-show="$select.opened"><ul class="sb-list"></ul></div>');
    $templateCache.put("sb-list-item/template.html",'<li class="list-row sb-list-item"><div class="list-row-inner"></div></li>');
    $templateCache.put("sb-list-null-item/template.html",'<li class="list-row sb-list-item null-item" ng-show="$select.nullItem !== undefined"><div class="list-row-inner"></div></li>');
}]);

var KEY = {
    TAB: 9,
    ENTER: 13,
    ESC: 27,
    SPACE: 32,
    LEFT: 37,
    UP: 38,
    RIGHT: 39,
    DOWN: 40,
    SHIFT: 16,
    CTRL: 17,
    ALT: 18,
    PAGE_UP: 33,
    PAGE_DOWN: 34,
    HOME: 36,
    END: 35,
    BACKSPACE: 8,
    DELETE: 46,
    COMMAND: 91,

    MAP: { 91 : "COMMAND", 8 : "BACKSPACE" , 9 : "TAB" , 13 : "ENTER" , 16 : "SHIFT" , 17 : "CTRL" , 18 : "ALT" , 19 : "PAUSEBREAK" , 20 : "CAPSLOCK" , 27 : "ESC" , 32 : "SPACE" , 33 : "PAGE_UP", 34 : "PAGE_DOWN" , 35 : "END" , 36 : "HOME" , 37 : "LEFT" , 38 : "UP" , 39 : "RIGHT" , 40 : "DOWN" , 43 : "+" , 44 : "PRINTSCREEN" , 45 : "INSERT" , 46 : "DELETE", 48 : "0" , 49 : "1" , 50 : "2" , 51 : "3" , 52 : "4" , 53 : "5" , 54 : "6" , 55 : "7" , 56 : "8" , 57 : "9" , 59 : ";", 61 : "=" , 65 : "A" , 66 : "B" , 67 : "C" , 68 : "D" , 69 : "E" , 70 : "F" , 71 : "G" , 72 : "H" , 73 : "I" , 74 : "J" , 75 : "K" , 76 : "L", 77 : "M" , 78 : "N" , 79 : "O" , 80 : "P" , 81 : "Q" , 82 : "R" , 83 : "S" , 84 : "T" , 85 : "U" , 86 : "V" , 87 : "W" , 88 : "X" , 89 : "Y" , 90 : "Z", 96 : "0" , 97 : "1" , 98 : "2" , 99 : "3" , 100 : "4" , 101 : "5" , 102 : "6" , 103 : "7" , 104 : "8" , 105 : "9", 106 : "*" , 107 : "+" , 109 : "-" , 110 : "." , 111 : "/", 112 : "F1" , 113 : "F2" , 114 : "F3" , 115 : "F4" , 116 : "F5" , 117 : "F6" , 118 : "F7" , 119 : "F8" , 120 : "F9" , 121 : "F10" , 122 : "F11" , 123 : "F12", 144 : "NUMLOCK" , 145 : "SCROLLLOCK" , 186 : ";" , 187 : "=" , 188 : "," , 189 : "-" , 190 : "." , 191 : "/" , 192 : "`" , 219 : "[" , 220 : "\\" , 221 : "]" , 222 : "'"
    },

    isControl: function (e) {
        var k = e.which;
        switch (k) {
            case KEY.COMMAND:
            case KEY.SHIFT:
            case KEY.CTRL:
            case KEY.ALT:
                return true;
        }
        return !!(e.metaKey || e.ctrlKey || e.altKey);
    },
    isFunctionKey: function (k) {
        k = k.which ? k.which : k;
        return k >= 112 && k <= 123;
    },
    isVerticalMovement: function (k){
        return ~[KEY.UP, KEY.DOWN].indexOf(k);
    },
    isHorizontalMovement: function (k){
        return ~[KEY.LEFT,KEY.RIGHT,KEY.BACKSPACE,KEY.DELETE].indexOf(k);
    },
    toSeparator: function (k) {
        var sep = {ENTER:"\n",TAB:"\t",SPACE:" "}[k];
        if (sep) return sep;
        // return undefined for special keys other than enter, tab or space.
        // no way to use them to cut strings.
        return KEY[k] ? undefined : k;
    }
};
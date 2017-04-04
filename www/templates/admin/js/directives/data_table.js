var DataTableDirective = function($document, $compile, $parse, $timeout, UrlConfigs){
    var compileFn = function (tElement, tAttrs){
        return linkFn;
    };
    var linkFn = function(scope, element, attrs, $table, transcludeFn) {
        $table.tableId = attrs.tableId;
        transcludeFn(scope, function(clone){
            element.append(clone);
        });
    };

    return {
        restrict: 'E',
        replace: true,
        transclude:true,
        require: 'dT',
        controller:'dataTableController',
        controllerAs:'$table',
        scope:true,
        templateUrl:'data-table/index.html',
        compile: compileFn
    };
};
adminApp.directive('dT', ['$document', '$compile', '$parse', '$timeout', 'UrlConfigs', DataTableDirective]);

var DataTableController =  function($scope, $element, $timeout, $filter, $parse, $injector, $window, RepeatParser){
    var self = this;
    self.columns = [];
    self.tableId = '';
    self.activeIndex = -1;
    self.parserResult = undefined;
    self.sortingColumn = undefined;

    self.addColumn = addColumn;
    self.parseRepeatAttr = parseRepeatAttr;
    self.sort = sort;
    activate();
    function activate() {}

    function addColumn(column) {
        self.columns.push(column);
    }
    
    function parseRepeatAttr(repeatAttr) {
        if (self.parserResult !== undefined) return false;
        self.parserResult = RepeatParser.parse(repeatAttr);
    }

    function sort(column, direction) {
        var items = self.parserResult.source($scope);
        var active = self.activeIndex > -1 ? items[self.activeIndex] : undefined;
        items.sort(function(a,b){
            if (~column.key.indexOf('.')){ //Поддержка вложенных объектов
                var t = column.key.split('.');
                for (var i=0;i<t.length;++i){
                    a = a[t[i]];
                    b = b[t[i]];
                }
                if (a > b) return direction === 'asc' ? 1 : -1;
                else if (a < b) return direction === 'asc' ? -1 : 1;
            }
            else if (a[column.key] > b[column.key]) return direction === 'asc' ? 1 : -1;
            else if (a[column.key] < b[column.key]) return direction === 'asc' ? -1 : 1;
            return 0;
        });
        if (angular.isDefined(active)){
            self.activeIndex = items.indexOf(active);
        }
        if (angular.isDefined(self.sortingColumn)){
            self.sortingColumn.isSorting = false;
        }
        column.isSorting = true;
        self.sortingColumn = column;


        //console.log('sort', column, self.columns.indexOf(column), direction, self.parserResult.source($scope));
    }
};
adminApp.controller('dataTableController', ['$scope', '$element', '$timeout', '$filter', '$parse', '$injector', '$window', 'RepeatParser', DataTableController]);

var DataTableHeaderDirective = function($document, $compile, $parse, $timeout, UrlConfigs){
    var compileFn = function (tElement, tAttrs){
        return linkFn;
    };
    var linkFn = function(scope, element, attrs, $table, transcludeFn) {
        transcludeFn(scope, function(clone){
            element.append(clone);
        });
    };
    return {
        restrict: 'E',
        replace: true,
        transclude:true,
        require: '^dT',
        controller:['$scope', function ($scope) {}],
        templateUrl:'data-table/header.html',
        compile: compileFn
    };
};
adminApp.directive('dtHeader', ['$document', '$compile', '$parse', '$timeout', 'UrlConfigs', DataTableHeaderDirective]);

var DataTableHeaderColumnDirective = function($document, $compile, $parse, $timeout, UrlConfigs){
    var compileFn = function (tElement, tAttrs){
        return function(scope, element, attrs, ctrls, transcludeFn) {
            var $table = ctrls[0];
            var $tableHeader = ctrls[1];

            var $column = ctrls[2];

            if (angular.isDefined(attrs.sortable)){
                var s = $parse(attrs.sortable);
                scope.$watch(s,function (n) {
                    $column.sortable = n;
                });
                if (angular.isDefined(attrs.sortDirection)){
                    var d = $parse(attrs.sortDirection);
                    scope.$watch(d, function (n) {
                        n = n.toLowerCase();
                        if (n !== 'asc' && n !== 'desc') throw Error('DataTable: sortDirection must be asc or desc. '+n+' is giving.');
                        $column.sortDirection = n;
                    });

                }
            }
            if (angular.isDefined(attrs.key)){
                var k = $parse(attrs.key);
                scope.$watch(k, function (n) {
                    $column.key = n;
                });
            }
            transcludeFn(scope, function(clone){
                $table.addColumn($column);
                angular.element(element.children()[0]).append(clone);
            });
        };
    };
    return {
        restrict: 'E',
        replace: true,
        transclude:true,
        scope:true,
        controller:'dthColumnController',
        controllerAs:'$column',
        require: ['^dT', '^dtHeader', 'dthColumn'],
        templateUrl:'data-table/header-column.html',
        compile: compileFn
    };
};
adminApp.directive('dthColumn', ['$document', '$compile', '$parse', '$timeout', 'UrlConfigs', DataTableHeaderColumnDirective]);

var DataTableHeaderColumnController =  function($scope, $element, $timeout, $filter, $parse, $injector, $window, RepeatParser){
    var self = this;

    self.setColumn = setColumn;
    self.onClick = onClick;
    activate();
    function activate() {
        self.$table = $scope.$table;
        var defaultData = {name:'', label:'', sortable:false, sortDirection:'asc', key:undefined, isSorting:false};
        self.setColumn(defaultData);
    }

    function setColumn(column) {
        angular.extend(self, column);
    }

    function onClick(event){
        if (self.sortable && angular.isDefined(self.key)){
            self.sortDirection = self.sortDirection == 'asc' ? 'desc' : 'asc';
            self.$table.sort(this, self.sortDirection);
        }
        //console.log('click s', self.$table);
    }
};
adminApp.controller('dthColumnController', ['$scope', '$element', '$timeout', '$filter', '$parse', '$injector', '$window', 'RepeatParser', DataTableHeaderColumnController]);


var DataTableRowsDirective = function($document, $compile, $parse, $timeout, UrlConfigs){
    var compileFn = function (tElement, tAttrs){
        return linkFn;
    };
    var linkFn = function(scope, element, attrs, $table, transcludeFn) {
        transcludeFn(scope, function(clone){
            angular.element(element).append(clone);
        });
    };
    return {
        restrict: 'E',
        replace: true,
        transclude:true,
        require: '^dT',
        controller:['$scope', function ($scope) {}],
        templateUrl:'data-table/rows.html',
        compile: compileFn
    };
};
adminApp.directive('dtRows', ['$document', '$compile', '$parse', '$timeout', 'UrlConfigs', DataTableRowsDirective]);

var DataTableRowsRowController = function($scope){
    var self = this;
    self.item = undefined;

    self.setItem = setItem;
    activate();
    function activate() {

    }

    function setItem(item) {
        self.item = item;
    }


};
adminApp.controller('dtrRowController', ['$scope', DataTableRowsRowController]);

var DataTableRowsRowDirective = function($document, $compile, $parse, $timeout, RepeatParser){
    var compileFn = function (tElement, tAttrs){

        return function(scope, element, attrs, ctrls, transcludeFn) {
            var $table = ctrls[0];
            var $tableRows = ctrls[1];
            var $row = ctrls[2];

            if (angular.isDefined(attrs.ngRepeat)){
                $table.parseRepeatAttr(attrs.ngRepeat);
            }
            $row.setItem(scope[$table.parserResult.itemName]);
            transcludeFn(scope, function(clone){
                angular.element(element).append(clone);
            });
        };
    };
    return {
        restrict: 'E',
        replace: true,
        transclude:true,
        require: ['^dT', '^dtRows', '^dtrRow'],
        controller:'dtrRowController',
        controllerAs:'$row',
        templateUrl:'data-table/rows-row.html',
        compile: compileFn
    };
};
adminApp.directive('dtrRow', ['$document', '$compile', '$parse', '$timeout', 'RepeatParser', DataTableRowsRowDirective]);

var DataTableRowsRowColumnDirective = function($document, $compile, $parse, $timeout, UrlConfigs){
    var compileFn = function (tElement, tAttrs){
        return linkFn;
    };
    var linkFn = function(scope, element, attrs, ctrls, transcludeFn) {
        var $table = ctrls[0];
        var $tableRows = ctrls[1];
        transcludeFn(scope, function(clone){
            angular.element(element).append(clone);
        });
    };
    return {
        restrict: 'E',
        replace: true,
        transclude:true,
        require: ['^dT', '^dtRows'],
        templateUrl:'data-table/column.html',
        compile: compileFn
    };
};
adminApp.directive('dtrrColumn', ['$document', '$compile', '$parse', '$timeout', 'UrlConfigs', DataTableRowsRowColumnDirective]);

adminApp.run(["$templateCache", function($templateCache) {
    $templateCache.put("data-table/index.html",'<div class="md-table"></div>');
    $templateCache.put("data-table/header.html",'<div class="head row"></div>');
    $templateCache.put("data-table/rows.html",'<ul class="data"></ul>');
    $templateCache.put("data-table/rows-row.html",'<li class="row" ng-class="{selected:$table.activeIndex === $index, marked:$row.item.marked}"></li>');
    $templateCache.put("data-table/column.html",'<div class="cell"></div>');
    $templateCache.put("data-table/header-column.html",'<div class="cell" ng-click="$column.onClick($event)" ng-class="{sortable:$column.sortable}"><span class="label"></span>' +
        '<span ng-show="$column.sortable && !$column.isSorting" class="sort-icon"><img ng-src="templates/admin/images/select_arrow_closing.png"><img ng-src="templates/admin/images/select_arrow_closing.png"></span>' +
        '<span ng-show="$column.sortable && $column.isSorting" class="sort-block" ng-class="[$column.sortDirection]"><img ng-src="templates/admin/images/select_arrow_closing.png"></span>' +
        '</div>');
}]);
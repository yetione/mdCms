var CalendarDirective = function($rootScope, urlConfigs, $timeout, $q){
    var compile = function(templateElement, templateAttrs){

    };

    var link = function($scope, $element, $attr){
        //$scope.selectDay($scope.currentDate);


        $scope.createPage();
    };
    var dayTemplate = '<div class="day-block">'+
        '<div class="rectangle">'+
        '<p class="number">{{date}}</p>'+
        '<p class="week-day">{{week_day}}</p>'+
        '</div>'+
        '<p class="month">{{month}}, {{year}}</p>'+
        '</div>';
    var controller = function($scope, $element){
        var self = this;

        self.prev = previousPage;
        self.next = nextPage;
        self.daysToPage = 7;

        self.monthName = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
        self.weekDaysName = ['Воскресенье', 'Понедельник','Вторник','Среда','Четверг','Пятница','Суббота'];

        self.currentPage = [];

        self.createPage = createPage;
        $scope.createPage = createPage;
        $scope.selectDay = selectDay;

        self.day = day;
        self.month = month;
        self.weekDay = weekDay;

        self.selectDay = selectDay;

        activate();
        function activate() {
            if ($scope.currentDate < $scope.minDate){
                console.log('CalendarDirective:current date less then min date');
                $scope.currentDate = $scope.minDate;
            }
            self.selectDay($scope.currentDate);
        }
        //self.selectDay(new Date($scope.currentDate));
        //self.activeDay = new Date($scope.currentDate);

        function createPage(){
            //var endDate = $scope.currentDate.setDate($scope.currentDate + self.daysToPage);
            var result = [];
            var tempDate;
            for (var i=0;i<self.daysToPage;i++){
                tempDate = new Date($scope.currentDate);
                tempDate.setDate(tempDate.getDate() + i);
                result.push(tempDate);
            }
            self.currentPage = result;
            $rootScope.$broadcast('calendar.pageCreated', {page: self.currentPage});
        }

        function selectDay(date){
            var deferred = $q.defer();
            var promise = deferred.promise;
            promise.then(function(date){
                self.activeDay = date;
            },function(date){});
            var f = function(){
                deferred.resolve(date);};
            $timeout(f);

            //return deferred.promise;
            //self.activeDay = date;
            $rootScope.$broadcast('calendar.daySelect', {date: date, deferred:deferred});
        }

        function day(i){
            if (parseInt(i) < 10) return '0'+i;
            return i;
        }

        function weekDay(i){
            return self.weekDaysName[i];
        }

        function month(i){
            return self.monthName[i];
        }


        function previousPage(){
            var tempDate = new Date($scope.currentDate);
            tempDate.setDate($scope.currentDate.getDate()-self.daysToPage);

            if ($scope.minDate >=  tempDate){
                $scope.currentDate = new Date($scope.minDate);
            }else{
                $scope.currentDate.setDate($scope.currentDate.getDate()-self.daysToPage);
            }
            self.createPage();
            console.log('prev')
        }

        function nextPage(){
            $scope.currentDate.setDate($scope.currentDate.getDate()+self.daysToPage);
            self.createPage();
            console.log('next', self.activeDay);
        }

        function setCurrentDate(date) {

        }


    };
    return {
        restrict:'A',
        scope:{
            currentDate:'=mdCalendar',
            minDate:'=startDate'

        },
        controller:controller,
        controllerAs:'calendar',
        link:link,
        //compile:compile,
        templateUrl:urlConfigs.buildUrl('templates/admin/templates/menu/calendar.html')
    };
};
adminApp.directive('mdCalendar', ['$rootScope', 'UrlConfigs', '$timeout', '$q', CalendarDirective]);
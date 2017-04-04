var ClockController = function($scope, $timeout){
    var self = this;
    self.tickRate = 1000;
    self.hours = 0;
    self.minutes = 0;
    self.date = '';
    self.day = '';

    self.timeStr ='';
    self.dateStr = '';

    var tick = function(){
        var date = new Date();
        self.hours = check(date.getHours());
        self.minutes = check(date.getMinutes());
        self.day = check(date.getDate());
        self.month = check(date.getMonth()+1);
        self.year = date.getFullYear();
        self.timeStr = self.hours+':'+self.minutes;
        self.dateStr = self.day+'.'+self.month+'.'+self.year;
        $timeout(tick, self.tickRate);
    };

    activate();
    function activate(){
        tick();
    }

    function check(i){
        if (i<10) i = '0'+i;
        return i;
    }
};

adminApp.controller('common.clockController', ['$scope', '$timeout',  ClockController]);
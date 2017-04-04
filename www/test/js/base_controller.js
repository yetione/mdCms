var BaseController = function($scope){
    var self = this;

    self.data = [];

    activate();
    function activate(){
        for (var i=0;i<10;++i){
            self.data.push({name:'Product '+i, description:'Product '+i+' description', id:i+1});
        }
        console.log('test activate', self.data);
    }

};

testApp.controller('BaseController', ['$scope', BaseController]);
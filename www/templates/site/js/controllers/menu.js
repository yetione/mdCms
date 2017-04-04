var MenuController = function($scope, $rootScope, entityFactory, CartService){
    var self = this;
    self.days = [];
    self.endOrderTime = {Hours:19, Minutes:0, Seconds:0};
    self.daysCount = 7;
    self.monthsName = ['января', 'февраля', 'марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
    self.daysName = ['вс','пн','вт','ср','чт','пт','сб'];
    self.activeDay = {};
    self.menuItems = [];


    self.menuManager = entityFactory('Menu');
    self.productManger = entityFactory('Product');

    self.setActiveDay = setActiveDay;
    self.getDatePrice = getDatePrice;

    $scope.$on('Cart.productAdd', onAddProduct);
    $scope.$on('Cart.loaded', onCartLoaded);

    $scope.$on('Cart.productRemove', onProductDelete);
    /*$scope.$on('Cart.productChanged', onCartChange);
    $scope.$on('Cart.clear', onCartChange);*/


    activate();
    function activate(){}

    function onCartLoaded(event,data){
        createMenu();
    }

    function onAddProduct(event, data){
        var day = getDay(data.Date);
        day.Price = getDatePrice(data.Date);
    }

    function getDay(date){
        for (var i=0;i<self.days.length;i++){
            if (self.days[i].Entity.Date == date){
                return self.days[i];
            }
        }
        return false;
    }
    function onProductDelete(event, data) {
        var day = getDay(data.Date);
        day.Price = getDatePrice(data.Date);
    }



    function createMenu(){
        var startDate = new Date(), endDate = new Date(), temp;
        endDate.setHours(self.endOrderTime.Hours);
        endDate.setMinutes(self.endOrderTime.Minutes);
        endDate.setSeconds(self.endOrderTime.Seconds);
        startDate.setDate(startDate.getDate()+(startDate > endDate ? 2 : 1)); //Заказ после определенного времени не доступен на след. день
        //startDate.setDate(startDate.getDate()+1); //Заказ после определенного времени не доступен на след. день
        self.menuManager.getList({Date:[getStrDate(startDate), '>='],Enabled:1,_count:self.daysCount,_orderBy:[['Date', 'ASC']]}).then(function(menuItems){
            var temp, day;
            self.days = menuItems.map(function(item, index, array){
                temp = new Date(item.Date);
                day = {
                    Entity:item,
                    Title:temp.getDate()+' '+self.monthsName[temp.getMonth()],
                    DayName:self.daysName[temp.getDay()],
                    Price:getDatePrice(item.Date),
                    HasProducts:false
                };
                return day;
            });
            self.setActiveDay(0);
        });
    }

    function getDatePrice(date){
        return null === CartService.getDateInfo(date) ? 0 : CartService.getDateInfo(date).price;
    }

    function setActiveDay(index){
        if (index < 0) return;
        self.activeDay = self.days[index];
        $rootScope.$broadcast('Menu.daySelected', {MenuItem:self.activeDay});
    }

    function getStrDate(date){
        var month = date.getMonth()+ 1, day = date.getDate();
        if (month < 10){ month = '0'+month}
        if (day < 10){ day = '0'+day}
        return date.getFullYear()+'-'+month+'-'+day;
    }


};

siteApp.controller('MenuController', ['$scope', '$rootScope', 'EntityFactory', 'CartService',  MenuController]);
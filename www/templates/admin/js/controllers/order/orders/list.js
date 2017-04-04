var OrderOrdersListController = function($scope, $rootScope, BackendService, EntityFactory, $idialog){
    var self = this;
    self.citiesManager = EntityFactory('City');
    self.ordersManager = EntityFactory('Order');
    self.cities = [];
    self.filters = {
        activeCity:null,
        text:'',
        date:{start:null, end:null}
    };
    self.loading = {city:false, orders:false};
    self.cityLoading = false;
    self.list = [];

    self.onCitySelect = onCitySelect;
    self.onDateChange = onDateChange;
    self.onItemMove = onItemMove;


    self.tableColumns = [
        {sortable:true}, {sortable:false, sortDirection:'asc'}
    ];

    $scope.$on('page:citiesChanged', function (e, cities) {
        self.cities = cities;
        self.onCitySelect(self.cities[0]);
    });
    $scope.$on('order:orders:item:saved', function (e, order) {
        updateList();
    });

    activate();
    function activate() {
        self.filters.date.start = moment().hours(0).minute(0).seconds(0);
        self.filters.date.end = moment().hours(0).minute(0).seconds(0).add(1, 'days');
    }

    function onCitySelect($item, $event, $isNull){
        self.filters.activeCity = $item;
        updateList();
    }

    function onDateChange(modelName, newDate) {
        var changedDate = modelName.substr(modelName.lastIndexOf('.')+1);
        if (self.filters.date.start > self.filters.date.end){
            self.filters.date.start = self.filters.date.end;
        }
        if (self.filters.date.end < self.filters.date.start){
            self.filters.date.end = self.filters.date.start;
        }
        updateList();
    }

    function updateList(){
        self.loading.orders = true;
        function getStrDate(m){
            return m.format('YYYY-MM-DD HH:mm:ss');
        }
        self.ordersManager.getList({CityId:self.filters.activeCity.Id, DateCreated:[[self.filters.date.start.format('YYYY-MM-DD HH:mm:ss'), self.filters.date.end.format('YYYY-MM-DD HH:mm:ss')], 'BETWEEN']}).then(function(response){
            response = response.map(function(item, i, arr){
                item.Price = parseFloat(item.Price);
                item.DateCreated = moment(item.DateCreated, 'YYYY-MM-DD HH:mm:ss');
                return item;
            });
            self.list = response;

            self.loading.orders = false;
        },function (response) {self.loading.orders = false;});
    }

    function onItemMove(item, index, event) {

        self.list.splice(index, 1);
        console.log('move', item.DateCreated, index, self.list);

    }
};

adminApp.controller('order.orders.list', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', OrderOrdersListController]);
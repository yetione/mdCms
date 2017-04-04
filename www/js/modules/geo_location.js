(function (windows, angular) {
    var GeoLocationService = function($rootScope, $cookies, EntityFactory, BackendService, $q, $timeout){
        var self = this;
        self.config = {
            cookiesCityKey:'GL_data_new'
        };
        self.citiesManger = EntityFactory('City');
        self.cities = [];
        self.currentCity = false;
        self.metroStations = {};
        self.metroStationsList = {};

        self.setCurrentCity = setCurrentCity;
        self.checkCurrentCity = checkCurrentCity;
        self.getCurrentCity = getCurrentCity;
        self.changeCurrentCity = changeCurrentCity;
        self.getCityMetroStation = getCityMetroStation;
        self.getCities = getCities;
        self.loadMetroStations = loadMetroStations;
        self.getMetroStations = getMetroStations;
        self.run = run;

        //activate();
        function activate(){
            setDefaultMetroStation();
            loadMetroStations();
            getCities().then(function(cities){
                if (!getCurrentCity()){
                    BackendService.get({module:'GeoLocation', controller:'Utils', action:'getDefaultCity'}).then(function(response){
                        var responseData = response.data;
                        if (responseData.status === 'OK'){
                            setCurrentCity(responseData.data);
                            if (!checkCurrentCity()){
                                console.error('GeoLocationService: Current city is not valid. Current city:', self.currentCity);
                            }
                        }else if(responseData.status === 'error'){
                            console.error('GeoLocationService: cant load default city.', response)
                        }
                    },function(response){
                        console.error('GeoLocationService: Cant load default city', response);
                    });
                }else{
                    if (!checkCurrentCity()){
                        console.error('GeoLocationService: Current city is not valid. Current city:', self.currentCity);
                    }
                }
            });
        }

        function getCities(update) {
            var deferred = $q.defer();
            var f = function(){
                if (self.cities.length && !update){
                    deferred.resolve(self.cities);
                }else{
                    self.citiesManger.getList({IsActive:1}).then(function (list) {
                        self.cities = list;
                        deferred.resolve(self.cities);
                    },function(response){deferred.reject(response)});
                }
            };
            $timeout(f);
            return deferred.promise;
        }

        function loadMetroStations() {
            var deferred = $q.defer();
            var f = function(){
                BackendService.load('_data/metro_stations.json').then(function(response){
                    self.metroStationsList = response.data;
                    deferred.resolve(response.data);
                }, function(response){console.error('GeoLocationService::loadMetroStations : cant load stations list');deferred.reject(response);});
            };
            $timeout(f);
            return deferred.promise;
        }

        function getMetroStations(city) {
            var deferred = $q.defer();
            var f = function(){
                if (Object.keys(self.metroStationsList).length == 0){
                    loadMetroStations().then(function(list){
                        deferred.resolve(city.Machine in self.metroStationsList ? self.metroStationsList[city.Machine] : []);
                    });
                }else{
                    deferred.resolve(city.Machine in self.metroStationsList ? self.metroStationsList[city.Machine] : []);
                }
            };
            $timeout(f);
            return deferred.promise;
        }

        function changeCurrentCity(cityId){
            return BackendService.get({module:'GeoLocation', controller:'Utils', action:'changeCurrentCity', Id:cityId}).then(function(response){
                var responseData = response.data;
                if (responseData.status === 'OK'){
                    self.citiesManger.addEntity(responseData.data);
                    setCurrentCity(responseData.data);
                    if (!checkCurrentCity()){
                        console.error('GeoLocationService: Current city is not valid. Current city:', self.currentCity);
                    }else{
                        $rootScope.$broadcast('GeoLocationService:currentCityChange', {City:self.currentCity});
                    }
                }else if(responseData.status === 'error'){
                    console.error('GeoLocationService: cant change city.', response)
                }
            }, function(response){
                console.error('GeoLocationService: cant change city.', response)
            });
        }

        function getCityMetroStation(city) {
            var data = city.Machine in self.metroStations ? self.metroStations[city.Machine] : [],
                result = [];
            for(var i=0;i<data.length;i++){
                result = result.concat(data[i].stations)
            }
            return result;
        }

        function setCurrentCity(city){
            self.currentCity = city;
            $cookies.putObject(self.config.cookiesCityKey, city);
        }

        function checkCurrentCity(){
            for (var i=0;i<self.cities.length;i++){
                if (self.cities[i].Id == self.currentCity.Id){
                    return true;
                }
            }
            $rootScope.$broadcast('GeoLocationService:currentCityIsNotValid', {City:self.currentCity});
            return false;
        }

        function getCurrentCity(){
            if (!self.currentCity || !('Id' in self.currentCity)){
                self.currentCity = $cookies.getObject(self.config.cookiesCityKey);
            }
            return self.currentCity;
        }

        function setDefaultMetroStation() {
            self.metroStations = {
                spb:[
                    {
                        "line": "Кировско-Выборгская",
                        "stations": ["Девяткино", "Гражданский проспект", "Академическая", "Политехническая", "Площадь Мужества", "Лесная", "Выборгская", "Площадь Ленина", "Чернышевская", "Площадь Восстания", "Владимирская", "Пушкинская", "Технологический институт", "Балтийская", "Нарвская", "Кировский завод", "Автово", "Ленинский проспект", "Проспект Ветеранов"]
                    },
                    {
                        "line": "Московско-Петроградская",
                        "stations": ["Парнас", "Проспект Просвещения", "Озерки", "Удельная", "Пионерская", "Чёрная речка", "Петроградская", "Горьковская", "Невский проспект", "Сенная площадь", "Технологический институт", "Фрунзенская", "Московские ворота", "Электросила", "Парк Победы", "Московская", "Звёздная", "Купчино"]
                    },
                    {
                        "line": "Невско-Василеостровская",
                        "stations": ["Приморская", "Василеостровская", "Гостиный двор", "Маяковская", "Площадь Александра Невского", "Елизаровская", "Ломоносовская", "Пролетарская", "Обухово", "Рыбацкое"]
                    },
                    {
                        "line": "Правобережная линия",
                        "stations": ["Спасская", "Достоевская", "Лиговский проспект", "Площадь Александра Невского", "Новочеркасская", "Ладожская", "Проспект Большевиков", "Улица Дыбенко"]
                    },
                    {
                        "line": "Фрунзенско-Приморская",
                        "stations": ["Комендантский проспект", "Старая Деревня", "Крестовский остров", "Чкаловская", "Спортивная", "Адмиралтейская", "Садовая", "Звенигородская", "Обводный канал", "Волковская", "Бухарестская", "Международная"]
                    }
                    ],
                msk:[
                    {
                        "line": "Сокольническая",
                        "stations": ["Бульвар Рокоссовского", "Черкизовская", "Преображенская площадь", "Сокольники", "Красносельская", "Комсомольская", "Красные ворота", "Чистые пруды", "Лубянка", "Охотный ряд", "Библиотека имени Ленина", "Кропоткинская", "Парк культуры", "Фрунзенская", "Спортивная", "Воробьёвы горы", "Университет", "Проспект Вернадского", "Юго-Западная", "Тропарёво", "Румянцево", "Саларьево"]
                    },
                    {
                        "line": "Замоскворецкая",
                        "stations": ["Алма-Атинская", "Красногвардейская", "Домодедовская", "Орехово", "Царицыно", "Кантемировская", "Каширская", "Коломенская", "Технопарк", "Автозаводская", "Павелецкая", "Новокузнецкая", "Театральная", "Тверская", "Маяковская", "Белорусская", "Динамо", "Аэропорт", "Сокол", "Войковская", "Водный стадион", "Речной вокзал"]
                    },
                    {
                        "line": "Арбатско-Покровская",
                        "stations": ["Щёлковская", "Первомайская", "Измайловская", "Партизанская", "Семёновская", "Электрозаводская", "Бауманская", "Курская", "Площадь Революции", "Арбатская", "Смоленская", "Киевская", "Парк Победы", "Славянский бульвар", "Кунцевская", "Молодёжная", "Крылатское", "Строгино", "Мякинино", "Волоколамская", "Митино", "Пятницкое шоссе"]
                    },
                    {
                        "line": "Филёвская",
                        "stations": ["Кунцевская", "Пионерская", "Филевский парк", "Багратионовская", "Фили", "Кутузовская", "Студенческая", "Киевская", "Смоленская", "Арбатская", "Александровский сад", "Выставочная", "Международная"]
                    },
                    {
                        "line": "Кольцевая",
                        "stations": ["Парк культуры", "Октябрьская", "Добрынинская", "Павелецкая", "Таганская", "Курская", "Комсомольская", "Проспект Мира", "Новослободская", "Белорусская", "Краснопресненская", "Киевская"]
                    },
                    {
                        "line": "Калужско-Рижская",
                        "stations": ["Медведково", "Бабушкинская", "Свиблово", "Ботанический сад", "ВДНХ", "Алексеевская", "Рижская", "Проспект Мира", "Сухаревская", "Тургеневская", "Китай-город", "Третьяковская", "Октябрьская", "Шаболовская", "Ленинский проспект", "Академическая", "Профсоюзная", "Новые Черёмушки", "Калужская", "Беляево", "Коньково", "Тёплый стан", "Ясенево", "Новоясеневская"]
                    },
                    {
                        "line": "Таганско-Краснопресненская",
                        "stations": ["Планерная", "Сходненская", "Тушинская", "Спартак", "Щукинская", "Октябрьское поле", "Полежаевская", "Беговая", "Улица 1905 года", "Баррикадная", "Пушкинская", "Кузнецкий мост", "Китай-город", "Таганская", "Пролетарская", "Волгоградский проспект", "Текстильщики", "Кузьминки", "Рязанский проспект", "Выхино", "Лермонтовский проспект", "Жулебино", "Котельники"]
                    },
                    {
                        "line": "Калининско-Солнцевская",
                        "stations": ["Новокосино", "Новогиреево", "Перово", "Шоссе Энтузиастов", "Авиамоторная", "Площадь Ильича", "Марксистская", "Третьяковская", "Деловой центр", "Парк победы"]
                    },
                    {
                        "line": "Серпуховско-Тимирязевская",
                        "stations": ["Алтуфьево", "Бибирево", "Отрадное", "Владыкино", "Петровско-Разумовская", "Тимирязевская", "Дмитровская", "Савёловская", "Менделеевская", "Цветной бульвар", "Чеховская", "Боровицкая", "Полянка", "Серпуховская", "Тульская", "Нагатинская", "Нагорная", "Нахимовский проспект", "Севастопольская", "Чертановская", "Южная", "Пражская", "Улица академика Янгеля", "Аннино", "Бульвар Дмитрия Донского"]
                    },
                    {
                        "line": "Люблинско-Дмитровская",
                        "stations": ["Марьина роща", "Достоевская", "Трубная", "Сретенский бульвар", "Чкаловская", "Римская", "Крестьянская застава", "Дубровка", "Кожуховская", "Печатники", "Волжская", "Люблино", "Братиславская", "Марьино", "Борисово", "Шипиловская", "Зябликово"]
                    },
                    {
                        "line": "Каховская",
                        "stations": ["Каширская", "Варшавская", "Каховская"]
                    },
                    {
                        "line": "Бутовская",
                        "stations": ["Битцевский парк", "Лесопарковая", "Улица Старокачаловская", "Улица Скобелевская", "Бульвар адмирала Ушакова", "Улица Горчакова", "Бунинская аллея"]
                    },
                    {
                        "line": "Московский монорельс",
                        "stations": ["Тимирязевская", "Улица Милашенкова", "Телецентр", "Улица Академика Королёва", "Выставочный центр", "Улица Сергея Эйзенштейна"]
                    },
                    {
                        "line": "Московское центральное кольцо",
                        "stations": ["Владыкино", "Окружная", "Лихоборы", "Коптево", "Балтийская", "Стрешнево", "Панфиловская", "Зорге", "Хорошево", "Шелепиха", "Деловой центр", "Кутузовская", "Лужники", "Площадь Гагарина", "Крымская", "Верхние котлы", "ЗИЛ", "Автозаводская", "Дубровка", "Угрешская", "Новохохловская", "Нижегородская", "Андроновка", "Шоссе энтузиастов", "Соколиная гора", "Измайлово", "Локомотив", "Бульвар Рокоссовского", "Белокаменная", "Ростокино", "Ботанический сад"]
                    }
                ]
            };
        }

        function run() {
            activate();
        }
    };

    angular.module('mdGeoLocation', ['ngCookies', 'mdEntity', 'mdBackend'])
        .service('GeoLocationService', ['$rootScope', '$cookies', 'EntityFactory', 'BackendService', '$q', '$timeout', GeoLocationService]);
})(window, window.angular);
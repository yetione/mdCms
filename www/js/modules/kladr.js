(function (windows, angular) {
    var KladrQuery = function(options){
        this.options = angular.extend({
            token:'',
            regionId:'',
            districtId:'',
            cityId:'',
            streetId:'',
            buildingId:'',
            query:'',
            contentType:'',
            withParent:'',
            limit:'',
            offset:'',
            typeCode:'',
            zip:'',
            oneString:0
        }, options);
    };

    KladrQuery.prototype.set = function(option, value){
        if (this.options.hasOwnProperty(option)){
            this.options[option] = value;
        }
    };

    KladrQuery.prototype.setOptions = function(options){
        this.options = angular.extend(this.options, options);
    };

    KladrQuery.prototype.getOptions = function(){
        return this.options;
    };

    var KladrService = function($rootScope, $http){
        var self = this;
        /**
         * Перечисление типов объектов
         * @type {{region: string, district: string, city: string, street: string, building: string}}
         */
        self.type = {
            region:   'region',   // Область
            district: 'district', // Район
            city:     'city',     // Город
            street:   'street',   // Улица
            building: 'building'  // Строение
        };
        /**
         * Перечисление типов населённых пунктов
         * @type {{city: number, settlement: number, village: number}}
         */
        self.typeCode = {
            city:       1, // Город
            settlement: 2, // Посёлок
            village:    4  // Деревня
        };
        self.configs = {
            token:'',
            serviceUri:'http://kladr-api.ru/api.php?',
            key:''
        };

        self.getQuery = getQuery;
        self.execute = execute;
        self.run = run;
        activate();
        function activate(){

        }

        function run(configs){
            self.configs = angular.extend(self.configs, configs);
            $rootScope.$broadcast('KladrService:run', this);
        }

        function getQuery(options){
            return new KladrQuery(options);
        }

        /**
         *
         * @returns {*}
         * @param query
         */
        function execute(query){
            if (!query.options.token){
                query.options.token = self.configs.token;
            }
            query.options.callback = 'JSON_CALLBACK';
            return $http.jsonp(self.configs.serviceUri, {params:query.getOptions()}).then(function(response){
                return response.data;

            });
        }
    };

    angular.module('ngKladr', [])
        .service('KladrService', ['$rootScope', '$http', KladrService]);
})(window, window.angular);
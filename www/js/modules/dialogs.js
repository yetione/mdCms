(function (windows, angular) {
    var DialogConfirmController = function($scope){
        var self = this;
        self.options = {
            yesCb:function($scope){$scope.hide();},
            noCb:function($scope){$scope.hide();},
            onDialogActivate:function($scope){},
            onControllerActivate:function ($scope) {},
            beforeShow:function ($scope, deferred) {deferred.resolve();return true;},
            onShow:function($scope){},
            beforeHide:function ($scope, deferred) {deferred.resolve();return true;},
            onHide:function($scope){},
            message:'', title:'Подтверждение действия'
        };
        self.yes = onYesClick;
        self.no = onNoClick;


        activate();
        function activate(){
            angular.extend(self.options, $scope.dialogOptions);
            if (self.options.onControllerActivate){
                self.options.onControllerActivate($scope);
            }
        }

        $scope.$on('show', function(event, data){
            if (self.options.onShow){
                self.options.onShow($scope);
            }
        });

        $scope.$on('hide', function(event, data){
            if (self.options.onHide){
                self.options.onHide($scope);
            }
        });

        function onYesClick(){
            self.options.yesCb($scope);
        }

        function onNoClick(){
            self.options.noCb($scope);
        }
    };

    var DialogMessageController = function($scope){
        var self = this;
        self.options = {
            message:'',title:'Сообщение',
            okCb:function ($scope) {
                $scope.hide();
            },
            onDialogActivate:function($scope){},
            onControllerActivate:function ($scope) {},
            beforeShow:function ($scope, deferred) {deferred.resolve();return true;},
            onShow:function($scope){},
            beforeHide:function ($scope, deferred) {deferred.resolve();return true;},
            onHide:function($scope){}
        };

        self.ok = onOKClick;

        activate();
        function activate(){
            angular.extend(self.options, $scope.dialogOptions);
            if (self.options.onControllerActivate){
                self.options.onControllerActivate($scope);
            }

        }
        $scope.$on('show', function(event, data){
            if (self.options.onShow){
                self.options.onShow($scope);
            }
        });

        $scope.$on('hide', function(event, data){
            if (self.options.onHide){
                self.options.onHide($scope);
            }
        });

        function onOKClick() {
            self.options.okCb($scope);
        }
    };

    angular.module('mdDialogs', [])
        .controller('mdDialogs.Message', ['$scope', DialogMessageController])
        .controller('mdDialogs.Confirm', ['$scope', DialogConfirmController]);
})(window, window.angular);
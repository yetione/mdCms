var ShopPromoCodePageController = function($scope, $rootScope, BackendService, EntityFactory, $idialog, Notification){
    var self = this;
    self.PCManager = EntityFactory('PromoCode');
    self.codesList = [];
    self.activeItem = null;

    self.onlyDigits = /^\-?\d+(\.\d{0,})?$/;
    self.emptyEntity = {};
    self.PCTypes = [{Id:1, Name:'Многоразовый'}, {Id:2, Name:'Одноразовый'}];
    self.filters = {};

    self.editItem = editItem;
    self.deleteItem = deleteItem;
    self.saveActiveItem = saveActiveItem;
    self.deleteActiveItem = deleteActiveItem;

    self.codeTypes = [
        {Id:1, Name:'Весь заказ'},
        {Id:2, Name:'Привоз'}

    ];
    self.actileList = [{Id:0, Name:'Не активен'}, {Id:1, Name:'Активен'}];
    self.usedList = [{Id:0, Name:'Нет'}, {Id:1, Name:'Да'}];



    activate();
    function activate() {
        self.PCManager.getAll().then(function(promoCodes){
            for (var i=0;i<promoCodes.length;++i){
                promoCodes[i] = fromEntity(promoCodes[i]);
            }
            self.codesList = promoCodes;
        });
        self.PCManager.getEmpty().then(function (code) {
            if (self.activeItem === null){
                code.Data = {Type:2, Options:{Value:'', Units:'percents'}};
                code.ExpireDate = moment();
                code.StartDate = moment();
                code.Type = 1;
                code.Active = 1;
                //code.ActiveModel = self.actileList[0];
                code.Used = 0;
                //code.UsedModel = self.usedList[0];
                self.emptyEntity = code;
                editItem();
            }
        });


    }

    function saveActiveItem() {
        var item = toEntity(angular.copy(self.activeItem));
        self.PCManager.saveItem(item).then(function(entity){
            entity = fromEntity(entity);
            /*if (!item.Id){
                item = fromEntity(item);
                //self.codesList.push(item);
            }else{
                for(var i=0;i<self.codesList.length;++i){
                    if (self.codesList[i].Id == item.Id){
                        self.codesList[i] = fromEntity(item);
                        item = self.codesList[i];
                        break;
                    }
                }
            }*/
            Notification.success({message:'Промо-код сохранен.', delay:1000, positionY:'bottom', positionX:'right'});
            $scope.PromoCodeEditForm.$setPristine();
            editItem(entity);

        });

    }

    function editItem(code){
        if (!code){
            code = self.emptyEntity;
        }
        var f = function () {
            var i;
            self.activeItem = angular.copy(code);
            for(i=0;i<self.PCTypes.length;++i){
                if (code.Type == self.PCTypes[i].Id){
                    self.activeItem.TypeModel = self.PCTypes[i];
                    break;
                }
            }
            self.activeItem.ActiveModel = self.actileList[parseInt(self.activeItem.Active)];
            self.activeItem.UsedModel = self.usedList[parseInt(self.activeItem.Used)];
            self.activeItem.CodeTypeModel = self.codeTypes.filter(function(item, i, arr){
                return item.Id == self.activeItem.Data.Type;
            })[0];
            $scope.PromoCodeEditForm.$setPristine();
        };
        if ($scope.PromoCodeEditForm.$dirty){
            $idialog('confirm-dialog',{dialogId:'resumeWithoutSave',options:{
                message:'Данные были изменены. Продолжить без сохранения?',
                yesCb:function($scope){
                    f();
                    $scope.hide();
                }
            }});
        }else{
            f();
        }


    }

    function deleteItem(code) {
        $idialog('confirm-dialog',{dialogId:'resumeWithoutSave',options:{
            message:'Подтверждаете удаление?',
            yesCb:function($scope){
                self.PCManager.deleteItem(code).then(function(resp){
                    Notification.success({message:'Промо-код удален.', delay:1000, positionY:'bottom', positionX:'right'});
                }, function (resp) {
                    Notification.success({message:'Ошибка при удалении промо-кода.', delay:1000, positionY:'bottom', positionX:'right'});
                    console.error('PromocodePage: error whe try to delete.', resp);
                });
                $scope.hide();
            }
        }});
    }

    function deleteActiveItem() {

    }

    function fromEntity(entity) {
        entity.Data = angular.fromJson(entity.Data);
        entity.ExpireDate = moment.unix(parseInt(entity.ExpireDate));
        entity.StartDate = moment.unix(parseInt(entity.StartDate));
        return entity;
    }

    function toEntity(entity) {
        entity.Data.Type = entity.CodeTypeModel.Id;

        entity.Data = angular.toJson(entity.Data);
        entity.ExpireDate = entity.ExpireDate.unix();
        entity.StartDate = entity.StartDate.unix();
        entity.Type = entity.TypeModel.Id;
        entity.Active = entity.ActiveModel.Id;
        entity.Used = entity.UsedModel.Id;
        return entity;
    }
};

adminApp.controller('shop.promoCodePage', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', 'Notification', ShopPromoCodePageController]);
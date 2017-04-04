<script type="text/ng-template" id="order/couriers/edit">
    <form name="EntityEditForm" ng-controller="mdDialogs.Message as dialogCtrl" ng-submit="dialogCtrl.options.formSubmit(this)" novalidate="" >
        <div class="window dialog">
            <div class="header">
                <span class="label" ng-show="dialogCtrl.options.entity.Id > 0">Редактирование данных о курьере</span>
                <span class="label" ng-show="dialogCtrl.options.entity.Id <= 0">Добавление нового курьера</span>
            </div>

            <div class="content">
                <section>
                    <div class="light-gray-block">
                        <span class="label">Ф.И.О.</span>
                        <input type="text" class="gray light-gray height-28 width-450" ng-model="dialogCtrl.options.entity.Name" style="width:auto;" required name="Name" autocomplete="off">
                    </div>
                    <div class="light-gray-block">
                        <span class="label">Телефон</span>
                        <input type="text" class="gray light-gray height-28 width-450" ng-model="dialogCtrl.options.entity.Phone" style="width:auto;" required name="Phone" ui-mask="{{dialogCtrl.options.phoneMask}}" ui-mask-placeholder="" autocomplete="off">
                    </div>
                    <div class="light-gray-block">
                        <span class="label">Город</span>
                        <select-box source="dialogCtrl.options.city.list" on-select="dialogCtrl.options.city.onCitySelect($item, $event, $isNull)" ng-model="dialogCtrl.options.city.activeCity" null-item="dialogCtrl.options.city.nullItem" class="width-450 height-33 lg">
                            <sb-header class="c-select-box-header pl">{{$select.selected.Name}}</sb-header>
                            <sb-list>
                                <sb-list-null-item><span class="Name">{{$select.nullItem.Name}}</span></sb-list-null-item>
                                <sb-list-item ng-repeat="item in $select.items track by item.Id">
                                    <span class="Name">{{item.Name}}</span> <span class="Machine">({{item.Machine}})</span>
                                </sb-list-item>
                            </sb-list>
                        </select-box>
                    </div>
                </section>
            </div>
            <div class="footer">
                <input type="submit" value="Сохранить" class="button" ng-disabled="dialogCtrl.options.updating">
                <button type="button" ng-click="dialogCtrl.options.cancel(this)" >Отмена</button>
                <button type="button" ng-click="dialogCtrl.options.remove(this)" class="ng-hide" ng-hide="dialogCtrl.options.entity.Id == 0">Удалить</button>
            </div>

        </div>
    </form>
</script>
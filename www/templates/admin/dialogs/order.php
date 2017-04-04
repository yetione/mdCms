<script type="text/ng-template" id="add-day">
    <div class="window dialog" ng-controller="mdDialogs.Message as dialogCtrl">
        <div class="header" ng-show="dialogCtrl.options.title != ''">
            <span class="label">{{dialogCtrl.options.title}}</span>
        </div>
        <div class="content">
            <section>
                <div class="light-gray-block">
                    <p class="label">Выберите дату</p>
                    <div date-picker max-view="date" min-view="date" ng-model="dialogCtrl.options.date" class="date-picker"></div>
                </div>
            </section>

        </div>
        <div class="footer">
            <button type="button" ng-click="dialogCtrl.options.add(this)">Добавить</button>
            <button type="button" ng-click="hide()">Закрыть</button>
        </div>
    </div>
</script>

<script type="text/ng-template" id="edit-courier-dialog">
    <form name="CourierEditForm" ng-controller="mdDialogs.Message as dialogCtrl" ng-submit="dialogCtrl.options.formSubmit(this)" novalidate="" >
        <div class="window dialog">
            <div class="header">
                <span class="label" ng-show="dialogCtrl.options.entity.Id > 0">Редактирование</span>
                <span class="label" ng-show="dialogCtrl.options.entity.Id <= 0">Добавление</span>
            </div>

            <div class="content">
                <section>
                    <div class="light-gray-block">
                        <span class="label">Ф.И.О.</span>
                        <input type="text" class="gray light-gray height-33" ng-model="dialogCtrl.options.entity.Name" style="width:auto;" required name="Name" autocomplete="off">
                    </div>
                    <div class="light-gray-block">
                        <span class="label">Телефон</span>
                        <input type="text" class="gray light-gray height-33" ng-model="dialogCtrl.options.entity.Phone" style="width:auto;" required name="Phone" autocomplete="off">
                    </div>
                    <div class="light-gray-block">
                        <span class="label">Город</span>
                        <div class="select-box width-250 height-33 lg" select-inline="couriers-city">
                            <div class="header">
                                <p class="label">{{dialogCtrl.options.cities.header}}</p>
                                <img class="arrow" ng-src="{{dialogCtrl.options.cities.arrowImg}}">
                            </div>
                            <div class="list-wrapper">
                                <ul>
                                    <li ng-repeat="city in dialogCtrl.options.cities.items">
                                        <label>
                                            <p ng-click="dialogCtrl.options.cities.setActiveItem($index)" class="label">{{city.Name}}</p>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="footer">
                <input type="submit" value="Сохранить" class="button">
                <button type="button" ng-click="dialogCtrl.options.hide(this)" >Отмена</button>
                <button type="button" ng-click="dialogCtrl.options.remove(this)" ng-hide="dialogCtrl.options.entity.Id == 0">Удалить</button>
            </div>

        </div>
    </form>
</script>

<script type="text/ng-template" id="order/documents/orders_list.html">
    <div class="window dialog orders-list" ng-controller="order.documents.ordersList as dialogCtrl" style="width: 1000px;">
        <div class="header">
            <span class="label">Заказы. Город: {{dialogCtrl.options.City.Name}}. {{dialogCtrl.options.Day.Moment.format('dddd, D MMMM')}}</span>
            <div style="margin-top: 15px;">
            <div class="select-box width-200 height-33" select-inline="order-status-list">
                <div class="header">
                    <p class="label">{{dialogCtrl.orderStatusesList.header}}</p>
                    <img class="arrow" ng-src="{{dialogCtrl.orderStatusesList.arrowImg}}">
                </div>
                <div class="list-wrapper">
                    <ul>
                        <li><p class="label" ng-click="dialogCtrl.orderStatusesList.setNull()">Любой</p></li>
                        <li class="delimetr"></li>
                        <li ng-repeat="item in dialogCtrl.orderStatusesList.items">
                            <p ng-click="dialogCtrl.orderStatusesList.setActiveItem($index)" class="label">{{item}}</p>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="select-box width-200 height-33" select-inline="delivery-type-list">
                <div class="header">
                    <p class="label">{{dialogCtrl.deliveryTypesList.header}}</p>
                    <img class="arrow" ng-src="{{dialogCtrl.deliveryTypesList.arrowImg}}">
                </div>
                <div class="list-wrapper">
                    <ul>
                        <li><p class="label" ng-click="dialogCtrl.deliveryTypesList.setNull()">Любой</p></li>
                        <li class="delimetr"></li>
                        <li ng-repeat="item in dialogCtrl.deliveryTypesList.items">
                            <p ng-click="dialogCtrl.deliveryTypesList.setActiveItem($index)" class="label">{{item}}</p>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="select-box width-200 height-33" select-inline="payment-type-list">
                <div class="header">
                    <p class="label">{{dialogCtrl.paymentTypesList.header}}</p>
                    <img class="arrow" ng-src="{{dialogCtrl.paymentTypesList.arrowImg}}">
                </div>
                <div class="list-wrapper">
                    <ul>
                        <li><p class="label" ng-click="dialogCtrl.paymentTypesList.setNull()">Любой</p></li>
                        <li class="delimetr"></li>
                        <li ng-repeat="item in dialogCtrl.paymentTypesList.items">
                            <p ng-click="dialogCtrl.paymentTypesList.setActiveItem($index)" class="label">{{item}}</p>
                        </li>
                    </ul>
                </div>
            </div>

                <div style="display: inline-block;position: relative">
                    <input type="text" placeholder="Улица" ng-model="dialogCtrl.filters.Street" class="gray width-150 height-33 search" style="padding-right: 20px;width:200px;" autocomplete="off">
                    <img src="templates/admin/images/search.png" style="position: absolute;right: 3px;top: 9px;">
                </div>
            </div>
        </div>
        <div class="content">
            <section style="height: 350px;overflow-y: auto;background-color: #070c12;">
                <table class="data-table" ng-hide="dialogCtrl.listIsUpdating">
                    <thead>
                    <tr>
                        <th class="selected"></th>
                        <th class="delivery-time">Время доставки</th>
                        <th class="delivery-address">Адресс доставки</th>
                        <th class="payment-type">Способ оплаты</th>
                        <th class="price">Сумма</th>
                        <th class="status">Статус</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr ng-repeat="order in dialogCtrl.list | filter:dialogCtrl.filters">
                        <td class="selected"><input type="checkbox" ng-model="order.Selected"></td>
                        <td class="delivery-time" ng-click="dialogCtrl.selectOrder(order)">{{order.DeliveryTime}}</td>
                        <td class="delivery-address" ng-click="dialogCtrl.selectOrder(order)">{{order.Street}} д. {{order.Building}} кв. {{order.Room}}</td>
                        <td class="payment-type" ng-click="dialogCtrl.selectOrder(order)">{{order.PaymentType}}</td>
                        <td class="price" ng-click="dialogCtrl.selectOrder(order)">{{order.Price}}</td>
                        <td class="status" ng-click="dialogCtrl.selectOrder(order)">{{order.Status}}</td>
                    </tr>
                    </tbody>
                </table>
                <div class="loading-block" ng-show="dialogCtrl.listIsUpdating">
                    <div class="cssload-jumping">
                        <span></span><span></span><span></span><span></span><span></span>
                    </div>
                </div>
            </section>
        </div>
        <div class="footer">
            <button type="button" ng-click="dialogCtrl.add()">Добавить</button>
            <button type="button" ng-click="hide()">Закрыть</button>

        </div>
    </div>
</script>

<script type="text/ng-template" id="order/documents/select_courier.html">
    <div class="window dialog orders-list" ng-controller="order.documents.selectCourier as dialogCtrl" style="width: 600px;">
        <div class="header">
            <span class="label">Выбор курьера</span>
        </div>
        <div class="content">
            <section style="height: 200px;overflow-y: auto;background-color: #070c12;">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th class="Name">Имя</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr ng-repeat="item in dialogCtrl.list">
                        <td class="Name" ng-click="dialogCtrl.selectItem(item)">{{item.Name}}</td>
                    </tr>
                    </tbody>
                </table>
            </section>
        </div>
        <div class="footer">
            <button type="button" ng-click="dialogCtrl.setNull()">Удалить курьера</button>
            <button type="button" ng-click="hide()">Закрыть</button>
        </div>
    </div>
</script>

<?php require 'order/edit_courier.php';?>
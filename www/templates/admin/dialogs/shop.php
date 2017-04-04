<script type="text/ng-template" id="edit-product-type-dialog">
    <form name="ProductTypeEditForm" ng-controller="mdDialogs.Message as dialogCtrl" ng-submit="dialogCtrl.options.formSubmit(this)" novalidate="" >
        <div class="window dialog">
            <div class="header">
                <span class="label" ng-show="dialogCtrl.options.typeId > 0">Редактирование категории</span>
                <span class="label" ng-show="dialogCtrl.options.typeId <= 0">Добавление категории</span>
            </div>

            <div class="content">
                <section>
                    <div class="light-gray-block">
                        <span class="label">Название</span>
                        <input type="text" class="gray light-gray height-33" ng-model="dialogCtrl.options.typeName" style="width:auto;" required name="Name" autocomplete="off">
                    </div>
                </section>
            </div>
            <div class="footer">
                <input type="submit" value="Сохранить" class="button" ng-disabled="ProductTypeEdit.$invalid">
                <button type="button" ng-click="dialogCtrl.options.hide(this)" >Отмена</button>
                <button type="button" ng-click="dialogCtrl.options.remove(this)" ng-show="dialogCtrl.options.typeId > 0">Удалить</button>
            </div>

        </div>
    </form>
</script>

<script type="text/ng-template" id="products-list">
    <div class="window dialog products-list" ng-controller="order.productsList as dialogCtrl">
        <div class="header">
            <span class="label">Товары</span>
            <div class="select-box width-150 height-33" select-inline="category">
                <div class="header">
                    <p class="label">{{dialogCtrl.categoryFilter.items[dialogCtrl.categoryFilter.activeItem].Name}}</p>
                    <img class="arrow" ng-src="{{dialogCtrl.categoryFilter.arrowImg}}">
                </div>
                <div class="list-wrapper">
                    <ul>
                        <li ng-repeat="category in dialogCtrl.categoryFilter.items" >
                            <p ng-click="dialogCtrl.categoryFilter.setActiveItem($index)" class="label">{{category.Name}}</p>
                            <!--<span ng-click="rightCtrl.editProductType(type)" title="Редактировать категорию" style="float: right" class="icon"><img src="templates/admin/images/edit.png"></span>-->
                        </li>
                    </ul>
                </div>
            </div>
            <div style="display: inline-block;position: relative">
                <input type="text" placeholder="Быстрый поиск" ng-model="dialogCtrl.nameFilter" class="gray width-150 height-33 search" style="padding-right: 20px;width:200px;" autocomplete="off">
                <img src="templates/admin/images/search.png" style="position: absolute;right: 3px;top: 9px;">
            </div>

            <div class="select-box width-150 height-33" select-inline="types">
                <div class="header">
                    <p class="label">{{dialogCtrl.productTypeFilter.items[dialogCtrl.productTypeFilter.activeItem].Name}}</p>
                    <img class="arrow" ng-src="{{dialogCtrl.productTypeFilter.arrowImg}}">
                </div>
                <div class="list-wrapper">
                    <ul>
                        <li ng-repeat="productType in dialogCtrl.productTypeFilter.items" >
                            <p ng-click="dialogCtrl.productTypeFilter.setActiveItem($index)" class="label">{{productType.Name}}</p>
                            <!--<span ng-click="rightCtrl.editProductType(type)" title="Редактировать категорию" style="float: right" class="icon"><img src="templates/admin/images/edit.png"></span>-->
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="content">
            <section style="height: 350px;overflow-y: auto;background-color: #070c12;">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th style="width:330px;" class="name">Название блюда</th>
                        <th style="width:80px;" class="type">Категория</th>
                        <th class="description">Описание</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr ng-repeat="product in dialogCtrl.productsList.items |  filter:dialogCtrl.filter" ng-click="dialogCtrl.selectProduct(product)">
                        <td class="name">{{product.Name}}</td>
                        <td class="type">{{dialogCtrl.getProductType(product.TypeId).Name}}</td>
                        <td class="description"><p>{{product.Description}}</p></td>
                    </tr>
                    </tbody>
                </table>
            </section>
            <section>
                <h3>Выбраный продукт</h3>
                <div class="light-gray-block">
                    <span class="label" style="width: 250px;">{{dialogCtrl.selectedProduct.Product.Name}}</span>
                    <input type="text" class="gray light-gray height-28 width-150 " ng-model="dialogCtrl.selectedProduct.Amount" name="ProductAmount" autocomplete="off">
                    <span class="label">шт.</span>
                </div>
            </section>
        </div>
        <div class="footer">
            <button type="button" ng-click="dialogCtrl.add()">Добавить</button>
            <button type="button" ng-click="hide()">Закрыть</button>

        </div>
    </div>
</script>


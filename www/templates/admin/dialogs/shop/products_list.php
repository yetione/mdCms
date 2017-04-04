<script type="text/ng-template" id="shop/products-list.html">
    <div class="window dialog products-list new" ng-controller="shop.dialogs.productsList as dialogCtrl">
        <div class="header">
            <span class="label">Товары</span>
            <select-box  source="dialogCtrl.categories" on-select="dialogCtrl.onCategorySelect($item, $event, $isNull)" ng-model="dialogCtrl.activeCategory" class="width-200 height-33">
                <sb-header>{{$select.selected.Name}}</sb-header>
                <sb-list>
                    <sb-list-item ng-repeat="item in $select.items">
                        <span class="Name">{{item.Name}}</span>
                    </sb-list-item>
                </sb-list>
            </select-box>

            <select-box  source="dialogCtrl.PTSelect" on-select="dialogCtrl.onProductTypeSelect($item, $event, $isNull)" ng-model="dialogCtrl.activeProductType" null-item="dialogCtrl.nullProductType" class="width-200 height-33">
                <sb-header>{{$select.selected.Name}}</sb-header>
                <sb-list>
                    <sb-list-null-item>
                        <span class="Name">{{$select.nullItem.Name}}</span>
                    </sb-list-null-item>
                    <sb-list-item ng-repeat="item in $select.items">
                        <span class="Name">{{item.Name}}</span>
                    </sb-list-item>
                </sb-list>
            </select-box>


            <div style="display: inline-block;position: relative">
                <input type="text" placeholder="Быстрый поиск" ng-model="dialogCtrl.nameFilter" class="gray width-150 height-33 search" style="padding-right: 20px;width:200px;" autocomplete="off">
                <img src="templates/admin/images/search.png" style="position: absolute;right: 3px;top: 9px;">
            </div>


        </div>
        <div class="content">
            <section style="height: 350px;overflow-y: auto;background-color: #070c12;">
                <d-t class="products-list" table-id="shop.dialogs.productsList.table">
                    <dt-header>
                        <dth-column class="name" sortable="'true'" key="'Name'">Название</dth-column>
                        <dth-column class="category" sortable="'true'" key="'ProductType.Name'">Категория</dth-column>
                        <dth-column class="description" >Описание</dth-column>
                    </dt-header>
                    <dt-rows>
                        <dtr-row ng-repeat="product in dialogCtrl.productsList | filter:{Name:dialogCtrl.nameFilter} track by product.Id" ng-click="dialogCtrl.selectProduct(product)">
                            <dtrr-column class="name">
                                {{product.Name}}
                            </dtrr-column>
                            <dtrr-column class="category">
                                {{product.ProductType.Name}}
                            </dtrr-column>
                            <dtrr-column class="description">
                                {{product.Description}}
                            </dtrr-column>
                        </dtr-row>
                    </dt-rows>
                </d-t>
            </section>
            <section ng-if="dialogCtrl.selectedProducts.length">
                <h3>Выбранные продукты</h3>
                <div class="light-gray-block tags-list">
                    <div class="tag" ng-repeat="product in dialogCtrl.selectedProducts">
                        <span class="label">{{product.Name}}</span>
                        <span class="icon"><i ng-click="dialogCtrl.selectProduct(product)" class="fa fa-trash" aria-hidden="true"></i></span>
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
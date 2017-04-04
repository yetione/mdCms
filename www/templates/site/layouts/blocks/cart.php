<div class="cart" ng-controller="CartController as cartCtrl">
    <div class="quantity"><i class="fa fa-shopping-bag" aria-hidden="true"></i> {{cartCtrl.cart.ProductsCount}}</div>
    <div class="sum">{{cartCtrl.cart.TotalPrice}}&nbsp;₽</div>
    <div class="summary">
        <div class="summary-wrap mCustomScrollbar" data-mcs-theme="light">
            <section ng-repeat="day in cartCtrl.cart.Data">
                <div class="date"><span>{{cartCtrl.getDayStr(day.date)}}</span><span>{{day.price}}&nbsp;₽</span></div>
                <div class="orderposition" ng-repeat="product in day.products"><span class="name">{{cartCtrl.getProduct(product.id).Name}} <span ng-show="product.amount>1">({{product.amount}})</span></span><span>{{product.amount*product.price}}&nbsp;₽</span><span class="deletebutton"><i class="fa fa-trash-o" aria-hidden="true" ng-click="cartCtrl.removeProduct(day.date, cartCtrl.getProduct(product.id))"></i></span></div>
            </section>
        </div>
        <section>
            <div class="button" ng-show="!cartCtrl.cart.IsBlocked"><a ng-href="cart" class="buttonlink orange">Оформить заказ</a></div>
            <div class="button" ng-show="cartCtrl.cart.IsBlocked">
                <a class="buttonlink grey" ng-show="cartCtrl.cart.BlockingReason.code == 1">Заказ недоступен</a>
                <a class="buttonlink grey" ng-show="cartCtrl.cart.BlockingReason.code == 3">Корзина пуста</a>
            </div>
        </section>
    </div>
</div>
<nav>
    <div class="wrap">
        <div class="cityselection" ng-controller="CitiesController as citiesCtrl" ng-show="citiesCtrl.showTooltip">
            <span class="pseudolink" style="text-transform: uppercase;" ng-click="citiesCtrl.showSelectCityDialog()">{{citiesCtrl.currentCity.Name}} <!--<i class="fa fa-caret-down" aria-hidden="true"></i>--></span>
        </div>
        <ul>
            <!--li><a href="">БЛОГ</a-->
            <li><a href="delivery">ДОСТАВКА И ОПЛАТА</a>
            <!--li><a href="">АКЦИИ</a-->
        </ul>
<!--        <div class="search"><input type="search" placeholder="Поиск по сайту"></div>-->
        <div class="login" ng-controller="UserbarController as userCtrl">
            <a ng-href1="login" ng-show="!userCtrl.currentUser.Id" ng-click="userCtrl.showLogin()"><span class="pseudolink"><i class="fa fa-sign-in" aria-hidden="true"></i> ВХОД</span></a>
            <a ng-href="profile" ng-show="userCtrl.currentUser.Id"><span class="pseudolink"><i class="fa fa-home" aria-hidden="true"></i> ЛИЧНЫЙ КАБИНЕТ</span></a>
            <a ng-href="logout" ng-show="userCtrl.currentUser.Id"> <span class="pseudolink"><i class="fa fa-sign-out" aria-hidden="true"></i> ВЫХОД</span></a>
        </div>

        <?php require 'cart.php';?>
    </div>
</nav>
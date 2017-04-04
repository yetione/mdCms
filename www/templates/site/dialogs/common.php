<script type="text/ng-template" id="common/confirm">
    <div class="modal" ng-controller="mdDialogs.Confirm as dialogCtrl">
        <div class="header" ng-show="dialogCtrl.options.title != ''">
            <p class="title">{{dialogCtrl.options.title}}</p>
<!--            <span class="label">{{dialogCtrl.options.title}}</span>-->
        </div>
        <div class="content">
            <p>{{dialogCtrl.options.message}}</p>
        </div>
        <div class="footer">
            <button type="button" ng-click="dialogCtrl.yes()">Да</button>
            <button type="button" ng-click="dialogCtrl.no()">Нет</button>
        </div>
    </div>
</script>

<script type="text/ng-template" id="common/message">
    <div class="modal" ng-controller="mdDialogs.Message as dialogCtrl">
        <div class="header" ng-show="dialogCtrl.options.title != ''">
            <p class="title">{{dialogCtrl.options.title}}</p>
        </div>
        <div class="content">
            <p>{{dialogCtrl.options.message}}</p>
        </div>
        <div class="footer">
            <button type="button" ng-click="dialogCtrl.ok()">OK</button>
        </div>
    </div>
</script>

<script type="text/ng-template" id="city-select-dialog">
    <div class="modal cityconfirm" ng-controller="mdDialogs.Message as dialogCtrl">
        <p>Выберите город.</p>
        <div class="buttons">
            <a class="buttonlink" ng-repeat="city in dialogCtrl.options.cities track by city.Id"  ng-href="geo-location/change-city/{{city.Id}}">{{city.Name}}</a>
        </div>
    </div>
</script>

<script type="text/ng-template" id="is-detected-city">
    <div class="modal cityconfirm" ng-controller="mdDialogs.Message as dialogCtrl">
        <p>Ваш город — {{dialogCtrl.options.currentCity.Name}}, верно?</p>
        <div class="buttons">
            <button ng-click="dialogCtrl.options.yes(this)">Да, точно!</button>
            <button ng-click="dialogCtrl.options.no(this)">Не угадали</button>
        </div>
    </div>
</script>

<script type="text/ng-template" id="form-error">
    <div class="modal" ng-controller="mdDialogs.Message as dialogCtrl">
        <p>Не заполнены обязательные поля!</p>
        <div class="buttons">
            <a class="buttonlink" ng-click="dialogCtrl.options.hide(this)">ОК</a>
        </div>
    </div>
</script>

<script type="text/ng-template" id="login-dialog">
    <div class="modal" data-name="login" ng-controller="mdDialogs.Message as dialogCtrl">
        <p>Вход в личный кабинет</p>
        <div class="selection">
            <form >
                <div><input placeholder="Email" ng-model="dialogCtrl.options.User.Email"></div>
                <div><input type="password" placeholder="Пароль" autocomplete="new-password" ng-model="dialogCtrl.options.User.Password"></div>
            </form>
            <div class="socialsauth">
                <p>Вход через соцсети</p>
                <div class="links">
                    <a ng-href="{{dialogCtrl.options.VkLoginLink}}"><img src="<?php echo BASE_URL;?>templates/site/images/vk.png"></a>
                    <a ng-href="{{dialogCtrl.options.FbLoginLink}}" style="margin-left: 10px;"><img src="<?php echo BASE_URL;?>templates/site/images/fb.png"></a>
                </div>
            </div>
        </div>
        <div class="buttons" role="submit">
            <button ng-click="dialogCtrl.options.formSubmit(this)">Войти</button>
            или <span class="pseudolink"><a ng-href="registration">зарегистрироваться</a></span>
        </div>
        <div class="buttons" style="padding-bottom: 0">
            <p style="text-align: center;margin: 0;margin-top: 15px;font-size: 12px;"><a href="/login/sendpassword" style="color:#6f6f6f">Забыли пароль?</a></p>
        </div>

    </div>
</script>
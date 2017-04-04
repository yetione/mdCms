<script type="text/ng-template" id="FileManager/Main">
    <div class="window dialog" ng-controller="mdFileManager.SelectFile as dialogCtrl">
        <div class="header" ng-show="dialogCtrl.options.title != ''">
            <span class="label">{{dialogCtrl.options.title}}</span>
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
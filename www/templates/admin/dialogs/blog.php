<script type="text/ng-template" id="dialogs/blog/edit-category">
    <form name="EditBlogCategory" ng-controller="blog.editCategoryDialog as dialogCtrl" ng-submit="dialogCtrl.save(this)">
    <div class="window dialog blog-edit-category">
        <div class="header">
            <span class="label">Добавление категории</span>
        </div>
        <div class="content">
            <section>
                <h3>Основная информация</h3>
                <div class="light-gray-block">
                    <span class="label">Название *</span>
                    <input type="text" class="gray light-gray height-28 width-450 " ng-model="dialogCtrl.category.Name" name="Name" autocomplete="off" ng-trim="true">
                </div>
                <div class="light-gray-block">
                    <span class="label">Статус</span>
                    <div class="select-box width-150 height-33 lg" select-inline="category-status">
                        <div class="header">
                            <p class="label">{{dialogCtrl.CSList.items[dialogCtrl.CSList.activeItem].label}}</p>
                            <img class="arrow" ng-src="{{dialogCtrl.CSList.arrowImg}}">
                        </div>
                        <div class="list-wrapper">
                            <ul>
                                <li ng-repeat="status in dialogCtrl.categoryStatuses" >
                                    <p ng-click="dialogCtrl.CSList.setActiveItem($index)" class="label">{{status.label}}</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="light-gray-block">
                    <span class="label">Дата создания</span>
<!--                    <input type="text" class="gray light-gray height-28 width-450 " ng-model="dialogCtrl.category.CreationDate" name="CreationDate" autocomplete="off">-->
                    <input type="text" date-time format="DD.MM.YYYY" id="DaysList-MinDate" name="CreationDate"
                           max-view="date" min-view="date" ng-model="dialogCtrl.category.CreationDate"
                           date-change="dialogCtrl.onDateChange"
                           class="date-picker gray width-150 height-28 search" style="padding-right: 20px;width:200px;" ng-disabled="true">
                </div>
                <div class="light-gray-block">
                    <span class="label">"Создатель"</span>
                    <input type="text" class="gray light-gray height-28 width-450 " ng-model="dialogCtrl.currentUser.Login" name="CurrentUserLogin" autocomplete="off" ng-disabled="1">
                </div>
                <div class="light-gray-block">
                    <span class="label">Дата обновления</span>
                    <input type="text" class="gray light-gray height-28 width-450 " ng-model="dialogCtrl.category.UpdateDate" name="UpdateDate" autocomplete="off" ng-disabled="true">
                </div>
                <div class="light-gray-block">
                    <span class="label">"Обновитель"</span>
                    <input type="text" class="gray light-gray height-28 width-450 " ng-model="dialogCtrl.category.UpdaterId" name="UpdaterId" autocomplete="off" ng-disabled="true">
                </div>
                <div class="light-gray-block">
                    <span class="label">Заголовок в браузере</span>
                    <input type="text" class="gray light-gray height-28 width-450 " ng-model="dialogCtrl.category.HtmlTitle" name="HtmlTitle" autocomplete="off">
                </div>
            </section>
            <section>
                <h3>SEO</h3>
                <div class="light-gray-block">
                    <span class="label">URL</span>
                    <input type="text" class="gray light-gray height-28 width-450 " ng-model="dialogCtrl.category.Url" name="Url" autocomplete="off">
                </div>
                <div class="light-gray-block">
                    <span class="label">Ключевые слова</span>
                    <input type="text" class="gray light-gray height-28 width-450 " ng-model="dialogCtrl.category.SeoKeywords" name="SeoKeywords" autocomplete="off">
                </div>
                <div class="light-gray-block">
                    <span class="label">Описание</span>
                    <textarea ng-model="dialogCtrl.category.SeoDescription" name="SeoDescription" class="light-gray gray width-450 height-100"></textarea>
                </div>
            </section>
            <section>
                <h3>Статистика</h3>
                <div class="light-gray-block">
                    <span class="label">Кол-во записей</span>
                    <input type="text" class="gray light-gray height-28 width-150 " ng-model="dialogCtrl.category.PostsCount" name="PostsCount" autocomplete="off" ng-disabled="true">
                </div>
                <div class="light-gray-block">
                    <span class="label">Кол-во опубликованных записей</span>
                    <input type="text" class="gray light-gray height-28 width-150 " ng-model="dialogCtrl.category.PublicPostsCount" name="PublicPostsCount" autocomplete="off" ng-disabled="true">
                </div>
            </section>
            <section>
                <h3>Тексты</h3>
                <div class="light-gray-block">
                    <span class="label">Анонс</span>
                    <textarea ng-model="dialogCtrl.category.Announce" name="Announce" class="light-gray gray width-450 height-250"></textarea>
                </div>
                <div class="light-gray-block">
                    <span class="label">Содержимое страницы</span>
                    <textarea ng-model="dialogCtrl.category.Content" name="Content" class="light-gray gray width-450 height-250"></textarea>
                </div>
            </section>
        </div>
        <div class="footer">
            <input type="submit" value="Сохранить" class="button" ng-show="EditBlogCategory.$dirty">
            <button type="button" ng-click="hide()">Закрыть</button>
        </div>
    </div>
    </form>

</script>
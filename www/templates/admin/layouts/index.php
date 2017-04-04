<?php $user = $this->getCore()->getSession()->get('Users.current_user'); ?>
<!DOCTYPE html>
<html ng-app="adminApp">
<head lang="en">
    <meta charset="UTF-8">
    <title>MakeD Admin panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <base href="<?php echo BASE_URL?>">
    <!--
    <script src="https://code.angularjs.org/1.2.6/angular.min.js"></script>
    <script src="https://code.angularjs.org/1.2.6/angular-resource.min.js"></script>
    <script src="https://code.angularjs.org/1.2.6/angular-route.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.2.6/angular-animate.min.js"></script>
    -->


    <script src="js/libs/moment.js"></script>
    <script src="js/libs/moment-timezone.js"></script>
    <script src="js/libs/ru.js"></script>
    <script src="js/ckeditor/ckeditor.js"></script>

    <script src="js/libs/angular.min.js"></script>
<!--    <script src="--><?php //echo TEMPLATES_PATH;?><!--admin/js/libs/angular-resource.js"></script>-->
<!--    <script src="--><?php //echo TEMPLATES_PATH;?><!--admin/js/libs/angular-route.js"></script>-->
    <script src="js/libs/angular-animate.min.js"></script>
    <script src="js/libs/angular-drag-n-drop-lists.min.js"></script>
    <script src="js/libs/list_object.js"></script>
    <script src="js/libs/angular-cookies.min.js"></script>
<!--    <script src="js/libs/angular-datepicker.min.js"></script>-->
    <script src="js/libs/angular-datepicker.js"></script>
    <script src="js/libs/angular-ckeditor.min.js"></script>
    <script src="js/libs/angular-ui-notification.min.js"></script>
    <script src="js/libs/mask.min.js"></script>

    <script src="js/modules/backend.js"></script>
    <script src="js/modules/cart.js"></script>
    <script src="js/modules/entity.js"></script>
    <script src="js/modules/geo_location.js"></script>
    <script src="js/modules/dialogs.js"></script>
    <script src="js/modules/users.js"></script>
    <script src="js/modules/utils.js"></script>
    <script src="js/modules/file_manager.js"></script>
    <script src="js/modules/shop.js"></script>
    <script src="js/modules/kladr.js"></script>
    <script src="js/modules/repeat_parser.js"></script>
    <script src="js/modules/idialog.js"></script>



<!--    <script src="js/libs/ng-ckeditor.min.js"></script>-->

    <script src="<?php echo TEMPLATES_PATH;?>admin/js/angular-file-upload.min.js"></script>

    <script src="<?php echo TEMPLATES_PATH;?>admin/js/app.js"></script>

<!--    <script src="--><?php //echo TEMPLATES_PATH;?><!--admin/js/idialog.js"></script>-->
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/directives/common.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/directives/calendar.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/directives/autocomplete.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/directives/select_box.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/directives/is_loading.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/directives/data_table.js"></script>


    <script src="<?php echo TEMPLATES_PATH;?>admin/js/services/data-service.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/services/entity-manager.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/services/menu.js"></script>

    <script src="<?php echo TEMPLATES_PATH;?>admin/js/factory/column.js"></script>



    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/common/main.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/common/clock.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/common/menu.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/common/userbar.js"></script>


    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/shop/center_column.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/shop/right_column.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/shop/promocode_page.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/shop/dialogs/products_list.js"></script>

    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/menu/center_column.js"></script>
<!--    <script src="--><?php //echo TEMPLATES_PATH;?><!--admin/js/controllers/menu/right_column.js"></script>-->
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/menu/menu_page.js"></script>

    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/center_column.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/right_column.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/order_item.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/products_list.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/documents/right_column.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/documents/days_list.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/documents/itinerary.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/documents/orders_list.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/documents/kitchen.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/documents/manage.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/documents/couriers_list.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/documents/select_courier.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/documents/stocks.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/couriers_page.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/orders/page.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/orders/list.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/orders/item.js"></script>

    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/manage/page.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/order/manage/days_list.js"></script>

    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/blog/center_column.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/blog/right_column.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/blog/edit_category.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/blog/posts_list.js"></script>
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/controllers/blog/post_item.js"></script>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
    <link rel="stylesheet" href="css/angular-ui-notification.min.css">
    <link rel="stylesheet" href="css/normalize.min.css" type="text/css">
    <link rel="stylesheet" href="css/animate.min.css" type="text/css">
    <link rel="stylesheet" href="css/angular-datepicker.min.css" type="text/css">
    <link rel="stylesheet" href="css/idialog.css" type="text/css">

    <link rel="stylesheet/less" type="text/css" href="<?php echo TEMPLATES_PATH;?>admin/less/styles.less" />
    <script src="<?php echo TEMPLATES_PATH;?>admin/js/libs/less.min.js"></script>
    <script src="https://use.fontawesome.com/f8aa076356.js"></script>
</head>
<body>
<div class="wrapper" ng-controller="common.applicationController as mainController">
    <div class="column first">
        <div class="column-content">
            <div class="block-top">
                <img src="<?php echo TEMPLATES_PATH;?>admin/images/logo.png">
            </div>
            <div class="block-bottom">

                <nav ng-controller="common.leftMenu as menuCtrl">
                    <a ng-repeat="item in menuCtrl.items" ng-class="{active:menuCtrl.activeItem==$index}" ng-click="menuCtrl.click($index);">
                        <img ng-src="<?php echo BASE_URL;?>{{item.Icon}}">
                        <span>{{item.Title}}</span>
                    </a>
                </nav>


            </div>
        </div>

    </div>
    <div class="column second">
        <div class="column-content" id="column-center">
        </div>

    </div>
    <div class="column third">
        <div class="column-content">
            <div class="block-top">
                <div class="block-left">
                    <div class="ws-block border-block-r cms-data">
                        <p class="full-lh">
                            <span class="cms-label">Make-d CMS</span>
                            <span class="version-label">v <?php echo CMS_VERSION;?></span>
                        </p>
                    </div>
                    <div class="ws-block border-block-r date_time-block" ng-controller="common.clockController as clock">
                        <p class="time">{{clock.timeStr}}</p>
                        <p class="date">{{clock.dateStr}}</p>
                    </div>
                </div>

                <div class="block-right" ng-controller="common.userBarController as userBarCtrl">
                    <div class="ws-block border-block-l top-icons">
                        <div><a href=""><img src="<?php echo TEMPLATES_PATH;?>admin/images/icons/top/help.png"></a></div>
                        <div><a href=""><img src="<?php echo TEMPLATES_PATH;?>admin/images/icons/top/settings.png"></a></div>
                    </div>
                    <div class="ws-block border-block-l user-data">
                        <span class="login"><a>{{userBarCtrl.currentUser.Login}}</a></span>
                        <a class="logout" ng-href="auth/logout">Выйти</a>
                    </div>
                </div>
            </div>

            <div class="block-bottom" id="column-right">

            </div>
            <div style="height:250px"></div>
        </div>
    </div>



</div>
<footer><span>MAKE-D CMS / <?php echo CMS_VERSION;?>. upd: 17.08.2016</span></footer>

<?php
require(BASE_PATH.DIRECTORY_SEPARATOR.TEMPLATES_DIR.'/admin/dialogs/common.php');
require(BASE_PATH.DIRECTORY_SEPARATOR.TEMPLATES_DIR.'/admin/dialogs/file_manager.php');
require(BASE_PATH.DIRECTORY_SEPARATOR.TEMPLATES_DIR.'/admin/dialogs/shop.php');
require(BASE_PATH.DIRECTORY_SEPARATOR.TEMPLATES_DIR.'/admin/dialogs/order.php');
require(BASE_PATH.DIRECTORY_SEPARATOR.TEMPLATES_DIR.'/admin/dialogs/blog.php');
require(BASE_PATH.DIRECTORY_SEPARATOR.TEMPLATES_DIR.'/admin/dialogs/shop/products_list.php');
?>
</body>
</html>
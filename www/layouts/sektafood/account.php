<!DOCTYPE html>
<html>
    <head>
<?php require_once 'tpl/common-head.tpl';?>
    </head>
    <body>
<?php require_once 'tpl/modals.tpl';?>
        <nav>
            <div class="wrap">
                <div class="cityselection">
                    <span class="pseudolink ui-show-modal" data-target="citychoice">САНКТ-ПЕТЕРБУРГ<!-- <i class="fa fa-caret-down" aria-hidden="true"></i>--></span>
                </div>
                <ul>
                    <li><a href="">БЛОГ</a>
                    <li><a href="">ДОСТАВКА И ОПЛАТА</a>
                    <li><a href="">АКЦИИ</a>
                </ul>
                <div class="search"><input type="search" placeholder="Поиск по сайту"></div>
                <div class="login"><span class="pseudolink"><i class="fa fa-sign-in" aria-hidden="true"></i> ВХОД</span></div>
<?php require_once 'tpl/cart.tpl';?>
            </div>
        </nav>
        <header>
            <div class="logophone wrap">
                <div class="logo"><img alt="" src="images/logo.png"></div>
                <div class="phones">
                    <p class="phone"><a href="tel:+7">+7 911 103-59-44</a></p>
                    <p class="slogan">ДОСТАВКА ЗДОРОВОЙ ЕДЫ</p>
                </div>
            </div>
            <section class="personalinfo">
                <h3>Личный кабинет <span>(ID 192471)</span></h3>
                <p><input placeholder="Имя" required value="Иван"></p>
                <p><input placeholder="Отчество" required></p>
                <p><input placeholder="Фамилия" required value="Тестовый"></p>
                <p><input placeholder="Телефон" required></p>
                <p><input placeholder="Email" required></p>
                <p><button>Сохранить личную информацию</button></p>
            </section>
        </header>

        <section class="personalinfo-addresses">
            <div class="text">
                <h3>Ваши адреса</h3>
                <p>Вы можете хранить несколько адресов, и выбирать нужные из них при формировании заказа на один или несколько дней!</p>
            </div>
            <div class="address">
                <h4>Адрес 1 <span class="pseudolink">Переименовать</span></h4>
                <p><input placeholder="Улица"></p>
                <p><input placeholder="Дом"></p>
                <p><input placeholder="Квартира/офис"></p>
                <p><button>Сохранить адрес</button><button>Удалить адрес</button></p>
            </div>
            <div class="address">
                <h4>Адрес 2 <span class="pseudolink">Переименовать</span></h4>
                <p><input placeholder="Улица"></p>
                <p><input placeholder="Дом"></p>
                <p><input placeholder="Квартира/офис"></p>
                <p><button>Сохранить адрес</button><button>Удалить адрес</button></p>
            </div>
            <div class="address">
                <h4>Адрес 3 <span class="pseudolink">Переименовать</span></h4>
                <p><input placeholder="Улица"></p>
                <p><input placeholder="Дом"></p>
                <p><input placeholder="Квартира/офис"></p>
                <p><button>Сохранить адрес</button><button>Удалить адрес</button></p>
            </div>
            <section class="button"><button><i class="fa fa-plus" aria-hidden="true"></i> Добавить адрес</button></section>
        </section>
        <section class="orderhistory">
            <div class="text">
                <h3>История заказов</h3>
            </div>
            <div class="orderslist">
                <p><a href=""><span>1 сентября 2016</span><span>1700 ₽</span></a></p>
                <p><a href=""><span>1 сентября 2016</span><span>1700 ₽</span></a></p>
                <p><a href=""><span>1 сентября 2016</span><span>1700 ₽</span></a></p>
                <p><a href=""><span>1 сентября 2016</span><span>1700 ₽</span></a></p>
                <p><a href=""><span>1 сентября 2016</span><span>1700 ₽</span></a></p>
                <p><a href=""><span>1 сентября 2016</span><span>1700 ₽</span></a></p>
                <p><a href=""><span>1 сентября 2016</span><span>1700 ₽</span></a></p>
                <p><a href=""><span>1 сентября 2016</span><span>1700 ₽</span></a></p>
                <p><a href=""><span>1 сентября 2016</span><span>1700 ₽</span></a></p>
                <p><a href=""><span>1 сентября 2016</span><span>1700 ₽</span></a></p>
                <p><a href=""><span>1 сентября 2016</span><span>1700 ₽</span></a></p>
            </div>
            <section class="button"><button>Показать еще</button></section>
        </section>
        
<?php require_once 'tpl/footer-scripts.tpl';?>        
    </body>
</html>
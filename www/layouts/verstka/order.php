<!DOCTYPE html>
<html>
    <head>
<?php require_once 'tpl/common-head.tpl';?>
    </head>
    <body>
        <div class="modals">
            <div class="modal" data-name="cityconfirm">
                <p>Ваш город — Санкт-Петербург, верно?</p>
                <div class="buttons">
                    <button>Да, точно!</button>
                    <button>Не угадали</button>
                </div>
            </div>
            <div class="modal" data-name="citychoice">
                <p>Выберите город.</p>
                <div class="buttons">
                    <button>Москва</button>
                    <button>Санкт-Петербург</button>
                </div>
            </div>
        </div>
        <nav>
            <div class="wrap">
                <div class="cityselection">
                    <span class="pseudolink ui-show-modal" data-target="citychoice">САНКТ-ПЕТЕРБУРГ<!-- <i class="fa fa-caret-down" aria-hidden="true"></i>--></span>
                </div>
                <ul>
                    <li><a href="">О НАС</a>
                    <li><a href="">ДОСТАВКА И ОПЛАТА</a>
                    <li><a href="">БЛОГ</a>
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
        </header>
        <section class="orderdetails">
            <h3>Оформление заказа</h3>
            <p>Заказ на 2 дня</p>
            <p>5 наименований</p>
            <p>На сумму 1700 ₽</p>
            <p></p>
        </section>
        <section class="foodselection">
            <div class="header">
                <div class="text">
                    <h3>1 июля (вторник)</h3>
                    <p>4 наименования, 2000 ₽</p>
                </div>
            </div>
            <div class="cards">
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="remove anim"><i class="fa fa-times" aria-hidden="true"></i></div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="itemresult">
                        <button>&minus;</button>
                        <input value="1">
                        <button>+</button>
                        <div class="total">500 ₽</div>
                    </div>
                </div>
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="remove anim"><i class="fa fa-times" aria-hidden="true"></i></div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка</h4>
                    <div class="itemresult">
                        <button>&minus;</button>
                        <input value="1">
                        <button>+</button>
                        <div class="total">500 ₽</div>
                    </div>
                </div>
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="remove anim"><i class="fa fa-times" aria-hidden="true"></i></div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="itemresult">
                        <button>&minus;</button>
                        <input value="1">
                        <button>+</button>
                        <div class="total">500 ₽</div>
                    </div>
                </div>
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="remove anim"><i class="fa fa-times" aria-hidden="true"></i></div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="itemresult">
                        <button>&minus;</button>
                        <input value="1">
                        <button>+</button>
                        <div class="total">500 ₽</div>
                    </div>
                </div>
            </div>
        </section>
        <section class="foodselection">
            <div class="header">
                <div class="text">
                    <h3>2 июля (среда)</h3>
                    <p>1 наименование, 500 ₽</p>
                </div>
            </div>
            <div class="cards">
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="remove anim"><i class="fa fa-times" aria-hidden="true"></i></div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="itemresult">
                        <button>&minus;</button>
                        <input value="1">
                        <button>+</button>
                        <div class="total">500 ₽</div>
                    </div>
                </div>
            </div>
        </section>
        <section class="customerdetails">
            <h3>Информация о доставке</h3>
            <p>
                <input type="radio" name="type" checked> <label>Единые данные на все дни</label>
                <input type="radio" name="type"> <label>Уникальные данные на каждый день</label>
            </p>
            <p>
                <select name="day">
                    <option value="">1 июля</option>
                    <option value="">2 июля</option>
                </select>
            </p>
            <p><input placeholder="Имя и фамилия"></p>
            <p><input placeholder="Адрес доставки"></p>
            <p><input placeholder="Телефон"></p>
            <p><input placeholder="Email"></p>
            <p><textarea>Комментарии к заказу</textarea></p>
        </section>
        <section class="send">
            <button>Сделать заказ</button>
        </section>
        
        <footer>
            <ul>
                <li><a href="">О НАС</a>
                <li><a href="">ДОСТАВКА И ОПЛАТА</a>
                <li><a href="">БЛОГ</a>
                <li><a href="">АКЦИИ</a>
                <li><a href="">КОНТАКТНАЯ ИНФОРМАЦИЯ</a>
            </ul>
            <div>&copy; SEKTAFOOD.RU, 2016</div>
        </footer>


        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
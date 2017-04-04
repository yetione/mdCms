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
            <div class="slider">
                <div class="slide">
                    <h2>Здоровое питание — правильно!</h2>
                    <p>Наша миссия — сделать здоровую и натуральную еду как можно более
доступной для широкого круга потребителей.</p>
                    <a class="buttonlink" href="">УЗНАТЬ БОЛЬШЕ</a>
                </div>
            </div>
        </header>
        <section class="daysselection">
            <h3>Выберите день недели</h3>
            <div class="days">
                <div class="day active">
                    <div class="orderexistsmark"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
                    <div class="date">12 июня</div>
                    <div class="weekday anim">ПН</div>
                    <p>800 ₽</p>
                </div>
                <div class="day orderexists">
                    <div class="orderexistsmark"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
                    <div class="date">13 июня</div>
                    <div class="weekday anim">ВТ</div>
                    <p>800 ₽</p>
                </div>
                <div class="day">
                    <div class="orderexistsmark"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
                    <div class="date">14 июня</div>
                    <div class="weekday anim">СР</div>
                    <p>800 ₽</p>
                </div>
                <div class="day">
                    <div class="orderexistsmark"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
                    <div class="date">15 июня</div>
                    <div class="weekday anim">ЧТ</div>
                    <p>800 ₽</p>
                </div>
                <div class="day">
                    <div class="orderexistsmark"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
                    <div class="date">16 июня</div>
                    <div class="weekday anim">ПТ</div>
                    <p>800 ₽</p>
                </div>
                <div class="day">
                    <div class="orderexistsmark"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
                    <div class="date">17 июня</div>
                    <div class="weekday anim">СБ</div>
                    <p>800 ₽</p>
                </div>
                <div class="day">
                    <div class="orderexistsmark"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
                    <div class="date">18 июня</div>
                    <div class="weekday anim">ВС</div>
                    <p>800 ₽</p>
                </div>
            </div>
        </section>
        <section class="foodselection">
            <div class="header">
                <div class="text">
                    <h3>Выберите блюда</h3>
                    <p>Выберите блюда на этот день (рекомендуется 3 блюда)</p>
                </div>
                <div class="digit">1</div>
            </div>
            <div class="cards">
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="params">
                        <p><span>Жиры</span><span>100</span></p>
                        <p><span>Белки</span><span>200</span></p>
                        <p><span>Углеводы</span><span>300</span></p>
                        <p><span>Ккал</span><span>400</span></p>
                    </div>
                    <div class="button">
                        <button>Добавить к заказу</button>
                    </div>
                </div>
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="params">
                        <p><span>Жиры</span><span>100</span></p>
                        <p><span>Белки</span><span>200</span></p>
                        <p><span>Углеводы</span><span>300</span></p>
                        <p><span>Ккал</span><span>400</span></p>
                    </div>
                    <div class="button">
                        <button>Добавить к заказу</button>
                    </div>
                </div>
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="params">
                        <p><span>Жиры</span><span>100</span></p>
                        <p><span>Белки</span><span>200</span></p>
                        <p><span>Углеводы</span><span>300</span></p>
                        <p><span>Ккал</span><span>400</span></p>
                    </div>
                    <div class="button">
                        <button>Добавить к заказу</button>
                    </div>
                </div>
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="params">
                        <p><span>Жиры</span><span>100</span></p>
                        <p><span>Белки</span><span>200</span></p>
                        <p><span>Углеводы</span><span>300</span></p>
                        <p><span>Ккал</span><span>400</span></p>
                    </div>
                    <div class="button">
                        <button>Добавить к заказу</button>
                    </div>
                </div>
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка</h4>
                    <div class="params">
                        <p><span>Жиры</span><span>100</span></p>
                        <p><span>Белки</span><span>200</span></p>
                        <p><span>Углеводы</span><span>300</span></p>
                        <p><span>Ккал</span><span>400</span></p>
                    </div>
                    <div class="button">
                        <button>Добавить к заказу</button>
                    </div>
                </div>
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="params">
                        <p><span>Жиры</span><span>100</span></p>
                        <p><span>Белки</span><span>200</span></p>
                        <p><span>Углеводы</span><span>300</span></p>
                        <p><span>Ккал</span><span>400</span></p>
                    </div>
                    <div class="button">
                        <button>Добавить к заказу</button>
                    </div>
                </div>
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="params">
                        <p><span>Жиры</span><span>100</span></p>
                        <p><span>Белки</span><span>200</span></p>
                        <p><span>Углеводы</span><span>300</span></p>
                        <p><span>Ккал</span><span>400</span></p>
                    </div>
                    <div class="button">
                        <button>Добавить к заказу</button>
                    </div>
                </div>
            </div>
        </section>
        <section class="foodselection">
            <div class="header">
                <div class="text">
                    <h3>Выберите перекусы</h3>
                    <p>Выберите блюда на этот день (рекомендуется 3 блюда)</p>
                </div>
                <div class="digit">2</div>
            </div>
            <div class="cards">
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="params">
                        <p><span>Жиры</span><span>100</span></p>
                        <p><span>Белки</span><span>200</span></p>
                        <p><span>Углеводы</span><span>300</span></p>
                        <p><span>Ккал</span><span>400</span></p>
                    </div>
                    <div class="button">
                        <button>Добавить к заказу</button>
                    </div>
                </div>
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="params">
                        <p><span>Жиры</span><span>100</span></p>
                        <p><span>Белки</span><span>200</span></p>
                        <p><span>Углеводы</span><span>300</span></p>
                        <p><span>Ккал</span><span>400</span></p>
                    </div>
                    <div class="button">
                        <button>Добавить к заказу</button>
                    </div>
                </div>
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="params">
                        <p><span>Жиры</span><span>100</span></p>
                        <p><span>Белки</span><span>200</span></p>
                        <p><span>Углеводы</span><span>300</span></p>
                        <p><span>Ккал</span><span>400</span></p>
                    </div>
                    <div class="button">
                        <button>Добавить к заказу</button>
                    </div>
                </div>
            </div>
        </section>
        <section class="foodselection">
            <div class="header">
                <div class="text">
                    <h3>Выберите напитки</h3>
                    <p>Выберите блюда на этот день (рекомендуется 3 блюда)</p>
                </div>
                <div class="digit">3</div>
            </div>
            <div class="cards">
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="params">
                        <p><span>Жиры</span><span>100</span></p>
                        <p><span>Белки</span><span>200</span></p>
                        <p><span>Углеводы</span><span>300</span></p>
                        <p><span>Ккал</span><span>400</span></p>
                    </div>
                    <div class="button">
                        <button>Добавить к заказу</button>
                    </div>
                </div>
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="params">
                        <p><span>Жиры</span><span>100</span></p>
                        <p><span>Белки</span><span>200</span></p>
                        <p><span>Углеводы</span><span>300</span></p>
                        <p><span>Ккал</span><span>400</span></p>
                    </div>
                    <div class="button">
                        <button>Добавить к заказу</button>
                    </div>
                </div>
                <div class="card anim">
                    <div class="price">220 ₽</div>
                    <div class="image">
                        <img alt="" src="data/test01.jpg">
                    </div>
                    <h4>Запеканка с кабачками</h4>
                    <div class="params">
                        <p><span>Жиры</span><span>100</span></p>
                        <p><span>Белки</span><span>200</span></p>
                        <p><span>Углеводы</span><span>300</span></p>
                        <p><span>Ккал</span><span>400</span></p>
                    </div>
                    <div class="button">
                        <button>Добавить к заказу</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="recipes">
            <h2>Новые рецепты</h2>
            <div class="cards">
                <div class="card">
                    <div class="image">
                        <img alt="" src="data/test02.jpg">
                    </div>
                    <h3>«Булгур со свининой»</h3>
                    <p>Отличное легкое блюдо для обеда или ужина, которое очень легко приготовить!</p>
                    <div class="button">
                        <a class="buttonlink" href="">Посмотреть рецепт</a>
                    </div>
                </div>
                <div class="card">
                    <div class="image">
                        <img alt="" src="data/test02.jpg">
                    </div>
                    <h3>«Булгур со свининой»</h3>
                    <p>Отличное легкое блюдо для обеда или ужина, которое очень легко приготовить!</p>
                    <div class="button">
                        <a class="buttonlink" href="">Посмотреть рецепт</a>
                    </div>
                </div>
                <div class="card">
                    <div class="image">
                        <img alt="" src="data/test02.jpg">
                    </div>
                    <h3>«Булгур со свининой»</h3>
                    <p>Отличное легкое блюдо для обеда или ужина, которое очень легко приготовить!</p>
                    <div class="button">
                        <a class="buttonlink" href="">Посмотреть рецепт</a>
                    </div>
                </div>
                <div class="card">
                    <div class="image">
                        <img alt="" src="data/test02.jpg">
                    </div>
                    <h3>«Булгур со свининой»</h3>
                    <p>Отличное легкое блюдо для обеда или ужина, которое очень легко приготовить!</p>
                    <div class="button">
                        <a class="buttonlink" href="">Посмотреть рецепт</a>
                    </div>
                </div>
            </div>
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
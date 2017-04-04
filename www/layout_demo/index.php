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
                <div class="basket">
                    <div class="quantity"><i class="fa fa-shopping-bag" aria-hidden="true"></i> 7</div>
                    <div class="sum">4500 ₽</div>
                    <div class="summary">
                        <div class="date"><span>12 июня (понедельник)</span><span>1250 ₽</span></div>
                        <div class="orderposition"><span class="name">Запеканка с кабачками (2)</span><span>500 ₽</span><span class="deletebutton"><i class="fa fa-trash-o" aria-hidden="true"></i></span></div>
                        <div class="orderposition"><span class="name">Запеканка с кабачками</span><span>250 ₽</span><span class="deletebutton"><i class="fa fa-trash-o" aria-hidden="true"></i></span></div>
                        <div class="orderposition"><span class="name">Запеканка с кабачками (2)</span><span>500 ₽</span><span class="deletebutton"><i class="fa fa-trash-o" aria-hidden="true"></i></span></div>
                        <div class="date"><span>13 июня (вторник)</span><span>750 ₽</span></div>
                        <div class="orderposition"><span class="name">Запеканка с кабачками</span><span>250 ₽</span><span class="deletebutton"><i class="fa fa-trash-o" aria-hidden="true"></i></span></div>
                        <div class="orderposition"><span class="name">Запеканка с кабачками</span><span>250 ₽</span><span class="deletebutton"><i class="fa fa-trash-o" aria-hidden="true"></i></span></div>
                        <div class="orderposition"><span class="name">Запеканка с кабачками</span><span>250 ₽</span><span class="deletebutton"><i class="fa fa-trash-o" aria-hidden="true"></i></span></div>
                        <div class="date"><span>14 июня (среда)</span><span>10000 ₽</span></div>
                        <div class="orderposition"><span class="name">Запеканка с кабачками (19)</span><span>9500 ₽</span><span class="deletebutton"><i class="fa fa-trash-o" aria-hidden="true"></i></span></div>
                        <div class="orderposition"><span class="name">Запеканка с кабачками</span><span>500 ₽</span><span class="deletebutton"><i class="fa fa-trash-o" aria-hidden="true"></i></span></div>
                        <div class="button"><button class="orange">Оформить заказ</button></div>
                    </div>
                </div>
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
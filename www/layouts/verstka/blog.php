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

        <section class="blog-main">
            <div class="header">
                <h2>Блог</h2>
                <div class="pagination">
                    <a class="arrow prev" href=""><i class="fa fa-caret-left" aria-hidden="true"></i></a>
                    <a class="number active" href="">1</a>
                    <a class="number" href="">2</a>
                    <a class="number" href="">3</a>
                    <a class="number" href="">4</a>
                    <a class="number" href="">5</a>
                    <a class="number" href="">6</a>
                    <a class="number" href="">7</a>
                    <a class="arrow next" href=""><i class="fa fa-caret-right" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="cards">
                <div class="card">
                    <div class="image">
                        <img alt="" src="data/test-blogcover01.jpg">
                    </div>
                    <h3>Летняя акция</h3>
                    <time datetime="2016-09-01">1.09.16</time>
                    <p>Отличное легкое блюдо для обеда или ужина, которое очень легко приготовить!</p>
                    <div class="button">
                        <a class="buttonlink" href="">Читать полностью</a>
                    </div>
                </div>
                <div class="card">
                    <div class="image">
                        <img alt="" src="data/test-blogcover01.jpg">
                    </div>
                    <h3>Летняя акция 2</h3>
                    <time datetime="2016-09-01">1.09.16</time>
                    <p>Отличное легкое блюдо для обеда или ужина, которое очень легко приготовить!</p>
                    <div class="button">
                        <a class="buttonlink" href="">Читать полностью</a>
                    </div>
                </div>
                <div class="card">
                    <div class="image">
                        <img alt="" src="data/test-blogcover01.jpg">
                    </div>
                    <h3>Летняя акция 3</h3>
                    <time datetime="2016-09-01">1.09.16</time>
                    <p>Отличное легкое блюдо для обеда или ужина, которое очень легко приготовить!</p>
                    <div class="button">
                        <a class="buttonlink" href="">Читать полностью</a>
                    </div>
                </div>
                <div class="card">
                    <div class="image">
                        <img alt="" src="data/test-blogcover01.jpg">
                    </div>
                    <h3>Летняя акция 4</h3>
                    <time datetime="2016-09-01">1.09.16</time>
                    <p>Отличное легкое блюдо для обеда или ужина, которое очень легко приготовить!</p>
                    <div class="button">
                        <a class="buttonlink" href="">Читать полностью</a>
                    </div>
                </div>
                <div class="card">
                    <div class="image">
                        <img alt="" src="data/test-blogcover01.jpg">
                    </div>
                    <h3>Летняя акция 5</h3>
                    <time datetime="2016-09-01">1.09.16</time>
                    <p>Отличное легкое блюдо для обеда или ужина, которое очень легко приготовить! Отличное легкое блюдо для обеда или ужина, которое очень легко приготовить! Отличное легкое блюдо для обеда или ужина, которое очень легко приготовить!</p>
                    <div class="button">
                        <a class="buttonlink" href="">Читать полностью</a>
                    </div>
                </div>
                <div class="card">
                    <div class="image">
                        <img alt="" src="data/test-blogcover01.jpg">
                    </div>
                    <h3>Летняя акция 6</h3>
                    <time datetime="2016-09-01">1.09.16</time>
                    <p>Отличное легкое блюдо для обеда или ужина, которое очень легко приготовить!</p>
                    <div class="button">
                        <a class="buttonlink" href="">Читать полностью</a>
                    </div>
                </div>
            </div>
            <div class="footer">
                <div class="pagination">
                    <a class="arrow prev" href=""><i class="fa fa-caret-left" aria-hidden="true"></i></a>
                    <a class="number active" href="">1</a>
                    <a class="number" href="">2</a>
                    <a class="number" href="">3</a>
                    <a class="number" href="">4</a>
                    <a class="number" href="">5</a>
                    <a class="number" href="">6</a>
                    <a class="number" href="">7</a>
                    <a class="arrow next" href=""><i class="fa fa-caret-right" aria-hidden="true"></i></a>
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
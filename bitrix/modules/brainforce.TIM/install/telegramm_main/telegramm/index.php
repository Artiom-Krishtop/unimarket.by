<?php
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
    $APPLICATION->SetTitle("Подключить TIM-бота");
    require_once 'config.php';
    $url = "https://t.me/".BOT_USERNAME."?start=1";
?>
    <div class="main-wrapper container">
        <div class="row stats-row" id="prompt_string">
            <div class="col-lg-12">
                <div class="card card-transactions">
                    <div class="card-body">
                        <h5 class="card-title">Подключение TIM-бота по ссылке</h5>
                        <p>Для подключения TIM-бота и удобной работы в нём перейдите по ссылке <a href="<?=$url; ?>" target="_blank">@<?=BOT_USERNAME?></a> и после запустите его (выполнив команду <b>Начать</b>).<br> Либо нажмите кнопку
                            <br><br>
                            <b><a href="https://tele.click/<?=BOT_USERNAME?>?start=1" target="_blank" class="btn btn-primary">ПОДКЛЮЧИТЬ</a></b><br>
                        </p>
                        <h5 class="card-title">Подключение TIM-бота через QR код</h5>
                        <p>
                            Или наведите камеру своего телефона на QR-код ниже (и далее нажмите команду <b>Начать</b>)<br>
                            <img src="https://chart.apis.google.com/chart?choe=UTF-8&chld=H&cht=qr&chs=150x150&chl=<?=$url; ?>">
                            <br>

                        </p>
                        <p>
                            После подключения вы сможете пользоваться сайтом в TIM-боте, не заходя на этот сайт:
                        </p>
                        <ul>
                            <li>Искать нужные вам товары</li>
                            <li>Заказывать товары</li>
                            <li>Приглашать друзей</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
<?php

use Bitrix\Main\Config\Option;
use Bitrix\Sale\Payment;

/**
 * @var Payment $payment
 */
?>
<style>
  .oplati {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .oplati img {
    width: 300px;
    height: 300px;
  }
</style>
<div class="oplati" id="oplati-<?php echo $payment->getId() ?>"></div>

<script>
  "use strict";

  (function() {
    var paymentId = <?php echo $payment->getId() ?>;
    var interval = undefined;
    var timeout = undefined;
    var awaitTime = <?php echo Option::get('oplati.paysystem', 'payment_confirm_await_time', 30) ?>;

    var demandPayment = function demandPayment(paymentId) {
      return fetch('/bitrix/tools/sale_ps_result.php' + '?action=demandPayment' + '&BX_HANDLER=OPLATI' + '&orderNumber=' + paymentId);
    };

    var consumerStatus = function consumerStatus(paymentId, sessionId) {
      return fetch('/bitrix/tools/sale_ps_result.php' + '?action=consumerStatus' + '&BX_HANDLER=OPLATI' + '&sessionId=' + sessionId + '&orderNumber=' + paymentId);
    };

    var consumerReady = function consumerReady(paymentId, sessionId) {
      return fetch('/bitrix/tools/sale_ps_result.php' + '?action=consumerReady' + '&BX_HANDLER=OPLATI' + '&sessionId=' + sessionId + '&orderNumber=' + paymentId);
    };

    var paymentStatus = function paymentStatus(paymentId) {
      return fetch('/bitrix/tools/sale_ps_result.php' + '?action=paymentStatusAwait' + '&BX_HANDLER=OPLATI' + '&orderNumber=' + paymentId);
    };

    var onPaySuccess = function onPaySuccess() {
      clearTimeout(timeout);
      var div = getOplatiDiv();

      var span = div.querySelector('span');
      span.innerText = 'Платеж совершен';
      span.style.color = '#007308';
    };

    var onPayFail = function onPayFail() {
      clearTimeout(timeout);
      var div = getOplatiDiv();

      var span = div.querySelector('span');
      span.innerText = 'Не удалось подтвердить платеж';
      span.style.color = '#f62';
    };

    var onConsumerReady = function onConsumerReady(sessionId) {
      return consumerReady(paymentId, sessionId).then(function(res) {
        return res.json();
      }).then(function(_ref) {
        var success = _ref.success;

        if (success === 1) {
          paymentStatus(paymentId).then(function(res) {
            return res.json();
          }).then(function(_ref2) {
            var success = _ref2.success;

            if (success === true) {
              onPaySuccess();
            } else {
              onPayFail();
            }
          });
        }
      });
    };

    var getOplatiDiv = function getOplatiDiv() {
      return document.getElementById("oplati-" + paymentId);
    };

    var waitForConsumerStatus = function waitForConsumerStatus(sessionId) {
      return interval = setInterval(function() {
        return consumerStatus(paymentId, sessionId).then(function(res) {
          return res.json();
        }).then(function(_ref3) {
          var isConsumerReady = _ref3.isConsumerReady;

          if (isConsumerReady === true) {
            clearInterval(interval);
            onConsumerReady(sessionId);
          }
        });
      }, 3000);
    };

    demandPayment(paymentId).then(function(res) {
      return res.json();
    }).then(function(_ref4) {
      var dynamicQR = _ref4.dynamicQR,
        sessionId = _ref4.sessionId;
      var div = getOplatiDiv();
      var img = document.createElement('img');
      img.src = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" + dynamicQR;
      var span = document.createElement('span');
      span.innerText = `Отсканируйте QR-код приложением "Оплати"`;
      div.appendChild(img);
      div.appendChild(span);
      waitForConsumerStatus(sessionId);
      timeout = setTimeout(function() {
        onPayFail();
      }, awaitTime * 1000);
    });
  })();
</script>
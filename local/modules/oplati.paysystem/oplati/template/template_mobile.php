<?php

/**
 * @var CMain $APPLICATION
 */
global $APPLICATION;
?>
<div id="oplati-<?php echo $payment->getId() ?>"></div>

<script>
  "use strict";

  (function() {
    var paymentId = <?php echo $payment->getId() ?>;

    var demandPayment = function demandPayment(paymentId) {
      return fetch('/bitrix/tools/sale_ps_result.php' + '?action=demandPayment' + '&BX_HANDLER=OPLATI' + '&orderNumber=' + paymentId);
    };

    var consumerStatus = function consumerStatus(paymentId, sessionId) {
      return fetch('/bitrix/tools/sale_ps_result.php' + '?action=consumerStatusAwait' + '&BX_HANDLER=OPLATI' + '&sessionId=' + sessionId + '&orderNumber=' + paymentId);
    };

    var getOplatiDiv = function getOplatiDiv() {
      return document.getElementById("oplati-" + paymentId);
    };

    demandPayment(paymentId).then(function(res) {
      return res.json();
    }).then(function(_ref4) {
      var dynamicQR = _ref4.dynamicQR,
        sessionId = _ref4.sessionId;
      var div = getOplatiDiv();
      var a = document.createElement('a');
      a.href = `https://getapp.o-plati.by/map/?app_link=${dynamicQR}&back_url=${window.location}`;
      a.innerText = 'Оплатить через Оплати!';
      div.appendChild(a);

      consumerStatus(paymentId, sessionId).then(function(res) {
        return res.json()
      }).then(function(_ref5) {
        var success = _ref5.success;
        if (success !== 1) {
          window.location = '/bitrix/tools/sale_ps_fail.php';
        } else {
          window.location = '/bitrix/tools/sale_ps_success.php';
        }
      });

    });
  })();
</script>
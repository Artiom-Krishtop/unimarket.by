<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

\Bitrix\Main\Page\Asset::getInstance()->addCss('/local/php_interface/include/sale_payment/paymentgate/template/style.css');

if(empty($params['ERROR_MESSAGE']) && ($params['ORDER_STATUS']!='' || !empty($params['FORM_URL']))):
	if($params['ORDER_STATUS']!='') {
		?><p class="pg-message"><?=Loc::GetMessage('PAYMENT_GATE_ORDER_STATUS_'.$params['ORDER_STATUS'])?></p><?
	}else {
		?><!--noindex--><a rel="nofollow" target="_blank" href="<?= $params['FORM_URL'] ?>"><?= Loc::getMessage('SALE_HPS_PAYMENTGATE_PAY')?></a><!--/noindex--><?
	}
else:
	if(!empty($params['ERROR_MESSAGE']))
		echo '<p class="pg-error">'.$params['ERROR_MESSAGE'].'</p>';
	if(!empty($params['BINDINGS'])):
		?><form method="post"><?
			?><b><?=Loc::getMessage('BINDING_SELECT_LABEL')?>:</b><?
			$checked = false;
			foreach($params['BINDINGS'] as $row):
				?><p class="pg-radio"><label><input<?if($row['selected']) {echo ' checked="checked"'; $checked=true;}?> type="radio" name="BINDING" value="<?=$row['bindingId']?>"> <?=$row['maskedPan'].' / '.$row['expiryDate']?></label></p><?
			endforeach;
			?><p><label><input<?if(!$checked) echo ' checked="checked"';?> type="radio" name="BINDING" value="other"> <?=Loc::getMessage('BINDING_OTHER_CART_LABEL')?></label></p><?
			?><button type="submit"><?= Loc::getMessage('SALE_HPS_PAYMENTGATE_PAY') ?></button><?
		?></form><?
	endif;
endif;
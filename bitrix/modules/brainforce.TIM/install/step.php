<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
CModule::IncludeModule('highloadblock');

//функция создающая класс для раюоты с таблицей
function GetEntityDataClass($HlBlockId){
	if (empty($HlBlockId) || $HlBlockId < 1) {
		return false;
	}
	$hlblock = HLBT::getById($HlBlockId)->fetch();
	$entity = HLBT::compileEntity($hlblock);
	$entity_data_class = $entity->getDataClass();
	return $entity_data_class;
}

Loc::loadMessages(__FILE__);

if(!check_bitrix_sessid()){

	return;
}

if($errorException = $APPLICATION->GetException()){

	echo(CAdminMessage::ShowMessage($errorException->GetString()));
}else{

	echo(CAdminMessage::ShowNote(Loc::getMessage("BRAINFORCE_TIM_STEP_BEFORE")." ".Loc::getMessage("BRAINFORCE_TIM_STEP_AFTER")));
}

$IdHl = \Bitrix\Highloadblock\HighloadBlockTable::getList(
	array(
		'filter'=>array('=TABLE_NAME'=> "telegramm_message")
	)
)->Fetch()['ID'];

$HlBlockMessage = GetEntityDataClass($IdHl);
$MenuButton = $HlBlockMessage::getList(array(
	'select' => array('ID'),
	'filter' => array('UF_COMMAND_TRIGGER' => 'MENU')
));
if(!$Menu = $MenuButton->Fetch()){
	$res = $HlBlockMessage::add(array(
		'UF_LINE_COMMAND'    => [
			'0' => '[smile4246] Каталог~catalog~1~1',
			'1' => '[smile3582] О Компании~about~2~1',
			'2' => '[smile4097] Обратная связь~feedback~1~2',
			'3' => '[smile3752] Поделиться с другом~repost~2~2',
		],
		'UF_COMMAND_TRIGGER' => 'MENU',
		'UF_DATE_CREATE'     => date('d.m.Y H:i:s', time())
	));
}


?>

<form action="<? echo($APPLICATION->GetCurPage()); ?>">
	<input type="hidden" name="lang" value="<? echo(LANG); ?>" />
	<input type="submit" value="<? echo(Loc::getMessage("BRAINFORCE_TIM_STEP_SUBMIT_BACK")); ?>">
</form>
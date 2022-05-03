<?
if(IsModuleInstalled('sproduction.integration'))
{
	\Bitrix\Main\Loader::includeModule('sproduction.integration');
	$sync_active = \SProduction\Integration\Settings::get('active');
	if ($sync_active) {
		try {
			$app_info = \SProduction\Integration\Rest::getAppInfo();
			$script_url = $app_info['site'] . '/bitrix/admin/sprod_integr_order_create.php';
			// Remove old placement
			\SProduction\Integration\Rest::execute('placement.unbind', [
				'PLACEMENT' => 'CRM_DEAL_DETAIL_TOOLBAR',
				'HANDLER' => $script_url,
			]);
			// Add new placement
			$source_name = \SProduction\Integration\Settings::get('source_name');
			$title = 'Создать заказ'.($source_name ? (' (' . $source_name . ')') : '');
			\SProduction\Integration\Rest::execute('placement.bind', [
				'PLACEMENT' => 'CRM_DEAL_LIST_TOOLBAR',
				'HANDLER' => $script_url,
				'TITLE' => $title,
			]);
		} catch (\Exception $e) {
		}
	}
}
?>
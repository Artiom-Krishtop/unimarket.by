<?php
ini_set('max_execution_time', 1000);
header('Content-Type: text/html; charset=utf-8');

file_put_contents(__DIR__ .'/0start.st', time());

require_once __DIR__ . '/config/onliner.php';
require_once __DIR__ . '/core/Onliner.php';
require_once __DIR__ . '/config/b24.php';
require_once __DIR__ . '/core/B24.php';

$b24 = new B24(B24_URL_API);

define('DAY_INTERVAL', 0);
define('MINUTES_INTERVAL', 30);
$dateExportDate = new DateTime();
$dateExportDate->modify('-' . DAY_INTERVAL . ' day');
$dateExportDate->modify('-' . MINUTES_INTERVAL . ' minutes');
$timeStart = $dateExportDate->format('Y-m-d H:i:s');

$onlinerShops=array(
	array(
		'nameShop'=>'Dadget 16204',
		'prefixCode' => 'OnlineDadget_',
		'source'=>2,
		'assigned'=>1,
		'clientIdOnliner' => '6bdca2561d97d307b101',
		'clientSecretOnliner' => '76a9e6529048b249000a9b0c2c19105961ffdee7',
	),

	array(
		'nameShop'=>'Unimarket 18678',
		'prefixCode' => 'OnlinerUnimarket_',
		'source'=>3,
		'assigned'=>1,
		'clientIdOnliner' => '426d854d4edb1ff6893e',
		'clientSecretOnliner' => '0c2dce4562ad528f23eb1611da139974347e5d11',
	),
);


foreach ($onlinerShops as $shop){
	$onliner = new Onliner(
		$shop['clientIdOnliner'],
		$shop['clientSecretOnliner'],
		ONLINER_API_URL,
		ONLINER_TOKEN_URL
	);

	/*****************************************************
	 *  STAGE #1 - ADD NEW ORDER FROM ONLINER TO BITRIX
	 *****************************************************/

	$loadOrderOnliner = $onliner->getOrders(array('status' => 'new,confirmed', 'limit' => 100));


	if(count($loadOrderOnliner['orders'])!=0) {
		foreach ($loadOrderOnliner['orders'] as $onlinerOrder) {
			/**
			 * Check add orders
			 */
			if (file_exists(__DIR__ . '/orders/' . $shop['prefixCode'] . $onlinerOrder['key'] . '.json')) {
				continue;
			}

			$order = $onliner->getOrderByIDWithDetail($onlinerOrder['key']);

			$newOrder = array(
				'TITLE' => 'Заявка с onliner ' . $shop['nameShop'] . ' № ' . $order['key'],
				'TYPE_ID' => 'SALE',
				'STAGE_ID' => 'NEW',
				'SOURCE_ID' => $shop['source'],
				'OPENED' => "Y",
				'ASSIGNED_BY_ID' => $shop['assigned'],
				'UF_CRM_1628961218' => $shop['prefixCode'] . $order['key'], //ORDER_XML
				'UF_CRM_1628970482' => '', //ORDER_XML
				'COMMENTS' => $order['comment']
			);

			if($order['payment']['type'] == 'online' || $order['payment']['type'] == 'halva'){
				$newOrder['UF_CRM_1628970482'] = 'STATUS_PAY_CONTROL';
			}
			
			/**
			 * Add Delivery Price
			 */
			if(isset($order['delivery']['price']['amount'])){
				$newOrder['COMMENTS'] .= ' Цена доставки: ' . $order['delivery']['price']['amount'] . ' ' . $order['delivery']['price']['currency'];
			}

			/**
			 * Find Product
			 */
			$orderProducts = array();

			if (count($order['positions']) != 0) {
				foreach ($order['positions'] as $position) {
					$product = array(
						'PRICE' => (float)$position['position_price']['amount'],
						'QUANTITY' => (float)$position['quantity'],
					);

					$idProductB24 = '';

					if($position['article'] != ''){
						$requestGetProduct = $b24->getProductByFilter(array('PROPERTY_359' => $position['article']));

						if (isset($requestGetProduct['result'][0]['ID'])) {
							$product['PRODUCT_ID'] = $requestGetProduct['result'][0]['ID'];
							$orderProducts[] = $product;
						} else {
							$newOrder['COMMENTS'] .= ' ' . $position['product']['full_name'] . ' кол.' . $position['quantity'] . ' цена ' . $position['position_price']['amount'] . ' ' . $position['position_price']['currency'];
						}
					}else{
						$newOrder['COMMENTS'] .= ' ' . $position['product']['full_name'] . ' кол.' . $position['quantity'] . ' цена ' . $position['position_price']['amount'] . ' ' . $position['position_price']['currency'];
					}
				}
			}

			$newOrder['COMMENTS'] = trim($newOrder['COMMENTS']);

			/**
			 * SEND To Bitrix Order
			 */

			$requestAddOrder = $b24->addNewDeal($newOrder);

			$idDeal = $requestAddOrder['result'];

			if ($idDeal != '') {
				/**
				 * SEND Product List
				 */
				if (count($orderProducts) != 0) {
					$addProduct = $b24->addProductDeal(array(
						'id' => $idDeal,
						'rows' => $orderProducts
					));
				}

				/**
				 * ADD File Log
				 */
				file_put_contents(__DIR__ . '/orders/' . $shop['prefixCode'] . $order['key'] . '.json', json_encode(
					array(
						'shop' => $shop['nameShop'],
						'key' => $order['key'],
						'statusOnliner' => 'new',
						'idBitrix' => $idDeal,
						'statusBitrix' => 'NEW',
					)
				));
			}
		}
	}
	/***********************************************************
	 *  STAGE #2 - CONFIRMED ORDER ONLINER and ADD add CUSTOMER
	 ***********************************************************/

	$requestDealBitrix = $b24->getDealByFilter(
		array(
			'SOURCE_ID' => $shop['source'],
			'STAGE_ID' => 'PREPARATION',
		),
		array('ID', 'UF_CRM_1628961218', 'CONTACT_ID', 'COMMENTS')
	);

	if(count($requestDealBitrix['result'])!=0){
		foreach ($requestDealBitrix['result'] as $deal){
			if($deal['UF_CRM_1628961218'] == ''){ continue; }
			if(file_exists(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json')){
				$logOrder = json_decode(file_get_contents(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json'), 1);

				if($logOrder['statusOnliner'] == 'new'){
					/**
					 * CONFIRMED ORDER ONLINER
					 */
					$onliner->changeStatusOrder($logOrder['key'], 'processing');
					sleep(2);
					$onliner->changeStatusOrder($logOrder['key'], 'confirmed');
					sleep(2);

					$order =$onliner->getOrderByIDWithDetail($logOrder['key']);

					/**
					 * Get/Add User
					 */
					$user = array(
						'name'=>$order['contact']['name'],
						'email'=>$order['contact']['email'],
						'phone'=>str_replace(array(' ', '-', '*'), '', $order['contact']['phone']),
					);

					$idUserInCRM = '';

					$idUserInCRMRequest = $b24->getUserByPhone($user['phone']);

					if(isset($idUserInCRMRequest['total']) && $idUserInCRMRequest['total'] != 0 ){
						$idUserInCRM = $idUserInCRMRequest['result'][0]['ID'];
					}

					if($idUserInCRM == ''){
						$newUserDate = array(
							'NAME'=> $user['name'],
							'TYPE_ID'=>'CLIENT',
							'ASSIGNED_BY_ID' => $shop['assigned'],
							"PHONE" => array(array("VALUE" => $user['phone'], "VALUE_TYPE" => "WORK" )),
							"EMAIL" => array(array("VALUE" => $user['email'], "VALUE_TYPE" => "WORK" )),
						);

						$addUser = $b24->addNewUser($newUserDate);
						$idUserInCRM = $addUser['result'];
					}

					$updateDeal = array(
						'CONTACT_ID'=>$idUserInCRM,
						'COMMENTS'=>$deal['COMMENTS']
					);

					$updateDeal['COMMENTS'] .= ' ТИП ДОСТАВКИ:' . $order['delivery']['type'];
					if(isset( $order['promotions']['mastercard_free_delivery'])){
						$updateDeal['COMMENTS'] .= ' Акции: mastercard_free_delivery';
					}
					$updateDeal['COMMENTS'] .= ' АДРЕС: ' . $order['delivery']['city'] . ' '. $order['delivery']['address'];
					$updateDeal['COMMENTS'] .= ' ОПЛАТА ТИП:' . $order['payment']['type'];

					/**
					 * SEND AND UPDATE LOG
					 */
					$requestUpdateDeal= $b24->updateDeal($deal['ID'], $updateDeal);

					$logOrder['statusOnliner'] = 'confirmed';
					$logOrder['statusBitrix'] = 'PREPARATION';

					file_put_contents(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json', json_encode( $logOrder ));
				}
			}
		}
	}

	/***********************************************************
	 *  STAGE #3 - CONTROL ONLINE PAY
	 ***********************************************************/

	$requestDealBitrix = $b24->getDealByFilter(
		array(
			'SOURCE_ID' => $shop['source'],
			'UF_CRM_1628970482' => 'STATUS_PAY_CONTROL',
			'STAGE_ID' => array('PREPARATION', 'PREPAYMENT_INVOICE', 'EXECUTING'),
		),
		array('ID', 'UF_CRM_1628961218', 'UF_CRM_1628970482', 'CONTACT_ID', 'COMMENTS')
	);


	if(count($requestDealBitrix['result'])!=0){
		foreach ($requestDealBitrix['result'] as $deal){
			if($deal['UF_CRM_1628961218'] == ''){ continue; }
			if(file_exists(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json')){
				$logOrder = json_decode(file_get_contents(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json'),1);

				$order =$onliner->getOrderByIDWithDetail($logOrder['key']);

				if(isset($order['payment']['status'])){
					if($order['payment']['status'] == 'authorized'){

						$requestUpdateDeal= $b24->updateDeal($logOrder['idBitrix'], array('STAGE_ID'=>'FINAL_INVOICE'));

						$logOrder['statusBitrix'] = 'FINAL_INVOICE';

						file_put_contents(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json', json_encode( $logOrder ));
					}
				}
			}
		}
	}


	/***********************************************************
	 *  STAGE #4 - SEND INFO PICKUP-POINT-DELIVERED
	 ***********************************************************/

//	$requestDealBitrix = $b24->getDealByFilter(
//		array(
//			'SOURCE_ID' => $shop['source'],
//			'STAGE_ID' => 'PICKUP-POINT-DELIVERED',
//		),
//		array('ID', 'UF_CRM_1628961218', 'CONTACT_ID', 'COMMENTS')
//	);
//
//	if(count($requestDealBitrix['result'])!=0){
//		foreach ($requestDealBitrix['result'] as $deal){
//			if($deal['UF_CRM_1628961218'] == ''){ continue; }
//			if(file_exists(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json')){
//				$logOrder = json_decode(file_get_contents(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json'), 1);
//
//				if(!isset($logOrder['pickupPointDelivered'])){
//					$order =$onliner->getOrderByIDWithDetail($logOrder['key']);
//					if($order['delivery']['type'] == 'pickup_point') {
//						$messages = 'Ваш заказ укомплектован для самовывоза в нашем магазине';
//
//						$onliner->setOrderInPickupPoint($logOrder['key'], $messages);
//
//						$logOrder['pickupPointDelivered'] = 'add';
//
//						file_put_contents(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json', json_encode( $logOrder ));
//					}
//				}
//			}
//		}
//	}


	/***********************************************************
	 *  STAGE #5 - close a missed order (user_canceled Отменен покупателем)
	 ***********************************************************/
	$loadOrderOnliner = $onliner->getOrders(array('status' => 'user_canceled', 'limit' => 5));

	if(count($loadOrderOnliner['orders'])!=0) {
		foreach ($loadOrderOnliner['orders'] as $onlinerOrder) {
			if(file_exists(__DIR__ . '/orders/' . $shop['prefixCode'] . $onlinerOrder['key'] . '.json')){
				$logOrder = json_decode(file_get_contents(__DIR__ . '/orders/'.  $shop['prefixCode'] . $onlinerOrder['key'] . '.json'), 1);

				if($logOrder['statusOnliner'] != 'user_canceled'){

					$requestUpdateDeal= $b24->updateDeal($logOrder['idBitrix'], array('STAGE_ID'=>8));

					$logOrder['statusOnliner'] = 'user_canceled';
					$logOrder['statusBitrix'] = '8';

					file_put_contents(__DIR__ . '/orders/'. $shop['prefixCode'] . $onlinerOrder['key'] . '.json', json_encode( $logOrder ));
				}
			}
		}
	}

	/***********************************************************
	 *  STAGE #6 - close a missed order (system_canceled Отменен системой)
	 ***********************************************************/
	$loadOrderOnliner = $onliner->getOrders(array('status' => 'system_canceled', 'limit' => 5));
	if(count($loadOrderOnliner['orders'])!=0) {
		foreach ($loadOrderOnliner['orders'] as $onlinerOrder) {
			if(file_exists(__DIR__ . '/orders/' . $shop['prefixCode'] . $onlinerOrder['key'] . '.json')){
				$logOrder = json_decode(file_get_contents(__DIR__ . '/orders/'.  $shop['prefixCode'] . $onlinerOrder['key'] . '.json'), 1);

				if($logOrder['statusOnliner'] != 'system_canceled'){

					$requestUpdateDeal= $b24->updateDeal($logOrder['idBitrix'], array('STAGE_ID'=>9));

					$logOrder['statusOnliner'] = 'system_canceled';
					$logOrder['statusBitrix'] = '9';

					file_put_contents(__DIR__ . '/orders/'. $shop['prefixCode'] . $onlinerOrder['key'] . '.json', json_encode( $logOrder ));
				}
			}
		}
	}

	/***********************************************************
	 *  STAGE #7 - close a missed order (expired - Упущен)
	 ***********************************************************/
	$loadOrderOnliner = $onliner->getOrders(array('status' => 'expired', 'limit' => 5));
	if(count($loadOrderOnliner['orders'])!=0) {
		foreach ($loadOrderOnliner['orders'] as $onlinerOrder) {
			if(file_exists(__DIR__ . '/orders/' . $shop['prefixCode'] . $onlinerOrder['key'] . '.json')){
				$logOrder = json_decode(file_get_contents(__DIR__ . '/orders/'.  $shop['prefixCode'] . $onlinerOrder['key'] . '.json'), 1);

				if($logOrder['statusOnliner'] != 'expired'){

					$requestUpdateDeal= $b24->updateDeal($logOrder['idBitrix'], array('STAGE_ID'=>4));

					$logOrder['statusOnliner'] = 'expired';
					$logOrder['statusBitrix'] = '4';

					file_put_contents(__DIR__ . '/orders/'. $shop['prefixCode'] . $onlinerOrder['key'] . '.json', json_encode( $logOrder ));
				}
			}
		}
	}

	/***********************************************************
	 *  STAGE #8 - SEND INFO shipping
	 ***********************************************************/
	$requestDealBitrix = $b24->getDealByFilter(
		array(
			'SOURCE_ID' => $shop['source'],
			'STAGE_ID' => '1',
		),
		array('ID', 'UF_CRM_1628961218', 'CONTACT_ID', 'COMMENTS')
	);

	if(count($requestDealBitrix['result'])!=0){
		foreach ($requestDealBitrix['result'] as $deal){
			if($deal['UF_CRM_1628961218'] == ''){ continue; }
			if(file_exists(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json')){
				$logOrder = json_decode(file_get_contents(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json'), 1);

				if($logOrder['statusOnliner']!='shipping'){
					$onliner->changeStatusOrder($logOrder['key'], 'shipping');

					$logOrder['statusOnliner'] = 'shipping';
					$logOrder['statusBitrix'] = '1';

					file_put_contents(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json', json_encode( $logOrder ));
				}
			}
		}
	}

	/***********************************************************
	 *  STAGE #9 - SEND successful close
	 ***********************************************************/
	$requestDealBitrix = $b24->getDealByFilter(
		array(
			'SOURCE_ID' => $shop['source'],
			'STAGE_ID' => 'WON',
			'>DATE_MODIFY'=>$timeStart
		),
		array('ID', 'UF_CRM_1628961218', 'CONTACT_ID', 'COMMENTS')
	);

	if(count($requestDealBitrix['result'])!=0){
		foreach ($requestDealBitrix['result'] as $deal){
			if($deal['UF_CRM_1628961218'] == ''){ continue; }
			if(file_exists(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json')){
				$logOrder = json_decode(file_get_contents(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json'), 1);

				if($logOrder['statusOnliner']!='delivered'){
					$onliner->changeStatusOrder($logOrder['key'], 'delivered');

					$logOrder['statusOnliner'] = 'delivered';
					$logOrder['statusBitrix'] = 'WON';

					file_put_contents(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json', json_encode( $logOrder ));
				}
			}
		}
	}

	/***********************************************************
	 *  STAGE #9 - SEND unsuccessful close
	 ***********************************************************/
	$requestDealBitrix = $b24->getDealByFilter(
		array(
			'SOURCE_ID' => $shop['source'],
			'STAGE_ID' => array('LOSE', 'APOLOGY', '2', '3', '4', '5', '6', '7', '8', '9' ),
			'>DATE_MODIFY'=>$timeStart
		),
		array('ID', 'UF_CRM_1628961218', 'CONTACT_ID', 'COMMENTS', 'STAGE_ID')
	);

	if(count($requestDealBitrix['result'])!=0){
		foreach ($requestDealBitrix['result'] as $deal){
			if($deal['UF_CRM_1628961218'] == ''){ continue; }
			if(file_exists(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json')){
				$logOrder = json_decode(file_get_contents(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json'), 1);

				if(in_array($logOrder['statusOnliner'], array('new', 'processing', 'confirmed', 'shipping'))){

					$onliner->changeStatusOrderUnsuccessful($logOrder['key'], array(
						'status'=>'shop_canceled',
						'reason'=>array(
							'id'=>7,
							'comment'=>'Иной'
						)
					));

					$logOrder['statusOnliner'] = 'shop_canceled';
					$logOrder['statusBitrix'] = $deal['STAGE_ID'];

					file_put_contents(__DIR__ . '/orders/'. $deal['UF_CRM_1628961218'] . '.json', json_encode( $logOrder ));
				}
			}
		}
	}
}

file_put_contents(__DIR__ .'/0finish.st', time());
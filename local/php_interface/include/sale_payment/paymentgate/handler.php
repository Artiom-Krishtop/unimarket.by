<?
namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Request;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PriceMaths;
use Bitrix\Sale\BusinessValue;

Loc::loadMessages(__FILE__);

class PaymentGateHandler extends PaySystem\ServiceHandler
{
	/**
	 * @return array
	 */
	static public function getIndicativeFields()
	{
		return array('BX_HANDLER' => 'PAYMENTGATE');
	}

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		if(Loader::includeModule('bgpb.paymentgate')){
			if(is_null($request))
				$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
			$busValues = $this->getParamsBusValue($payment);

			$extraParams = array(
				'BINDINGS'=>false,
				'ENCODING' => $this->service->getField('ENCODING')
			);

			if(empty($busValues['ORDER_ID'])){
				$order = $payment->getCollection()->getOrder();
				$pg = new \BGPB\PaymentGate($busValues['USER_NAME'],$busValues['PASSWORD'],$busValues['URL'],$busValues['PORT']);
				if($busValues['PS_IS_TEST']=='Y')
					/*$pg->setTestMode(true);*/
					$pg->setTestMode(false);
				if($busValues['USE_SSL']=='Y' && !empty($busValues['SSL_KEY']) && $busValues['SSL_KEY_PASSWORD']!='' && !empty($busValues['SSL_CERTIFICATE'])){
					$pg->setSslMode(true);
					$pg->setSslKey($_SERVER['DOCUMENT_ROOT'].\CFile::GetPath($busValues['SSL_KEY']));
					$pg->setSslPasswd($busValues['SSL_KEY_PASSWORD']);
					$pg->setSslCert($_SERVER['DOCUMENT_ROOT'].\CFile::GetPath($busValues['SSL_CERTIFICATE']));
					/*$pg->setSslKey('http'. (isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].\CFile::GetPath($busValues['SSL_KEY']));
					$pg->setSslPasswd($busValues['SSL_KEY_PASSWORD']);
					$pg->setSslCert('http'. (isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].\CFile::GetPath($busValues['SSL_CERTIFICATE']));*/
				}


				if(!empty($busValues['CLIENT_ID']) && $busValues['PS_IS_BINDING'] == 'Y'){


					$result = $pg->getBindings($busValues['CLIENT_ID']);
					if($result['errorCode']==0)
						$extraParams['BINDINGS'] = $result['bindings'];
				}

				$params = array(
					'orderNumber'=>$busValues['PAYMENT_ID'],
					'clientId'=>$busValues['CLIENT_ID'],
					'amount'=>$busValues['SUM'],
					'language'=>\Bitrix\Main\Application::getInstance()->getContext()->getLanguage()
				);
				if(!empty($busValues['RETURN_URL']))
					$params['returnUrl'] = $busValues['RETURN_URL'].'?PAYMENT_ID='.$payment->getId();
				if(!empty($busValues['FAIL_URL']))
					$params['failUrl'] = $busValues['FAIL_URL'].'?PAYMENT_ID='.$payment->getId();
				//if(!empty($busValues['DESCRIPTION']))
				if ( LANG_CHARSET == 'windows-1251')
				{
					//$utf8str = $APPLICATION->ConvertCharset("кирилица", "windows-1251", "Unicode");
				        /*\Bitrix\Main\Diag\Debug::writeToFile($busValues['SITE_NAME']);*/

					/*$utf8str = iconv( 'CP1251', 'UTF-8', $busValues['SITE_NAME']);*/

				        /*\Bitrix\Main\Diag\Debug::writeToFile($utf8str);*/

					$params['description'] = iconv( 'CP1251', 'UTF-8', $busValues['SITE_NAME']);//'PaymentGate Order #'.$busValues['PAYMENT_ID']; //$busValues['DESCRIPTION'];

				}
				else
					$params['description'] = $busValues['SITE_NAME'];//'PaymentGate Order #'.$busValues['PAYMENT_ID']; //$busValues['DESCRIPTION'];
	
				if(!empty($busValues['CURRENCY']))
					$params['currency'] = $busValues['CURRENCY'];
				if($params['currency']==933)
					$params['amount'] = $params['amount']*100;

				if(!empty($extraParams['BINDINGS']) && $request->getPost('BINDING')!='' && $busValues['PS_IS_BINDING']=='Y' && $request->getPost('BINDING')!='other'){
					//bindings payment
					$found = false;
					foreach($extraParams['BINDINGS'] as $key=>$row){
						if($row['bindingId'] == $request->getPost('BINDING')){
							$found = true;
							$extraParams['BINDINGS'][$key]['selected'] = true;
							break;
						}
					}
					if($found)
						$params['jsonParams'] = json_encode(
							array(
								'transaction_type' => 'payment',
								'sender_type'=>'binding',
								'sender_id'=>$request->getPost('BINDING')
							)
						);
					else
						$extraParams['ERROR_MESSAGE'] = Loc::getMessage('SALE_HPS_PAYMENTGATE_BINDING_NOT_FOUNDED');
				}

				if(empty($extraParams['BINDINGS']) || $request->getPost('BINDING')=='other' || !empty($params['jsonParams']) || $busValues['PS_IS_BINDING']=='N'){
					//form payment
					$result = $pg->registerOrder($params);
					if((empty($result['errorCode']) || $result['errorCode']==0) && !empty($result['formUrl'])){
						//save order id
						if(!empty($result['orderId'])){
							$orderIdProp = BusinessValue::getMapping(
								'ORDER_ID',
								$this->service->getConsumerName(),
								$order->getPersonTypeId(),
								array('MATCH' => BusinessValue::MATCH_COMMON)
							);

							switch($orderIdProp['PROVIDER_KEY']):
								case 'PAYMENT':
									$payment->setField($orderIdProp['PROVIDER_VALUE'],$result['orderId']);
									$payment->save();
									break;
								case 'ORDER':
									$order->setField($orderIdProp['PROVIDER_VALUE'],$result['orderId']);
									$order->save();
									break;
								case 'PROPERTY':
									foreach ($order->getPropertyCollection() as $property){
										if($property->getField('CODE')==$orderIdProp['PROVIDER_VALUE']){
											$property->setValue($result['orderId']);
											$order->save();
											break;
										}
									}
									break;
							endswitch;
						}

						//save form url
						$formUrlProp = BusinessValue::getMapping(
							'FORM_URL',
							$this->service->getConsumerName(),
							$order->getPersonTypeId(),
							array('MATCH' => BusinessValue::MATCH_COMMON)
						);
						switch($formUrlProp['PROVIDER_KEY']):
							case 'PAYMENT':
								$payment->setField($formUrlProp['PROVIDER_VALUE'],$result['formUrl']);
								$payment->save();
								break;
							case 'ORDER':
								$order->setField($formUrlProp['PROVIDER_VALUE'],$result['formUrl']);
								$order->save();
								break;
							case 'PROPERTY':
								foreach ($order->getPropertyCollection() as $property){
									if($property->getField('CODE')==$formUrlProp['PROVIDER_VALUE']){
										$property->setValue($result['formUrl']);
										$order->save();
										break;
									}
								}
								break;
						endswitch;
						$extraParams['FORM_URL'] = $result['formUrl'];
					}elseif($result['errorCode']!=0){
						$extraParams['ERROR_MESSAGE'] = $result['errorMessage'];
						$extraParams['ERROR_CODE'] = $result['errorCode'];
					}else{
						$extraParams['ERROR_MESSAGE'] = Loc::getMessage('SALE_HPS_PAYMENTGATE_UNKNOWN_ERROR');
					}
				}
			}

			$this->setExtraParams($extraParams);

			return $this->showTemplate($payment, 'template');
		}else{
			$result = new ServiceResult();
			$result->addError(new Error(Loc::getMessage('SALE_HPS_PAYMENTGATE_MODULE_NOT_FOUND')));
			return $result;
		}
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		return $request->get('PAYMENT_ID');
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return bool
	 */
	private function isCorrectSum($paySum, $pgSum, $currency)
	{
		return PriceMaths::roundByFormatCurrency($paySum, $currency) == PriceMaths::roundByFormatCurrency($pgSum, $currency);
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	public function processRequest(Payment $payment, Request $request)
	{
		/** @var PaySystem\ServiceResult $serviceResult */
		$serviceResult = new PaySystem\ServiceResult();

		$busValues = $this->getParamsBusValue($payment);
		if(empty($busValues['ORDER_ID'])){
			$serviceResult->addError(new Error(Loc::getMessage('SALE_HPS_PAYMENTGATE_ORDER_ID_ERROR')));
		}else{
			if(Loader::includeModule('bgpb.paymentgate')){
				$pg = new \BGPB\PaymentGate($busValues['USER_NAME'],$busValues['PASSWORD'],$busValues['URL'],$busValues['PORT']);
				if($busValues['PS_IS_TEST']=='Y')
					$pg->setTestMode(true);
				if($busValues['USE_SSL']=='Y' && !empty($busValues['SSL_KEY']) && $busValues['SSL_KEY_PASSWORD']!='' && !empty($busValues['SSL_CERTIFICATE'])){
					$pg->setSslMode(true);
					$pg->setSslKey($_SERVER['DOCUMENT_ROOT'].\CFile::GetPath($busValues['SSL_KEY']));
					$pg->setSslPasswd($busValues['SSL_KEY_PASSWORD']);
					$pg->setSslCert($_SERVER['DOCUMENT_ROOT'].\CFile::GetPath($busValues['SSL_CERTIFICATE']));
					/*$pg->setSslKey('http'. (isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].\CFile::GetPath($busValues['SSL_KEY']));
					$pg->setSslPasswd($busValues['SSL_KEY_PASSWORD']);
					$pg->setSslCert('http'. (isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].\CFile::GetPath($busValues['SSL_CERTIFICATE']));*/
				}
				$params = array(
					'orderId'=>$busValues['ORDER_ID'],
					'language'=>\Bitrix\Main\Application::getInstance()->getContext()->getLanguage()
				);

				$result = $pg->getOrderStatus($params);
				if($result['ErrorCode']!=0){
					$serviceResult->addError(new Error($result['ErrorMessage']));
				}elseif ($this->isCorrectSum($busValues['SUM'], $result['currency']==933?$result['Amount']/100:$result['Amount'], $payment->getField('CURRENCY')) && $busValues['PAYMENT_ID'] == $result['OrderNumber']){
					switch($result['OrderStatus']){
						case 0:
							$serviceResult->setData(
								array(
									'orderStatus'=>$result['OrderStatus'],
									'orderStatusDesc'=>Loc::getMessage('SALE_HPS_PAYMENTGATE_STATUS_'.$result['OrderStatus'])
								)
							);
							return $serviceResult;
							break;
						case 2:
							if(!$payment->isPaid()){
								$serviceResult->setData(
									array(
										'orderStatus'=>$result['OrderStatus'],
										'orderStatusDesc'=>Loc::getMessage('SALE_HPS_PAYMENTGATE_STATUS_'.$result['OrderStatus'])
									)
								);
								$serviceResult->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
								$serviceResult->setPsData(
									array(
										'PS_STATUS' => 'Y',
										'PS_STATUS_CODE' => $result['OrderStatus'],
										'PS_STATUS_DESCRIPTION' => Loc::getMessage('STATUS_'.$result['OrderStatus'].'_DESCRIPTION'),
										'PS_STATUS_MESSAGE' => $this->isTestMode($payment)?Loc::getMessage('SALE_HPS_PAYMENTGATE_TEST'):'',
										'PS_SUM' => $busValues['SUM'],
										'PS_CURRENCY' => $payment->getField('CURRENCY'),
										'PS_RESPONSE_DATE' => new DateTime()
									)
								);
							}else{
								$serviceResult->addError(new Error(Loc::getMessage('SALE_HPS_PAYMENTGATE_ALREADY_PAID')));
							}
							break;
						case 4:
							$serviceResult->setData(
								array(
									'orderStatus'=>$result['OrderStatus'],
									'orderStatusDesc'=>Loc::getMessage('SALE_HPS_PAYMENTGATE_STATUS_'.$result['OrderStatus'])
								)
							);
							$serviceResult->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
							$serviceResult->setPsData(
								array(
									'PS_STATUS' => 'N',
									'PS_STATUS_CODE' => $result['OrderStatus'],
									'PS_STATUS_DESCRIPTION' => Loc::getMessage('STATUS_'.$result['OrderStatus'].'_DESCRIPTION'),
									'PS_STATUS_MESSAGE' => $this->isTestMode($payment)?Loc::getMessage('SALE_HPS_PAYMENTGATE_TEST'):'',
									'PS_SUM' => $busValues['SUM'],
									'PS_CURRENCY' => $payment->getField('CURRENCY'),
									'PS_RESPONSE_DATE' => new DateTime()
								)
							);
							break;
					}
				}else{
					$serviceResult->addError(new Error(Loc::getMessage('SALE_HPS_PAYMENTGATE_SUM_OR_ID_ERROR')));
				}
			}else{
				$serviceResult->addError(new Error(Loc::getMessage('SALE_HPS_PAYMENTGATE_MODULE_NOT_FOUND')));
			}
		}

		return $serviceResult;
	}

	public function sendResponse(PaySystem\ServiceResult $result, Request $request)
	{
		if($result->isSuccess()){
			$data = $result->getData();
			echo '<p>'.(empty($data['orderStatusDesc'])?Loc::getMessage('SALE_HPS_PAYMENTGATE_STATUS_OK'):$data['orderStatusDesc']).'</p>';
		}else{
			echo '<p class="error">'.implode('<br/>',$result->getErrorMessages()).'</p>';
		}
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return array('RUB', 'USD', 'EUR');
	}

	/**
	 * @param Payment $payment
	 * @return mixed
	 */
	protected function isTestMode(Payment $payment = null)
	{
		return $this->getBusinessValue($payment, 'PS_IS_TEST');
	}
}
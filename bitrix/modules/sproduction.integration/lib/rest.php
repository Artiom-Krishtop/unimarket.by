<?php
/**
 *    Rest
 *
 * @mail support@s-production.online
 * @link s-production.online
 */

namespace SProduction\Integration;

use Bitrix\Main,
    Bitrix\Main\DB\Exception,
    Bitrix\Main\Config\Option,
	SProduction\Integration\Settings;

class Rest
{
    const MODULE_ID = 'sproduction.integration';
	protected static $MANUAL_RUN = false;
    static $site = false;
    static $portal = false;
    static $app_id = false;
    static $secret = false;

	public static function setBulkRun() {
		self::$MANUAL_RUN = true;
	}

	public static function isBulkRun() {
		return self::$MANUAL_RUN;
	}

	/**
	 * Get Bitrix24 application info
	 */

    public static function getAppInfo() {
		$info = false;
	    if (self::$site === false) {
		    self::$site = Settings::get("site");
	    }
	    if (self::$portal === false) {
		    self::$portal = Settings::get("portal");
	    }
	    if (self::$app_id === false) {
		    self::$app_id = Settings::get("app_id");
	    }
	    if (self::$secret === false) {
		    self::$secret = Settings::get("secret");
	    }
	    if (self::$site && self::$portal && self::$app_id && self::$secret) {
		    $info = [
		    	'site' => self::$site,
		    	'portal' => self::$portal,
		    	'app_id' => self::$app_id,
		    	'secret' => self::$secret,
		    ];
	    }
		return $info;
    }

	/**
	 * Save file with auth data
	 *
	 * @param $info
	 *
	 * @return bool|int
	 */

	public static function saveAuthInfo($info) {
		$res = Settings::save("credentials", $info, true);
		return $res;
	}

	/**
	 * Read auth data
	 *
	 * @return bool|mixed
	 */

	public static function getAuthInfo() {
		$info = Settings::get("credentials", true);
		return $info;
	}

	/**
	 * Get link for application authentication
	 *
	 * @return bool|string
	 */
	public static function getAuthLink() {
		$app_info = self::getAppInfo();
		if (!$app_info) {
			return false;
		}
		$link = $app_info['portal'].'/oauth/authorize/?client_id='.$app_info['app_id'].'&response_type=code';
		return $link;
	}

	/**
	 * Limits control
	 */

	public static function controlLimits() {
		$delay = 0;
		// Get values
		$last_exec = Settings::get('rest_last_exec');
		$count_exec = Settings::get('rest_count_exec');
		// Waiting for end of executions
		$current_exec = microtime(true);
		if ($current_exec < $last_exec) {
			$diff = $last_exec - $current_exec;
			$delay += $diff * 1000000;
			$current_exec = $last_exec;
		}
		// Update limits
		$diff = $current_exec - $last_exec;
		$count_exec -= $diff * 2;
		$count_exec = $count_exec >= 0 ? $count_exec : 0;
		$count_exec++;
		// Calc delay
		if ($count_exec > 30) {
			$diff = 1;
			$delay += $diff * 1000000;
			$current_exec += $diff;
			$count_exec -= $diff * 1;
		}
		// Save values
		Settings::save('rest_last_exec', $current_exec);
		Settings::save('rest_count_exec', $count_exec);
		// Delay
		if ($delay) {
			\SProdIntegration::Log('(controlLimits) delay '.$delay);
			usleep($delay);
		}
	}

	/**
     * Get auth token
     */

	public static function restToken($code) {
	    $app_info = self::getAppInfo();
        if (!$code || !$app_info) {
            return false;
        }

        $query_url = 'https://oauth.bitrix.info/oauth/token/';
        $query_data = http_build_query($queryParams = array(
            'grant_type' => 'authorization_code',
            'client_id' => $app_info['app_id'],
            'client_secret' => $app_info['secret'],
            'code' => $code,
        ));
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $query_url . '?' . $query_data,
        ));
        $result = curl_exec($curl);
        curl_close($curl);
        $cred = json_decode($result, true);

        if (!$cred['error']) {
            // Save new auth credentials
            self::saveAuthInfo($cred);
        }

        return $cred;
    }


    /**
     * Refresh access token
     *
     * @param array $refresh_token
     * @return bool|mixed
     */

	public static function refreshToken($refresh_token) {
    	$app_info = self::getAppInfo();
        if (!isset($refresh_token) || !$app_info) {
            return false;
        }

		self::controlLimits();

        $query_url = 'https://oauth.bitrix.info/oauth/token/';
        $query_data = http_build_query($queryParams = array(
            'grant_type' => 'refresh_token',
            'client_id' => $app_info['app_id'],
            'client_secret' => $app_info['secret'],
            'refresh_token' => $refresh_token,
        ));
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $query_url.'?'.$query_data,
        ));
        $result = curl_exec($curl);
        curl_close($curl);
	    $resp = json_decode($result, true);

		if ( $resp['error']) {
			throw new Exception($resp['error_description'], $resp['error']);
		}

        return $resp;
    }


    /**
     * Send rest query to Bitrix24.
     *
     * @param $method - Rest method, ex: methods
     * @param array $params - Method params, ex: []
     * @param array $cred - Authorize data, ex: Array('domain' => 'https://test.bitrix24.com', 'access_token' => '7inpwszbuu8vnwr5jmabqa467rqur7u6')
     * @param boolean $auth_refresh - If authorize is expired, refresh token
     *
     * @return mixed
     */

    public static function execute($method, array $params = [], $cred = false, $auth_refresh = true, $only_res=true, $err_repeate=true) {
	    \SProdIntegration::Log('(rest execute) method '.$method);
	    $app_info = self::getAppInfo();
	    if (!$app_info) {
		    return false;
	    }
	    if (!$cred) {
		    $cred = self::getAuthInfo();
	    }

	    self::controlLimits();

	    // Command to the REST server
        $query_url = $app_info['portal'] . '/rest/' . $method;
        $query_data = http_build_query(array_merge($params, ['auth' => $cred["access_token"]]));
//	    \SProdIntegration::Log('(rest execute) query_data '.$query_data);
        $curl = curl_init();
        curl_setopt_array($curl, [
	        CURLOPT_POST => 1,
	        CURLOPT_HEADER => 0,
	        CURLOPT_RETURNTRANSFER => 1,
	        CURLOPT_SSL_VERIFYPEER => 1,
	        CURLOPT_URL => $query_url,
	        CURLOPT_POSTFIELDS => $query_data,
        ]);
        $result = curl_exec($curl);
        curl_close($curl);
        $resp = json_decode($result, true);

	    // Error to the log
	    if ($resp['error'] || $resp['error_description']) {
		    \SProdIntegration::Log('(rest execute) query "'.$method.'" error: '.$resp['error_description'].' ['.$resp['error'].']');
		    // If token expired then refresh it
		    if (in_array($resp['error'], array('expired_token', 'invalid_token'))) {
			    if ($auth_refresh) {
			    	// Try to get new access token
				    $i = 0;
			    	do {
			    		if ($i > 0) {
			    			sleep(1);
					    }
					    try {
						    $cred = self::refreshToken($cred['refresh_token']);
					    } catch (\Exception $e) {
						    \SProdIntegration::Log('(rest execute) query "' . $method . '" refresh token error: ' . $e->getMessage() . ' [' . $e->getCode() . ']');
					    }
					    $i++;
				    } while (!$cred["access_token"] && $i <= 3);
			    	if (is_array($cred)) {
					    foreach ($cred as $k => $value) {
						    $cred_log[$k] = mb_strimwidth($value, 0, 8, '***');
					    }
				    }
				    \SProdIntegration::Log('(rest execute) query "' . $method . '" repeat result: ' . print_r($cred_log, true));
				    if ($cred["access_token"]) {
					    // Save new auth credentials
					    self::saveAuthInfo($cred);
					    // Execute again
					    $resp = self::execute($method, $params, $cred, false, false);
				    }
			    }
		    }
		    // Other errors
		    else {
				if ($err_repeate) {
					$i = 0;
					while (($resp['error'] || $resp['error_description']) && $i < 2) {
						sleep(1);
						// Execute again
						try {
							$resp = self::execute($method, $params, $cred, $auth_refresh, false, false);
						}
						catch (\Exception $e) {
							\SProdIntegration::Log('(rest execute) query "'.$method.'" repeat error: ' . $e->getMessage().' ['.$e->getCode().']');
						}
						$i++;
					}
				}
				// Return exception
			    if ($resp['error'] || $resp['error_description']) {
				    \SProdIntegration::Log('(rest execute) query "'.$method.'" exception');
				    throw new Exception($resp['error_description'], $resp['error']);
			    }
		    }
	    }

        // Get results
        if ($only_res) {
	        $result = $resp['result'];
        }
        else {
	        $result = $resp;
        }

        if (self::isBulkRun()) {
	        global $DBHost, $DBLogin, $DBPassword;
	        $link = mysqli_connect($DBHost, $DBLogin, $DBPassword, true);
	        if ($link) {
		        mysqli_close($link);
	        }
        }

        return $result;
    }

	public static function executeGetFlat($method, array $params = [], $cred = false) {
		\SProdIntegration::Log('(rest execute) method ' . $method);
		$app_info = self::getAppInfo();
		if ( ! $app_info) {
			return false;
		}
		if ( ! $cred) {
			$cred = self::getAuthInfo();
		}
		// Command to the REST server
		$query_url = $app_info['portal'] . '/rest/' . $method;
		$query_data = http_build_query(array_merge($params, ['auth' => $cred["access_token"]]));
		return $query_url . '?' . $query_data;
	}


	/**
	 * Batch request
	 */

	public static function batch(array $req_list, $cred = false) {
		$result = [];
		if (!empty($req_list)) {
			$req_limit = 50;
			$req_count  = ceil(count($req_list) / $req_limit);
			for ($i = 0; $i < $req_count; $i ++) {
				$req_list_f = [];
				$j = 0;
				foreach ($req_list as $id => $item) {
					if ($j >= $i * $req_limit && $j < ($i + 1) * $req_limit) {
						$params          = isset($item['params']) ? http_build_query($item['params']) : '';
						$req_list_f[$id] = $item['method'] . '?' . $params;
					}
					$j++;
				}
				if ( ! empty($req_list_f)) {
					$resp   = self::execute('batch', [
						"halt" => false,
						"cmd"  => $req_list_f,
					], $cred);
					$result = array_merge($result, $resp['result']);
				}
			}
		}
		return $result;
	}


	/**
	 * Universal list
	 */

	public static function getList($method, $sub_array='', $params=[], $limit=0) {
		$list = [];
		$resp = self::execute($method, $params, false, true, false);
		$count = $resp['total'];
		if ($count) {
			$req_list = [];
			$req_count = ceil($count / 50);
			for ($i=0; $i<$req_count; $i++) {
				$next = $i * 50;
				$params['start'] = $next;
				$req_list[$i] = [
					'method' => $method,
					'params' => $params,
				];
			}
			$resp = self::batch($req_list);
			foreach ($resp as $step_list) {
				if ($sub_array) {
					$step_list = $step_list[$sub_array];
				}
				if (is_array($step_list)) {
					foreach ($step_list as $item) {
						if ( ! $limit || $i < $limit) {
							$list[] = $item;
							$i ++;
						}
					}
				}
			}
		}
		return $list;
	}

	public static function getBgrRequestSecret() {
		$secret = Settings::get('bgr_request_secret');
		if (!$secret) {
			$secret = md5(time());
			Settings::save('bgr_request_secret', $secret);
		}
		return $secret;
	}

	/**
	 * Send request on background
	 */

	public static function sendBgrRequest($uri, $data) {
		$data['secret_key'] = self::getBgrRequestSecret();
		$app_info = self::getAppInfo();
		$site = $app_info['site'];
		$url_info = parse_url($site);
		$is_https = $url_info['scheme'] == 'https' ? true : false;
		$server = $url_info['host'];
		$query_url = ($is_https ? 'https://' : 'http://') . $server . $uri;
		$query_data = http_build_query($data);
//		\SProdIntegration::Log('(Rest::sendBgrRequest) query ' . $query_url . '?' . $query_data);
		if ($server) {
			$success = false;
			for ($i=1; $i<=3 && !$success; $i++) {
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_POST           => 1,
					CURLOPT_HEADER         => 0,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_SSL_VERIFYPEER => 0,
					CURLOPT_URL            => $query_url,
					CURLOPT_POSTFIELDS     => $query_data,
					CURLOPT_FRESH_CONNECT  => true,
					CURLOPT_TIMEOUT        => 1 * $i,
				));
				curl_exec($curl);
				$info = curl_getinfo($curl);
				$success = (int)$info['http_code'] < 300;
				\SProdIntegration::Log('(Rest::sendBgrRequest) response ' . (!$success ? 'failure' : 'success'));
				if (!$success) {
					\SProdIntegration::Log('(Rest::sendBgrRequest) info ' . print_r($info, true));
				}
				curl_close($curl);
			}
		}
	}

}
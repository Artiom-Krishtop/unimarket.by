<?php
class Onliner{
    private $api_client;
    private $api_secret;
    private $api_url;
    private $token_api_url;
    private $auth_token;

    public function __construct($api_client, $api_secret, $api_url, $token_api_url)
    {
        $this->api_client = $api_client;
        $this->api_secret = $api_secret;
        $this->api_url = $api_url;
        $this->token_api_url = $token_api_url;

        $this->auth_token = $this->auth();
    }

    private function auth(){
        $process = curl_init($this->token_api_url);
        curl_setopt($process, CURLOPT_HTTPHEADER, array('Accept: application/json; charset=utf-8'));
        curl_setopt($process, CURLOPT_USERPWD, $this->api_client.":".$this->api_secret);
        curl_setopt($process, CURLOPT_POST, 1);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($process, CURLOPT_POSTFIELDS, array('grant_type' => 'client_credentials'));
        $result = curl_exec($process);
        curl_close($process);
        $result = json_decode($result, true);
        $onlinerAccessToken = $result['access_token'];

        return $onlinerAccessToken;
    }

    public function getOrderByID($id,$filter = array()){
        $params = '';
        if (count($filter)>0){
            foreach ($filter as $key=>$val){
                $params .= $key.'='.$val.'&';
            }
            $params = substr($params,0,-1);
        }
        $process = curl_init($this->api_url."/orders/".$id."?".$params);
        curl_setopt($process, CURLOPT_HTTPHEADER, array(
            'Accept: application/json; charset=utf-8',
            'Authorization: Bearer '.$this->auth_token
        ));
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);

        $result = json_decode(curl_exec($process),true);

        curl_close($process);


        return $result;
    }

    public function getOrderByIDWithDetail($id){
        $process = curl_init($this->api_url."/orders/".$id."?include=shop,positions,status_change_log");
        curl_setopt($process, CURLOPT_HTTPHEADER, array(
            'Accept: application/json; charset=utf-8',
            'Authorization: Bearer '.$this->auth_token
        ));
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);

        $result = json_decode(curl_exec($process), true);
        curl_close($process);

        return $result;
    }

    public function getOrders($filter = array()){
        $params = '';
        if (count($filter)>0){
            foreach ($filter as $key=>$val){
                $params .= $key.'='.$val.'&';
            }
            $params = substr($params,0,-1);
        }
        $process = curl_init($this->api_url."/orders?".$params);

        curl_setopt($process, CURLOPT_HTTPHEADER, array(
            'Accept: application/json; charset=utf-8',
            'Authorization: Bearer '.$this->auth_token
        ));
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
        $result = json_decode(curl_exec($process), true);
        curl_close($process);

        return $result;
    }

	public function setOrderInPickupPoint($id, $comment){
		$process = curl_init($this->api_url."/orders/".$id . "/pickup-point-delivered");
		curl_setopt($process, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($process, CURLOPT_HTTPHEADER, array(
			'Accept: application/json; charset=utf-8',
			'Authorization: Bearer '.$this->auth_token,
			'Content-Type: application/json; charset=utf-8'
		));
		curl_setopt($process, CURLOPT_POST, true);
		curl_setopt($process, CURLOPT_POSTFIELDS, json_encode(array('comment'=>$comment)));
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);

		$result = json_decode(curl_exec($process), true);
		curl_close($process);

		return $result;
	}


    public function changeStatusOrder($id, $status){
        $process = curl_init($this->api_url."/orders/".$id);
        curl_setopt($process, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($process, CURLOPT_HTTPHEADER, array(
            'Accept: application/json; charset=utf-8',
            'Authorization: Bearer '.$this->auth_token,
            'Content-Type: application/json; charset=utf-8'
        ));
        curl_setopt($process, CURLOPT_POST, true);
        curl_setopt($process, CURLOPT_POSTFIELDS, json_encode(array('status'=>$status)));
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);

        $result = json_decode(curl_exec($process), true);
        curl_close($process);

        return $result;
    }

    public function changeStatusOrderUnsuccessful($id, $status){
        $process = curl_init($this->api_url."/orders/".$id);
        curl_setopt($process, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($process, CURLOPT_HTTPHEADER, array(
            'Accept: application/json; charset=utf-8',
            'Authorization: Bearer '.$this->auth_token,
            'Content-Type: application/json; charset=utf-8'
        ));
        curl_setopt($process, CURLOPT_POST, true);
        curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($status));
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);

        $result = json_decode(curl_exec($process), true);
        curl_close($process);

        return $result;
    }

}
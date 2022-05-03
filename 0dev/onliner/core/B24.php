<?php
class B24{
    private $urlWebHook;

    public function __construct($urlWebHook){
        $this->urlWebHook = $urlWebHook;
    }

    private function bitrixSender($method,$data){
        sleep(1);
        $url = $this->urlWebHook;
        $queryData = http_build_query($data);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url.$method,
            CURLOPT_POSTFIELDS => $queryData,
        ));
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result, 1);
        if(isset($result['error'])){
            if($result['error'] =='QUERY_LIMIT_EXCEEDED'){
                sleep(3);
                $this->bitrixSender($method,$data);
            }
        }
        return $result;
    }


    public function batch($arrRequest, $halt = 0){
        $arDataRest = array();

        foreach($arrRequest as $key => $data)
        {
            if(!empty($data[ 'method' ])){
                $arDataRest[ 'cmd' ][ $key ] = $data[ 'method' ];
                if(!empty($data[ 'params' ])) {
                    $arDataRest[ 'cmd' ][ $key ] .= '?' . http_build_query($data[ 'params' ]);
                }

            }
        }

        if(!empty($arDataRest)) {
            $arDataRest[ 'halt' ] = $halt;
            $arPost = [
                'method' => 'batch',
                'params' => $arDataRest
            ];
        }

        $result = $this->bitrixSender('/batch/', $arDataRest);

        return $result;
    }

    public function getCatalog(){
        $result = $this->bitrixSender('/crm.catalog.list/', array());

        return $result;
    }

    public function getCategoryByXML($xmlID){
        $data = array('filter' => array('XML_ID' => $xmlID));
        $result = $this->bitrixSender('/crm.productsection.list/', $data);

        return $result;
    }

    public function addCategory($data){
        $result = $this->bitrixSender('/crm.productsection.add/', $data);

        return $result;
    }

    public function updateCategory($id, $fields){
        $date = array(
            'id'=>$id,
            'fields'=>$fields
        );

        $result = $this->bitrixSender('/crm.productsection.update/', $date);

        return $result;
    }

    public function getProductById($ID){
        $data = array('id' => $ID);
        $result = $this->bitrixSender('/crm.product.get/', $data);

        return $result;
    }

    public function getProductByXML($xmlID){
        $data = array('filter' => array('XML_ID' => $xmlID));
        $result = $this->bitrixSender('/crm.product.list/', $data);

        return $result;
    }
    public function getProductByFilter($filter, $select = array(), $start = 0){
        $data = array(
            'filter' => $filter,
            'select' => $select,
        );
        $result = $this->bitrixSender('/crm.product.list//?start=' . $start, $data);

        return $result;
    }


    public function addProduct($data){
        $result = $this->bitrixSender('/crm.product.add/', $data);

        return $result;
    }

    public function updateProduct($id, $fields){
        $date = array(
            'id'=>$id,
            'fields'=>$fields
        );

        $result = $this->bitrixSender('/crm.product.update/', $date);

        return $result;
    }

    public function deleteProduct($id){
        $result = $this->bitrixSender('/crm.product.delete/', array('id'=>$id));

        return $result;
    }

    public function getUserByID($id){
        $filter = array(
            'id' => $id,
        );

        $result = $this->bitrixSender('/crm.contact.get/', $filter);

        return $result;
    }

    public function getUserByPhone($phone){
        $filter = array(
            'filter' => array('PHONE'=>$phone),
            'select' => array('*')
        );

        $result = $this->bitrixSender('/crm.contact.list/', $filter);

        return $result;
    }

    public function addNewUser($user){

        $filter = array(
            'fields' => $user,
            'params' => array('REGISTER_SONET_EVENT'=> 'Y')
        );

        $result = $this->bitrixSender('/crm.contact.add/', $filter);

        return $result;
    }

    public function getCompanyByID($id){
        $filter = array(
            'id' => $id,
        );

        $result = $this->bitrixSender('/crm.company.get/', $filter);

        return $result;
    }

    public function addNewDeal($deal){

        $filter = array(
            'fields' => $deal,
            'params' => array("REGISTER_SONET_EVENT" => "Y")
        );

        $result = $this->bitrixSender('/crm.deal.add/', $filter);

        return $result;
    }

    public function getDealByID($id){

        $filter = array(
            'id' => $id,
        );

        $result = $this->bitrixSender('/crm.deal.get/', $filter);

        return $result;
    }

    public function getDealByFilter($filter, $select = array(), $start = 0){
        $data = array(
            'filter' => $filter,
            'select' => $select,
        );
        $result = $this->bitrixSender('/crm.deal.list/?start=' . $start, $data);

        return $result;
    }

    public function updateDeal($id, $fields){
        $date = array(
            'id'=>$id,
            'fields'=>$fields
        );

        $result = $this->bitrixSender('/crm.deal.update/', $date);

        return $result;
    }

    public function getDealProducts($id){
        $filter = array(
            'id' => $id,
        );

        $result = $this->bitrixSender('/crm.deal.productrows.get/', $filter);

        return $result;
    }

    public function addProductDeal($productsToCrm){

        $result = $this->bitrixSender('/crm.deal.productrows.set/', $productsToCrm);

        return $result;
    }

    public function getDealFieldsList(){
        $data = array();
        $result = $this->bitrixSender('/crm.deal.fields/', $data);

        return $result;
    }

    public function addMessageDeal($idDeal, $title, $message ){
        $fields = array(
            'fields'=>array(
                'POST_TITLE' => $title,
                'MESSAGE' => $message,
                'ENTITYTYPEID' => 2,
                'ENTITYID' => $idDeal,
            )
        );

        $result = $this->bitrixSender('/crm.livefeedmessage.add/', $fields);

        return $result;
    }

    public function getContactFieldsList(){
        $data = array();
        $result = $this->bitrixSender('/crm.contact.fields/', $data);

        return $result;
    }

    public function getStatusList($filter){
        $data = array(
            'filter' => $filter);
        $result = $this->bitrixSender('/crm.status.list/', $data);

        return $result;
    }





}
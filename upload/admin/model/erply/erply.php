<?php

/**
 * Class ModelErplyErply
 * @property ModelSettingSetting model_setting_setting
 * @property Loader load
 */
class ModelErplyErply extends Model
{


    public function saveProduct($data){
        $api = $this->getAPI();
        $api->invoke('saveProduct' , $data);
    }

    public function getAllProducts($changedSince = 0 , $getStockInfo = 0)
    {

        $products = array();
        $api = $this->getAPI();
        $this->debug("Loading all products from Eply ");
        $page = 1;
        $recordsOnPage =  $getStockInfo ? 100 : 1000;
        do {
            $this->debug("Loading all products from Eply page nr $page");
            $response = $api->invoke('getProducts', array(
                'displayedInWebshop' => 1,
                'active' => 1,
                'recordsOnPage' => $recordsOnPage,
                'pageNo' => $page++,
                'getStockInfo' => $getStockInfo,
                'changedSince' => $changedSince
            ));
            $this->debug("Loaded page $page " . print_r($response->status , true));
            $products = array_merge($products, $response->records);
        } while ($response->status->recordsTotal > count($products));

        $this->debug("Loaded " . count($products). " products");
        return $products;
    }

    public function getProduct($id, $getStockInfo = 1, $getFIFOCost = 1, $getRelatedProducts = 1)
    {
        $api = $this->getAPI();
        $response = $api->invoke('getProducts', array(
            'productID' => $id,
            'getStockInfo' => $getStockInfo,
            'getFIFOCost' => $getFIFOCost,
            'getRelatedProduct' => $getRelatedProducts,
        ));

        return $response->records[0];
    }

    public function getStockInfo($warehouseID = 1, $productID = null)
    {
        $api = $this->getAPI();
        $response = $api->invoke('getProductStock', array(
            'productID' => $productID,
            'warehouseID' => $warehouseID,
            'getAmountReserved' => 1,
        ));
        return $productID !== null ? $response->records[0] : $response->records;
    }


    public function getPriceList($priceListID)
    {
        $api = $this->getAPI();
        $response = $api->invoke('getPriceLists', array(
            'pricelistID' => $priceListID,
            'getPricesWithVAT' => 1
        ));
        return $response->records[0];
    }

    private function getAPI()
    {
        $this->debug("Loading Erply library");
        $this->load->library('eapi');
        $this->debug("Loading setting model");
        $this->load->model('setting/setting');
        $this->debug("Getting erply settings");
        $setting = $this->model_setting_setting->getSetting('erply');

        $url = $setting['erply_url'];
        $client = $setting['erply_client'];
        $username = $setting['erply_username'];
        $password = $setting['erply_password'];
        $this->debug("Init new API $url, $client, $username, $password");
        return new EAPI($url, $client, $username, $password);
    }

    private function debug($s){
        //echo "<p><strong>ModelErplyErply:</strong> $s</p>\n";
    }

    public function deletePictures($productID){
        $api = $this->getAPI();
        $api->invoke('deleteProductPicture', array(
            'productID' => $productID
        ));
    }

    public function addPicture($productID , $picture){
        $imageData = file_get_contents($picture);
        $api = $this->getAPI();
        $api->invoke('saveProductPicture', array(
            'productID' => $productID,
            'picture' => base64_encode($imageData),
        ));
    }
}
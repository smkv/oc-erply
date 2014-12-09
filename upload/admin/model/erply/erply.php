<?php

class ModelErplyErply extends Model
{


    public function getAllProducts()
    {
        $products = array();
        $api = $this->getAPI();

        $page = 1;
        do {
            $response = $api->invoke('getProducts', array(
                'recordsOnPage' => 1000,
                'pageNo' => $page++
            ));
            $products = array_merge($products, $response->records);
        } while ($response->status->recordsTotal > count($products));

        return $products;
    }

    public function getProduct($id, $getStockInfo = 1, $getFIFOCost = 1, $getPriceListPrices = 1, $getRelatedProducts = 1)
    {
        $api = $this->getAPI();
        $response = $api->invoke('getProducts', array(
            'productID' => $id,
            'getStockInfo' => $getStockInfo,
            'getFIFOCost' => $getFIFOCost,
            'getPriceListPrice' => $getPriceListPrices,
            'getRelatedProduct' => $getRelatedProducts,
        ));

        return $response->records[0];
    }


    private function getAPI()
    {
        $this->load->library('eapi');
        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('erply');

        $url = $setting['url'];
        $client = $setting['client'];
        $username = $setting['username'];
        $password = $setting['password'];
        return new EAPI($url, $client, $username, $password);
    }
}
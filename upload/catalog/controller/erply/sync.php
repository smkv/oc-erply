<?php
/**
 * @property ModelErplyErply model_erply_erply
 * @property ModelCatalogProduct model_catalog_product
 * @property ModelSettingSetting model_setting_setting
 * @property Request request
 * @property Response response
 * @property Url url
 */
class ControllerErplySync extends Controller{
    public function index() {
        $this->load->model('erply/erply');
        $this->load->model('catalog/product');
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('erply_sync');
        $changedSince = isset($settings['erply_sync_last_update']) ? intval($settings['erply_sync_last_update']) : 0 ;

        $allProducts = $this->model_erply_erply->getAllProducts($changedSince , 1);
        $ids = $this->getAllProductIds();
        $success = 0;
        foreach ($allProducts as $product) {
            if(in_array($product->productID , $ids)){
                $this->updateProduct($product);
                $success++;
            }
        }

        $settings['erply_sync_last_update'] = time();
        $this->editSetting('erply_sync' , $settings);
        $this->response->setOutput("Erply product count=" . count($allProducts). " Shop product count=" . count($ids)." success updated count=" .$success);

    }

    public function editSetting($code, $data, $store_id = 0) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");

        foreach ($data as $key => $value) {
            if (substr($key, 0, strlen($code)) == $code) {
                if (!is_array($value)) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
                } else {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1'");
                }
            }
        }
    }

    private function getAllProductIds(){
        $query = $this->db->query('SELECT sku FROM ' . DB_PREFIX . 'product p  WHERE sku IS NOT NULL');
        $ids = array();
        foreach($query->rows as $row){
            $ids[] = $row['sku'];
        }
        return $ids;
    }

    private function updateProduct($product)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "product SET ean = '" . $this->db->escape($product->code2) . "', quantity = '" . (int)$this->getFreeQuantity($product->warehouses) . "', price = '" . (float)$product->price . "', date_modified = NOW() WHERE sku = '" . (int)$product->productID . "'");

    }

    private function getFreeQuantity($warehouses)
    {
        $quantity = 0;
        if (is_array($warehouses)) {
            foreach ($warehouses as $warehouse) {
                $quantity += intval($warehouse->free);
            }
        }
        return $quantity;
    }
}
<?php

/**
 * Class ModelErplyProduct
 * @property DB db
 */
class ModelErplyProduct extends Model{
    public function getProductBySKU($sku){
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)  WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.sku = '" . $this->db->escape($sku) . "' ORDER BY pd.name ASC");

        return $query->row;
    }
}